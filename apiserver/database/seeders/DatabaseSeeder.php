<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Geo\BlockSeeder;
use Database\Seeders\Geo\CountrySeeder;
use Database\Seeders\Geo\StateSeeder;
use Illuminate\Database\Seeder;

/**
 * Main database seeder.
 *
 * Orchestrates all seeders in proper order with clear grouping.
 * Supports both production and staging environments.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔═══════════════════════════════════════════════════════════╗');
        $this->command->info('║         COMMERINITY PRO - DATABASE SEEDER                 ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Always run production seeders
        $this->runProductionSeeders();

        // Run staging seeders only in non-production environment
        if (app()->environment(['local', 'staging', 'testing'])) {
            $this->runStagingSeeders();
        }

        $this->command->info('');
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
    }

    /**
     * Production seeders - essential data required for the app to function.
     */
    protected function runProductionSeeders(): void
    {
        $this->command->info('');
        $this->command->info('📦 Running Production Seeders...');
        $this->command->info('───────────────────────────────────────────');

        $this->call([
            // ═══════════════════════════════════════════════════════════
            // GEOGRAPHY - Countries, States, Blocks (Districts)
            // ═══════════════════════════════════════════════════════════
            CountrySeeder::class,
            StateSeeder::class,
            BlockSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // ADMINISTRATION - Admin users and roles
            // ═══════════════════════════════════════════════════════════
            AdminSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // MEMBERSHIP SYSTEM - Stages and Levels for MLM
            // ═══════════════════════════════════════════════════════════
            StageSeeder::class,
            LevelSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // SMS CONFIGURATION - Providers and Templates
            // ═══════════════════════════════════════════════════════════
            SmsProviderSeeder::class,
            SmsTemplateSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // HELPDESK - Support topics and FAQs
            // ═══════════════════════════════════════════════════════════
            HelpdeskTopicSeeder::class,
            HelpdeskFaqSeeder::class,
        ]);
    }

    /**
     * Staging seeders - demo data for development and testing.
     */
    protected function runStagingSeeders(): void
    {
        $this->command->info('');
        $this->command->info('🔧 Running Staging/Demo Seeders...');
        $this->command->info('───────────────────────────────────────────');

        $this->call([
            // ═══════════════════════════════════════════════════════════
            // DEMO USERS - Test accounts for each user type
            // ═══════════════════════════════════════════════════════════
            DemoUserSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // USER DATA - Addresses, Wallets for demo users
            // ═══════════════════════════════════════════════════════════
            AddressSeeder::class,
            WalletSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // RECRUITMENT - Job postings for career page
            // ═══════════════════════════════════════════════════════════
            RecruitmentSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // TRANSACTIONS - Demo transactions for testing
            // ═══════════════════════════════════════════════════════════
            TransactionSeeder::class,

            // ═══════════════════════════════════════════════════════════
            // MLM NETWORK - Full MLM tree with commissions, genealogy
            // ═══════════════════════════════════════════════════════════
            DemoMlmSeeder::class,
        ]);
    }
}
