<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Affiliate Commissions table serves as a complete audit ledger for all commission
     * transactions. Every commission earned, approved, paid, or reversed is tracked
     * here with full traceability.
     */
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // ========================================
            // RECIPIENT
            // ========================================

            // User who receives the commission
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Link to user's genealogy record
            $table->foreignId('genealogy_id')->nullable()->constrained('affiliate_genealogy')->nullOnDelete();

            // ========================================
            // SOURCE OF COMMISSION
            // ========================================

            // User whose action triggered this commission (e.g., new recruit, purchaser)
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();

            // What triggered the commission (UserSubscription, Order, Transaction, etc.)
            $table->nullableMorphs('commissionable', 'aff_comm_source_idx');

            // ========================================
            // COMMISSION TYPE
            // ========================================

            $table->enum('type', [
                'sponsor_bonus',        // One-time direct referral bonus
                'level_commission',     // Level-based commission (depth 1-4)
                'matching_bonus',       // % of downline's earnings
                'level_achievement',    // Bonus for reaching a level (level 2/3/4)
                'pool_bonus',           // Global pool distribution
                'purchase_commission',  // Commission on product purchase
                'renewal_bonus',        // Bonus on subscription renewal
                'adjustment',           // Manual adjustment by admin
                'reversal',             // Reversal/clawback of previous commission
            ])->default('level_commission');

            // ========================================
            // LEVEL INFO (for level_commission type)
            // ========================================

            // Which level depth this commission is from (1, 2, 3, or 4)
            $table->unsignedTinyInteger('level')->nullable();

            // Commission rate applied (percentage or multiplier)
            $table->decimal('rate_percent', 5, 2)->default(0);

            // ========================================
            // AMOUNTS (all in paisa)
            // ========================================

            // Base amount the commission is calculated on
            $table->unsignedBigInteger('base_amount')->default(0);

            // Gross commission before deductions
            $table->unsignedBigInteger('gross_amount')->default(0);

            // TDS deducted (Indian Tax Deducted at Source)
            $table->unsignedBigInteger('tds_amount')->default(0);

            // Platform/admin fee deducted
            $table->unsignedBigInteger('admin_fee')->default(0);

            // Net amount after all deductions
            $table->unsignedBigInteger('net_amount')->default(0);

            // ========================================
            // STATUS
            // ========================================

            $table->enum('status', [
                'pending',      // Calculated, awaiting approval
                'approved',     // Approved, ready for payout
                'processing',   // Payout in progress
                'paid',         // Credited to wallet
                'held',         // On hold (compliance, verification, etc.)
                'cancelled',    // Cancelled before payout
                'reversed',     // Clawed back after payout
            ])->default('pending');

            // ========================================
            // PAYOUT TRACKING
            // ========================================

            // Transaction when commission was paid to wallet
            $table->foreignId('paid_via_transaction_id')->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            // When it was paid
            $table->timestamp('paid_at')->nullable();

            // ========================================
            // PERIOD TRACKING
            // ========================================

            // Date the commission is attributed to
            $table->date('commission_date');

            // Period key for grouping (e.g., "2025-01" for monthly, "2025-W02" for weekly)
            $table->string('period_key', 20)->nullable();

            // ========================================
            // AUDIT & METADATA
            // ========================================

            // Human-readable description
            $table->text('description')->nullable();

            // Additional metadata (JSON)
            $table->json('metadata')->nullable();

            // Who approved (if manual approval required)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // If reversed, link to original commission
            $table->foreignId('reversed_commission_id')->nullable()
                ->constrained('affiliate_commissions')
                ->nullOnDelete();

            // ========================================
            // TIMESTAMPS
            // ========================================

            $table->timestamps();
            $table->softDeletes();

            // ========================================
            // INDEXES
            // ========================================

            // Find all commissions for a user
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'commission_date']);

            // Find commissions from a specific user's actions
            $table->index('from_user_id');

            // Filter by status and date for payout processing
            $table->index(['status', 'commission_date']);
            $table->index(['status', 'approved_at']);

            // Group by period
            $table->index('period_key');

            // Polymorphic lookup (nullableMorphs already creates index)
            // $table->index(['commissionable_type', 'commissionable_id']);

            // Find reversals
            $table->index('reversed_commission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
