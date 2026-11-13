<?php

use App\Http\Controllers\Manager\ActivityController;
use App\Http\Controllers\Manager\BookingsController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\RepairsController;
use App\Http\Controllers\Manager\ScheduleController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('/login', 'auth.login')->name('login');

Route::view('/register', 'auth.register')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::view('/book', 'customer.book')->name('customer.book');
Route::view('/book/schedule', 'customer.schedule')->name('customer.schedule');
Route::view('/book/review', 'customer.review')->name('customer.review');
Route::view('/book/confirmation', 'customer.confirmation')->name('customer.confirmation');
Route::view('/customer/bookings', 'customer.bookings')->name('customer.bookings');
Route::view('/customer', 'customer.dashboard')->name('customer.dashboard');
Route::view('/customer/bookings/{booking}', 'customer.booking-detail')->name('customer.booking.detail');
Route::redirect('/customer/repairs', '/customer/bookings')->name('customer.repairs');
Route::get('/customer/repairs/{booking}', fn (string $booking) => redirect()->route('customer.booking.detail', $booking))->name('customer.repair');
Route::view('/customer/bookings/{booking}/payment', 'customer.payment')->name('customer.payment');
Route::view('/payments/{payment}/success', 'customer.payment-result', ['result' => 'success'])->name('customer.payment.success');
Route::view('/payments/{payment}/failed', 'customer.payment-result', ['result' => 'failed'])->name('customer.payment.failed');

Route::prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/bookings', [BookingsController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{booking}/decision', [BookingsController::class, 'decide'])->name('bookings.decision');
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::post('/schedule/{booking}/calendar-sync', [ScheduleController::class, 'retryCalendarSync'])->name('schedule.calendar-sync.retry');
    Route::get('/repairs', [RepairsController::class, 'index'])->name('repairs.index');
    Route::post('/repairs/{booking}/complete', [RepairsController::class, 'markCompleted'])->name('repairs.complete');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
});
