<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('helpdesk_id')->constrained('helpdesks')->cascadeOnDelete();
            $table->text('message');
            $table->nullableMorphs('authorable'); // Nullable for bot messages
            $table->enum('source', ['human', 'bot'])->default('human');
            $table->boolean('is_internal')->default(false);
            $table->json('bot_metadata')->nullable();
            $table->json('attachment')->nullable();
            $table->timestamps();

            $table->index(['helpdesk_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_conversations');
    }
};
