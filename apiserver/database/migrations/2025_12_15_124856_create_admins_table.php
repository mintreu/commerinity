<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile', 15)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('type')->default('executive');
            $table->string('status')->default('active');

            // Hierarchy
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(5);

            // Profit sharing
            $table->decimal('profit_share_percent', 5, 2)->default(0.00);
            $table->boolean('profit_share_active')->default(true);

            // Settings
            $table->string('locale', 5)->default('en');
            $table->json('preferences')->nullable();

            // Security
            $table->string('two_factor_secret', 100)->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['type', 'status']);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
