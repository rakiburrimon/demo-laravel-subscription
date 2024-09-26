<?php

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaypalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // This should point to your home view
})->name('home');

Route::post('/subscribe', [SubscriptionController::class, 'create'])->name('subscribe');
Route::get('/paypal/checkout/{id}', [PaypalController::class, 'checkout'])->name('paypal.checkout');
Route::get('/paypal/success', [PaypalController::class, 'success'])->name('paypal.success');
Route::get('/paypal/cancel', [PaypalController::class, 'cancel'])->name('paypal.cancel');
Route::post('/paypal/webhook', [PaypalController::class, 'webhook'])->name('paypal.webhook');

