<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesks', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, awaiting_reply, in_progress, resolved, closed
            $table->foreignId('topic_id')->constrained('helpdesk_topics')->cascadeOnDelete();
            $table->morphs('authorable'); // User, Guest, Admin who created the ticket
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('chatbot_session_id')->nullable();
            $table->json('chatbot_context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->tinyInteger('satisfaction_rating')->nullable(); // 1-5 stars
            $table->text('satisfaction_feedback')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('topic_id');
            // morphs('authorable') already creates this index automatically
            $table->index('assigned_to');
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesks');
    }
};
