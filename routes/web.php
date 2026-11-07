<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Customer\ProductController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\SubscriptionController;
use App\Http\Controllers\Admin\PaymentReceiptController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;

Route::middleware(['guest'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/masuk', 'login')->name('login');
        Route::post('/masuk', 'login_post')->name('login_post');
        Route::get('/daftar', 'register')->name('register');
        Route::post('/daftar', 'register_post')->name('register_post');
    });
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/pembayaran/langganan/{slug}', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/pembayaran/langganan/{paket}', [SubscriptionController::class, 'store'])->name('subscription.store');

    Route::get('/pembayaran', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/pembayaran/{slug}', [CheckoutController::class, 'buy_now'])->name('checkout.buy_now');
    Route::post('/pembayaran', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/pembayaran/{slug}', [CheckoutController::class, 'buy_now_store'])->name('checkout.buy_now_store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{id}/faktur', [OrderController::class, 'generateInvoice'])->name('orders.invoice');
    Route::put('/pesanan/{id}/terima', [OrderController::class, 'received'])->name('orders.received');

    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/tambah/{slug}', [CartController::class, 'store'])->name('cart.store');
    Route::post('/keranjang/{id}/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/keranjang/{id}/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/keranjang/{id}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

    Route::post('/produk/{slug}/ulasan', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::prefix('dashboard')->middleware(['auth', 'admin'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard.index');
    });
    Route::controller(AdminProductController::class)->group(function () {
    Route::get('/produk', 'index')->name('dashboard.products.index');
    Route::get('/produk/tambah', 'create')->name('dashboard.products.create');
    Route::post('/produk/tambah', 'store')->name('dashboard.products.store');
    Route::get('/produk/edit/{slug}', 'edit')->name('dashboard.products.edit');
    Route::put('/produk/edit/{slug}', 'update')->name('dashboard.products.update');
    Route::delete('/produk/delete/{id}', 'destroy')->name('dashboard.products.destroy');
});

    Route::controller(AdminOrderController::class)->group(function () {
        Route::get('/pesanan', 'index')->name('dashboard.orders.index');
        Route::get('/pesanan/kelola/{id}', 'manage')->name('dashboard.orders.manage');
        Route::post('/pesanan/kirim/{id}', 'send')->name('dashboard.orders.send');
        Route::post('/pesanan/tolak/{id}', 'reject')->name('dashboard.orders.reject');
    });

    Route::controller(AdminSubscriptionController::class)->group(function () {
        Route::get('/langganan', 'index')->name('dashboard.subscriptions.index');
        Route::post('/langganan/setujui/{id}', 'approve')->name('dashboard.subscriptions.approve');
        Route::post('/langganan/tolak/{id}', 'reject')->name('dashboard.subscriptions.reject');
    });


    Route::controller(UserController::class)->group(function () {
        Route::get('/pengguna', 'index')->name('dashboard.users.index');
    });

    Route::controller(AdminProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('dashboard.profile.index');
    });
    
});

Route::middleware('auth')->group(function () {
    Route::get('/bukti-pembayaran/{payment}', [PaymentReceiptController::class, 'show'])->name('payments.receipt');
    Route::put('/profile', [ProfileController::class, 'profile_update'])->name('profile.update');
    Route::put('/profile/ubah-password', [ProfileController::class, 'change_password'])->name('profile.change_password');
});


Route::fallback(function () {
    return response()->view('pages.error.404', [], 404);
});
