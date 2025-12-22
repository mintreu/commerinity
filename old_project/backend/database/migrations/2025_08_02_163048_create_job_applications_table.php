<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mintreu\LaravelRecruitment\Casts\JobApplicationStatusCast;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->morphs('applicant');

            $table->string('guardian_name');

            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('amount')->default(0);

            // Form Related
            $table->json('educations')->nullable();
            $table->json('skills')->nullable();
            $table->json('experiences')->nullable();

            // Reference By
            $table->string('reference_name')->nullable();
            $table->string('reference_contact')->nullable();

            $table->dateTime('submitted_at')->nullable();

            $table->foreignId('recruitment_id')->nullable()->constrained('recruitments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->cascadeOnUpdate()->nullOnDelete();

            $table->string('status')->default(JobApplicationStatusCast::AWAITING_PAYMENT->value);
            $table->text('status_feedback')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
