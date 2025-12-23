<?php

declare(strict_types=1);

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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();

            // Contact Information
            $table->string('title')->nullable();
            $table->string('person_name');
            $table->string('person_email')->nullable()->index();
            $table->string('person_mobile', 15);
            $table->string('alternate_contact', 15)->nullable();

            // Address Type
            $table->enum('type', ['home', 'office', 'billing', 'shipping', 'warehouse', 'store'])
                ->default('home')
                ->index();

            // Address Details
            $table->text('address_1');
            $table->text('address_2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city');
            $table->string('postal_code', 10)->index();

            // Geo-Hierarchical References
            $table->foreignId('block_id')
                ->nullable()
                ->constrained('blocks')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('state_code', 10)->nullable();
            $table->foreign('state_code')
                ->references('code')
                ->on('states')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('country_code', 2)->default('IN');
            $table->foreign('country_code')
                ->references('iso_code_2')
                ->on('countries')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Optional: Direct lat/long (overrides block location)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Metadata
            $table->boolean('default')->default(false);
            $table->unsignedInteger('priority')->default(1);
            $table->string('pickup_location')->nullable();

            // Polymorphic Ownership (null = standalone address)
            $table->nullableMorphs('addressable');

            $table->timestamps();
            $table->softDeletes();

            // Additional indexes (nullableMorphs already creates addressable index)
            $table->index(['type', 'default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
