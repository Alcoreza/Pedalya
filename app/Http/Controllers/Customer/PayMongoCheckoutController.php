<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoCheckoutController extends Controller
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

        if ($payment->status === 'paid') {
            return redirect()->route('customer.payment.success', ['payment' => $booking->reference]);
        }

        if ($payment->checkout_url && $payment->provider_checkout_session_id) {
            return redirect()->away($payment->checkout_url);
        }

        $secretKey = config('services.paymongo.secret_key');
        if (! $secretKey) {
            return back()->withErrors(['payment' => 'PayMongo is not configured yet. Add the server-side test secret key to the app environment.']);
        }

        $amount = (int) $payment->amount_minor;
        $requestBody = [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'name' => $booking->service->name,
                        'amount' => $amount,
                        'currency' => 'PHP',
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => config('services.paymongo.payment_method_types'),
                    'success_url' => route('customer.payment.success', ['payment' => $booking->reference], true),
                    'cancel_url' => route('customer.payment.failed', ['payment' => $booking->reference], true),
                    'reference_number' => $booking->reference,
                    'description' => 'Pedalya repair booking '.$booking->reference,
                ],
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withBasicAuth($secretKey, '')
                ->withHeaders(['Idempotency-Key' => 'pedalya-booking-'.$booking->id.'-checkout'])
                ->timeout(30)
                ->post(config('services.paymongo.base_url').'/checkout_sessions', $requestBody);
        } catch (\Throwable $exception) {
            Log::warning('PayMongo checkout request failed.', ['booking_reference' => $booking->reference]);

            return back()->withErrors(['payment' => 'PayMongo could not be reached. Your booking is saved; please try again.']);
        }

        $sessionId = $response->json('data.id');
        $checkoutUrl = $response->json('data.attributes.checkout_url');

        if (! $response->successful() || ! $sessionId || ! $checkoutUrl) {
            Log::warning('PayMongo rejected the checkout request.', [
                'booking_reference' => $booking->reference,
                'http_status' => $response->status(),
            ]);

            return back()->withErrors(['payment' => 'PayMongo could not start checkout. Your booking is saved; please try again.']);
        }

        $payment->update([
            'provider_checkout_session_id' => $sessionId,
            'checkout_url' => $checkoutUrl,
            'status' => 'pending',
        ]);

        return redirect()->away($checkoutUrl);
    }
}
