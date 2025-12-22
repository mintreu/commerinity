<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'balance' => LaravelMoney::format($this->balance ?? 0),
            'balance_raw' => $this->balance ?? 0, // For calculations
            'coins' => $this->points ?? 0, // Future coin system
            'qr' => $this->getQRCode(),

            'transactions' => $this->whenLoaded(
                'transactions',
                TransactionResource::collection($this->transactions)
            ),

            'beneficiary' => $this->whenLoaded(
                'beneficiary',
                BeneficiaryAccountResource::make($this->beneficiary)
            ),

            // Comprehensive statistics
            'stats' => $this->when(isset($this->stats), function () {
                $stats = $this->stats;

                return [
                    // Today's activity
                    'today' => [
                        'credits' => LaravelMoney::format($stats['today']['credits'] ?? 0),
                        'credits_raw' => $stats['today']['credits'] ?? 0,
                        'debits' => LaravelMoney::format($stats['today']['debits'] ?? 0),
                        'debits_raw' => $stats['today']['debits'] ?? 0,
                        'net_activity' => LaravelMoney::format($stats['today']['net_activity'] ?? 0),
                        'net_activity_raw' => $stats['today']['net_activity'] ?? 0,
                    ],

                    // This week
                    'week' => [
                        'credits' => LaravelMoney::format($stats['week']['credits'] ?? 0),
                        'credits_raw' => $stats['week']['credits'] ?? 0,
                        'debits' => LaravelMoney::format($stats['week']['debits'] ?? 0),
                        'debits_raw' => $stats['week']['debits'] ?? 0,
                        'net_activity' => LaravelMoney::format($stats['week']['net_activity'] ?? 0),
                        'net_activity_raw' => $stats['week']['net_activity'] ?? 0,
                    ],

                    // This month
                    'month' => [
                        'credits' => LaravelMoney::format($stats['month']['credits'] ?? 0),
                        'credits_raw' => $stats['month']['credits'] ?? 0,
                        'debits' => LaravelMoney::format($stats['month']['debits'] ?? 0),
                        'debits_raw' => $stats['month']['debits'] ?? 0,
                        'net_activity' => LaravelMoney::format($stats['month']['net_activity'] ?? 0),
                        'net_activity_raw' => $stats['month']['net_activity'] ?? 0,
                    ],

                    // Transaction counts
                    'counts' => [
                        'total' => $stats['counts']['total'] ?? 0,
                        'credits' => $stats['counts']['credits'] ?? 0,
                        'debits' => $stats['counts']['debits'] ?? 0,
                        'pending' => $stats['counts']['pending'] ?? 0,
                        'failed' => $stats['counts']['failed'] ?? 0,
                    ],

                    // Beneficiary info
                    'beneficiary' => [
                        'count' => $stats['beneficiary']['count'] ?? 0,
                        'has_default' => $stats['beneficiary']['has_default'] ?? false,
                    ],

                    // Last transactions
                    'last_transaction' => $this->formatLastTransaction($stats['last_transaction'] ?? null),
                    'last_completed_transaction' => $this->formatLastTransaction($stats['last_completed_transaction'] ?? null),
                ];
            }),
        ];
    }

    /**
     * Format last transaction data
     */
    protected function formatLastTransaction(?array $transaction): ?array
    {
        if (!$transaction) {
            return null;
        }

        return [
//            'id' => $transaction['id'] ?? null,
            'uuid' => $transaction['uuid'] ?? null,
            'purpose' => $transaction['purpose'] ?? null,
            'amount' => LaravelMoney::format($transaction['amount'] ?? 0),
            'amount_raw' => $transaction['amount'] ?? 0,
            'type' => $transaction['type'] ?? null,
            'status' => $transaction['status'] ?? null,
            'created_at' => $transaction['created_at'] ?? null,
        ];
    }
}
