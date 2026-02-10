<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Models\Sms\SmsTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed SMS templates for various notification types.
 *
 * Templates follow DLT format with {#var#} placeholders.
 */
class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding SMS templates...');

        $integration = Integration::query()
            ->ofType(IntegrationTypeCast::SMS->value)
            ->where('is_default', true)
            ->first();

        if (! $integration) {
            $this->command->warn('No default SMS integration found. Seed integrations first.');

            return;
        }

        $appSenderId = Str::of((string) config('app.name'))
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->substr(0, 20)
            ->toString();
        $appSenderId = $appSenderId !== '' ? $appSenderId : 'APPNAME';
        $defaultSenderId = (string) ($integration->getCredential('sender_id')
            ?? config('services.sms.fast2sms.sender_id')
            ?? $appSenderId);
        $defaultEntityId = (string) ($integration->getCredential('entity_id')
            ?? config('services.sms.fast2sms.entity_id')
            ?? '');

        $templates = [
            // ═══════════════════════════════════════════════════════════
            // OTP TEMPLATES
            // ═══════════════════════════════════════════════════════════
            [
                'name' => 'Login OTP',
                'slug' => 'login-otp',
                'message_id' => 'OTP_LOGIN_001',
                'category' => 'otp',
                'content' => '{#otp#} is your OTP to login to Commerinity Pro. Valid for {#validity#} minutes. Do not share with anyone. - COMMERINITY',
                'variables' => ['otp', 'validity'],
            ],
            [
                'name' => 'Registration OTP',
                'slug' => 'registration-otp',
                'message_id' => 'OTP_REG_001',
                'category' => 'otp',
                'content' => '{#otp#} is your OTP to verify your mobile number on Commerinity Pro. Valid for {#validity#} minutes. - COMMERINITY',
                'variables' => ['otp', 'validity'],
            ],
            [
                'name' => 'Password Reset OTP',
                'slug' => 'password-reset-otp',
                'message_id' => 'OTP_PWD_001',
                'category' => 'otp',
                'content' => '{#otp#} is your OTP to reset your password on Commerinity Pro. Valid for {#validity#} minutes. Do not share. - COMMERINITY',
                'variables' => ['otp', 'validity'],
            ],
            [
                'name' => 'Transaction OTP',
                'slug' => 'transaction-otp',
                'message_id' => 'OTP_TXN_001',
                'category' => 'otp',
                'content' => '{#otp#} is your OTP for transaction of Rs.{#amount#} on Commerinity Pro. Valid for {#validity#} mins. Do not share. - COMMERINITY',
                'variables' => ['otp', 'amount', 'validity'],
            ],

            // ═══════════════════════════════════════════════════════════
            // TRANSACTIONAL TEMPLATES
            // ═══════════════════════════════════════════════════════════
            [
                'name' => 'Welcome Message',
                'slug' => 'welcome',
                'message_id' => 'TXN_WELCOME_001',
                'category' => 'transactional',
                'content' => 'Welcome to Commerinity Pro, {#name#}! Your account has been created. Start your journey at commerinity.com - COMMERINITY',
                'variables' => ['name'],
            ],
            [
                'name' => 'Wallet Credit',
                'slug' => 'wallet-credit',
                'message_id' => 'TXN_WCREDIT_001',
                'category' => 'transactional',
                'content' => 'Rs.{#amount#} credited to your Commerinity wallet. Txn ID: {#txn_id#}. New balance: Rs.{#balance#}. - COMMERINITY',
                'variables' => ['amount', 'txn_id', 'balance'],
            ],
            [
                'name' => 'Wallet Debit',
                'slug' => 'wallet-debit',
                'message_id' => 'TXN_WDEBIT_001',
                'category' => 'transactional',
                'content' => 'Rs.{#amount#} debited from your Commerinity wallet. Txn ID: {#txn_id#}. Available balance: Rs.{#balance#}. - COMMERINITY',
                'variables' => ['amount', 'txn_id', 'balance'],
            ],
            [
                'name' => 'Commission Earned',
                'slug' => 'commission-earned',
                'message_id' => 'TXN_COMM_001',
                'category' => 'transactional',
                'content' => 'Congratulations! You earned Rs.{#amount#} commission on {#type#}. Credited to your wallet. Total earnings: Rs.{#total#}. - COMMERINITY',
                'variables' => ['amount', 'type', 'total'],
            ],
            [
                'name' => 'Withdrawal Processed',
                'slug' => 'withdrawal-processed',
                'message_id' => 'TXN_WDRAW_001',
                'category' => 'transactional',
                'content' => 'Withdrawal of Rs.{#amount#} processed. Ref: {#ref_id#}. Amount will be credited to your bank in 24-48 hrs. - COMMERINITY',
                'variables' => ['amount', 'ref_id'],
            ],
            [
                'name' => 'KYC Approved',
                'slug' => 'kyc-approved',
                'message_id' => 'TXN_KYC_001',
                'category' => 'transactional',
                'content' => 'Dear {#name#}, your KYC verification is complete. You now have full access to all features on Commerinity Pro. - COMMERINITY',
                'variables' => ['name'],
            ],
            [
                'name' => 'Subscription Activated',
                'slug' => 'subscription-activated',
                'message_id' => 'TXN_SUB_001',
                'category' => 'transactional',
                'content' => 'Your {#plan#} subscription is now active on Commerinity Pro. Valid till {#expiry#}. Thank you for upgrading! - COMMERINITY',
                'variables' => ['plan', 'expiry'],
            ],
            [
                'name' => 'Job Application Received',
                'slug' => 'job-application-received',
                'message_id' => 'TXN_JOB_001',
                'category' => 'transactional',
                'content' => 'Dear {#name#}, your application for {#position#} has been received. Application ID: {#app_id#}. We will contact you soon. - COMMERINITY',
                'variables' => ['name', 'position', 'app_id'],
            ],

            // ═══════════════════════════════════════════════════════════
            // PROMOTIONAL TEMPLATES
            // ═══════════════════════════════════════════════════════════
            [
                'name' => 'Referral Bonus',
                'slug' => 'referral-bonus',
                'message_id' => 'PROMO_REF_001',
                'category' => 'promotional',
                'content' => 'Great news! Your referral {#referred_name#} joined Commerinity. Rs.{#bonus#} bonus added to your wallet. Keep sharing! - COMMERINITY',
                'variables' => ['referred_name', 'bonus'],
            ],
        ];

        foreach ($templates as $data) {
            SmsTemplate::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'integration_id' => $integration->id,
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'message_id' => $data['message_id'],
                    'entity_id' => $defaultEntityId ?: null,
                    'template_id' => Str::upper(Str::random(10)), // Placeholder - replace with actual DLT IDs
                    'sender_id' => $defaultSenderId,
                    'content' => $data['content'],
                    'variables' => $data['variables'],
                    'variable_count' => count($data['variables']),
                    'category' => $data['category'],
                    'language' => 'en',
                    'is_active' => true,
                    'is_dlt_approved' => false, // Set to true after DLT approval
                ]
            );
        }

        $this->command->info('Seeded '.count($templates).' SMS templates.');
    }
}

