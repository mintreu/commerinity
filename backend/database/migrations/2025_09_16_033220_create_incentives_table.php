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
        Schema::create('incentives', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Who receives the incentive
            $table->morphs('incentivable'); // incentivable_id + incentivable_type

            // Who generated the incentive (optional)
            $table->morphs('sourceable');   // sourceable_id + sourceable_type

            $table->string('type');   // handled by Enum cast
            $table->unsignedInteger('depth')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentives');
    }
};
