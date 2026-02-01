<?php

declare(strict_types=1);

namespace App\Console\Commands\E2E;

use App\Models\User;
use App\Models\Product;
use App\Models\CommissionRule;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedTestWorldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'e2e:seed-test-world {--force : Force the operation to run in production environment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds a deterministic test world for E2E MLM commission validation.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return Command::FAILURE;
        }

        $this->info('Starting E2E test world seeding...');

        // 1. Refresh Database
        $this->call('migrate:fresh');
        $this->info('Database refreshed.');

        // 2. Freeze Time (optional for seeder, but good practice if any models use timestamps internally)
        Carbon::setTestNow(Carbon::create(2026, 1, 15, 12, 0, 0));
        // Seed RNG for deterministic data where Faker is used
        mt_srand(12345); // Set a fixed seed for random number generation
        rand(0, 1); // Call once to initialize on some systems

        // 3. Create Users: Customer and Distributor Tree (at least 3 levels)
        $this->info('Creating users...');

        // Customer User
        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
        ]);

        // Distributor Tree: Level 1 (Root)
        /** @var \App\Models\User $distributorLevel1 */
        $distributorLevel1 = User::factory()->distributor()->create([
            'name' => 'Distributor L1',
            'email' => 'distributor1@example.com',
            'password' => Hash::make('password'),
            'upline_id' => null, // Top-level distributor
        ]);
        $this->info("Created Distributor L1: {$distributorLevel1->name}");

        // Distributor Tree: Level 2
        /** @var \App\Models\User $distributorLevel2a */
        $distributorLevel2a = User::factory()->distributor()->create([
            'name' => 'Distributor L2a',
            'email' => 'distributor2a@example.com',
            'password' => Hash::make('password'),
            'upline_id' => $distributorLevel1->id,
        ]);
        $this->info("Created Distributor L2a: {$distributorLevel2a->name}");

        /** @var \App\Models\User $distributorLevel2b */
        $distributorLevel2b = User::factory()->distributor()->create([
            'name' => 'Distributor L2b',
            'email' => 'distributor2b@example.com',
            'password' => Hash::make('password'),
            'upline_id' => $distributorLevel1->id,
        ]);
        $this->info("Created Distributor L2b: {$distributorLevel2b->name}");

        // Distributor Tree: Level 3
        /** @var \App\Models\User $distributorLevel3a */
        $distributorLevel3a = User::factory()->distributor()->create([
            'name' => 'Distributor L3a',
            'email' => 'distributor3a@example.com',
            'password' => Hash::make('password'),
            'upline_id' => $distributorLevel2a->id,
        ]);
        $this->info("Created Distributor L3a: {$distributorLevel3a->name}");

        // 4. Create Products with Stock, BV/PV
        $this->info('Creating products...');
        Product::factory()->count(5)->create([
            'stock' => 10,
            'price' => fake()->randomFloat(2, 10, 100),
            'bv' => fake()->randomFloat(2, 5, 50), // Business Volume
            'pv' => fake()->randomFloat(2, 5, 50), // Personal Volume
        ]);
        // Specific product for testing out-of-stock
        Product::factory()->create([
            'name' => 'Out Of Stock Product',
            'stock' => 0,
            'price' => 25.00,
            'bv' => 10.00,
            'pv' => 10.00,
        ]);
        $this->info('Products created.');

        // 5. Seed Commission Rules (Version 1)
        $this->info('Seeding commission rules (v1)...');
        // This assumes a simple structure for CommissionRule model
        CommissionRule::create([
            'version' => 'v1',
            'name' => 'Standard MLM Commission v1',
            'rules' => [
                'level_1_percentage' => 0.10, // 10%
                'level_2_percentage' => 0.05, // 5%
                'level_3_percentage' => 0.02, // 2%
                'max_levels' => 3,
                'min_order_bv_for_commission' => 20.00,
            ],
            'is_active' => true,
        ]);
        $this->info('Commission rules v1 seeded.');

        // 6. Initial Wallet States (Optional, if needed for specific test scenarios)
        // For E2E, we typically start with fresh wallets or implicitly create them upon first transaction.
        // If an initial balance is required for specific tests, it would be set here.
        // Example: Wallet::factory()->create(['user_id' => $distributorLevel1->id, 'balance' => 100.00]);
        $this->info('Initial wallet states configured (if any).');

        $this->info('E2E test world seeded successfully!');
        Carbon::setTestNow(); // Clear frozen time
        return Command::SUCCESS;
    }
}
