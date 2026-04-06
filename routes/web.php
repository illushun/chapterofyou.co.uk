<?php

use App\Http\Controllers\Admin\AdminBatchSheetController;
use App\Http\Controllers\Admin\AdminBroadcastEmailController;
use App\Http\Controllers\Admin\AdminCourierController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminVoucherController;
use App\Http\Controllers\Admin\AdminWishlistController;
use App\Http\Controllers\Admin\Label\OilController;
use App\Http\Controllers\Cart\VoucherController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketingOptInController;
use App\Http\Controllers\Order\ConfirmationController;
use App\Http\Controllers\SitemapController;
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
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [HomeController::class, 'about'])->name('about');

Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'storeContact'])->name('contact.store');

Route::get('/delivery', fn () => inertia('Delivery'))->name('delivery');

Route::get('/terms', fn () => inertia('Terms'))->name('terms');

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

    Route::post('/account/marketing', [MarketingOptInController::class, 'update'])->name('account.marketing');

    Route::get('/account/orders', [OrderController::class, 'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}', [OrderController::class, 'show'])->name('account.order.view');

    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('address.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('address.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('address.destroy');

    Route::post('/product/{product}/review', [ProductController::class, 'storeReview'])->name('products.review.store');
    Route::delete('/product/{review}/review', [ProductController::class, 'destroyReview'])->name('products.review.destroy');

    Route::get('/account/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
});

Route::get('sl/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('socialite.redirect');
Route::get('/sl/{provider}/callback', [SocialiteController::class, 'handleProviderCallback'])->name('socialite.callback');

Route::get('/account/addresses/lookup', [AccountController::class, 'lookupAddress'])->name('address.lookup');

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/payment-intent', [CheckoutController::class, 'getPaymentIntent'])->name('payment_intent');
    Route::post('/process-payment', [CheckoutController::class, 'processPayment'])->name('process_payment');
    Route::post('/voucher/apply', [VoucherController::class, 'apply'])->name('voucher.apply');
    Route::post('/voucher/remove', [VoucherController::class, 'remove'])->name('voucher.remove');
});

Route::get('/order/confirmation/{id}', [ConfirmationController::class, 'show'])
    ->name('order.confirmation')
    ->middleware('auth');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // resources
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('couriers', AdminCourierController::class)->except(['show']);

    Route::get('products/relationships', [AdminProductController::class, 'relationshipIndex'])->name('products.relationships');
    Route::post('products/assign-relationship', [AdminProductController::class, 'assignRelationship'])->name('products.assign-relationship');
    Route::post('products/remove-relationship', [AdminProductController::class, 'removeRelationship'])->name('products.remove-relationship');

    Route::get('messages', [AdminMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [AdminMessageController::class, 'show'])->name('messages.show');

    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
    Route::put('reviews/update/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::post('reviews/reply/{review}', [AdminReviewController::class, 'reply'])->name('reviews.reply');

    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/dispatch-email', [AdminOrderController::class, 'sendDispatchEmail'])->name('orders.dispatch-email');
    Route::post('orders/{order}/resend-confirmation', [AdminOrderController::class, 'resendConfirmation'])->name('orders.resend-confirmation');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');

    Route::get('carts', [AdminCartController::class, 'index'])->name('carts.index');
    Route::get('carts/{cart}', [AdminCartController::class, 'show'])->name('carts.show');

    Route::get('/oils', [OilController::class, 'index'])->name('oils.index');
    Route::post('/oils', [OilController::class, 'store'])->name('oils.store');
    Route::post('/oils/{oil}/sds', [OilController::class, 'uploadSds'])->name('oils.sds.upload');
    Route::put('/oils/{oil}/hazards/{hazard}', [OilController::class, 'updateHazard'])->name('oils.hazards.update');
    Route::post('/oils/{oil}/hazards', [OilController::class, 'storeHazard'])->name('oils.hazards.store');
    Route::delete('/oils/{oil}/hazards/{hazard}', [OilController::class, 'destroyHazard'])->name('oils.hazards.destroy');
    Route::delete('/oils/{oil}/components/{component}', [OilController::class, 'destroyComponent'])->name('oils.components.destroy');

    Route::get('/clp-labels', [CLPLabelController::class, 'index'])->name('clp-labels.index');
    Route::get('/clp-labels/{product}/calculate', [CLPLabelController::class, 'calculate'])->name('clp-labels.calculate');
    Route::post('/clp-labels/{product}/save', [CLPLabelController::class, 'save'])->name('clp-labels.save');
    Route::get('/clp-labels/{product}/print', [CLPLabelController::class, 'print'])
        ->name('clp-labels.print');

    Route::get('/vouchers', [AdminVoucherController::class, 'index'])       ->name('vouchers.index');
    Route::get('/vouchers/create', [AdminVoucherController::class, 'create'])      ->name('vouchers.create');
    Route::post('/vouchers', [AdminVoucherController::class, 'store'])       ->name('vouchers.store');
    Route::get('/vouchers/{voucher}/edit', [AdminVoucherController::class, 'edit'])        ->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [AdminVoucherController::class, 'update'])      ->name('vouchers.update');
    Route::delete('/vouchers/{voucher}', [AdminVoucherController::class, 'destroy'])     ->name('vouchers.destroy');
    Route::get('/vouchers/{voucher}/usage', [AdminVoucherController::class, 'usage'])       ->name('vouchers.usage');
    Route::get('/vouchers/generate-code', [AdminVoucherController::class, 'generateCode'])->name('vouchers.generate-code');

    Route::get('/wishlists', [AdminWishlistController::class, 'index'])->name('wishlists.index');
    Route::get('/wishlists/{user}', [AdminWishlistController::class, 'show'])->name('wishlists.show');

    Route::get('/batch-sheets', [AdminBatchSheetController::class, 'index'])  ->name('batch-sheets.index');
    Route::get('/batch-sheets/create', [AdminBatchSheetController::class, 'create']) ->name('batch-sheets.create');
    Route::post('/batch-sheets', [AdminBatchSheetController::class, 'store'])  ->name('batch-sheets.store');
    Route::get('/batch-sheets/{batch_sheet}', [AdminBatchSheetController::class, 'show'])   ->name('batch-sheets.show');
    Route::get('/batch-sheets/{batch_sheet}/edit', [AdminBatchSheetController::class, 'edit'])   ->name('batch-sheets.edit');
    Route::put('/batch-sheets/{batch_sheet}', [AdminBatchSheetController::class, 'update']) ->name('batch-sheets.update');
    Route::delete('/batch-sheets/{batch_sheet}', [AdminBatchSheetController::class, 'destroy'])->name('batch-sheets.destroy');
    Route::get('/batch-sheets/{batch_sheet}/pdf', [AdminBatchSheetController::class, 'pdf'])    ->name('batch-sheets.pdf');

    Route::get('broadcasts', [AdminBroadcastEmailController::class, 'index'])->name('broadcasts.index');
    Route::get('broadcasts/create', [AdminBroadcastEmailController::class, 'create'])->name('broadcasts.create');
    Route::post('broadcasts', [AdminBroadcastEmailController::class, 'store'])->name('broadcasts.store');
    Route::post('broadcasts/waitlist-launch', [AdminBroadcastEmailController::class, 'sendWaitlistLaunch'])
        ->name('broadcasts.waitlist-launch');
    Route::get('broadcasts/{broadcast}', [AdminBroadcastEmailController::class, 'show'])->name('broadcasts.show');
});

Route::post('/waitlist', WaitlistController::class)->name('waitlist.store');

Route::get('/unsubscribe/{user}', [MarketingOptInController::class, 'unsubscribeShow'])->name('unsubscribe.show');
Route::post('/unsubscribe/confirm/{user}', [MarketingOptInController::class, 'unsubscribeConfirm'])->name('unsubscribe.confirm');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
