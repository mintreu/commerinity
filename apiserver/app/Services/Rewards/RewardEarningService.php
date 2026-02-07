<?php

declare(strict_types=1);

namespace App\Services\Rewards;

use App\Casts\RewardStatusCast;
use App\Casts\RewardTypeCast;
use App\Models\Ecommerce\VoucherCode;
use App\Models\Rewards\RewardEarning;
use App\Models\User;
use App\Services\Ecommerce\VoucherManager;
use Illuminate\Support\Carbon;

final class RewardEarningService
{
    public function issueCoins(
        User $user,
        int $coins,
        string $sourceType,
        ?int $sourceId = null,
        ?Carbon $expiresAt = null
    ): RewardEarning {
        return RewardEarning::create([
            'user_id' => $user->id,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reward_type' => RewardTypeCast::COINS,
            'coins' => $coins,
            'status' => RewardStatusCast::ISSUED,
            'expires_at' => $expiresAt,
        ]);
    }

    public function issueVoucher(
        User $user,
        array $voucherData,
        string $sourceType,
        ?int $sourceId = null,
        ?Carbon $expiresAt = null
    ): RewardEarning {
        $voucher = VoucherManager::create($voucherData, true);

        /** @var VoucherCode|null $code */
        $code = $voucher->codes()->where('is_primary', true)->first();
        if (! $code) {
            $code = $voucher->codes()->first();
        }

        return RewardEarning::create([
            'user_id' => $user->id,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'reward_type' => RewardTypeCast::VOUCHER,
            'voucher_code_id' => $code?->id,
            'status' => RewardStatusCast::ISSUED,
            'expires_at' => $expiresAt,
        ]);
    }
}
