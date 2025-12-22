<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiary_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();

            $table->enum('type', array_column(
                \Mintreu\LaravelTransaction\Casts\BeneficiaryAccountTypeCast::cases(),
                'value'
            ))->nullable();

            // Accountable owner (User, Merchant, Wallet, etc.)
            $table->morphs('accountable');

            // UPI specific
            $table->string('upi_handle')->nullable();

            // Bank fields
            $table->string('ifsc')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();


            // Status
            $table->boolean('default')->default(false);
            $table->enum('status', array_column(
                \Mintreu\LaravelTransaction\Casts\BeneficiaryAccountStatusCast::cases(),
                'value'
            ));
            $table->text('status_feedback')->nullable();

            // Verified by provider
            $table->foreignId('integration_id')
                ->nullable()
                ->constrained('integrations')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // Payment Provider generated fields
            $table->string('source_fund_account')->nullable();
            $table->string('source_upi_handle')->nullable();

            // Provider related fields
            $table->string('provider_beneficiary_id')->nullable();
            $table->string('provider_beneficiary_type')->nullable();
            $table->string('provider_upi_handle')->nullable();
            $table->boolean('beneficiary_active')->default(false);
            $table->json('provider_data')->nullable();

            // Optional wallet relation (configurable)
            $config = config('laravel-transaction.wallet');
            $table->unsignedBigInteger('wallet_id')->nullable();
            if (($config['status'] ?? false) === true) {
                $table->foreign('wallet_id')
                    ->references('id')->on($config['table'] ?? 'wallets')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }

            // Flexible extension
            $table->json('extra')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficiary_accounts');
    }
};
