<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();

            // Recruitment reference
            $table->foreignId('recruitment_id')->constrained()->cascadeOnDelete();

            // Applicant (polymorphic - User or external candidate)
            $table->morphs('applicant');

            // Guardian/Parent info (required for verification)
            $table->string('guardian_name');

            // Address reference
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();

            // Qualifications (JSON for flexibility)
            $table->json('educations')->nullable(); // [{degree, institution, year, percentage}]
            $table->json('skills')->nullable(); // [{skill, description}]
            $table->json('experiences')->nullable(); // [{company, role, duration, description}]

            // Reference info
            $table->string('reference_name')->nullable();
            $table->string('reference_contact')->nullable();

            // Payment
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('amount')->default(0); // in paisa
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();

            // Status workflow
            $table->string('status')->default('draft');
            // draft -> awaiting_payment -> submitted -> under_review -> accepted/rejected/withdrawn
            $table->text('status_feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Import tracking (for bulk imports)
            $table->string('import_batch_id')->nullable();
            $table->json('import_data')->nullable(); // Original import row data

            $table->timestamps();
            $table->softDeletes();

            $table->index(['recruitment_id', 'status']);
            // morphs() already creates index for applicant_type + applicant_id
            $table->index('import_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
