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
        Schema::create('naukri_applications', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();

            // Applicant Profile
            $table->string('name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('gender');
            $table->date('dob');
            $table->string('guardian_name');


            $table->boolean('is_paid')->default(false);

            // Form Related
            $table->json('educations')->nullable();
            $table->json('skills')->nullable();
            $table->json('experience')->nullable();

            // Reference By
            $table->string('reference_name')->nullable();
            $table->string('reference_contact')->nullable();

            $table->dateTime('submitted_at')->nullable();

            $table->foreignId('naukri_id')->nullable()->constrained('naukris')->cascadeOnUpdate()->nullOnDelete();

            $table->string('status')->default('pending');
            $table->text('status_feedback')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('naukri_applications');
    }
};
