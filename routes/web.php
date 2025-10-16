<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CheckoutController;
use App\Http\Controllers\Account\AccountController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');

Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/product/{idOrSlug}', [ProductController::class, 'show'])->name('products.show');

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'view'])->name('cart.view');

    Route::post('/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/update/{productId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');

    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('address.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('address.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('address.destroy');
});
Route::get('/account/addresses/lookup', [AccountController::class, 'lookupAddress'])->name('address.lookup');

Route::prefix('checkout')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('checkout.index');

    Route::post('/payment-intent', [CheckoutController::class, 'getPaymentIntent'])->name('checkout.payment_intent');
    Route::post('/process-payment', [CheckoutController::class, 'processPayment'])->name('checkout.process_payment');
});

Route::get('/order/confirmation', function () {
    return Inertia::render('order/Confirmation');
})->name('order.confirmation');

Route::post('/waitlist', WaitlistController::class)->name('waitlist.store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
