<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Customer diarahkan ke katalog, selain itu ke dashboard.
Route::get('/', function () {
    $isCustomer = auth()->check() && auth()->user()->isCustomer();

    return redirect()->route($isCustomer ? 'shop.index' : 'dashboard');
});

// ---------- Guest ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.attempt');
});

// ---------- Semua pengguna yang login ----------
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Admin only (Gate 'admin') ----------
// Didaftarkan lebih dulu agar /products/create tidak tertangkap oleh /products/{product}.
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::resource('products', ProductController::class)->except(['index', 'show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('users', UserController::class)->except(['show', 'destroy']);

    Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
});

// ---------- Admin & Staff (Gate 'staff-area'; customer ditolak 403) ----------
Route::middleware(['auth', 'can:staff-area'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::resource('products', ProductController::class)->only(['index', 'show']);

    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);

    // Pesanan online dari customer
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// ---------- Customer only (Gate 'customer') ----------
Route::middleware(['auth', 'can:customer'])->group(function () {
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

    Route::get('/my-orders', [CustomerOrderController::class, 'index'])->name('my-orders.index');
    Route::get('/my-orders/create', [CustomerOrderController::class, 'create'])->name('my-orders.create');
    Route::post('/my-orders', [CustomerOrderController::class, 'store'])->name('my-orders.store');
    Route::get('/my-orders/{order}', [CustomerOrderController::class, 'show'])->name('my-orders.show');
    Route::post('/my-orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('my-orders.cancel');
});
