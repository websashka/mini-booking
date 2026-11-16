<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Booking', ['timezone' => config('app.timezone')])->name('home');
