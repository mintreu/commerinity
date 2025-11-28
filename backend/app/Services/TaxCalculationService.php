<?php

namespace App\Services;

use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelMoney\LaravelMoney;

class TaxCalculationService
{
    /**
     * Calculate the applicable GST for a given product and line item price,
     * based on the seller and buyer locations.
     *
     * @param Product $product The product being sold.
     * @param LaravelMoney $lineItemPrice The price for the line item (quantity * unit price).
     * @param Address $sellerAddress The address of the stock point (origin).
     * @param Address $buyerAddress The customer's shipping address (destination).
     * @return array An array containing the tax breakdown.
     */
    public function calculate(Product $product, LaravelMoney $lineItemPrice, Address $sellerAddress, Address $buyerAddress): array
    {
        $slab = $product->tax_slab;

        // If there's no tax slab or product is exempt, return zero tax.
        if (is_null($slab) || $product->is_exempted) {
            return [
                'cgst_rate' => 0,
                'sgst_rate' => 0,
                'igst_rate' => 0,
                'cgst_amount' => LaravelMoney::make(0),
                'sgst_amount' => LaravelMoney::make(0),
                'igst_amount' => LaravelMoney::make(0),
                'total_tax' => LaravelMoney::make(0),
            ];
        }

        $isInterState = $sellerAddress->state_code !== $buyerAddress->state_code;

        if ($isInterState) {
            // Inter-state transaction: Apply IGST
            $igstRate = $slab->getIgstRate();
            $taxAmount = $lineItemPrice->multiply($igstRate / 100);
            return [
                'cgst_rate' => 0,
                'sgst_rate' => 0,
                'igst_rate' => $igstRate,
                'cgst_amount' => LaravelMoney::make(0),
                'sgst_amount' => LaravelMoney::make(0),
                'igst_amount' => $taxAmount,
                'total_tax' => $taxAmount,
            ];
        } else {
            // Intra-state transaction: Apply CGST + SGST
            $cgstRate = $slab->getCgstRate();
            $sgstRate = $slab->getSgstRate();
            $cgstAmount = $lineItemPrice->multiply($cgstRate / 100);
            $sgstAmount = $lineItemPrice->multiply($sgstRate / 100);
            $totalTax = $cgstAmount->add($sgstAmount);
            return [
                'cgst_rate' => $cgstRate,
                'sgst_rate' => $sgstRate,
                'igst_rate' => 0,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'igst_amount' => LaravelMoney::make(0),
                'total_tax' => $totalTax,
            ];
        }
    }
}
