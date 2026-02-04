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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 20)->unique();
            $table->nullableMorphs('creator');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->timestamps();
        });

        Schema::create('program_participants', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 20)->unique();
            $table->foreignId('program_id')
                ->constrained('programs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('role')->default('participant');
            $table->string('status')->default('invited')->index();
            $table->timestamp('joined_at')->nullable();
            $table->foreignId('invited_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_participants');
        Schema::dropIfExists('programs');
    }
};
