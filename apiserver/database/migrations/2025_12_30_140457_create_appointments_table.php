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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 20)->unique();
            $table->nullableMorphs('creator');
            $table->foreignId('advisor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('mentor_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('attendee_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        $table->string('title');
        $table->text('agenda')->nullable();
        $table->string('meeting_mode')->default('online');
        $table->string('meeting_link')->nullable();
        $table->timestamp('start_at');
        $table->timestamp('end_at')->nullable();
        $table->string('status')->default('pending')->index();
        $table->timestamps();
    });

        Schema::create('appointment_participants', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 20)->unique();
            $table->foreignId('appointment_id')
                ->constrained('appointments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('role')->default('participant');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_participants');
        Schema::dropIfExists('appointments');
    }
};
