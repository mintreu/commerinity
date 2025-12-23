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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Content
            $table->string('title');
            $table->text('content');
            $table->string('type')->default('info'); // info, warning, success, promo

            // Display settings
            $table->string('cta_text')->nullable(); // Call to action button text
            $table->string('cta_link')->nullable(); // Call to action link
            $table->string('icon')->nullable(); // Icon name
            $table->string('color')->nullable(); // Custom color

            // Media (optional image/video)
            $table->string('image_url')->nullable();

            // Targeting
            $table->json('target_user_types')->nullable(); // ['regular', 'member', 'promoter', etc.]
            $table->json('target_stages')->nullable(); // Target specific stages
            $table->boolean('is_global')->default(true); // Show to all users

            // Schedule
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher = more prominent

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index('priority');
        });

        // Dismissed notices tracking
        Schema::create('notice_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('dismissed_at');

            $table->unique(['notice_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_dismissals');
        Schema::dropIfExists('notices');
    }
};
