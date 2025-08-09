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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('title');

            $table->string('type')->index();
            $table->string('address_1');
            $table->string('city')->index();
            $table->string('postal_code')->index();

            $table->boolean('default')->default(false);

            $table->string('village')->nullable()->index();
            $table->string('person_name')->nullable();
            $table->string('person_email')->nullable();
            $table->string('person_mobile')->nullable();
            $table->string('alternate_contact')->nullable();
            $table->string('landmark')->nullable();

            $table->string('pickup_location')->nullable();


            $table->unsignedInteger('priority')->default(1);
            $table->nullableMorphs('addressable'); // nullable set for delivery address


            // Live Location
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->foreignId('block_id')->nullable()->constrained('blocks')->cascadeOnUpdate()->nullOnDelete();

            $table->string('state_code')->nullable();
            $table->foreign('state_code')->references('code')->on('states')->onUpdate('cascade')->onDelete('set null');


            $table->string('country_code')->nullable();
            $table->foreign('country_code')->references('iso_code_2')->on('countries')->onUpdate('cascade')->onDelete('set null');

            $table->timestamps();
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
