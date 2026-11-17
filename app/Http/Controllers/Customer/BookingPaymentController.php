<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingPaymentController extends Controller
{
    public function show(Request $request, string $reference): View
    {
        $booking = Booking::query()
            ->with(['service', 'payment'])
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return view('customer.payment', compact('booking'));
    }
}
