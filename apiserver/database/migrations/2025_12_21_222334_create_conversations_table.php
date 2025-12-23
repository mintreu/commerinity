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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Participants (for 1-on-1 or broadcast)
            $table->foreignId('user_one_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('user_two_id')->nullable()->constrained('users')->onDelete('cascade');

            // For broadcast messages from admin
            $table->boolean('is_broadcast')->default(false);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');

            // Conversation metadata
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedBigInteger('unread_count_user_one')->default(0);
            $table->unsignedBigInteger('unread_count_user_two')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_one_id', 'user_two_id']);
            $table->index('is_broadcast');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
