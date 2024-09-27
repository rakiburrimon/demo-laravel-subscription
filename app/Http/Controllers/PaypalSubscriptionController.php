<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalSubscriptionController extends Controller
{
    protected $paypalClientId;
    protected $paypalSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->paypalClientId = config('services.paypal.client_id');
        $this->paypalSecret = config('services.paypal.secret');
        $this->setEnvironment();
    }

    private function setEnvironment()
    {
        // Set the base URL based on the environment
        if (config('app.env') === 'production') {
            $this->baseUrl = 'https://api.paypal.com/v1/'; // Production URL
        } else {
            $this->baseUrl = 'https://api-m.sandbox.paypal.com/v1/'; // Sandbox URL
        }
    }

    private function getAccessToken()
    {
        // Ensure both client_id and client_secret are fetched correctly from the config
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.secret');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('PayPal Client ID or Secret is not set.');
        }

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post($this->baseUrl . 'oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->ok()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('Could not retrieve PayPal access token. Check your credentials.');
    }

    public function createSubscription(Request $request)
    {
        // Get access token
        $accessToken = $this->getAccessToken();

        // Prepare subscription data with metadata
        $subscriptionData = [
            'plan_id' => $request->input('plan_id'),
            'subscriber' => [
                'name' => [
                    'given_name' => $request->input('first_name'),
                    'surname' => $request->input('last_name'),
                ],
                'email_address' => $request->input('email'),
                'custom_id' => 'user_' . $request->input('user_id'), // custom user data
            ],
            'application_context' => [
                'brand_name' => 'My Laravel App',
                'locale' => 'en-US',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => route('paypal.subscription.success'),
                'cancel_url' => route('paypal.subscription.cancel'),
                'metadata' => [
                    'product_id' => $request->input('product_id'), // Metadata for product-related info
                    'user_id' => $request->input('user_id'), // Metadata for user-related info
                ],
            ],
            'custom_id' => 'product_' . $request->input('product_id'), // Additional metadata
            'invoice_id' => 'order_' . uniqid(), // Unique invoice or order ID
        ];

        // Make the PayPal API request
        $response = Http::withToken($accessToken)
            ->post($this->baseUrl . 'billing/subscriptions', $subscriptionData);

        if ($response->successful()) {
            $data = $response->json();
            return redirect($data['links'][0]['href']); // Redirect user to PayPal approval link
        } else {
            return response()->json(['error' => 'Subscription creation failed.'], 400);
        }
    }

    public function subscriptionSuccess(Request $request)
    {
        return response()->json(['message' => 'Subscription successful!', 'details' => $request->all()]);
    }

    public function subscriptionCancel()
    {
        return response()->json(['message' => 'Subscription was cancelled.']);
    }

    public function handleWebhook(Request $request)
    {
        // Get the webhook payload
        $payload = $request->all();

        // Log the webhook data for testing
        \Log::info('PayPal Webhook Received:', $payload);

        // Extract metadata from the webhook payload
        if (isset($payload['resource']['subscriber']['custom_id'])) {
            $userId = $payload['resource']['subscriber']['custom_id']; // Your custom user ID
        }

        if (isset($payload['resource']['custom_id'])) {
            $productId = $payload['resource']['custom_id']; // Your custom product ID
        }

        if (isset($payload['resource']['metadata'])) {
            $metadata = $payload['resource']['metadata']; // Extract metadata if present
            $productId = $metadata['product_id'] ?? null;
            $userId = $metadata['user_id'] ?? null;
        }

        // Process the webhook according to the event type (e.g., subscription_created, payment_success)
        switch ($payload['event_type']) {
            case 'BILLING.SUBSCRIPTION.CREATED':
                // Handle subscription creation logic
                \Log::info("Subscription created for user: {$userId} and product: {$productId}");
                break;

            case 'PAYMENT.SALE.COMPLETED':
                // Handle successful payment
                \Log::info("Payment completed for user: {$userId} and product: {$productId}");
                break;

            // Add other cases for different webhook event types
            default:
                \Log::info('Unhandled webhook event:', $payload['event_type']);
                break;
        }

        return response()->json(['status' => 'Webhook received']);
    }
}
