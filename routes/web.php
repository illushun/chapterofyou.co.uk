<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\Product\ProductController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::post('/product/{id}', [ProductController::class, 'showProduct'])->name('product.view');

Route::post('/waitlist', WaitlistController::class)->name('waitlist.store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
