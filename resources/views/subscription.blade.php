<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Subscription</title>
</head>
<body>
    <h1>Subscribe to our Service</h1>
    <form action="{{ route('paypal.subscription.create') }}" method="POST">
        @csrf

        <!-- Plan Information -->
        <div>
            <label for="plan_id">Plan ID:</label>
            <input type="text" name="plan_id" id="plan_id" placeholder="Enter Plan ID" required>
        </div>

        <!-- Subscriber Information -->
        <div>
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" placeholder="Enter First Name" required>
        </div>

        <div>
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" id="last_name" placeholder="Enter Last Name" required>
        </div>

        <div>
            <label for="email">Email Address:</label>
            <input type="email" name="email" id="email" placeholder="Enter Email Address" required>
        </div>

        <!-- Product Information -->
        <div>
            <label for="product_id">Product ID:</label>
            <input type="text" name="product_id" id="product_id" placeholder="Enter Product ID" required>
        </div>

        <!-- User Information (If User is Logged In) -->
        @if(auth()->check())
            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
        @else
            <!-- If user is not logged in, allow manual user_id entry (optional) -->
            <div>
                <label for="user_id">User ID:</label>
                <input type="text" name="user_id" id="user_id" placeholder="Enter User ID (optional)">
            </div>
        @endif

        <!-- Submit Button -->
        <div>
            <button type="submit">Create Subscription</button>
        </div>
    </form>
</body>
</html>
