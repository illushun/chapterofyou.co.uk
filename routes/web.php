<?php

use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CheckoutController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCartController;
use App\Http\Controllers\Admin\Label\CLPLabelController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');

Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'storeContact'])->name('contact.store');

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

    Route::get('/account/orders', [OrderController::class, 'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}', [OrderController::class, 'show'])->name('account.order.view');

    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('address.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('address.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('address.destroy');

    Route::post('/product/{product}/review', [ProductController::class, 'storeReview'])->name('products.review.store');
    Route::delete('/product/{review}/review', [ProductController::class, 'destroyReview'])->name('products.review.destroy');
});

Route::get('sl/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/sl/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');

Route::get('/account/addresses/lookup', [AccountController::class, 'lookupAddress'])->name('address.lookup');

Route::prefix('checkout')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('checkout.index');

    Route::post('/payment-intent', [CheckoutController::class, 'getPaymentIntent'])->name('checkout.payment_intent');
    Route::post('/process-payment', [CheckoutController::class, 'processPayment'])->name('checkout.process_payment');
});

Route::get('/order/confirmation', function () {
    return Inertia::render('order/Confirmation');
})->name('order.confirmation');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::get('/products/relationships', [AdminProductController::class, 'relationshipIndex'])->name('products.relationships');

    Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');

    Route::get('messages', [AdminMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [AdminMessageController::class, 'show'])->name('messages.show');

    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
    Route::put('reviews/update/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    // Example: update status
    // Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');

    Route::get('carts', [AdminCartController::class, 'index'])->name('carts.index');
    Route::get('carts/{cart}', [AdminCartController::class, 'show'])->name('carts.show');

    Route::get('/clp-labels', [CLPLabelController::class, 'index'])->name('clp-labels.index');
    Route::post('/clp-labels', [CLPLabelController::class, 'store'])->name('clp-labels.store');
});

Route::post('/waitlist', WaitlistController::class)->name('waitlist.store');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
