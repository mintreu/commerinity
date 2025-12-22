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
        Schema::create('kycs', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')->unique();
            $table->string('user_type')->default(\App\Casts\KycTypeCast::INDIVIDUAL->value);
            $table->string('company_name')->nullable();
            $table->string('company_type')->nullable();
            $table->boolean('has_tax')->default(false);
            $table->string('gst')->nullable()->index();
            $table->string('pan')->nullable()->index();
            $table->string('aadhaar')->index();
            $table->json('utility_bills')->nullable();
            $table->morphs('kycable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kycs');
    }
};
