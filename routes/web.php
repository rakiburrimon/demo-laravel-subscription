<?php

use App\Http\Controllers\PayPalSubscriptionController;
use Illuminate\Support\Facades\Route;

// Route to show the subscription form
Route::get('paypal/subscription-form', function () {
    return view('subscription');
})->name('paypal.subscription.form');

// Route to create the subscription
Route::post('paypal/subscription', [PayPalSubscriptionController::class, 'createSubscription'])->name('paypal.subscription.create');

// Route to handle successful subscription
Route::get('paypal/subscription/success', [PayPalSubscriptionController::class, 'subscriptionSuccess'])->name('paypal.subscription.success');

// Route to handle canceled subscription
Route::get('paypal/subscription/cancel', [PayPalSubscriptionController::class, 'subscriptionCancel'])->name('paypal.subscription.cancel');

// Route to handle webhook from PayPal
Route::post('paypal/webhook', [PayPalSubscriptionController::class, 'handleWebhook'])->name('paypal.webhook');
