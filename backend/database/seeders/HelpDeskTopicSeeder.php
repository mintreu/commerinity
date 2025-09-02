<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Mintreu\LaravelHelpdesk\Models\HelpdeskTopic;

class HelpDeskTopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allowedTopics = [
            'Account',
            'Order',
            'Subscription',
            'Recruitment',
            'Rewards',
            'Team',
            'Payout',
            'Others'
        ];

        foreach ($allowedTopics as $topic)
        {
            HelpdeskTopic::create([
                'name' => $name= $topic,
                'slug' => Str::slug($name),
                'tickable' => true,
                'description' => fake()->text,
                'active'    => true,
            ]);
        }



    }


}
