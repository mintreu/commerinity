<?php

declare(strict_types=1);

namespace App\Models\Affiliate;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateFundAccount extends Model
{
    use HasFactory;

    protected $table = 'affiliate_fund_accounts';

    protected $fillable = [
        'user_id',
        'fund_type',
        'balance',
        'total_credited',
        'total_debited',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'total_credited' => 'integer',
            'total_debited' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AffiliateFundTransaction::class, 'fund_account_id');
    }
}
