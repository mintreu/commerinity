<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HelpdeskTopic;
use Illuminate\Database\Seeder;

final class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['name' => 'Account & Login', 'slug' => 'account-login', 'description' => 'Issues related to account access, login problems, password reset'],
            ['name' => 'Wallet & Payments', 'slug' => 'wallet-payments', 'description' => 'Questions about wallet balance, transactions, withdrawals'],
            ['name' => 'MLM & Commissions', 'slug' => 'mlm-commissions', 'description' => 'Network questions, commission calculations, genealogy'],
            ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'App errors, bugs, technical issues'],
            ['name' => 'General Inquiry', 'slug' => 'general-inquiry', 'description' => 'General questions and other topics'],
        ];

        foreach ($topics as $index => $topic) {
            HelpdeskTopic::create([
                ...$topic,
                'tickable' => true,
                'active' => true,
                'order' => $index,
            ]);
        }
    }
}
