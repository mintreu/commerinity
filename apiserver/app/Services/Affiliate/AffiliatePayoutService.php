<?php

declare(strict_types=1);

namespace App\Services\Affiliate;

use App\Casts\AffiliatePayoutStatusCast;
use App\Casts\AffiliateVolumeStatusCast;
use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Affiliate\AffiliateFundAccount;
use App\Models\Affiliate\AffiliateFundTransaction;
use App\Models\Affiliate\AffiliatePayout;
use App\Models\Affiliate\AffiliateVolumeLedger;
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Notifications\Affiliate\AffiliatePayoutNotification;
use App\Services\MoneyService;
use App\Services\UserServices\UserWalletService;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AffiliatePayoutService
{
    public function __construct(
        private readonly UserWalletService $walletService,
        private readonly AffiliateConfigService $configService,
    ) {
    }

    /**
     * Process monthly payouts for confirmed BV/PV.
     *
     * @return Collection<int, AffiliatePayout>
     */
    public function processMonthly(?Carbon $periodEnd = null): Collection
    {
        $periodEnd = ($periodEnd ?? now())->copy()->startOfMonth()->subDay();
        $periodStart = $periodEnd->copy()->startOfMonth();

        $confirmed = AffiliateVolumeLedger::query()
            ->where('status', AffiliateVolumeStatusCast::CONFIRMED)
            ->whereBetween('confirmed_at', [$periodStart->startOfDay(), $periodEnd->endOfDay()])
            ->get()
            ->groupBy('user_id');

        $payouts = collect();

        foreach ($confirmed as $userId => $ledgers) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $totalPv = (int) $ledgers->sum('pv');
            $totalBv = (int) $ledgers->sum('bv');

            $gross = $this->convertVolumesToPaisa($totalPv, $totalBv);
            $minThreshold = (int) config('affiliate.payout.min_threshold_paisa', 10000);
            if ($gross < $minThreshold) {
                continue;
            }

            $userType = $user->type?->value ?? 'member';
            $platformFee = $this->configService->calculatePlatformFee($gross, $userType, 'monthly');
            $platformFeeGst = $this->configService->calculatePlatformFeeGst($platformFee, $userType);
            $tds = $this->configService->calculateTds($gross, $gross);
            $tcs = $this->configService->calculateTcs($gross, $gross);
            $net = max(0, $gross - $tds - $tcs - $platformFee - $platformFeeGst);

            DB::transaction(function () use (
                $user,
                $periodStart,
                $periodEnd,
                $totalPv,
                $totalBv,
                $gross,
                $tds,
                $net,
                &$payouts
            ): void {
                $payout = AffiliatePayout::create([
                    'user_id' => $user->id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                    'pv' => $totalPv,
                    'bv' => $totalBv,
                    'gross_amount' => $gross,
                    'platform_fee' => $platformFee,
                    'platform_fee_gst' => $platformFeeGst,
                    'tds_amount' => $tds,
                    'tcs_amount' => $tcs,
                    'net_amount' => $net,
                    'status' => AffiliatePayoutStatusCast::PAID,
                    'paid_at' => now(),
                    'meta' => [
                        'conversion' => $this->getConversionConfig(),
                    ],
                ]);

                $split = $this->splitNetAmount($user, $net);

                $walletAmount = $split['wallet'] ?? 0;
                if ($walletAmount > 0) {
                    $transaction = $this->walletService->credit(
                        $user,
                        $walletAmount,
                        'affiliate_disbursement',
                        $payout,
                        'Affiliate monthly disbursement'
                    );
                    $payout->update([
                        'transaction_id' => $transaction->id,
                        'meta' => array_merge($payout->meta ?? [], ['split' => $split]),
                    ]);

                    $user->notify(new GeneralNotification(
                        title: 'Commission Disbursed',
                        message: MoneyService::format($walletAmount).' credited to your wallet as affiliate commission.',
                        actionUrl: rtrim((string) config('app.client_url'), '/').'/wallet',
                        actionText: 'View Wallet',
                        channels: ['database', 'push', 'mail'],
                        type: 'success',
                    ));
                }

                foreach ($split as $fundType => $amount) {
                    if ($fundType === 'wallet' || $amount <= 0) {
                        continue;
                    }

                    $this->creditFund($user, $fundType, $amount, $payout);
                }

                if ($platformFee > 0) {
                    $this->creditPlatformFeeWallet($platformFee + $platformFeeGst, $payout);
                }

                $user->notify(new AffiliatePayoutNotification($payout));
                Notification::make()
                    ->title('Affiliate disbursement processed')
                    ->body('Disbursement for user #'.$user->id.' | Net: '.$net)
                    ->sendToDatabase(Admin::all());
                $payouts->push($payout);
            });
        }

        return $payouts;
    }

    private function convertVolumesToPaisa(int $pv, int $bv): int
    {
        $pvRate = (int) config('affiliate.payout.pv_to_paisa_rate', 100);
        $bvRate = (int) config('affiliate.payout.bv_to_paisa_rate', 100);

        $pvAmount = (int) floor(($pv * $pvRate) / 100);
        $bvAmount = (int) floor(($bv * $bvRate) / 100);

        return $pvAmount + $bvAmount;
    }

    private function getConversionConfig(): array
    {
        return [
            'pv_to_paisa_rate' => (int) config('affiliate.payout.pv_to_paisa_rate', 100),
            'bv_to_paisa_rate' => (int) config('affiliate.payout.bv_to_paisa_rate', 100),
        ];
    }

    /**
     * Split net amount by stage/level config.
     *
     * @return array<string, int>
     */
    private function splitNetAmount(User $user, int $netAmount): array
    {
        $splitConfig = $this->resolveFundSplitConfig($user);
        $totalPercent = array_sum($splitConfig);

        if ($totalPercent <= 0) {
            return ['wallet' => $netAmount];
        }

        $allocated = [];
        $remaining = $netAmount;
        foreach ($splitConfig as $key => $percent) {
            $amount = (int) floor(($netAmount * $percent) / 100);
            $allocated[$key] = $amount;
            $remaining -= $amount;
        }

        // Add any rounding remainder to wallet
        $allocated['wallet'] = ($allocated['wallet'] ?? 0) + max(0, $remaining);

        return $allocated;
    }

    private function resolveFundSplitConfig(User $user): array
    {
        $default = config('affiliate.payout.fund_split.default', ['wallet' => 100]);

        $genealogy = $user->genealogy?->loadMissing(['currentStage', 'currentLevel']);
        $stageKey = $genealogy?->currentStage?->slug;
        $levelKey = $genealogy?->currentLevel?->slug;

        $levelSplit = $levelKey ? config("affiliate.payout.fund_split.levels.{$levelKey}") : null;
        if (is_array($levelSplit) && ! empty($levelSplit)) {
            return $levelSplit;
        }

        $stageSplit = $stageKey ? config("affiliate.payout.fund_split.stages.{$stageKey}") : null;
        if (is_array($stageSplit) && ! empty($stageSplit)) {
            return $stageSplit;
        }

        return $default;
    }

    private function creditFund(User $user, string $fundType, int $amount, AffiliatePayout $payout): void
    {
        $account = AffiliateFundAccount::firstOrCreate(
            ['user_id' => $user->id, 'fund_type' => $fundType],
            ['balance' => 0, 'total_credited' => 0, 'total_debited' => 0, 'is_active' => true]
        );

        $account->balance += $amount;
        $account->total_credited += $amount;
        $account->save();

        AffiliateFundTransaction::create([
            'fund_account_id' => $account->id,
            'source_type' => $payout->getMorphClass(),
            'source_id' => $payout->id,
            'type' => \App\Casts\FundTransactionTypeCast::CREDIT,
            'amount' => $amount,
            'balance_after' => $account->balance,
            'notes' => 'Affiliate fund split credit',
            'meta' => [
                'payout_uuid' => $payout->uuid,
            ],
        ]);
    }

    private function creditPlatformFeeWallet(int $amount, AffiliatePayout $payout): void
    {
        if ($amount <= 0) {
            return;
        }

        $walletRef = (string) config('affiliate.platform_fee.collection_wallet', '');
        if ($walletRef === '') {
            return;
        }

        $wallet = null;
        if (is_numeric($walletRef)) {
            $wallet = \App\Models\Wallet::find((int) $walletRef);
        } elseif (str_starts_with($walletRef, 'WAL-')) {
            $wallet = \App\Models\Wallet::where('uuid', $walletRef)->first();
        } elseif (str_starts_with($walletRef, 'wallet:')) {
            $wallet = \App\Models\Wallet::where('uuid', substr($walletRef, 7))->first();
        }

        if (! $wallet) {
            Log::warning('Platform fee wallet not found', ['ref' => $walletRef]);
            return;
        }

        $wallet->increment('balance', $amount);
        $wallet->increment('total_credited', $amount);

        Transaction::create([
            'wallet_id' => $wallet->id,
            'transactionable_type' => $payout->getMorphClass(),
            'transactionable_id' => $payout->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => $amount,
            'fee' => 0,
            'tax' => 0,
            'net_amount' => $amount,
            'currency' => $wallet->currency,
            'payment_method' => PaymentMethodCast::WALLET,
            'purpose' => 'platform_fee',
            'description' => 'Platform fee collected from affiliate disbursement',
            'verified' => true,
            'verified_at' => now(),
            'balance_after' => $wallet->balance,
            'metadata' => [
                'payout_uuid' => $payout->uuid,
            ],
        ]);
    }
}
