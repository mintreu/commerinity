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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->unique();
            $table->integer('validate_years')->default(1);
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('status')->default(false);
            $table->integer('team_member_limit')->default(0);
            $table->decimal('joining_bonus', 10, 2)->default(0.00)->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
