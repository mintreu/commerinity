<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskPriorityCast;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskStatusCast;
use Mintreu\LaravelHelpdesk\Models\HelpdeskTopic;

class HelpDeskTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(1);
        $topic = HelpdeskTopic::where('tickable',true)->first();

        $user->tickets()->create([
            'title' => fake()->text,
            'description'   => fake()->realText,
            'topic_id'  => $topic->id,
            'priority'  => HelpdeskPriorityCast::MEDIUM,
            'status' => HelpdeskStatusCast::OPEN,
        ]);

        $user->tickets()->create([
            'title' => fake()->text,
            'description'   => fake()->realText,
            'topic_id'  => $topic->id,
            'priority'  => HelpdeskPriorityCast::HIGH,
            'status' => HelpdeskStatusCast::OPEN,
        ]);

    }
}
