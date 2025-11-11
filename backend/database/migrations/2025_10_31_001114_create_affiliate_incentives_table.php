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
        Schema::create('affiliate_incentives', function (Blueprint $table) {
            $table->id();
            // Who receives the incentive
            $table->morphs('incentivable'); // incentivable_id + incentivable_type

            // Who generated the incentive (optional)
            $table->morphs('sourceable');   // sourceable_id + sourceable_type

            $table->string('type');   // handled by Enum cast
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_incentives');
    }
};
