<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MayaCheckoutController extends Controller
{
    public function start(Request $request, string $reference): RedirectResponse
    {
        $booking = Booking::query()
            ->with(['service', 'payment'])
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)
            ->firstOrFail();
        $payment = $booking->payment;

        if (! $payment) {
            abort(404);
        }

        if ($booking->booking_status !== 'confirmed') {
            return redirect()->route('customer.confirmation', ['reference' => $booking->reference])
                ->withErrors(['payment' => 'Payment becomes available after the manager approves this appointment time.']);
        }

        if ($payment->status === 'paid') {
            return redirect()->route('customer.payment.success', ['payment' => $booking->reference]);
        }

        // Maya Checkout sessions expire after one hour. Reuse a recent session,
        // but make a fresh payment reference when the customer retries later.
        if ($payment->status === 'pending' && $payment->checkout_url && $payment->provider_payment_id && $payment->created_at
            && $payment->created_at->isAfter(now()->subHour())) {
            return redirect()->away($payment->checkout_url);
        }

        $publicKey = config('services.maya.public_key');
        if (! $publicKey) {
            return back()->withErrors(['payment' => 'Maya Checkout is not configured. Add the Maya sandbox keys to the Laravel environment.']);
        }

        $requestReference = $booking->reference.'-'.Str::upper(Str::random(8));
        $amount = round((int) $payment->amount_minor / 100, 2);
        $requestBody = [
            'totalAmount' => ['value' => $amount, 'currency' => $payment->currency],
            'requestReferenceNumber' => $requestReference,
            'redirectUrl' => [
                'success' => route('customer.payment.success', ['payment' => $booking->reference], true),
                'failure' => route('customer.payment.failed', ['payment' => $booking->reference], true),
                'cancel' => route('customer.payment.failed', ['payment' => $booking->reference], true),
            ],
            'items' => [[
                'name' => $booking->service->name,
                'quantity' => 1,
                'amount' => ['value' => $amount],
                'totalAmount' => ['value' => $amount],
            ]],
        ];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withBasicAuth($publicKey, '')
                ->timeout(20)
                ->post(config('services.maya.base_url').'/checkout/v1/checkouts', $requestBody);
        } catch (\Throwable $exception) {
            Log::warning('Maya checkout request failed.', ['booking_reference' => $booking->reference]);

            return back()->withErrors(['payment' => 'Maya could not be reached. Your booking is saved; please try again.']);
        }

        $checkoutId = $response->json('checkoutId');
        $checkoutUrl = $response->json('redirectUrl');

        if (! $response->successful() || ! $checkoutId || ! $checkoutUrl) {
            Log::warning('Maya rejected the checkout request.', [
                'booking_reference' => $booking->reference,
                'http_status' => $response->status(),
            ]);

            return back()->withErrors(['payment' => 'Maya could not start checkout. Your booking is saved; please try again.']);
        }

        $payment->update([
            'provider' => 'maya',
            'provider_checkout_session_id' => $checkoutId,
            'provider_payment_intent_id' => null,
            'provider_payment_id' => $checkoutId,
            'provider_request_reference_number' => $requestReference,
            'checkout_url' => $checkoutUrl,
            'status' => 'pending',
            'webhook_event_id' => null,
            'paid_at' => null,
            'created_at' => now(),
        ]);
        $booking->update(['payment_status' => 'pending']);

        return redirect()->away($checkoutUrl);
    }
}
