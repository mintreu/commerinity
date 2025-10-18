<?php

namespace Mintreu\LaravelTransaction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mintreu\LaravelIntegration\Casts\PaymentMethodTypeCast;
use Mintreu\LaravelIntegration\Traits\HasLaravelIntegration;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Casts\TransactionTypeCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;
use Mintreu\Toolkit\Traits\HasUnique;
use RuntimeException;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasPackageModelFactory, HasLaravelIntegration, HasUnique;

    protected $fillable = [
        'uuid',
        'type',
        'purpose',
        'checkout_type',
        'provider_gen_id',
        'provider_gen_session',
        'provider_gen_link',
        'provider_gen_qr',
        'provider_transaction_id',
        'provider_generated_sign',
        'amount',
        'verified',
        'metadata',
//        'provider_id',
        'success_url',
        'failure_url',
        'success_redirect_url',
        'failure_redirect_url',
        'status',
        'expire_at',
        'integration_id',
        'wallet_id',
        'transactionable_type',
        'transactionable_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
       // 'amount'        => LaravelMoneyCast::class,
        'verified'      => 'boolean',
        'metadata'      => 'array',
        'type'          => TransactionTypeCast::class,
        'status'        => TransactionStatusCast::class,
        'checkout_type' => PaymentMethodTypeCast::class,
        'expire_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            if (is_null($transaction->uuid)) {
                $transaction->setUniqueCode('uuid');
            }
        });

        parent::booted();
    }

    /**
     * Specify the key for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the model that the payment belongs to.
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Redirect URL on success.
     */
    public function redirectOnSuccess(): ?string
    {
        return $this->success_redirect_url;
    }

    public function redirectOnFailure():?string
    {
        return $this->failure_redirect_url;
    }





    /**
     * Conditionally define wallet relation based on config and table existence.
     */
    public function wallet(): ?BelongsTo
    {
        $config = config('laravel-transaction.wallet');

        // Only enable relation if explicitly turned on
        if (
            ($config['status'] ?? false) === true &&
            !empty($config['model']) &&
            class_exists($config['model']) &&
            Schema::hasTable($config['table'] ?? 'wallets')
        ) {
            return $this->belongsTo($config['model'], 'wallet_id');
        }

        return null;
    }





}
