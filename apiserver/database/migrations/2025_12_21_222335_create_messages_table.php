<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Conversation reference
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');

            // Sender (user or admin)
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_admin_id')->nullable()->constrained('admins')->onDelete('cascade');

            // Message content
            $table->text('body');
            $table->string('type')->default('text'); // text, image, file, system

            // Attachments (JSON array of file paths)
            $table->json('attachments')->nullable();

            // Read status
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('conversation_id');
            $table->index('sender_user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
