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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity')->unsigned()->default(1); // default 1
            $table->integer('discount')->nullable()->default(0); // default 0

            // Morph columns for item and user
            $table->nullableMorphs('cartable'); // For Product or Service
            $table->nullableMorphs('ownerable'); // For User or Customer

            $table->string('guest_id')->nullable()->index();
            $table->string('guest_token')->nullable()->index();
            $table->boolean('is_guest')->default(false)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
