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

        $appSenderId = Str::of((string) config('app.name'))
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->substr(0, 20)
            ->toString();
        $appSenderId = $appSenderId !== '' ? $appSenderId : 'APPNAME';

        $defaultSenderId = (string) ($integration->getCredential('sender_id')
            ?? config('services.sms.fast2sms.dlt_sender_id')
            ?? config('services.sms.fast2sms.sender_id')
            ?? $appSenderId);

        $defaultEntityId = (string) ($integration->getCredential('entity_id')
            ?? config('services.sms.fast2sms.entity_id')
            ?? '');

        $templates = [
            [
                'name' => 'OTP General',
                'slug' => 'otp-general',
                'message_id' => 'OTP_GENERAL_001',
                'category' => 'otp',
                'content' => 'Your OTP is {#number#} for {#purpose#}. Valid for 10 minutes. Do not share it. - {#app_name#}',
                'variables' => ['number', 'purpose', 'app_name'],
                'is_dlt_approved' => true,
            ],
            [
                'name' => 'OTP Transaction',
                'slug' => 'otp-transaction',
                'message_id' => 'OTP_TRANSACTION_001',
                'category' => 'otp',
                'content' => 'Your OTP is {#number#} for {#purpose#} Rs {#amount#}. Valid for 10 minutes. Do not share it. - {#app_name#}',
                'variables' => ['number', 'purpose', 'amount', 'app_name'],
                'is_dlt_approved' => true,
            ],
            [
                'name' => 'Wallet Update',
                'slug' => 'wallet-update',
                'message_id' => 'TXN_WALLET_UPDATE_001',
                'category' => 'transactional',
                'content' => 'Rs {#amount#} has been {#action#} to your wallet. Available balance Rs {#balance#}. - {#app_name#}',
                'variables' => ['amount', 'action', 'balance', 'app_name'],
                'is_dlt_approved' => true,
            ],
            [
                'name' => 'Job Application Received',
                'slug' => 'job-application-received',
                'message_id' => 'TXN_JOB_RECEIVED_001',
                'category' => 'transactional',
                'content' => 'Dear {#name#}, application {#application_id#} received. We will contact you soon. - {#app_name#}',
                'variables' => ['name', 'application_id', 'app_name'],
                'is_dlt_approved' => true,
            ],
            [
                'name' => 'Job Interview Scheduled',
                'slug' => 'job-interview-scheduled',
                'message_id' => 'TXN_JOB_INTERVIEW_001',
                'category' => 'transactional',
                'content' => 'Dear {#name#}, interview for application {#application_id#} on {#interview_datetime#} at {#interview_area#}. Venue: {#venue_address#}. - {#app_name#}',
                'variables' => ['name', 'application_id', 'interview_datetime', 'interview_area', 'venue_address', 'app_name'],
                'is_dlt_approved' => true,
            ],
            [
                'name' => 'Withdrawal Status',
                'slug' => 'withdrawal-status',
                'message_id' => 'TXN_WITHDRAWAL_STATUS_001',
                'category' => 'transactional',
                'content' => 'Withdrawal Rs {#amount#} is {#status#}. Ref {#reference#}. - {#app_name#}',
                'variables' => ['amount', 'status', 'reference', 'app_name'],
                'is_dlt_approved' => true,
            ],
            [
                'name' => 'Subscription Status',
                'slug' => 'subscription-status',
                'message_id' => 'TXN_SUBSCRIPTION_STATUS_001',
                'category' => 'transactional',
                'content' => 'Your subscription is {#status#}. Plan {#plan#}. Ref {#reference#}. - {#app_name#}',
                'variables' => ['status', 'plan', 'reference', 'app_name'],
                'is_dlt_approved' => true,
            ],
        ];

        foreach ($templates as $data) {
            $template = SmsTemplate::withTrashed()->firstOrNew(['slug' => $data['slug']]);

            if ($template->trashed()) {
                $template->restore();
            }

            $template->integration_id = $integration->id;
            $template->name = $data['name'];
            $template->slug = $data['slug'];
            $template->message_id = $template->message_id ?: $data['message_id'];
            $template->entity_id = $template->entity_id ?: ($defaultEntityId ?: null);
            $template->sender_id = $template->sender_id ?: $defaultSenderId;
            $template->content = $data['content'];
            $template->variables = $data['variables'];
            $template->variable_count = count($data['variables']);
            $template->category = $data['category'];
            $template->language = 'en';
            $template->is_active = true;
            $template->is_dlt_approved = $template->exists
                ? ($template->is_dlt_approved || (bool) $data['is_dlt_approved'])
                : (bool) $data['is_dlt_approved'];

            $template->save();
        }

        $this->command->info('Seeded '.count($templates).' SMS templates.');
    }
}