<?php

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
        Schema::create('helpdesk_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   // Topic name (e.g., "Billing", "Technical Support")
            $table->string('slug')->unique();         // URL-friendly slug for routing / linking
            $table->boolean('tickable')->default(true); // Can this topic be used for tickets (true) or only FAQ (false)
            $table->text('description')->nullable(); // Optional description for the topic
            $table->integer('order')->default(0);    // Sort order for displaying topics
            $table->boolean('active')->default(true); // Enable/disable topic visibility
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_topics');
    }
};
