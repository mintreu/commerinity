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
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->unique();
            $table->text('desc')->nullable();

            $table->unsignedBigInteger('price')->default(0);


            // For Cart Quantity Range*************************************
            $table->integer('min_per_order')->default(1);
            $table->integer('max_per_order')->default(1);

            $table->integer('max_team_members')->default(0);
            $table->integer('estimated_total_joining_points')->default(0);
            $table->integer('estimated_total_purchasing_points')->default(0);
            $table->json('benefits')->nullable();
            $table->json('accessibility')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
