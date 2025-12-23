<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kycs', function (Blueprint $table) {
            $table->id();
            $table->morphs('kycable');
            $table->string('kyc_type')->default('personal');
            $table->string('company_name')->nullable();
            $table->string('company_type')->nullable();
            $table->string('pan_number', 10)->unique();
            $table->string('aadhaar_number', 12)->nullable();
            $table->string('gst_number', 15)->nullable()->unique();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('pan_number');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kycs');
    }
};
