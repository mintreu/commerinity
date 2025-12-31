<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\User;
use App\Models\Wallet;

// Create test user
$user = User::factory()->create();
$wallet = Wallet::factory()->for($user, 'walletable')->create();

// Create topup
$response = \Illuminate\Support\Facades\Http::postJson('http://localhost:8000/api/wallet/topup', [
    'amount' => 100,
], [
    'Authorization' => 'Bearer '.$user->createToken('test-token'),
]);

echo "Response status: ".$response->status().PHP_EOL;
echo "Transaction ID: ".$response->json('data.transaction_id').PHP_EOL;

// Try to fetch checkout
$transactionId = $response->json('data.transaction_id');
$checkoutResponse = \Illuminate\Support\Facades\Http::get("http://localhost:8000/api/checkout/{$transactionId}");

echo "Checkout status: ".$checkoutResponse->status().PHP_EOL;
echo "Checkout body: ".$checkoutResponse->body().PHP_EOL;
