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
        Schema::create('user_level_task_progress', function (Blueprint $table) {
            $table->id();
            $table->integer('score')->default(0);  //  score that add when task complete
            $table->foreignId('level_task_id')->constrained('level_tasks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->morphs('player');
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_level_task_progress');
    }
};
