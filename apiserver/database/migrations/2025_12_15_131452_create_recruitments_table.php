<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->string('title'); // Job title
            $table->text('description')->nullable();
            $table->string('role'); // advisor, trainer, etc.
            $table->string('location')->nullable();
            $table->string('employment_type')->default('full_time'); // full_time, part_time, contractual, internship
            $table->unsignedInteger('vacancy')->default(1);
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();

            // Fees
            $table->boolean('is_payable')->default(false);
            $table->unsignedBigInteger('fees')->default(0); // in paisa

            // Requirements
            $table->json('requirements')->nullable(); // Array of requirement strings
            $table->json('benefits')->nullable(); // Array of benefit strings
            $table->json('eligibility')->nullable(); // min_age, max_age, education, etc.

            // Status
            $table->string('status')->default('draft'); // draft, published, closed, archived
            $table->text('status_feedback')->nullable();

            // Media (using Spatie Media Library)
            // display_image, info_pdf collections

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'open_date', 'close_date']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
