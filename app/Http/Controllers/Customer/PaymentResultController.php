<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentResultController extends Controller
{
    public function success(Request $request, string $payment): View
    {
        $booking = Booking::query()
            ->with('payment')
            ->where('user_id', $request->user()->id)
            ->where('reference', $payment)
            ->firstOrFail();

        $this->syncSuccessfulMayaPayment($booking);

        return $this->renderResult($booking->refresh()->load('payment'), 'success');
    }

    public function failed(Request $request, string $payment): View
    {
        $booking = Booking::query()
            ->with('payment')
            ->where('user_id', $request->user()->id)
            ->where('reference', $payment)
            ->firstOrFail();

        return $this->renderResult($booking, 'failed');
    }

    private function renderResult(Booking $booking, string $returnState): View
    {
        $status = $booking->payment_status;
        $paid = $status === 'paid';
        $message = match (true) {
            $paid => 'Maya confirmed this payment. Your booking payment is complete.',
            $returnState === 'failed' => 'Checkout was not completed. Your booking is saved and you can try payment again.',
            default => 'Maya is confirming the result. Your booking will show paid after Maya verifies the transaction.',
        };

        return view('customer.payment-result', [
            'result' => $paid ? 'success' : $returnState,
            'booking' => $booking,
            'paymentStatus' => ucfirst($status),
            'resultTitle' => $paid ? 'Your payment is complete.' : ($returnState === 'failed' ? 'Payment was not completed.' : 'Payment confirmation is pending.'),
            'resultMessage' => $message,
        ]);
    }

    /**
     * Maya's browser redirect is not proof of payment. On the success return,
     * verify the saved Maya payment ID server-to-server so a delayed webhook
     * does not leave a successfully paid sandbox booking showing as pending.
     */
    private function syncSuccessfulMayaPayment(Booking $booking): void
    {
        $payment = $booking->payment;
        $secretKey = config('services.maya.secret_key');

        if (! $payment || $payment->provider !== 'maya' || ! $payment->provider_payment_id || ! $secretKey) {
            return;
        }

        try {
            $response = Http::acceptJson()
                ->withBasicAuth($secretKey, '')
                ->timeout(4)
                ->get(config('services.maya.base_url').'/payments/v1/payments/'.rawurlencode($payment->provider_payment_id));
        } catch (\Throwable $exception) {
            Log::warning('Could not verify Maya payment after customer return.', ['booking_reference' => $booking->reference]);
            return;
        }

        if (! $response->successful()) {
            return;
        }

        $mayaPayment = $response->json();
        $mayaStatus = strtoupper((string) ($mayaPayment['paymentStatus'] ?? $mayaPayment['status'] ?? ''));
        $requestReference = $mayaPayment['requestReferenceNumber'] ?? null;
        $mayaAmount = data_get($mayaPayment, 'totalAmount.value') ?? data_get($mayaPayment, 'amount');
        $mayaCurrency = data_get($mayaPayment, 'totalAmount.currency') ?? data_get($mayaPayment, 'currency');
        $expectedAmount = round((int) $payment->amount_minor / 100, 2);

        if (($mayaPayment['id'] ?? null) !== $payment->provider_payment_id
            || $mayaStatus !== 'PAYMENT_SUCCESS'
            || $requestReference !== $payment->provider_request_reference_number
            || $mayaAmount === null
            || round((float) $mayaAmount, 2) !== $expectedAmount
            || strtoupper((string) $mayaCurrency) !== strtoupper($payment->currency)) {
            return;
        }

        DB::transaction(function () use ($payment, $booking, $requestReference): void {
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->first();

            if (! $lockedPayment
                || $lockedPayment->provider !== 'maya'
                || $lockedPayment->provider_payment_id !== $payment->provider_payment_id
                || $lockedPayment->provider_request_reference_number !== $requestReference
                || $lockedPayment->status === 'paid') {
                return;
            }

            $lockedPayment->update([
                'webhook_event_id' => hash('sha256', $payment->provider_payment_id.'|'.$requestReference.'|PAYMENT_SUCCESS'),
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            $booking->update(['payment_status' => 'paid']);
        });
    }
}
