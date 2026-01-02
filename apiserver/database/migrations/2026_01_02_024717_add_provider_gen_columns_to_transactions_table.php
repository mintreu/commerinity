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
        Schema::table('transactions', function (Blueprint $table) {
            // Provider generated IDs at order creation time
            $table->string('provider_gen_id')->nullable()->unique()->after('provider_signature');
            $table->string('provider_gen_session')->nullable()->after('provider_gen_id');
            $table->string('provider_gen_link')->nullable()->after('provider_gen_session');
            $table->string('provider_gen_qr')->nullable()->after('provider_gen_link');
            $table->string('provider_generated_sign')->nullable()->after('provider_gen_qr');

            // Redirect URLs after confirmation
            $table->string('success_redirect_url')->nullable()->after('success_url');
            $table->string('failure_redirect_url')->nullable()->after('failure_url');

            // Checkout type
            $table->string('checkout_type')->nullable()->after('payment_method');

            // Rename is_verified to verified to match old_project
            $table->renameColumn('is_verified', 'verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'provider_gen_id',
                'provider_gen_session',
                'provider_gen_link',
                'provider_gen_qr',
                'provider_generated_sign',
                'success_redirect_url',
                'failure_redirect_url',
                'checkout_type',
            ]);

            // Revert rename
            $table->renameColumn('verified', 'is_verified');
        });
    }
};
