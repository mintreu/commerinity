<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Support\HelpdeskFaq;
use App\Models\Support\HelpdeskTopic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HelpdeskFaqSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Seeding helpdesk FAQs...');

        // Get topics
        $topics = HelpdeskTopic::pluck('id', 'slug');

        $faqs = [
            // Getting Started (for everyone)
            [
                'topic' => 'getting-started',
                'question' => 'How do I create an account?',
                'answer' => "Creating an account is simple:\n\n1. Click on the **Register** button on the homepage\n2. Enter your full name, email, and mobile number\n3. Create a strong password\n4. Verify your email address by clicking the link we send\n5. Complete your profile with additional details\n\nOnce verified, you can start exploring the platform!",
                'tags' => ['registration', 'account', 'signup'],
                'keywords' => ['create account', 'register', 'sign up', 'new account'],
            ],
            [
                'topic' => 'getting-started',
                'question' => 'What is a referral code and do I need one?',
                'answer' => "A **referral code** is a unique code shared by existing members. While not mandatory, using a referral code:\n\n- Connects you with a sponsor/mentor\n- May provide special benefits or bonuses\n- Helps you join a team for guidance\n\nIf you don't have a referral code, you can still register and will be assigned to the default team.",
                'tags' => ['referral', 'registration', 'sponsor'],
                'keywords' => ['referral code', 'sponsor code', 'referral'],
            ],

            // Account & Profile (for all users)
            [
                'topic' => 'account-profile',
                'question' => 'How do I update my profile information?',
                'answer' => "To update your profile:\n\n1. Go to **Settings** > **Profile**\n2. Click on the field you want to edit\n3. Make your changes\n4. Click **Save Changes**\n\nNote: Some fields like email and mobile require verification after change. Name changes may require KYC re-verification.",
                'tags' => ['profile', 'settings', 'update'],
                'keywords' => ['update profile', 'change name', 'edit profile'],
            ],
            [
                'topic' => 'account-profile',
                'question' => 'How do I change my password?',
                'answer' => "To change your password:\n\n1. Go to **Settings** > **Security**\n2. Click **Change Password**\n3. Enter your current password\n4. Enter and confirm your new password\n5. Click **Update Password**\n\n**Password Requirements:**\n- Minimum 8 characters\n- At least one uppercase letter\n- At least one number\n- At least one special character",
                'tags' => ['password', 'security', 'account'],
                'keywords' => ['change password', 'reset password', 'new password'],
            ],

            // Login & Security
            [
                'topic' => 'login-security',
                'question' => 'How do I enable Two-Factor Authentication (2FA)?',
                'answer' => "Two-Factor Authentication adds an extra layer of security:\n\n1. Go to **Settings** > **Security**\n2. Find **Two-Factor Authentication** section\n3. Click **Enable 2FA**\n4. Scan the QR code with an authenticator app (Google Authenticator, Authy)\n5. Enter the 6-digit code to verify\n6. Save your backup codes securely\n\n**Important:** Keep your backup codes safe - they're needed if you lose access to your authenticator app.",
                'tags' => ['2fa', 'security', 'authentication'],
                'keywords' => ['two factor', '2fa', 'authenticator', 'secure account'],
            ],
            [
                'topic' => 'login-security',
                'question' => 'I forgot my password. How do I reset it?',
                'answer' => "If you've forgotten your password:\n\n1. Click **Forgot Password** on the login page\n2. Enter your registered email address\n3. Check your email for the reset link (check spam folder too)\n4. Click the link and create a new password\n5. Log in with your new password\n\n**Note:** The reset link expires in 60 minutes. If it expires, request a new one.",
                'tags' => ['password', 'reset', 'login'],
                'keywords' => ['forgot password', 'reset password', 'cant login'],
            ],

            // Wallet & Transactions
            [
                'topic' => 'wallet-transactions',
                'question' => 'How do I add money to my wallet?',
                'answer' => "To add money to your wallet:\n\n1. Go to **Wallet** > **Add Money**\n2. Enter the amount (minimum ₹100)\n3. Choose your payment method:\n   - UPI\n   - Debit/Credit Card\n   - Net Banking\n4. Complete the payment\n5. Amount reflects instantly after successful payment\n\n**Note:** A small processing fee may apply for certain payment methods.",
                'tags' => ['wallet', 'deposit', 'payment'],
                'keywords' => ['add money', 'deposit', 'recharge wallet'],
            ],
            [
                'topic' => 'wallet-transactions',
                'question' => 'How long do withdrawals take?',
                'answer' => "Withdrawal processing times:\n\n| Method | Processing Time |\n|--------|----------------|\n| Bank Transfer | 1-3 business days |\n| UPI | 1-24 hours |\n| IMPS | 1-24 hours |\n\n**Requirements for withdrawal:**\n- Completed KYC verification\n- Valid bank account linked\n- Minimum withdrawal amount: ₹500\n- Wallet PIN set up\n\n**Note:** First-time withdrawals may take longer due to security verification.",
                'tags' => ['withdrawal', 'bank', 'payout'],
                'keywords' => ['withdraw money', 'bank transfer', 'withdrawal time'],
            ],

            // Commission & Earnings (for members and above)
            [
                'topic' => 'commission-earnings',
                'question' => 'How are commissions calculated?',
                'answer' => "Commission calculation depends on your membership level and type:\n\n**Direct Referral Commission:**\n- Earn when your direct referrals make purchases or upgrade\n- Percentage varies by your level (5% to 15%)\n\n**Team Commission:**\n- Earn from your downline's activity\n- Depth and percentage vary by level\n\n**Performance Bonus:**\n- Monthly bonus for achieving targets\n- Calculated on 1st of each month\n\nView your detailed commission breakdown in **Dashboard** > **Earnings**.",
                'tags' => ['commission', 'earnings', 'calculation'],
                'keywords' => ['commission calculation', 'how much earn', 'commission rate'],
            ],
            [
                'topic' => 'commission-earnings',
                'question' => 'When are commissions credited?',
                'answer' => "Commission credit schedule:\n\n| Commission Type | Credit Time |\n|-----------------|-------------|\n| Direct Referral | Instant (after verification) |\n| Team Commission | Daily at midnight |\n| Performance Bonus | 1st of next month |\n| Leadership Bonus | 5th of each month |\n\n**Note:** Commissions are first added to your wallet. You can then withdraw or use them for purchases.",
                'tags' => ['commission', 'payout', 'credit'],
                'keywords' => ['when commission', 'commission time', 'commission credit'],
            ],

            // Membership (for members)
            [
                'topic' => 'membership-subscription',
                'question' => 'What are the different membership levels?',
                'answer' => "We offer 5 membership stages:\n\n1. **Starter** (Free)\n   - Basic access, limited features\n\n2. **Member** (₹999/year)\n   - Full access, basic commissions\n\n3. **Promoter** (₹2,999/year)\n   - Higher commissions, marketing tools\n\n4. **Advisor** (₹5,999/year)\n   - Premium rates, dedicated support\n\n5. **Mentor** (₹9,999/year)\n   - Maximum benefits, profit sharing\n\nUpgrade anytime from **Settings** > **Membership**.",
                'tags' => ['membership', 'plans', 'upgrade'],
                'keywords' => ['membership plans', 'upgrade', 'subscription'],
            ],
            [
                'topic' => 'membership-subscription',
                'question' => 'How do I upgrade my membership?',
                'answer' => "To upgrade your membership:\n\n1. Go to **Settings** > **Membership**\n2. View available plans and benefits\n3. Select your desired plan\n4. Click **Upgrade Now**\n5. Complete the payment\n6. Benefits activate instantly!\n\n**Pro Tip:** Upgrades are prorated - you only pay the difference for remaining days.",
                'tags' => ['upgrade', 'membership', 'subscription'],
                'keywords' => ['upgrade membership', 'change plan', 'better plan'],
            ],

            // Team & Referrals (for promoters and above)
            [
                'topic' => 'team-referrals',
                'question' => 'How do I view my team structure?',
                'answer' => "To view your team genealogy:\n\n1. Go to **Team** > **Genealogy**\n2. You'll see a tree view of your network\n3. Use filters to search specific members\n4. Click on any member to see their details\n\n**Available Views:**\n- Tree View (hierarchical)\n- List View (tabular)\n- Analytics View (statistics)\n\n**Export:** Download your team data as CSV from the Analytics section.",
                'tags' => ['team', 'genealogy', 'network'],
                'keywords' => ['view team', 'genealogy', 'downline', 'network'],
            ],
            [
                'topic' => 'team-referrals',
                'question' => 'How do I share my referral link?',
                'answer' => "Your unique referral link can be found in multiple places:\n\n1. **Dashboard** - Quick copy button\n2. **Team** > **Refer & Earn** - Full sharing options\n\n**Sharing Options:**\n- Copy link to clipboard\n- Share via WhatsApp\n- Share via Email\n- Download QR code\n- Share referral code directly\n\n**Tip:** Personalized landing pages convert better - customize yours in Settings!",
                'tags' => ['referral', 'share', 'invite'],
                'keywords' => ['referral link', 'share link', 'invite friends'],
            ],

            // KYC (for all)
            [
                'topic' => 'kyc-verification',
                'question' => 'What documents are required for KYC?',
                'answer' => "**Required Documents:**\n\n**Identity Proof (any one):**\n- Aadhaar Card\n- PAN Card\n- Voter ID\n- Passport\n- Driving License\n\n**Address Proof (any one):**\n- Aadhaar Card\n- Utility Bill (last 3 months)\n- Bank Statement (last 3 months)\n- Passport\n\n**Additional:**\n- Clear selfie/photo\n- Bank account proof (passbook/cheque)\n\n**Tips:**\n- Ensure documents are clear and readable\n- All four corners should be visible\n- File size should be under 5MB",
                'tags' => ['kyc', 'documents', 'verification'],
                'keywords' => ['kyc documents', 'verification documents', 'id proof'],
            ],
            [
                'topic' => 'kyc-verification',
                'question' => 'How long does KYC verification take?',
                'answer' => "KYC verification timeline:\n\n| Status | Expected Time |\n|--------|---------------|\n| Document Review | 24-48 hours |\n| Identity Verification | 1-2 business days |\n| Address Verification | 1-2 business days |\n| Final Approval | Same day after verification |\n\n**Total:** Usually 2-4 business days\n\n**Speed up verification:**\n- Submit clear, complete documents\n- Ensure all information matches\n- Respond promptly to any queries\n\nCheck status in **Settings** > **KYC**.",
                'tags' => ['kyc', 'verification', 'time'],
                'keywords' => ['kyc time', 'verification time', 'how long kyc'],
            ],

            // Admin specific FAQs
            [
                'topic' => 'general-inquiry',
                'question' => 'How do I access the admin panel?',
                'answer' => "Admin panel access:\n\n1. Go to `/admin` on the main website\n2. Enter your admin credentials\n3. Complete 2FA if enabled\n\n**Admin Capabilities:**\n- User management\n- Transaction oversight\n- Content management\n- Reports and analytics\n- System settings\n\n**Note:** Access levels vary by admin role. Contact SuperAdmin for permission changes.",
                'tags' => ['admin', 'panel', 'access'],
                'keywords' => ['admin access', 'admin panel', 'admin login'],
            ],
        ];

        $count = 0;
        foreach ($faqs as $faq) {
            $topicSlug = $faq['topic'];
            unset($faq['topic']);

            if (! isset($topics[$topicSlug])) {
                $this->command->warn("Topic not found: {$topicSlug}");

                continue;
            }

            HelpdeskFaq::updateOrCreate(
                ['url' => Str::slug(Str::limit($faq['question'], 50, ''))],
                array_merge($faq, [
                    'topic_id' => $topics[$topicSlug],
                    'views' => rand(10, 500),
                    'helpful_count' => rand(5, 100),
                    'not_helpful_count' => rand(0, 10),
                ])
            );
            $count++;
        }

        $this->command->info("Seeded {$count} helpdesk FAQs.");
    }
}

