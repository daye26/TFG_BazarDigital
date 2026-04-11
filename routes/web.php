<?php

use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'home'])->name('home');
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/latest', [ProductController::class, 'latest'])->name('products.latest');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [ProductManagementController::class, 'index'])->name('admin.index');
    Route::get('/admin/orders', [OrderManagementController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [OrderManagementController::class, 'show'])->name('admin.orders.show');
    Route::patch('/admin/orders/{order}/ready', [OrderManagementController::class, 'ready'])->name('admin.orders.ready');
    Route::patch('/admin/orders/{order}/complete', [OrderManagementController::class, 'complete'])->name('admin.orders.complete');
    Route::get('/admin/products', [ProductManagementController::class, 'manage'])->name('admin.products.manage');
    Route::get('/admin/products/create', [ProductManagementController::class, 'create'])->name('admin.products.create');
    Route::post('/admin/products', [ProductManagementController::class, 'store'])->name('admin.products.store');
    Route::patch('/admin/products/{product}/details', [ProductManagementController::class, 'updateDetails'])->name('admin.products.update.details');
    Route::patch('/admin/products/{product}/pricing', [ProductManagementController::class, 'updatePricing'])->name('admin.products.update.pricing');
    Route::get('/admin/categories', [ProductManagementController::class, 'manageCategories'])->name('admin.categories.manage');
    Route::get('/admin/categories/create', [ProductManagementController::class, 'createCategory'])->name('admin.categories.create');
    Route::post('/admin/categories', [ProductManagementController::class, 'storeCategory'])->name('admin.categories.store');
    Route::patch('/admin/categories/{category}', [ProductManagementController::class, 'updateCategory'])->name('admin.categories.update');
});

Route::get('/dashboard', function (Request $request) {
    return redirect(route($request->user()->redirectRouteName(), absolute: false));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/payment-method/store', [OrderController::class, 'switchToStorePayment'])->name('orders.payment.store');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::post('/orders/{order}/checkout', [StripeCheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/orders/{order}/checkout/success', [StripeCheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders/{order}/checkout/cancel', [StripeCheckoutController::class, 'cancel'])->name('checkout.cancel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
