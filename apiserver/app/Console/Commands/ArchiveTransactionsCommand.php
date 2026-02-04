<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HistoricalTransaction;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class ArchiveTransactionsCommand extends Command
{
    protected $signature = 'transactions:archive {--days=365 : Archive transactions older than this many days} {--chunk=500 : Chunk size for processing}';

    protected $description = 'Move old transactions into the historical_transactions table';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $chunkSize = max(50, (int) $this->option('chunk'));
        $cutoff = now()->subDays($days);

        $this->info("Archiving transactions older than {$cutoff->toDateTimeString()}");

        $query = Transaction::query()
            ->where('created_at', '<', $cutoff)
            ->orderBy('id');

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No transactions to archive.');

            return self::SUCCESS;
        }

        $this->info("Found {$total} transactions to archive.");

        $query->chunkById($chunkSize, function ($transactions) {
            DB::transaction(function () use ($transactions) {
                $rows = [];
                $now = now();

                foreach ($transactions as $transaction) {
                    $rows[] = [
                        'source_transaction_id' => $transaction->id,
                        'uuid' => $transaction->uuid,
                        'wallet_id' => $transaction->wallet_id,
                        'transactionable_type' => $transaction->transactionable_type,
                        'transactionable_id' => $transaction->transactionable_id,
                        'type' => $transaction->type->value,
                        'status' => $transaction->status->value,
                        'amount' => $transaction->amount,
                        'fee' => $transaction->fee,
                        'tax' => $transaction->tax,
                        'net_amount' => $transaction->net_amount,
                        'currency' => $transaction->currency,
                        'payment_method' => $transaction->payment_method?->value,
                        'checkout_type' => $transaction->checkout_type,
                        'integration_id' => $transaction->integration_id,
                        'provider_order_id' => $transaction->provider_order_id ?? null,
                        'provider_gen_id' => $transaction->provider_gen_id,
                        'provider_gen_session' => $transaction->provider_gen_session,
                        'provider_gen_link' => $transaction->provider_gen_link,
                        'provider_gen_qr' => $transaction->provider_gen_qr,
                        'provider_transaction_id' => $transaction->provider_transaction_id,
                        'provider_signature' => $transaction->provider_signature,
                        'provider_generated_sign' => $transaction->provider_generated_sign,
                        'qr_code_url' => $transaction->qr_code_url,
                        'success_url' => $transaction->success_url,
                        'failure_url' => $transaction->failure_url,
                        'success_redirect_url' => $transaction->success_redirect_url ?? null,
                        'failure_redirect_url' => $transaction->failure_redirect_url ?? null,
                        'verified' => (bool) $transaction->verified,
                        'verified_at' => $transaction->verified_at,
                        'description' => $transaction->description,
                        'purpose' => $transaction->purpose,
                        'notes' => $transaction->notes,
                        'reference_number' => $transaction->reference_number,
                        'parent_transaction_id' => $transaction->parent_transaction_id,
                        'expires_at' => $transaction->expires_at,
                        'balance_after' => $transaction->balance_after,
                        'metadata' => $transaction->metadata ? json_encode($transaction->metadata) : null,
                        'provider_response' => $transaction->provider_response ? json_encode($transaction->provider_response) : null,
                        'archived_at' => $now,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                        'deleted_at' => $transaction->deleted_at,
                    ];
                }

                HistoricalTransaction::query()->insert($rows);
                Transaction::query()->whereIn('id', $transactions->pluck('id'))->forceDelete();
            });
        });

        $this->info('Archiving completed.');

        return self::SUCCESS;
    }
}
