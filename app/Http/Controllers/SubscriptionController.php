<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'subscription_type' => 'required|string',
        ]);

        // Create a new subscription
        $subscription = Subscription::create([
            'user_id' => '101',
            'subscription_type' => $request->subscription_type,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // Example for a monthly subscription
        ]);

        return redirect()->route('paypal.checkout', $subscription->id);
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update(['status' => 'canceled']);
        return redirect()->route('home')->with('message', 'Subscription canceled');
    }

    public function refund(Subscription $subscription)
    {
        // Logic to handle refund (via PayPal API)
        $subscription->update(['status' => 'refunded']);
        return redirect()->route('home')->with('message', 'Subscription refunded');
    }
}
