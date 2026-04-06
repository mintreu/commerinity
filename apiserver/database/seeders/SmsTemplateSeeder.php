<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Models\Sms\SmsTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $approvedMap = $this->buildApprovedTemplateMap();

        $appSenderId = Str::of((string) config('app.name'))
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->substr(0, 20)
            ->toString();
        $appSenderId = $appSenderId !== '' ? $appSenderId : 'APPNAME';

        $defaultSenderId = (string) ($integration->getCredential('sender_id') ?? $appSenderId);

        $defaultEntityId = (string) ($integration->getCredential('entity_id') ?? '');

        // Master template catalog from .codex/sms_template_bodies_grouped.txt
        // Slug is the single source identifier in app code.
        $templates = [
            [
                'name' => 'OTP General',
                'slug' => 'otp-general',
                'category' => 'otp',
                'content' => 'Your OTP is {#number#} for {#purpose#}. Valid for 10 minutes. Do not share it. - {#app_name#}',
                'variables' => ['number', 'purpose'],
                'send_sms' => true,
            ],
            [
                'name' => 'OTP Transaction',
                'slug' => 'otp-transaction',
                'category' => 'otp',
                'content' => 'Your OTP is {#number#} for {#purpose#} Rs {#amount#}. Valid for 10 minutes. Do not share it. - {#app_name#}',
                'variables' => ['number', 'purpose', 'amount'],
                'send_sms' => true,
            ],
            [
                'name' => 'Wallet Update',
                'slug' => 'wallet-update',
                'category' => 'transactional',
                'content' => 'Rs {#amount#} has been {#action#} to your wallet. Available balance Rs {#balance#}. - {#app_name#}',
                'variables' => ['amount', 'action', 'balance'],
                'send_sms' => true,
            ],
            [
                'name' => 'Job Application Received',
                'slug' => 'job-application-received',
                'category' => 'transactional',
                'content' => 'Dear {#name#}, application {#application_id#} received. We will contact you soon. - {#app_name#}',
                'variables' => ['name', 'application_id'],
                'send_sms' => true,
            ],
            [
                'name' => 'Job Interview Scheduled',
                'slug' => 'job-interview-scheduled',
                'category' => 'transactional',
                'content' => 'Dear {#name#}, interview for application {#application_id#} on {#interview_datetime#} at {#interview_area#}. Venue: {#venue_address#}. - {#app_name#}',
                'variables' => ['name', 'application_id', 'interview_datetime', 'interview_area', 'venue_address', 'app_name'],
                'send_sms' => true,
            ],
            [
                'name' => 'Withdrawal Status',
                'slug' => 'withdrawal-status',
                'category' => 'transactional',
                'content' => 'Withdrawal Rs {#amount#} is {#status#}. Ref {#reference#}. - {#app_name#}',
                'variables' => ['amount', 'status', 'reference', 'app_name'],
                'send_sms' => true,
            ],
            [
                'name' => 'Subscription Status',
                'slug' => 'subscription-status',
                'category' => 'transactional',
                'content' => 'Your subscription is {#status#}. Plan {#plan#}. Ref {#reference#}. - {#app_name#}',
                'variables' => ['status', 'plan', 'reference', 'app_name'],
                'send_sms' => true,
            ],
            [
                'name' => 'Order Shipment Status',
                'slug' => 'order-shipment-status',
                'category' => 'transactional',
                'content' => 'Your order {#order_number#} is {#status#}. Please check your account for more information. - {#app_name#}',
                'variables' => ['order_number', 'status', 'app_name'],
                'send_sms' => true,
            ]

            // NOT REQUIRED AS SMS
            //,
//            [
//                'name' => 'KYC Approved',
//                'slug' => 'kyc-approved',
//                'category' => 'transactional',
//                'content' => 'Dear {#name#}, your KYC is approved. You can now access all features. - {#app_name#}',
//                'variables' => ['name', 'app_name'],
//                'send_sms' => false,
//            ],
//            [
//                'name' => 'Referral Bonus',
//                'slug' => 'referral-bonus',
//                'category' => 'service',
//                'content' => 'Your referral joined successfully. Rs {#amount#} bonus credited to your wallet. - {#app_name#}',
//                'variables' => ['amount', 'app_name'],
//                'send_sms' => false,
//            ],
        ];

        $seeded = 0;

        foreach ($templates as $data) {
            $slug = $data['slug'];
            $approved = $approvedMap[$slug] ?? null;

            $messageId = $approved['message_id'] ?? null;
            $dltTemplateId = $approved['dlt_template_id'] ?? null;
            $senderId = $approved['sender_id'] ?? $defaultSenderId;

            $template = SmsTemplate::withTrashed()->firstOrNew(['slug' => $slug]);

            if ($template->trashed()) {
                $template->restore();
            }

            $template->integration_id = $integration->id;
            $template->name = $data['name'];
            $template->slug = $slug;
            $template->message_id = $messageId ?? ($dltTemplateId ?? '');
            $template->dlt_template_id = $dltTemplateId;
            $template->entity_id = $template->entity_id ?: ($defaultEntityId ?: null);
            $template->template_id = $dltTemplateId;
            $template->sender_id = $senderId;
            $template->content = $data['content'];
            $template->variables = $data['variables'];
            $template->variable_count = count($data['variables']);
            $template->category = $data['category'];
            $template->language = 'en';
            $template->is_active = (bool) $data['send_sms'];
            $template->is_dlt_approved = (bool) ($data['send_sms'] && (($messageId !== null && $messageId !== '') || ($dltTemplateId !== null && $dltTemplateId !== '')));
            $template->dlt_approved_at = $template->is_dlt_approved ? ($template->dlt_approved_at ?? now()) : null;
            $template->save();

            $seeded++;
        }

        $this->command->info('Seeded '.$seeded.' SMS templates.');
    }

    /**
     * @return array<string, array{message_id?: string, dlt_template_id?: string, sender_id?: string|null}>
     */
    private function buildApprovedTemplateMap(): array
    {
        return [
            'otp-general' => [
                'message_id' => '212683',
                'dlt_template_id' => '1207177513937547837',
                'sender_id' => 'VRIVIK',
            ],
            'otp-transaction' => [
                'message_id' => '212684',
                'dlt_template_id' => '1207177513720055571',
                'sender_id' => 'VRIVIK',
            ],
            'job-application-received' => [
                'message_id' => '212685',
                'dlt_template_id' => '1207177523082546822',
                'sender_id' => 'VRIVIK',
            ],
            'wallet-update' => [
                'message_id' => '212686',
                'dlt_template_id' => '1207177523443855741',
                'sender_id' => 'VRIVIK',
            ],
        ];
    }
}
