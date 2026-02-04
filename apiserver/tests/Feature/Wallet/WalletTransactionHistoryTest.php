<?php

declare(strict_types=1);

use App\Http\Controllers\Api\WalletController;
use App\Models\HistoricalTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('wallet transactions default to recent year and expose history availability', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->forUser($user)->create();

    Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'transactionable_type' => Wallet::class,
        'transactionable_id' => $wallet->id,
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'transactionable_type' => Wallet::class,
        'transactionable_id' => $wallet->id,
        'created_at' => now()->subDays(400),
        'updated_at' => now()->subDays(400),
    ]);

    Artisan::call('transactions:archive --days=365');

    expect(Transaction::count())->toBe(1);
    expect(HistoricalTransaction::count())->toBe(1);
    expect(Transaction::where('wallet_id', $wallet->id)->count())->toBe(1);

    Sanctum::actingAs($user);
    $request = Request::create('/api/wallet/transactions', 'GET');
    $request->setUserResolver(fn () => $user);

    $payload = app(WalletController::class)
        ->transactions($request)
        ->response()
        ->getData(true);

    expect($payload['meta']['total'] ?? 0)->toBe(1);
    expect($payload['history_available'] ?? false)->toBeTrue();
});

test('wallet transactions load historical data only after recent pages', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->forUser($user)->create();

    $recent = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'transactionable_type' => Wallet::class,
        'transactionable_id' => $wallet->id,
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ]);

    $old = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'transactionable_type' => Wallet::class,
        'transactionable_id' => $wallet->id,
        'created_at' => now()->subDays(450),
        'updated_at' => now()->subDays(450),
    ]);

    Artisan::call('transactions:archive --days=365');

    expect(Transaction::where('wallet_id', $wallet->id)->count())->toBe(1);
    expect(HistoricalTransaction::where('wallet_id', $wallet->id)->count())->toBe(1);

    Sanctum::actingAs($user);
    $requestPage1 = Request::create('/api/wallet/transactions', 'GET', [
        'include_history' => 1,
        'per_page' => 1,
        'page' => 1,
    ]);
    $requestPage1->setUserResolver(fn () => $user);

    $payload1 = app(WalletController::class)
        ->transactions($requestPage1)
        ->response()
        ->getData(true);

    expect($payload1['data'][0]['uuid'] ?? null)->toBe($recent->uuid);

    $requestPage2 = Request::create('/api/wallet/transactions', 'GET', [
        'include_history' => 1,
        'per_page' => 1,
        'page' => 2,
    ]);
    $requestPage2->setUserResolver(fn () => $user);

    $payload2 = app(WalletController::class)
        ->transactions($requestPage2)
        ->response()
        ->getData(true);

    expect($payload2['data'][0]['uuid'] ?? null)->toBe($old->uuid);
});
