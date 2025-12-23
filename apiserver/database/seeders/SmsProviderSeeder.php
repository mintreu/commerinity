<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Sms\SmsProvider;
use Illuminate\Database\Seeder;

/**
 * Seed SMS providers configuration.
 *
 * Production: Configure actual API keys via admin panel or .env
 * Development: Uses log driver for testing
 */
class SmsProviderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding SMS providers...');

        $providers = [
            // Production provider - Fast2SMS
            [
                'name' => 'Fast2SMS',
                'slug' => 'fast2sms',
                'driver' => 'fast2sms',
                'api_key' => env('FAST2SMS_API_KEY', ''), // Set in .env for production
                'api_secret' => null,
                'sender_id' => env('FAST2SMS_SENDER_ID', 'CMRNTY'),
                'entity_id' => env('FAST2SMS_ENTITY_ID', ''),
                'config' => [
                    'base_url' => 'https://www.fast2sms.com/dev/bulkV2',
                    'route' => 'dlt',
                ],
                'balance' => 0,
                'per_sms_cost' => 0.15,
                'min_balance_threshold' => 100,
                'is_active' => app()->environment('production'),
                'is_default' => app()->environment('production'),
                'priority' => 1,
                'supports_dlt' => true,
                'supports_otp' => true,
                'supports_promotional' => true,
                'supports_whatsapp' => false,
                'supports_voice_otp' => false,
            ],

            // Development/Testing provider - Log driver
            [
                'name' => 'Log Provider (Dev)',
                'slug' => 'log',
                'driver' => 'log',
                'api_key' => null,
                'api_secret' => null,
                'sender_id' => 'DEVTST',
                'entity_id' => null,
                'config' => [
                    'channel' => 'sms',
                ],
                'balance' => 999999,
                'per_sms_cost' => 0,
                'min_balance_threshold' => 0,
                'is_active' => ! app()->environment('production'),
                'is_default' => ! app()->environment('production'),
                'priority' => 10,
                'supports_dlt' => false,
                'supports_otp' => true,
                'supports_promotional' => true,
                'supports_whatsapp' => false,
                'supports_voice_otp' => false,
            ],
        ];

        foreach ($providers as $data) {
            SmsProvider::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('Seeded '.count($providers).' SMS providers.');
    }
}
