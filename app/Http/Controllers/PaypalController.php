<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Payment;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;

class PaypalController extends Controller
{
    protected $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal')); // Make sure this line is correctly fetching the PayPal configuration
        $this->paypal->setAccessToken($this->paypal->getAccessToken());
    }

    public function checkout($id)
    {
        $subscription = Subscription::findOrFail($id);

        $response = $this->paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "USD",
                    "value" => "10.00", // Example amount; replace with subscription amount
                ],
            ]],
        ]);

        if (isset($response['id'])) {
            return redirect($response['links'][1]['href']);
        }

        return redirect()->back()->with('error', 'Unable to create order');
    }

    public function success(Request $request)
    {
        $orderId = $request->query('token');

        $response = $this->paypal->capturePaymentOrder($orderId);

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            $subscription = Subscription::where('transaction_id', $orderId)->first();
            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'transaction_id' => $response['id'],
                'amount' => 10.00, // Example amount
                'status' => 'success',
            ]);
            return redirect()->route('home')->with('message', 'Payment successful');
        }

        return redirect()->route('home')->with('error', 'Payment failed');
    }

    public function cancel()
    {
        return redirect()->route('home')->with('message', 'Payment canceled');
    }

    public function webhook(Request $request)
    {
        // Handle PayPal webhook for subscription events
        // Validate the request and handle cancellation, refunds, etc.
        return response()->json(['status' => 'success']);
    }
}
