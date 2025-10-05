<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WaitlistController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/waitlist', WaitlistController::class)->name('waitlist.store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
