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
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('name');

            $table->string('email')->nullable();
            $table->unique('email', 'users_email_unique');

            $table->timestamp('email_verified_at')->nullable();

            $table->string('mobile')->nullable();
            $table->unique('mobile', 'users_mobile_unique');

            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('password');

            $table->string('status')
                ->default(\App\Casts\AuthStatusCast::DRAFT->value)
                ->index('users_status_index');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};
