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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 20)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('reward_type')->default('coin');
            $table->unsignedInteger('reward_value')->default(0);
            $table->string('goal_type')->default('custom');
            $table->unsignedInteger('goal_value')->default(0);
            $table->nullableMorphs('targetable');
            $table->string('target_user_type')->nullable();
            $table->foreignId('target_level_id')
                ->nullable()
                ->constrained('levels')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('target_stage_id')
                ->nullable()
                ->constrained('stages')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
