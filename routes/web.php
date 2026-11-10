<?php

use App\Http\Controllers\Manager\ActivityController;
use App\Http\Controllers\Manager\BookingsController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\RepairsController;
use App\Http\Controllers\Manager\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('/login', 'auth.login')->name('login');

Route::view('/register', 'auth.register')->name('register');

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
