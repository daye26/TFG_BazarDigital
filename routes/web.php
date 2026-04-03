<?php

use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'home'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/latest', [ProductController::class, 'latest'])->name('products.latest');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', [ProductManagementController::class, 'index'])->name('admin.index');
    Route::get('/admin/products/create', [ProductManagementController::class, 'create'])->name('admin.products.create');
    Route::post('/admin/products', [ProductManagementController::class, 'store'])->name('admin.products.store');
    Route::get('/admin/categories/create', [ProductManagementController::class, 'createCategory'])->name('admin.categories.create');
    Route::post('/admin/categories', [ProductManagementController::class, 'storeCategory'])->name('admin.categories.store');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
