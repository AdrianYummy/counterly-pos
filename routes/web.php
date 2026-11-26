<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SessionSecurityController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isAdmin() ? 'dashboard' : 'sales.create');
    }

    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::post('/session/close', [SessionSecurityController::class, 'close'])->name('session.close');

    Route::middleware('session.secure')->group(function () {
        Route::post('/session/heartbeat', [SessionSecurityController::class, 'heartbeat'])->name('session.heartbeat');
        Route::post('/session/activity', [SessionSecurityController::class, 'activity'])->name('session.activity');
        Route::get('/dashboard', DashboardController::class)->middleware('admin')->name('dashboard');

        Route::middleware('admin')->group(function () {
            Route::resource('products', ProductController::class)->except(['index', 'show']);
            Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
            Route::resource('staff', StaffController::class)->parameters(['staff' => 'staff']);
        });

        Route::resource('products', ProductController::class)->only(['index', 'show']);
        Route::resource('customers', CustomerController::class)->except(['destroy']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/sales/{sale}/email-receipt', [SaleController::class, 'emailReceipt'])->name('sales.email-receipt');
    });
});
