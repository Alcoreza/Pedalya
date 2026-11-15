<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.paymongo.webhook_secret');
        $signature = $request->header('Paymongo-Signature');
        $rawBody = $request->getContent();

        if (! $secret || ! $signature || ! $this->hasValidSignature($signature, $rawBody, $secret)) {
            return response()->json(['received' => false], 401);
        }

        $payload = json_decode($rawBody, true);
        $eventAttributes = data_get($payload, 'data.attributes', []);

        if (($eventAttributes['type'] ?? null) !== 'checkout_session.payment.paid') {
            return response()->json(['received' => true]);
        }

        $session = $eventAttributes['data'] ?? [];
        $sessionAttributes = $session['attributes'] ?? [];
        $reference = $sessionAttributes['reference_number'] ?? null;
        $eventId = data_get($payload, 'data.id');
        $paidPayment = collect($sessionAttributes['payments'] ?? [])
            ->first(fn (array $item): bool => data_get($item, 'attributes.status') === 'paid');
        $paidAttributes = $paidPayment['attributes'] ?? [];
        $amount = (int) ($paidAttributes['amount'] ?? data_get($sessionAttributes, 'payment_intent.attributes.amount', 0));
        $currency = $paidAttributes['currency'] ?? data_get($sessionAttributes, 'payment_intent.attributes.currency');

        if (! $reference || ! $eventId || empty($session['id'])) {
            return response()->json(['received' => true]);
        }

        DB::transaction(function () use ($reference, $eventId, $session, $paidPayment, $amount, $currency): void {
            $payment = Payment::query()
                ->with('booking')
                ->where('provider_checkout_session_id', $session['id'])
                ->lockForUpdate()
                ->first();

            if (! $payment || ! $payment->booking || $payment->booking->reference !== $reference) {
                Log::warning('PayMongo paid event did not match a stored booking.', ['event_id' => $eventId]);
                return;
            }

            if ($payment->status === 'paid' || $payment->webhook_event_id === $eventId) {
                return;
            }

            if ($amount !== (int) $payment->amount_minor || $currency !== $payment->currency || ! $paidPayment) {
                Log::warning('PayMongo paid event amount did not match the stored payment.', [
                    'event_id' => $eventId,
                    'booking_reference' => $reference,
                ]);
                return;
            }

            $payment->update([
                'provider_payment_intent_id' => data_get($session['attributes'] ?? [], 'payment_intent.id'),
                'provider_payment_id' => $paidPayment['id'] ?? null,
                'webhook_event_id' => $eventId,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            $payment->booking->update(['payment_status' => 'paid']);
        });

        return response()->json(['received' => true]);
    }

    private function hasValidSignature(string $header, string $rawBody, string $secret): bool
    {
        $parts = collect(explode(',', $header))
            ->mapWithKeys(function (string $part): array {
                [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

                return [$key => $value];
            });
        $timestamp = (string) $parts->get('t', '');
        $payload = json_decode($rawBody, true);
        $livemode = (bool) data_get($payload, 'data.attributes.livemode', false);
        $providedSignature = (string) $parts->get($livemode ? 'li' : 'te', '');

        if ($timestamp === '' || $providedSignature === '') {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        return hash_equals($expectedSignature, $providedSignature);
    }
}
