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
        Schema::create('users', function (Blueprint $table) {
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

            $table->string('referral_code')->nullable();
            $table->unique('referral_code', 'users_referral_code_unique');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->nullableMorphs('originator'); // Record Created By Model (company recruited Advisor job related

            $table->text('bio')->nullable();
            $table->string('gender')->default(\Mintreu\Toolkit\Casts\GenderCast::OTHER->value);
            $table->date('dob')->nullable();

            $table->string('type')
                ->default(\App\Casts\AuthTypeCast::REGULAR->value)
                ->index('users_type_index');

            $table->string('status')
                ->default(\App\Casts\AuthStatusCast::DRAFT->value)
                ->index('users_status_index');

            $table->text('status_feedback')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('user_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('depth'); // 0 = self, 1 = direct parent, etc.

            $table->primary(['ancestor_id', 'user_id']);

            $table->foreign('ancestor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index('depth');
        });





        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
