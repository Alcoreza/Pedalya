<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MayaWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $notice = $request->json()->all();
        $paymentId = $notice['id'] ?? null;

        if (! $paymentId) {
            return response()->json(['received' => true]);
        }

        // Treat the webhook as a notification only. Confirm the transaction
        // directly with Maya before changing any booking or payment records.
        $secretKey = config('services.maya.secret_key');
        if (! $secretKey) {
            Log::error('Maya webhook cannot verify payment because the secret key is missing.');
            return response()->json(['received' => false], 503);
        }

        try {
            $verification = Http::acceptJson()
                ->withBasicAuth($secretKey, '')
                ->timeout(4)
                ->get(config('services.maya.base_url').'/payments/v1/payments/'.rawurlencode($paymentId));
        } catch (\Throwable $exception) {
            return response()->json(['received' => false], 503);
        }

        if (! $verification->successful()) {
            return response()->json(['received' => false], 503);
        }

        $payload = $verification->json();
        $requestReference = $payload['requestReferenceNumber'] ?? null;
        $mayaStatus = strtoupper((string) ($payload['paymentStatus'] ?? $payload['status'] ?? ''));

        if (! $requestReference || ! $mayaStatus || ($notice['id'] ?? null) !== ($payload['id'] ?? null)) {
            return response()->json(['received' => true]);
        }

        $eventId = hash('sha256', $paymentId.'|'.$requestReference.'|'.$mayaStatus);
        $mappedStatus = match ($mayaStatus) {
            'PAYMENT_SUCCESS' => 'paid',
            'PAYMENT_FAILED', 'PAYMENT_EXPIRED', 'PAYMENT_CANCELLED' => 'failed',
            default => null,
        };

        if (! $mappedStatus) {
            return response()->json(['received' => true]);
        }

        $amount = data_get($payload, 'totalAmount.value') ?? data_get($payload, 'amount');
        $currency = data_get($payload, 'totalAmount.currency') ?? data_get($payload, 'currency');

        DB::transaction(function () use ($paymentId, $requestReference, $eventId, $mappedStatus, $amount, $currency): void {
            $payment = Payment::query()
                ->with('booking')
                ->where('provider', 'maya')
                ->where('provider_payment_id', $paymentId)
                ->lockForUpdate()
                ->first();

            if (! $payment || ! $payment->booking
                || $payment->provider_request_reference_number !== $requestReference) {
                Log::warning('Maya webhook did not match a saved payment.', ['maya_payment_id' => $paymentId]);
                return;
            }

            if ($payment->status === 'paid' || $payment->webhook_event_id === $eventId) {
                return;
            }

            $expectedAmount = round((int) $payment->amount_minor / 100, 2);
            if ($amount === null || round((float) $amount, 2) !== $expectedAmount
                || strtoupper((string) $currency) !== strtoupper($payment->currency)) {
                Log::warning('Maya webhook amount or currency did not match the saved payment.', [
                    'maya_payment_id' => $paymentId,
                    'booking_reference' => $payment->booking->reference,
                ]);
                return;
            }

            $payment->update([
                'webhook_event_id' => $eventId,
                'status' => $mappedStatus,
                'paid_at' => $mappedStatus === 'paid' ? now() : null,
            ]);
            $payment->booking->update(['payment_status' => $mappedStatus]);
        });

        return response()->json(['received' => true]);
    }
}
