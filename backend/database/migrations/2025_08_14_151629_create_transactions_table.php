<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Required
            $table->string('uuid')->unique();
            $table->string('type');                         // type : credit or debit
            $table->unsignedBigInteger('amount')->default(0);
            $table->morphs('transactionable');              // use HasRelation on your Order Model or Subscription Model with this relationship

            $table->string('purpose',120)->nullable();          // user choice tag for categorize or grouping transaction moto

            // Provider Info On Creation
            $table->string('provider_gen_id')->nullable()->unique();
            $table->string('provider_gen_session')->nullable();
            $table->string('provider_gen_link')->nullable();
            $table->string('provider_gen_qr')->nullable();

            // Provider Callbacks
            $table->string('success_url')->nullable();
            $table->string('failure_url')->nullable();

            // Redirect After Confirmed Or Failed to User App
            $table->string('success_redirect_url')->nullable();
            $table->string('failure_redirect_url')->nullable();

            // After Confirmation
            $table->string('provider_transaction_id')->nullable();
            $table->string('provider_generated_sign')->nullable();



            // Confirmation & Validation
            $table->boolean('verified')->default(false);
            $table->dateTime('expire_at')->nullable();
            $table->string('status')->default(TransactionStatusCast::PENDING->value);


            // Store full provider response
            $table->json('metadata')->nullable();


            // Integration Info
            $table->foreignId('integration_id')
                ->constrained('integrations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('checkout_type')->nullable();                // checkout type such as standard web checkout, through link, through qr or upi id

            // Wallet If Allowed
            // Conditionally add wallet_id (nullable always)
            $config = config('laravel-transaction.wallet');
            if (($config['status'] ?? false) === true) {
                $table->unsignedBigInteger('wallet_id')->nullable();
            }

            $table->timestamps();
        });

        // Conditionally add foreign key if enabled + table exists
        $config = config('laravel-transaction.wallet');
        if (
            ($config['status'] ?? false) === true &&
            Schema::hasTable($config['table'] ?? 'wallets')
        ) {
            Schema::table('transactions', function (Blueprint $table) use ($config) {
                $table->foreign('wallet_id')
                    ->references('id')->on($config['table'])
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
