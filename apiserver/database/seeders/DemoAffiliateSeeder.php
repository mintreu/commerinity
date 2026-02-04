<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Casts\GenderCast;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Casts\WalletStatusCast;
use App\Models\Address;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * DemoAffiliateSeeder - Creates a realistic Affiliate network for testing
 *
 * Structure:
 * - 1 Root user (Founder - Advisor stage, Diamond level)
 * - 5 Level 1 users (direct under root)
 * - 15 Level 2 users (3 under each L1)
 * - 30 Level 3 users (2 under each L2)
 * - 20 Level 4 users (scattered)
 *
 * Total: ~71 users with realistic:
 * - Subscriptions at various stages
 * - Commissions (sponsor, level, milestone bonuses)
 * - Wallet balances and transactions
 * - Addresses and profiles
 * - Team stats and genealogy
 */
final class DemoAffiliateSeeder extends Seeder
{
    private array $stages = [];

    private array $levels = [];

    private array $users = [];

    private array $indianNames = [
        'male' => [
            'Aarav Sharma', 'Vivaan Patel', 'Aditya Singh', 'Vihaan Kumar', 'Arjun Reddy',
            'Sai Krishnan', 'Reyansh Gupta', 'Ayaan Verma', 'Krishna Iyer', 'Ishaan Nair',
            'Shaurya Joshi', 'Atharva Desai', 'Advait Menon', 'Dhruv Pillai', 'Kabir Rao',
            'Ritvik Choudhury', 'Aarush Banerjee', 'Veer Mukherjee', 'Pranav Das', 'Arnav Bose',
            'Rudra Chatterjee', 'Darsh Ghosh', 'Yash Sarkar', 'Parth Dutta', 'Ansh Saha',
            'Shivansh Mondal', 'Laksh Roy', 'Rayan Bhattacharya', 'Om Majumdar', 'Harsh Sen',
        ],
        'female' => [
            'Saanvi Sharma', 'Aanya Patel', 'Aadhya Singh', 'Ananya Kumar', 'Pari Reddy',
            'Myra Krishnan', 'Sara Gupta', 'Ira Verma', 'Navya Iyer', 'Anika Nair',
            'Diya Joshi', 'Pihu Desai', 'Prisha Menon', 'Riya Pillai', 'Anvi Rao',
            'Kavya Choudhury', 'Ishita Banerjee', 'Kiara Mukherjee', 'Avni Das', 'Tara Bose',
            'Aditi Chatterjee', 'Aarohi Ghosh', 'Siya Sarkar', 'Zara Dutta', 'Nisha Saha',
            'Meera Mondal', 'Pooja Roy', 'Shreya Bhattacharya', 'Tanvi Majumdar', 'Neha Sen',
        ],
    ];

    private array $cities = [
        ['city' => 'Mumbai', 'state' => 'MH', 'postal' => '400001'],
        ['city' => 'Delhi', 'state' => 'DL', 'postal' => '110001'],
        ['city' => 'Bangalore', 'state' => 'KA', 'postal' => '560001'],
        ['city' => 'Chennai', 'state' => 'TN', 'postal' => '600001'],
        ['city' => 'Kolkata', 'state' => 'WB', 'postal' => '700001'],
        ['city' => 'Hyderabad', 'state' => 'TG', 'postal' => '500001'],
        ['city' => 'Pune', 'state' => 'MH', 'postal' => '411001'],
        ['city' => 'Ahmedabad', 'state' => 'GJ', 'postal' => '380001'],
        ['city' => 'Jaipur', 'state' => 'RJ', 'postal' => '302001'],
        ['city' => 'Lucknow', 'state' => 'UP', 'postal' => '226001'],
    ];

    private int $maleIndex = 0;

    private int $femaleIndex = 0;

    public function run(): void
    {
        $this->command->info('Starting DemoAffiliateSeeder...');

        // Load stages and levels
        $this->loadStagesAndLevels();

        if (empty($this->stages)) {
            $this->command->error('No stages found. Run StageSeeder and LevelSeeder first.');

            return;
        }

        DB::transaction(function () {
            // 1. Create root founder
            $this->command->info('Creating founder user...');
            $founder = $this->createFounder();

            // 2. Create Level 1 users (5 direct under founder)
            $this->command->info('Creating Level 1 users (5)...');
            $level1Users = $this->createLevel1Users($founder, 5);

            // 3. Create Level 2 users (3 under each L1 = 15)
            $this->command->info('Creating Level 2 users (15)...');
            $level2Users = [];
            foreach ($level1Users as $l1User) {
                $l2 = $this->createChildUsers($l1User, 3, 2);
                $level2Users = array_merge($level2Users, $l2);
            }

            // 4. Create Level 3 users (2 under each L2 = 30)
            $this->command->info('Creating Level 3 users (30)...');
            $level3Users = [];
            foreach ($level2Users as $l2User) {
                $l3 = $this->createChildUsers($l2User, 2, 3);
                $level3Users = array_merge($level3Users, $l3);
            }

            // 5. Create Level 4 users (scattered, ~20)
            $this->command->info('Creating Level 4 users (20)...');
            $level4Users = [];
            $l3Sample = array_slice($level3Users, 0, 10);
            foreach ($l3Sample as $l3User) {
                $l4 = $this->createChildUsers($l3User, 2, 4);
                $level4Users = array_merge($level4Users, $l4);
            }

            // 6. Create subscriptions for all users
            $this->command->info('Creating subscriptions...');
            $this->createSubscriptions();

            // 7. Create commissions
            $this->command->info('Creating commissions...');
            $this->createCommissions();

            // 8. Update genealogy stats
            $this->command->info('Updating genealogy stats...');
            $this->updateGenealogyStats();

            // 9. Create wallet transactions
            $this->command->info('Creating wallet transactions...');
            $this->createWalletTransactions();
        });

        $this->command->info('DemoAffiliateSeeder completed!');
        $this->command->info('Total users created: '.count($this->users));
        $this->command->newLine();
        $this->command->info('Demo Login Credentials:');
        $this->command->info('  Founder: founder@demo.com / password');
        $this->command->info('  Member:  member1@demo.com / password');
    }

    private function loadStagesAndLevels(): void
    {
        $this->stages = Stage::orderBy('sort_order')->get()->keyBy('slug')->toArray();
        $this->levels = Level::with('stage')->get()->groupBy('stage_id')->toArray();
    }

    private function createFounder(): User
    {
        $user = User::create([
            'name' => 'Rajesh Founder',
            'email' => 'founder@demo.com',
            'mobile' => '+919800000001',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::MENTOR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'gender' => GenderCast::MALE->value,
            'dob' => Carbon::now()->subYears(45)->subDays(rand(0, 365)),
            'bio' => 'Founder and top leader of the network. Building dreams together.',
            'mobile_verified_at' => now()->subMonths(12),
            'email_verified_at' => now()->subMonths(12),
            'onboarded' => true,
            'parent_id' => null,
        ]);

        // Create wallet with substantial balance
        $this->createWallet($user, 5000000); // 50,000 INR

        // Create address
        $this->createAddress($user);

        // Create genealogy
        $this->createGenealogy($user, null, 0);

        $this->users[$user->id] = $user;

        return $user;
    }

    private function createLevel1Users(User $parent, int $count): array
    {
        $users = [];
        $stageKeys = ['advisor', 'promoter', 'member', 'promoter', 'advisor'];

        for ($i = 0; $i < $count; $i++) {
            $gender = $i % 2 === 0 ? 'male' : 'female';
            $name = $this->getNextName($gender);
            $stageSlug = $stageKeys[$i] ?? 'member';

            $user = User::create([
                'name' => $name,
                'email' => 'member'.($i + 1).'@demo.com',
                'mobile' => '+91980000'.str_pad((string) ($i + 2), 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'type' => $this->getTypeForStage($stageSlug),
                'status' => UserStatusCast::ACTIVE->value,
                'gender' => $gender === 'male' ? GenderCast::MALE->value : GenderCast::FEMALE->value,
                'dob' => Carbon::now()->subYears(rand(25, 50))->subDays(rand(0, 365)),
                'bio' => 'Active network builder and team leader.',
                'mobile_verified_at' => now()->subMonths(rand(6, 11)),
                'email_verified_at' => now()->subMonths(rand(6, 11)),
                'onboarded' => true,
                'parent_id' => $parent->id,
            ]);

            $this->createWallet($user, rand(100000, 500000));
            $this->createAddress($user);
            $this->createGenealogy($user, $parent->id, 1);

            $this->users[$user->id] = $user;
            $users[] = $user;
        }

        return $users;
    }

    private function createChildUsers(User $parent, int $count, int $depth): array
    {
        $users = [];
        $stageOptions = $depth <= 2 ? ['promoter', 'member', 'advisor'] : ['member', 'starter', 'promoter'];

        for ($i = 0; $i < $count; $i++) {
            $gender = rand(0, 1) === 0 ? 'male' : 'female';
            $name = $this->getNextName($gender);
            $stageSlug = $stageOptions[array_rand($stageOptions)];

            $emailPrefix = strtolower(str_replace(' ', '.', $name));
            $email = $emailPrefix.rand(100, 999).'@demo.com';

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'mobile' => '+91'.rand(7000000000, 9999999999),
                'password' => Hash::make('password'),
                'type' => $this->getTypeForStage($stageSlug),
                'status' => rand(0, 10) > 1 ? UserStatusCast::ACTIVE->value : UserStatusCast::DRAFT->value,
                'gender' => $gender === 'male' ? GenderCast::MALE->value : GenderCast::FEMALE->value,
                'dob' => Carbon::now()->subYears(rand(22, 55))->subDays(rand(0, 365)),
                'bio' => null,
                'mobile_verified_at' => now()->subMonths(rand(1, 6)),
                'email_verified_at' => rand(0, 10) > 3 ? now()->subMonths(rand(1, 6)) : null,
                'onboarded' => rand(0, 10) > 2,
                'parent_id' => $parent->id,
            ]);

            $this->createWallet($user, rand(10000, 200000));

            if ($user->onboarded) {
                $this->createAddress($user);
            }

            $this->createGenealogy($user, $parent->id, $depth);

            $this->users[$user->id] = $user;
            $users[] = $user;
        }

        return $users;
    }

    private function createWallet(User $user, int $balanceInPaisa): void
    {
        Wallet::create([
            'walletable_type' => User::class,
            'walletable_id' => $user->id,
            'balance' => $balanceInPaisa,
            'hold_balance' => rand(0, (int) ($balanceInPaisa * 0.1)),
            'total_credited' => $balanceInPaisa + rand(100000, 500000),
            'total_debited' => rand(50000, 200000),
            'points' => rand(100, 5000),
            'pin' => Hash::make('123456'),
            'pin_updated_at' => now()->subDays(rand(1, 60)),
            'currency' => 'INR',
            'status' => WalletStatusCast::ACTIVE->value,
        ]);
    }

    private function createAddress(User $user): void
    {
        $city = $this->cities[array_rand($this->cities)];

        Address::create([
            'uuid' => Str::uuid()->toString(),
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'type' => 'home',
            'person_name' => $user->name,
            'person_mobile' => $user->mobile,
            'address_1' => rand(1, 999).', '.['MG Road', 'Station Road', 'Main Street', 'Park Avenue', 'Gandhi Nagar'][array_rand(['MG Road', 'Station Road', 'Main Street', 'Park Avenue', 'Gandhi Nagar'])],
            'address_2' => ['Near Bus Stand', 'Opposite Mall', 'Behind Temple', 'Next to School', null][array_rand(['Near Bus Stand', 'Opposite Mall', 'Behind Temple', 'Next to School', null])],
            'city' => $city['city'],
            'postal_code' => $city['postal'],
            'state_code' => $city['state'],
            'country_code' => 'IN',
            'default' => true,
        ]);
    }

    private function createGenealogy(User $user, ?int $parentId, int $depth): void
    {
        $advisorStage = Stage::where('slug', 'advisor')->first();
        $memberStage = Stage::where('slug', 'member')->first();

        $stageId = $depth === 0 ? $advisorStage?->id : $memberStage?->id;
        $levelId = null;

        if ($stageId) {
            $level = Level::where('stage_id', $stageId)
                ->orderBy('level_number', $depth === 0 ? 'desc' : 'asc')
                ->first();
            $levelId = $level?->id;
        }

        AffiliateGenealogy::create([
            'user_id' => $user->id,
            'placement_parent_id' => $parentId,
            'placement_position' => rand(1, 5),
            'depth' => $depth,
            'direct_count' => 0,
            'active_direct_count' => 0,
            'level_1_count' => 0,
            'level_2_count' => 0,
            'level_3_count' => 0,
            'level_4_count' => 0,
            'total_team_count' => 0,
            'active_team_count' => 0,
            'personal_sales' => rand(50000, 500000),
            'level_1_sales' => rand(100000, 1000000),
            'level_2_sales' => rand(50000, 500000),
            'level_3_sales' => rand(25000, 250000),
            'level_4_sales' => rand(10000, 100000),
            'total_team_sales' => rand(200000, 2000000),
            'personal_pv' => rand(100, 1000),
            'team_pv' => rand(500, 5000),
            'current_stage_id' => $stageId,
            'current_level_id' => $levelId,
            'highest_level_id' => $levelId,
            'is_active' => $user->status === UserStatusCast::ACTIVE->value,
            'activated_at' => now()->subDays(rand(30, 365)),
            'last_activity_at' => now()->subDays(rand(0, 30)),
        ]);
    }

    private function createSubscriptions(): void
    {
        foreach ($this->users as $user) {
            $genealogy = AffiliateGenealogy::where('user_id', $user->id)->first();

            if (! $genealogy || ! $genealogy->current_stage_id) {
                continue;
            }

            $stage = Stage::find($genealogy->current_stage_id);
            $level = Level::find($genealogy->current_level_id);

            if (! $stage) {
                continue;
            }

            $startsAt = now()->subDays(rand(30, 300));

            UserSubscription::create([
                'user_id' => $user->id,
                'stage_id' => $stage->id,
                'level_id' => $level?->id,
                'current_level_id' => $level?->id,
                'highest_level_id' => $level?->id,
                'level_achieved_at' => $startsAt->copy()->addDays(rand(1, 30)),
                'base_price' => $stage->base_price,
                'discount' => $stage->discount,
                'tax_amount' => $stage->tax_amount,
                'amount' => $stage->price,
                'is_paid' => true,
                'paid_at' => $startsAt,
                'starts_at' => $startsAt,
                'expires_at' => $startsAt->copy()->addDays($level?->validity_days ?? 365),
                'status' => UserSubscription::STATUS_ACTIVE,
                'personal_pv' => $genealogy->personal_pv,
                'team_pv' => $genealogy->team_pv,
                'total_commission_earned' => rand(10000, 500000),
                'current_month_commission' => rand(5000, 50000),
                'renewal_count' => rand(0, 3),
            ]);
        }
    }

    private function createCommissions(): void
    {
        $commissionTypes = [
            CommissionTypeCast::SPONSOR_BONUS,
            CommissionTypeCast::LEVEL_COMMISSION,
            CommissionTypeCast::MATCHING_BONUS,
            CommissionTypeCast::LEVEL_ACHIEVEMENT,
        ];

        foreach ($this->users as $user) {
            $genealogy = AffiliateGenealogy::where('user_id', $user->id)->first();

            if (! $genealogy) {
                continue;
            }

            // Create 3-10 commission records per user
            $commissionCount = rand(3, 10);

            for ($i = 0; $i < $commissionCount; $i++) {
                $type = $commissionTypes[array_rand($commissionTypes)];
                $grossAmount = rand(5000, 100000);
                $tdsAmount = $grossAmount > 50000 ? (int) ($grossAmount * 0.1) : 0;
                $netAmount = $grossAmount - $tdsAmount;

                $commissionDate = now()->subDays(rand(1, 180));

                AffiliateCommission::create([
                    'user_id' => $user->id,
                    'genealogy_id' => $genealogy->id,
                    'from_user_id' => $this->getRandomUserId($user->id),
                    'type' => $type,
                    'level' => $type === CommissionTypeCast::LEVEL_COMMISSION ? rand(1, 4) : null,
                    'rate_percent' => rand(5, 15),
                    'base_amount' => $grossAmount,
                    'gross_amount' => $grossAmount,
                    'tds_amount' => $tdsAmount,
                    'admin_fee' => 0,
                    'net_amount' => $netAmount,
                    'status' => $this->getRandomCommissionStatus(),
                    'commission_date' => $commissionDate->toDateString(),
                    'period_key' => $commissionDate->format('Y-m'),
                    'description' => $this->getCommissionDescription($type),
                    'paid_at' => rand(0, 1) ? $commissionDate->copy()->addDays(rand(1, 14)) : null,
                ]);
            }
        }
    }

    private function createWalletTransactions(): void
    {
        foreach ($this->users as $user) {
            $wallet = Wallet::where('walletable_type', User::class)
                ->where('walletable_id', $user->id)
                ->first();

            if (! $wallet) {
                continue;
            }

            // Create 5-15 transactions per wallet
            $transactionCount = rand(5, 15);

            for ($i = 0; $i < $transactionCount; $i++) {
                $isCredit = rand(0, 10) > 3;
                $amount = rand(10000, 200000);

                $purposes = $isCredit
                    ? ['commission', 'refund', 'bonus', 'deposit']
                    : ['withdrawal', 'purchase', 'transfer', 'fee'];

                $purpose = $purposes[array_rand($purposes)];
                $createdAt = now()->subDays(rand(1, 180));
                $paymentMethods = [PaymentMethodCast::WALLET, PaymentMethodCast::BANK_TRANSFER, PaymentMethodCast::UPI];

                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'transactionable_type' => Wallet::class,
                    'transactionable_id' => $wallet->id,
                    'type' => $isCredit ? TransactionTypeCast::CREDIT : TransactionTypeCast::DEBIT,
                    'purpose' => $purpose,
                    'amount' => $amount,
                    'fee' => 0,
                    'tax' => 0,
                    'net_amount' => $amount,
                    'balance_after' => $wallet->balance,
                    'status' => TransactionStatusCast::COMPLETED,
                    'description' => ucfirst($purpose).' transaction',
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function updateGenealogyStats(): void
    {
        // Update counters from bottom up
        $genealogies = AffiliateGenealogy::orderBy('depth', 'desc')->get();

        foreach ($genealogies as $genealogy) {
            $userId = $genealogy->user_id;

            // Count direct children
            $directCount = User::where('parent_id', $userId)->count();
            $activeDirectCount = User::where('parent_id', $userId)
                ->where('status', UserStatusCast::ACTIVE->value)
                ->count();

            // Count by levels
            $level1Ids = User::where('parent_id', $userId)->pluck('id');
            $level1Count = $level1Ids->count();

            $level2Ids = User::whereIn('parent_id', $level1Ids)->pluck('id');
            $level2Count = $level2Ids->count();

            $level3Ids = User::whereIn('parent_id', $level2Ids)->pluck('id');
            $level3Count = $level3Ids->count();

            $level4Ids = User::whereIn('parent_id', $level3Ids)->pluck('id');
            $level4Count = $level4Ids->count();

            $totalTeam = $level1Count + $level2Count + $level3Count + $level4Count;

            // Count active team members
            $allTeamIds = $level1Ids->merge($level2Ids)->merge($level3Ids)->merge($level4Ids);
            $activeTeamCount = User::whereIn('id', $allTeamIds)
                ->where('status', UserStatusCast::ACTIVE->value)
                ->count();

            $genealogy->update([
                'direct_count' => $directCount,
                'active_direct_count' => $activeDirectCount,
                'level_1_count' => $level1Count,
                'level_2_count' => $level2Count,
                'level_3_count' => $level3Count,
                'level_4_count' => $level4Count,
                'total_team_count' => $totalTeam,
                'active_team_count' => $activeTeamCount,
            ]);
        }
    }

    private function getNextName(string $gender): string
    {
        if ($gender === 'male') {
            $name = $this->indianNames['male'][$this->maleIndex % count($this->indianNames['male'])];
            $this->maleIndex++;
        } else {
            $name = $this->indianNames['female'][$this->femaleIndex % count($this->indianNames['female'])];
            $this->femaleIndex++;
        }

        return $name;
    }

    private function getTypeForStage(string $stageSlug): string
    {
        return match ($stageSlug) {
            'starter' => UserTypeCast::REGULAR->value,
            'member' => UserTypeCast::MEMBER->value,
            'promoter' => UserTypeCast::PROMOTER->value,
            'advisor' => UserTypeCast::ADVISOR->value,
            default => UserTypeCast::REGULAR->value,
        };
    }

    private function getRandomUserId(int $excludeId): ?int
    {
        $ids = array_keys($this->users);
        $filtered = array_filter($ids, fn ($id) => $id !== $excludeId);

        if (empty($filtered)) {
            return null;
        }

        return $filtered[array_rand($filtered)];
    }

    private function getRandomCommissionStatus(): string
    {
        $statuses = [
            CommissionStatusCast::PAID->value,
            CommissionStatusCast::PAID->value,
            CommissionStatusCast::PAID->value,
            CommissionStatusCast::PENDING->value,
            CommissionStatusCast::APPROVED->value,
            CommissionStatusCast::PROCESSING->value,
        ];

        return $statuses[array_rand($statuses)];
    }

    private function getCommissionDescription(CommissionTypeCast $type): string
    {
        return match ($type) {
            CommissionTypeCast::SPONSOR_BONUS => 'Direct sponsor bonus for new member',
            CommissionTypeCast::LEVEL_COMMISSION => 'Level commission from team activity',
            CommissionTypeCast::MATCHING_BONUS => 'Matching bonus from downline earnings',
            CommissionTypeCast::LEVEL_ACHIEVEMENT => 'Level achievement bonus',
            default => 'Commission earned',
        };
    }
}
