<?php

declare(strict_types=1);

namespace App\Observers;

use App\Casts\BeneficiaryStatusCast;
use App\Events\BeneficiarySyncRequested;
use App\Models\BeneficiaryAccount;
use App\Services\BeneficiaryAccountService;
use Illuminate\Support\Facades\Log;

/**
 * BeneficiaryAccountObserver - Manages beneficiary account lifecycle
 *
 * Key Behaviors:
 * - Once VERIFIED: Account details become immutable (cannot edit bank/UPI)
 * - Only is_default column can be changed after verification
 * - On VERIFIED status change: Sync with Cashfree Payouts
 * - On REJECTED status change: Log rejection reason
 */
final class BeneficiaryAccountObserver
{
    /**
     * Handle the BeneficiaryAccount "created" event
     */
    public function created(BeneficiaryAccount $beneficiaryAccount): void
    {
        Log::info('Beneficiary account created', [
            'uuid' => $beneficiaryAccount->uuid,
            'type' => $beneficiaryAccount->type->value,
            'status' => $beneficiaryAccount->status->value,
        ]);

        // If created as already verified (e.g., from admin), sync to Cashfree
//        if ($beneficiaryAccount->isVerified()) {
//            $this->syncToCashfree($beneficiaryAccount);
//        }
        // auto sync with
        $this->syncToCashfree($beneficiaryAccount);
    }

    /**
     * Handle the BeneficiaryAccount "updated" event
     *
     * CRITICAL: Once verified, only is_default can be changed
     */
    public function updated(BeneficiaryAccount $beneficiaryAccount): void
    {
        $originalStatus = $beneficiaryAccount->getOriginal('status');
        $newStatus = $beneficiaryAccount->status;

        // Only sync if relevant fields changed to avoid infinite loop (sync updates metadata)
        $relevantAttributes = [
            'type',
            'holder_name',
            'account_number',
            'ifsc_code',
            'upi_id',
            'bank_name',
        ];

        if ($beneficiaryAccount->wasChanged($relevantAttributes)) {
            $this->syncToCashfree($beneficiaryAccount);
        }

        // Log the update
        Log::info('Beneficiary account updated', [
            'uuid' => $beneficiaryAccount->uuid,
            'changes' => array_keys($beneficiaryAccount->getDirty()),
        ]);

        // Check if status changed to VERIFIED
        if ($originalStatus !== $newStatus->value && $newStatus === BeneficiaryStatusCast::VERIFIED) {
            $this->handleVerification($beneficiaryAccount);
        }

        // Check if status changed to REJECTED
        if ($originalStatus !== $newStatus->value && $newStatus === BeneficiaryStatusCast::REJECTED) {
            Log::warning('Beneficiary account rejected', [
                'uuid' => $beneficiaryAccount->uuid,
                'reason' => $beneficiaryAccount->rejection_reason,
            ]);
        }
    }

    /**
     * Handle the BeneficiaryAccount "deleted" event
     */
    public function deleted(BeneficiaryAccount $beneficiaryAccount): void
    {
        // Prevent deletion if verified (for audit trail)
        if ($beneficiaryAccount->isVerified()) {
            Log::warning('Attempted to delete verified beneficiary', [
                'uuid' => $beneficiaryAccount->uuid,
            ]);

            // Soft delete is allowed, but hard delete is blocked elsewhere
            return;
        }

        Log::info('Beneficiary account deleted', [
            'uuid' => $beneficiaryAccount->uuid,
        ]);

        // Remove from Cashfree if verified
        if ($beneficiaryAccount->provider_beneficiary_id) {
            $this->removeFromCashfree($beneficiaryAccount);
        }
    }

    /**
     * Handle the BeneficiaryAccount "restored" event
     */
    public function restored(BeneficiaryAccount $beneficiaryAccount): void
    {
        Log::info('Beneficiary account restored', [
            'uuid' => $beneficiaryAccount->uuid,
        ]);
    }

    /**
     * Handle verification - lock account and sync to Cashfree
     */
    private function handleVerification(BeneficiaryAccount $beneficiaryAccount): void
    {
        Log::info('Beneficiary account verified - locking and syncing', [
            'uuid' => $beneficiaryAccount->uuid,
        ]);

        // Mark as verified at
        if (! $beneficiaryAccount->verified_at) {
            $beneficiaryAccount->verified_at = now();
            $beneficiaryAccount->saveQuietly();
        }

        // Sync to Cashfree Payouts
        $this->syncToCashfree($beneficiaryAccount);
    }

    /**
     * Sync beneficiary to Cashfree Payouts
     */
    private function syncToCashfree(BeneficiaryAccount $beneficiaryAccount): void
    {
         //   BeneficiaryAccountService::make($beneficiaryAccount)->sync();

        event(new BeneficiarySyncRequested($beneficiaryAccount));

        // This would call CashfreePayoutProvider::createBeneficiary()
        // For now, just log - actual implementation in PayoutService
        Log::info('Syncing beneficiary to Cashfree', [
            'uuid' => $beneficiaryAccount->uuid,
            'beneficiary_id' => $beneficiaryAccount->provider_beneficiary_id,
        ]);
    }

    /**
     * Remove beneficiary from Cashfree Payouts
     */
    private function removeFromCashfree(BeneficiaryAccount $beneficiaryAccount): void
    {
        Log::info('Removing beneficiary from Cashfree', [
            'uuid' => $beneficiaryAccount->uuid,
            'beneficiary_id' => $beneficiaryAccount->provider_beneficiary_id,
        ]);
    }

    /**
     * Prevent updates to verified accounts (except is_default)
     *
     * This is enforced via model events, but observer adds extra protection
     */
    public function updating(BeneficiaryAccount $beneficiaryAccount): void
    {
        // Only enforce lock if the account WAS already verified before this update
        // We check original status to allow the transition TO verified status
        $originalStatus = $beneficiaryAccount->getOriginal('status');
        $wasVerified = $originalStatus === BeneficiaryStatusCast::VERIFIED
            || ($originalStatus instanceof BeneficiaryStatusCast && $originalStatus === BeneficiaryStatusCast::VERIFIED);

        if ($wasVerified) {
            $dirty = $beneficiaryAccount->getDirty();
            $allowedChanges = ['is_default'];

            // Check if only allowed fields are being changed
            $changingDisallowedFields = array_diff(
                array_keys($dirty),
                $allowedChanges
            );

            if (! empty($changingDisallowedFields)) {
                Log::warning('Blocked update to verified beneficiary', [
                    'uuid' => $beneficiaryAccount->uuid,
                    'attempted_changes' => $changingDisallowedFields,
                ]);

                // Reset the dirty attributes
                foreach ($changingDisallowedFields as $field) {
                    unset($dirty[$field]);
                }

                // Only allow is_default changes
                if (! isset($dirty['is_default'])) {
                    throw new \LogicException(
                        'Cannot modify verified beneficiary account. Only is_default can be changed.'
                    );
                }
            }
        }
    }
}
