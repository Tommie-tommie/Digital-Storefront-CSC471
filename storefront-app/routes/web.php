<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

# public: PLP + PDP
Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{sku}', [ProductController::class, 'show'])->whereNumber('sku')->name('products.show');

# customer auth
Route::get('/login', [AuthController::class, 'show_login'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'show_register'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

# customer only (middleware alias is in bootstrap/app.php)
Route::middleware('customer')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{sku}', [CartController::class, 'update'])->whereNumber('sku')->name('cart.update');
    Route::delete('/cart/{sku}', [CartController::class, 'remove'])->whereNumber('sku')->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order_id}', [OrderController::class, 'show'])->whereNumber('order_id')->name('orders.show');
});

# admin auth
Route::get('/admin/login', [AdminAuthController::class, 'show_login'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

# admin only
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{sku}/edit', [AdminProductController::class, 'edit'])->whereNumber('sku')->name('products.edit');
    Route::put('/products/{sku}', [AdminProductController::class, 'update'])->whereNumber('sku')->name('products.update');
    Route::patch('/products/{sku}/retire', [AdminProductController::class, 'retire'])->whereNumber('sku')->name('products.retire');
    Route::delete('/products/{sku}', [AdminProductController::class, 'destroy'])->whereNumber('sku')->name('products.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order_id}', [AdminOrderController::class, 'show'])->whereNumber('order_id')->name('orders.show');
});
