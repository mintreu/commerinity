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
        Schema::create('helpdesk_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('question');
            $table->text('answer');
            $table->foreignId('topic_id')->constrained('helpdesk_topics')->cascadeOnUpdate()->cascadeOnDelete();

            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->text('tags')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_faqs');
    }
};
