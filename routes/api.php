<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\SlotController;
use Illuminate\Support\Facades\Route;

Route::get('/slots', [SlotController::class, 'index'])->name('slots.index');

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
});
