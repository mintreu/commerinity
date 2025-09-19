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
        Schema::create('naukris', function (Blueprint $table) {
            $table->id();
            // Post Recruitments
            $table->string('name');
            $table->string('url')->unique();
            $table->text('description')->nullable();
            $table->string('role')->nullable(); // eg: engineer, design,

            $table->string('location')->nullable();
            $table->string('employment_type')->default(\Mintreu\LaravelNaukriManager\Casts\NaukriEmploymentTypeCast::INTERNSHIP->value);   //eg: 'Full-time', 'Part-time', 'Contract

            $table->integer('vacancy')->default(1);
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();

            $table->boolean('is_payable')->default(false);
            $table->integer('fees')->nullable();

            $table->string('status')->default(\Mintreu\Toolkit\Casts\PublishableStatusCast::PUBLISHED->value);
            $table->text('status_feedback')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('naukris');
    }
};
