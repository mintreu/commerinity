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
            $table->string('uuid', 20)->nullable();
            $table->unique('uuid', 'users_uuid_unique');
            $table->string('name');

            // Identity - Email nullable (optional), Mobile REQUIRED (enforced via onboarding)
            // Users can register with email for testing, but must add verified mobile before completing onboarding
            $table->string('email')->nullable();
            $table->unique('email', 'users_email_unique');
            $table->timestamp('email_verified_at')->nullable();

            $table->string('mobile', 15)->nullable(); // Nullable for email registration, but required before onboarding completes
            $table->unique('mobile', 'users_mobile_unique');
            $table->timestamp('mobile_verified_at')->nullable();

            $table->string('password');

            // Affiliate Tree
            $table->string('referral_code', 8)->nullable();
            $table->unique('referral_code', 'users_referral_code_unique');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Originator - Which Agent recruited this member (for salary calculation)
            // Used to track recruitment performance for company-appointed agents
            $table->nullableMorphs('originator');

            // Profile
            $table->text('bio')->nullable();
            $table->string('gender')->default(\App\Casts\GenderCast::OTHER->value);
            $table->date('dob')->nullable();

            // Type & Status
            $table->string('type')
                ->default(\App\Casts\UserTypeCast::REGULAR->value)
                ->index('users_type_index');

            $table->string('status')
                ->default(\App\Casts\UserStatusCast::DRAFT->value)
                ->index('users_status_index');

            $table->text('status_feedback')->nullable();

            // Onboarding
            $table->boolean('onboarded')->default(false);

            $table->rememberToken();
            $table->timestamps();
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
