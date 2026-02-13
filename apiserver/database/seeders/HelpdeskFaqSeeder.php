<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Support\HelpdeskFaq;
use App\Models\Support\HelpdeskTopic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class HelpdeskFaqSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Seeding production FAQ dataset...');

        $topics = HelpdeskTopic::query()->pluck('id', 'slug');
        $count = 0;

        foreach ($this->faqDataset() as $topicSlug => $rows) {
            $topicId = $topics->get($topicSlug);
            if (! $topicId) {
                $this->command->warn("FAQ topic not found: {$topicSlug}");
                continue;
            }

            $order = 0;
            foreach ($rows as $row) {
                $order++;
                $question = (string) Arr::get($row, 'question');
                $url = Arr::get($row, 'url') ?: Str::slug(Str::limit($question, 90, ''));
                $seedTag = "seed:{$topicSlug}";
                $tags = array_values(array_unique(array_merge(
                    Arr::get($row, 'tags', []),
                    [$seedTag]
                )));

                HelpdeskFaq::updateOrCreate(
                    ['url' => $url],
                    [
                        'question' => $question,
                        'answer' => (string) Arr::get($row, 'answer'),
                        'topic_id' => $topicId,
                        'active' => true,
                        'order' => Arr::get($row, 'order', $order),
                        'views' => (int) Arr::get($row, 'views', random_int(25, 350)),
                        'tags' => $tags,
                        'keywords' => Arr::get($row, 'keywords', []),
                        // Keep polymorphic audience nullable for guest/general scope.
                        'audience_type' => null,
                        'audience_id' => null,
                    ]
                );

                $count++;
            }
        }

        $this->command->info("Seeded/updated {$count} FAQs across guest + all user types.");
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function faqDataset(): array
    {
        return [
            // Public / guest footer FAQ
            'guest-faq' => [
                [
                    'url' => 'guest-how-to-register',
                    'question' => 'How can I create an account quickly?',
                    'answer' => "Open Register, enter your mobile or email, verify OTP, and complete profile basics. You can start shopping immediately after verification.",
                    'tags' => ['guest', 'registration'],
                    'keywords' => ['register', 'signup', 'new account'],
                ],
                [
                    'url' => 'guest-payment-methods',
                    'question' => 'What payment methods are supported?',
                    'answer' => "We support UPI, net banking, debit/credit cards, and wallet payments. Availability may vary based on checkout rules and region.",
                    'tags' => ['guest', 'payments'],
                    'keywords' => ['payment options', 'upi', 'cards'],
                ],
                [
                    'url' => 'guest-delivery-time',
                    'question' => 'How long does delivery usually take?',
                    'answer' => "Most orders are delivered within 2-7 business days depending on serviceability, seller dispatch speed, and shipping method.",
                    'tags' => ['guest', 'shipping'],
                    'keywords' => ['delivery', 'shipping time', 'order timeline'],
                ],
                [
                    'url' => 'guest-returns-refunds',
                    'question' => 'Can I return a product if something is wrong?',
                    'answer' => "Yes. Return eligibility depends on product return policy. Approved returns are processed according to the return/refund policy timeline.",
                    'tags' => ['guest', 'returns'],
                    'keywords' => ['return policy', 'refund'],
                ],
            ],

            // Regular user
            'regular-user-faq' => [
                [
                    'url' => 'regular-track-order-status',
                    'question' => 'Where can I track my order and shipment status?',
                    'answer' => "Go to Dashboard > Orders, open the order details, and check live fulfillment and shipping milestones.",
                    'tags' => ['regular', 'orders'],
                    'keywords' => ['track order', 'shipment'],
                ],
                [
                    'url' => 'regular-manage-addresses',
                    'question' => 'How do I update or switch delivery addresses?',
                    'answer' => "Use Address Management to add, edit, or mark default addresses. New orders will use the selected delivery address.",
                    'tags' => ['regular', 'address'],
                    'keywords' => ['address', 'delivery address'],
                ],
                [
                    'url' => 'regular-upgrade-membership',
                    'question' => 'How do I upgrade from regular to membership plans?',
                    'answer' => "Open Subscription from dashboard, compare plans, and complete payment. Benefits activate right after successful upgrade.",
                    'tags' => ['regular', 'upgrade'],
                    'keywords' => ['membership', 'upgrade plan'],
                ],
            ],

            // Member user
            'member-faq' => [
                [
                    'url' => 'member-commission-credit-cycle',
                    'question' => 'When are member commissions credited?',
                    'answer' => "Direct commissions may reflect quickly after qualifying events, while periodic earnings are processed on scheduled settlement cycles.",
                    'tags' => ['member', 'commission'],
                    'keywords' => ['commission cycle', 'earning credit'],
                ],
                [
                    'url' => 'member-wallet-withdrawal-requirements',
                    'question' => 'What is required before withdrawing wallet balance?',
                    'answer' => "Complete KYC, configure payout details, and keep wallet security settings updated. Minimum withdrawal limits may apply.",
                    'tags' => ['member', 'wallet'],
                    'keywords' => ['withdrawal', 'wallet kyc'],
                ],
                [
                    'url' => 'member-referral-visibility',
                    'question' => 'How can I see referral and team performance?',
                    'answer' => "Dashboard analytics and network modules show referral counts, active members, and related performance indicators.",
                    'tags' => ['member', 'referrals'],
                    'keywords' => ['network', 'team performance'],
                ],
            ],

            // Promoter user
            'promoter-faq' => [
                [
                    'url' => 'promoter-team-growth-metrics',
                    'question' => 'Where can I track promoter team growth?',
                    'answer' => "Use promoter dashboard charts and network reports to monitor direct growth, active team size, and contribution trends.",
                    'tags' => ['promoter', 'team'],
                    'keywords' => ['team growth', 'promoter analytics'],
                ],
                [
                    'url' => 'promoter-challenge-rewards',
                    'question' => 'How do promoter challenges and rewards work?',
                    'answer' => "Challenges define goals and reward rules. When criteria are met within timeline, benefits are credited based on challenge policy.",
                    'tags' => ['promoter', 'challenges'],
                    'keywords' => ['challenge', 'promoter rewards'],
                ],
                [
                    'url' => 'promoter-share-referral-assets',
                    'question' => 'How should I share referral links effectively?',
                    'answer' => "Use official share assets, your referral code/link, and campaign-safe messaging. Avoid spam channels to protect account health.",
                    'tags' => ['promoter', 'referral'],
                    'keywords' => ['share link', 'referral code'],
                ],
            ],

            // Advisor user
            'advisor-faq' => [
                [
                    'url' => 'advisor-appointment-handling',
                    'question' => 'How are advisor appointments managed?',
                    'answer' => "Use the appointments module to schedule, reschedule, and complete sessions. Keep slot availability accurate to avoid conflicts.",
                    'tags' => ['advisor', 'appointments'],
                    'keywords' => ['advisor schedule', 'appointments'],
                ],
                [
                    'url' => 'advisor-client-program-tracking',
                    'question' => 'Where can I monitor client programs and progress?',
                    'answer' => "Advisor dashboard provides participant status, current program activity, and timeline visibility for each active engagement.",
                    'tags' => ['advisor', 'programs'],
                    'keywords' => ['client progress', 'program tracking'],
                ],
                [
                    'url' => 'advisor-income-payouts',
                    'question' => 'How are advisor payouts calculated and released?',
                    'answer' => "Payouts follow defined settlement rules from completed eligible activities and are reflected in wallet/payout summaries.",
                    'tags' => ['advisor', 'payout'],
                    'keywords' => ['advisor income', 'payout'],
                ],
            ],

            // Mentor user
            'mentor-faq' => [
                [
                    'url' => 'mentor-session-planning',
                    'question' => 'How should mentors plan and publish sessions?',
                    'answer' => "Create or update programs with clear outcomes, schedule windows, and participant criteria before publishing.",
                    'tags' => ['mentor', 'sessions'],
                    'keywords' => ['mentor session', 'program planning'],
                ],
                [
                    'url' => 'mentor-program-participants',
                    'question' => 'How can I track participant completion in mentor programs?',
                    'answer' => "Use program insights to review participant status, completion indicators, and engagement signals.",
                    'tags' => ['mentor', 'programs'],
                    'keywords' => ['participant completion', 'mentor dashboard'],
                ],
                [
                    'url' => 'mentor-earnings-overview',
                    'question' => 'Where do mentor earnings and pending payouts appear?',
                    'answer' => "Mentor dashboard earnings cards and wallet/payout sections show credited and pending amounts with recent activity.",
                    'tags' => ['mentor', 'earnings'],
                    'keywords' => ['mentor payouts', 'mentor earnings'],
                ],
            ],

            // Admin / operations reference
            'admin-faq' => [
                [
                    'url' => 'admin-access-and-security',
                    'question' => 'How should admins secure their panel access?',
                    'answer' => "Use strong credentials, 2FA, least-privilege roles, and periodic session/device review for operational security.",
                    'tags' => ['admin', 'security'],
                    'keywords' => ['admin security', '2fa'],
                ],
                [
                    'url' => 'admin-user-resolution-flow',
                    'question' => 'How should admins handle user support escalations?',
                    'answer' => "Validate ticket context, review activity timeline, check transaction state, and respond with auditable resolution notes.",
                    'tags' => ['admin', 'support'],
                    'keywords' => ['escalation', 'support workflow'],
                ],
                [
                    'url' => 'admin-content-and-faq-maintenance',
                    'question' => 'How are FAQ updates rolled out safely?',
                    'answer' => "Update in staging first, verify topic coverage for each audience, then publish with versioned change logs.",
                    'tags' => ['admin', 'faq'],
                    'keywords' => ['faq maintenance', 'content governance'],
                ],
            ],

            // Keep legacy/functional topic coverage
            'getting-started' => [
                [
                    'url' => 'getting-started-account-verification',
                    'question' => 'Why is account verification recommended right after signup?',
                    'answer' => "Verification unlocks secure account recovery, faster approvals, and smoother transaction flows.",
                    'tags' => ['guest', 'onboarding'],
                    'keywords' => ['verify account', 'onboarding'],
                ],
                [
                    'url' => 'getting-started-first-order',
                    'question' => 'What should I do before placing my first order?',
                    'answer' => "Complete address details, verify contact, and review shipping/return policy to avoid delivery friction.",
                    'tags' => ['guest', 'orders'],
                    'keywords' => ['first order', 'new user'],
                ],
            ],
            'general-inquiry' => [
                [
                    'url' => 'general-contact-support-time',
                    'question' => 'How quickly does support reply?',
                    'answer' => "Support response windows depend on queue severity, but urgent payment/security issues are prioritized.",
                    'tags' => ['guest', 'support'],
                    'keywords' => ['support response', 'helpdesk time'],
                ],
            ],
        ];
    }
}
