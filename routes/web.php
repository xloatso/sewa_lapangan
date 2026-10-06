<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CourtController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('customer.index');
Route::get('/checkout', fn () => redirect()->route('customer.index'));
Route::post('/checkout', [BookingController::class, 'store'])->middleware('throttle:20,1')->name('customer.checkout');
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', AdminOnly::class])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
    Route::get('/admin/dashboard', [BookingController::class, 'dashboard'])->name('admin.dashboard');
    Route::patch('/admin/bookings/{booking}/status', [BookingController::class, 'status'])->name('admin.bookings.status');
    Route::resource('/admin/courts', CourtController::class)->except('show');
});
