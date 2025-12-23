<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Heroicon name (e.g., heroicon-o-question-mark-circle)
            $table->boolean('tickable')->default(true);
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['active', 'tickable']);
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_topics');
    }
};
