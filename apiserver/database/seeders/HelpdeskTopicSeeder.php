<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Helpdesk\HelpdeskTopic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HelpdeskTopicSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Seeding helpdesk topics...');

        $topics = [
            [
                'name' => 'Account & Profile',
                'slug' => 'account-profile',
                'description' => 'Help with your account settings, profile information, and personal details.',
                'icon' => 'heroicon-o-user-circle',
                'tickable' => true,
                'active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Login & Security',
                'slug' => 'login-security',
                'description' => 'Issues with login, password reset, two-factor authentication, and account security.',
                'icon' => 'heroicon-o-shield-check',
                'tickable' => true,
                'active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Wallet & Transactions',
                'slug' => 'wallet-transactions',
                'description' => 'Questions about your wallet balance, deposits, withdrawals, and transaction history.',
                'icon' => 'heroicon-o-wallet',
                'tickable' => true,
                'active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Payments & Billing',
                'slug' => 'payments-billing',
                'description' => 'Help with payments, subscriptions, invoices, and billing issues.',
                'icon' => 'heroicon-o-credit-card',
                'tickable' => true,
                'active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Commission & Earnings',
                'slug' => 'commission-earnings',
                'description' => 'Questions about your commissions, bonuses, earnings, and payout schedules.',
                'icon' => 'heroicon-o-currency-rupee',
                'tickable' => true,
                'active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Membership & Subscription',
                'slug' => 'membership-subscription',
                'description' => 'Help with membership plans, upgrades, renewals, and subscription benefits.',
                'icon' => 'heroicon-o-star',
                'tickable' => true,
                'active' => true,
                'order' => 6,
            ],
            [
                'name' => 'Team & Referrals',
                'slug' => 'team-referrals',
                'description' => 'Questions about your team, referral program, genealogy, and team management.',
                'icon' => 'heroicon-o-user-group',
                'tickable' => true,
                'active' => true,
                'order' => 7,
            ],
            [
                'name' => 'KYC & Verification',
                'slug' => 'kyc-verification',
                'description' => 'Help with document verification, KYC status, and identity verification.',
                'icon' => 'heroicon-o-identification',
                'tickable' => true,
                'active' => true,
                'order' => 8,
            ],
            [
                'name' => 'Mobile App',
                'slug' => 'mobile-app',
                'description' => 'Technical issues with the mobile application, features, and functionality.',
                'icon' => 'heroicon-o-device-phone-mobile',
                'tickable' => true,
                'active' => true,
                'order' => 9,
            ],
            [
                'name' => 'Technical Issues',
                'slug' => 'technical-issues',
                'description' => 'Website bugs, errors, performance issues, and technical problems.',
                'icon' => 'heroicon-o-cog-6-tooth',
                'tickable' => true,
                'active' => true,
                'order' => 10,
            ],
            [
                'name' => 'General Inquiry',
                'slug' => 'general-inquiry',
                'description' => 'Any other questions or inquiries not covered in other topics.',
                'icon' => 'heroicon-o-question-mark-circle',
                'tickable' => true,
                'active' => true,
                'order' => 99,
            ],
            [
                'name' => 'Getting Started',
                'slug' => 'getting-started',
                'description' => 'Guides and FAQs for new users to get started with the platform.',
                'icon' => 'heroicon-o-rocket-launch',
                'tickable' => false, // FAQ only
                'active' => true,
                'order' => 0,
            ],
        ];

        foreach ($topics as $topic) {
            HelpdeskTopic::updateOrCreate(
                ['slug' => $topic['slug']],
                $topic
            );
        }

        $this->command->info('Seeded '.count($topics).' helpdesk topics.');
    }
}
