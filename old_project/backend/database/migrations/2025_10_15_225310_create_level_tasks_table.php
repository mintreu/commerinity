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
        Schema::create('level_tasks', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('url')->unique();
            $table->text('description')->nullable();
            $table->integer('score')->default(0);  // offering score that add when task complete

            $table->integer('min_eligible_score')->default(0);
            $table->json('min_progress')->nullable();  // store props state and scores to matched with
            $table->string('game_type')->nullable();   // use model cast for village, spinner, etc
            $table->foreignId('level_id')->constrained('levels')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_tasks');
    }
};
