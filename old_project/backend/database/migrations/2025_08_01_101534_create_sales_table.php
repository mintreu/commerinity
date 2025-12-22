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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('uuid')->unique();
            $table->string('description')->nullable();
            $table->date('starts_from')->nullable();
            $table->date('ends_till')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('condition_type')->default(1);
            $table->json('conditions')->nullable();
            $table->boolean('end_other_rules')->default(false);
            $table->string('action_type')->nullable();
            $table->integer('discount_amount')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });


        Schema::create('sale_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnUpdate()->cascadeOnDelete();

            // polymorphic columns
            $table->morphs('target'); // creates target_id and target_type

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sale_targets');
    }
};
