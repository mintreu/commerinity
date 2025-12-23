<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('question');
            $table->text('answer');
            $table->foreignId('topic_id')->constrained('helpdesk_topics')->cascadeOnUpdate()->cascadeOnDelete();
            $table->nullableMorphs('audience'); // For role-specific FAQs (nullable for public FAQs)
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->integer('views')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            $table->json('tags')->nullable();
            $table->json('keywords')->nullable();
            $table->timestamps();

            $table->index(['active', 'order']);
            $table->index('topic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_faqs');
    }
};
