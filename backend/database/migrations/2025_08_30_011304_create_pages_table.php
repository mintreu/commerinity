<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            // URL structure
            $table->string('slug')->unique();    // e.g., 'about-us'
            $table->string('prefix')->nullable(); // e.g., 'pages', 'policies'
            $table->string('url')->unique();     // computed: "{prefix}/{slug}" or "{slug}"

            // Page content & metadata
            $table->string('title');
            $table->longText('content')->nullable(); // HTML, Markdown, or simple fallback
            $table->string('layout')->default('default'); // Frontend layout
            $table->string('template')->nullable();      // Blade template if needed
            $table->json('meta')->nullable();           // SEO: title, description, keywords

            // Builder & dynamic sections
            $table->json('sections')->nullable();       // JSON blocks for drag & drop
            $table->text('custom_css')->nullable();     // Page-specific CSS
            $table->text('custom_js')->nullable();      // Page-specific JS

            // Management & control
            $table->boolean('status')->default(true);   // Active/Inactive
            $table->integer('order')->default(0);       // Sorting / menu order

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
