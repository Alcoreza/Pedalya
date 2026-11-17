<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\BookingFlowController;
use App\Http\Controllers\Customer\BookingPaymentController;
use App\Http\Controllers\Customer\BookingsController as CustomerBookingsController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\GoogleCalendarConnectionController;
use App\Http\Controllers\Customer\MayaCheckoutController;
use App\Http\Controllers\Customer\PaymentResultController;
use App\Http\Controllers\Manager\ActivityController;
use App\Http\Controllers\Manager\BookingsController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\RepairsController;
use App\Http\Controllers\Manager\ScheduleController;
use App\Http\Controllers\Webhooks\MayaWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::view('/register', 'auth.register')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/book', [BookingFlowController::class, 'services'])->name('customer.book');
    Route::post('/book/service', [BookingFlowController::class, 'chooseService'])->name('customer.book.service');
    Route::get('/book/schedule', [BookingFlowController::class, 'schedule'])->name('customer.schedule');
    Route::post('/book/schedule', [BookingFlowController::class, 'saveSchedule'])->name('customer.schedule.store');
    Route::get('/book/review', [BookingFlowController::class, 'review'])->name('customer.review');
    Route::post('/book/review', [BookingFlowController::class, 'store'])->name('customer.review.store');
    Route::get('/book/confirmation/{reference}', [BookingFlowController::class, 'confirmation'])->name('customer.confirmation');
    Route::get('/customer/bookings', [CustomerBookingsController::class, 'index'])->name('customer.bookings');
    Route::get('/customer/bookings/{reference}/payment', [BookingPaymentController::class, 'show'])->name('customer.payment');
    Route::get('/customer/dashboard', CustomerDashboardController::class)->name('customer.dashboard');
    Route::get('/customer/calendar/connect', [GoogleCalendarConnectionController::class, 'connect'])->name('customer.calendar.connect');
    Route::get('/customer/calendar/callback', [GoogleCalendarConnectionController::class, 'callback'])->name('customer.calendar.callback');
    Route::delete('/customer/calendar/disconnect', [GoogleCalendarConnectionController::class, 'disconnect'])->name('customer.calendar.disconnect');
    Route::post('/book/confirmation/{reference}/checkout', [MayaCheckoutController::class, 'start'])->name('customer.checkout');
    Route::get('/payments/{payment}/success', [PaymentResultController::class, 'success'])->name('customer.payment.success');
    Route::get('/payments/{payment}/failed', [PaymentResultController::class, 'failed'])->name('customer.payment.failed');
});

Route::post('/webhooks/maya', MayaWebhookController::class)->name('webhooks.maya');

Route::redirect('/customer', '/customer/dashboard');
Route::get('/customer/profile', [ProfileController::class, 'edit'])->middleware('auth')->name('customer.profile');
Route::put('/customer/profile', [ProfileController::class, 'update'])->middleware('auth')->name('customer.profile.update');
Route::view('/customer/bookings/{booking}', 'customer.booking-detail')->name('customer.booking.detail');
Route::redirect('/customer/repairs', '/customer/bookings')->name('customer.repairs');
Route::get('/customer/repairs/{booking}', fn (string $booking) => redirect()->route('customer.booking.detail', $booking))->name('customer.repair');

Route::prefix('manager')->name('manager.')->middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/bookings', [BookingsController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/decision', [BookingsController::class, 'decide'])->name('bookings.decision');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/{booking}/calendar-sync', [ScheduleController::class, 'retryCalendarSync'])->name('schedule.calendar-sync.retry');
    Route::get('/repairs', [RepairsController::class, 'index'])->name('repairs.index');
    Route::post('/repairs/{booking}/arrive', [RepairsController::class, 'markArrived'])->name('repairs.arrive');
    Route::post('/repairs/{booking}/start', [RepairsController::class, 'start'])->name('repairs.start');
    Route::post('/repairs/{booking}/complete', [RepairsController::class, 'markCompleted'])->name('repairs.complete');
    Route::post('/repairs/{booking}/claim', [RepairsController::class, 'markClaimed'])->name('repairs.claim');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
});
