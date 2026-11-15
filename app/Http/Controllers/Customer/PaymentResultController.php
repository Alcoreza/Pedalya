<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentResultController extends Controller
{
    public function success(Request $request, string $payment): View
    {
        return $this->showResult($request, $payment, 'success');
    }

    public function failed(Request $request, string $payment): View
    {
        return $this->showResult($request, $payment, 'failed');
    }

    private function showResult(Request $request, string $reference, string $returnState): View
    {
        $booking = Booking::query()
            ->with('payment')
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)
            ->firstOrFail();

        $status = $booking->payment_status;
        $paid = $status === 'paid';
        $message = match (true) {
            $paid => 'PayMongo confirmed this payment. Your booking payment is complete.',
            $returnState === 'failed' => 'Checkout was not completed. Your booking is saved and you can try payment again.',
            default => 'PayMongo is confirming the result. This page will show paid after the verified webhook is received.',
        };

        return view('customer.payment-result', [
            'result' => $paid ? 'success' : $returnState,
            'booking' => $booking,
            'paymentStatus' => ucfirst($status),
            'resultTitle' => $paid ? 'Your payment is complete.' : ($returnState === 'failed' ? 'Payment was not completed.' : 'Payment confirmation is pending.'),
            'resultMessage' => $message,
        ]);
    }
}
