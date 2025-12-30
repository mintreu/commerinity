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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');                              // Admin-friendly name
            $table->string('slug')->unique();                    // URL-friendly identifier

            // Type & Placement
            $table->string('type')->default('native');           // AdTypeCast: native, google, facebook, etc.
            $table->string('placement');                         // AdPlacementCast: where to show
            $table->string('block')->nullable();                 // Optional block ID for special positions

            // Status & Scheduling
            $table->boolean('is_active')->default(true);         // Active/Inactive toggle
            $table->boolean('is_premium')->default(false);       // Premium/Special ad space
            $table->timestamp('starts_at')->nullable();          // Schedule start
            $table->timestamp('ends_at')->nullable();            // Schedule end

            // Native Ad Content (for type=native)
            $table->string('title')->nullable();                 // Ad headline
            $table->text('description')->nullable();             // Ad description
            $table->string('link_url')->nullable();              // Click-through URL
            $table->string('link_text')->nullable();             // CTA button text
            $table->boolean('open_in_new_tab')->default(true);   // Open link in new tab

            // Third-party Ad Code (for google, facebook, custom_html)
            $table->text('ad_code')->nullable();                 // HTML/JS ad code snippet
            $table->string('ad_unit_id')->nullable();            // Google AdSense unit ID, etc.

            // Affiliate (for type=affiliate)
            $table->string('affiliate_network')->nullable();     // e.g., Amazon, Flipkart
            $table->string('affiliate_tracking_id')->nullable(); // Tracking/Publisher ID

            // Display Settings
            $table->unsignedInteger('position')->default(0);     // Order within placement
            $table->json('display_pages')->nullable();           // Specific pages to show on
            $table->json('exclude_pages')->nullable();           // Pages to exclude
            $table->boolean('show_to_guests')->default(true);    // Show to non-logged users
            $table->boolean('show_to_members')->default(true);   // Show to logged-in users
            $table->json('target_user_types')->nullable();       // Specific user types

            // Responsive/Size
            $table->unsignedInteger('width')->nullable();        // Custom width
            $table->unsignedInteger('height')->nullable();       // Custom height
            $table->boolean('is_responsive')->default(true);     // Auto-resize

            // Analytics
            $table->unsignedBigInteger('impressions')->default(0);  // View count
            $table->unsignedBigInteger('clicks')->default(0);       // Click count
            $table->timestamp('last_impression_at')->nullable();
            $table->timestamp('last_click_at')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['placement', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
