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
        Schema::create('tax_codes', function (Blueprint $table) {
            $table->id();

            // Core Identification
            $table->string('code', 10)->unique()->comment('HSN or SAC code');
            $table->enum('type', ['goods', 'services'])->default('goods')->comment('Type of classification');
            $table->string('description')->nullable()->comment('Description of goods or services');

            // GST Components
            $table->decimal('cgst_rate', 5, 2)->default(0.00)->comment('Central GST rate (%)');
            $table->decimal('sgst_rate', 5, 2)->default(0.00)->comment('State GST rate (%)');
            $table->decimal('igst_rate', 5, 2)->default(0.00)->comment('Integrated GST rate (%)');
            $table->decimal('cess_rate', 5, 2)->default(0.00)->comment('Additional CESS rate (%)');

            // Status and Validity
            $table->boolean('is_active')->default(true)->comment('Indicates if this tax code is active');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_codes');
    }
};
