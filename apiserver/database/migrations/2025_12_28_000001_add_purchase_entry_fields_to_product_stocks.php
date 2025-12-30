<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purchase Entry Pattern for ProductStock
 *
 * Each stock record represents a purchase/inventory entry with:
 * - landing_cost: Total cost to acquire (purchase price + shipping + duties)
 * - profit_margin: Percentage profit margin for this batch
 * - bv/pv/reward_points: Affiliate values calculated from profit_margin
 * - supplier tracking via purchase_invoice_id
 *
 * Affiliate Commission Logic:
 * - BV (Business Volume) = Points for Affiliate commission calculations
 * - PV (Personal Volume) = Points for personal sales tracking
 * - reward_points = Points customer earns on purchase
 * - These are SET at purchase entry time based on profit_margin/landing_cost
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // Purchase Entry Fields (from old_project ProductTier)
            $table->unsignedInteger('landing_cost')->default(0)->after('address_id')
                ->comment('Total cost in paise (purchase + shipping + duties)');
            $table->decimal('profit_margin', 5, 2)->default(0)->after('landing_cost')
                ->comment('Profit margin percentage for this batch');
            $table->unsignedInteger('price')->nullable()->after('profit_margin')
                ->comment('Override price for this batch (null = use product price)');

            // Quantity Constraints (for wholesale)
            $table->unsignedInteger('min_quantity')->default(1)->after('price')
                ->comment('Minimum order quantity');
            $table->unsignedInteger('max_quantity')->nullable()->after('min_quantity')
                ->comment('Maximum order quantity (null = unlimited)');
            $table->unsignedInteger('wholesale_unit_quantity')->nullable()->after('max_quantity')
                ->comment('Unit quantity for wholesale orders');

            // Affiliate Commission Fields (calculated from profit_margin at entry time)
            $table->unsignedInteger('bv')->default(0)->after('wholesale_unit_quantity')
                ->comment('Business Volume for Affiliate commissions');
            $table->unsignedInteger('pv')->default(0)->after('bv')
                ->comment('Personal Volume for Affiliate tracking');
            $table->unsignedInteger('reward_points')->default(0)->after('pv')
                ->comment('Reward points customer earns');
            $table->decimal('commission_rate', 5, 2)->nullable()->after('reward_points')
                ->comment('Override commission % (null = use level default)');
            $table->boolean('is_commissionable')->default(true)->after('commission_rate')
                ->comment('Whether this stock generates Affiliate commissions');

            // Supplier/Invoice Tracking
            $table->foreignId('supplier_id')->nullable()->after('is_commissionable')
                ->constrained('users')->nullOnDelete()
                ->comment('Supplier who provided this stock');
            $table->string('purchase_invoice_number', 50)->nullable()->after('supplier_id')
                ->comment('External invoice/reference number');
            $table->date('purchase_date')->nullable()->after('purchase_invoice_number')
                ->comment('Date of purchase/receipt');
            $table->date('expiry_date')->nullable()->after('purchase_date')
                ->comment('Product expiry date for this batch');

            // Batch/Lot Tracking
            $table->string('batch_number', 50)->nullable()->after('expiry_date')
                ->comment('Batch/Lot number for tracking');
            $table->text('notes')->nullable()->after('batch_number')
                ->comment('Internal notes for this stock entry');

            // Indexes for Affiliate queries
            $table->index('is_commissionable');
            $table->index(['bv', 'pv']);
            $table->index('supplier_id');
            $table->index('batch_number');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropIndex(['is_commissionable']);
            $table->dropIndex(['bv', 'pv']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['batch_number']);
            $table->dropIndex(['expiry_date']);

            $table->dropForeign(['supplier_id']);

            $table->dropColumn([
                'landing_cost',
                'profit_margin',
                'price',
                'min_quantity',
                'max_quantity',
                'wholesale_unit_quantity',
                'bv',
                'pv',
                'reward_points',
                'commission_rate',
                'is_commissionable',
                'supplier_id',
                'purchase_invoice_number',
                'purchase_date',
                'expiry_date',
                'batch_number',
                'notes',
            ]);
        });
    }
};
