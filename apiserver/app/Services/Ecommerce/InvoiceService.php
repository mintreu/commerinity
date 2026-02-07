<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\GstTaxCast;
use App\Casts\GstTaxTypeCast;
use App\Models\Address;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderInvoice;
use App\Services\MoneyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class InvoiceService
{
    public function __construct(private readonly MoneyService $moneyService)
    {
    }

    public function ensureInvoice(Order $order): OrderInvoice
    {
        $existing = OrderInvoice::where('order_id', $order->id)->latest()->first();
        if ($existing) {
            return $existing;
        }

        $shipment = $order->shipments()->latest()->first();
        $orderItem = $order->items()->first();

        return OrderInvoice::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem?->id,
            'shipment_id' => $shipment?->id,
        ]);
    }

    public function pdf(Order $order): \Barryvdh\DomPDF\PDF
    {
        $data = $this->prepareInvoiceData($order);

        return Pdf::loadView('invoices.order_invoice', $data)->setWarnings(false);
    }

    public function download(Order $order): Response
    {
        return $this->pdf($order)->download($this->filename($order));
    }

    public function stream(Order $order): Response
    {
        return $this->pdf($order)->stream($this->filename($order));
    }

    private function prepareInvoiceData(Order $order): array
    {
        $order->loadMissing([
            'customer',
            'items.product.category',
            'items.stock.address',
            'shippingAddress',
            'billingAddress',
            'transaction',
        ]);

        $lineItems = $this->buildLineItems($order);
        $taxSummary = $this->buildTaxSummary($lineItems);

        return [
            'order' => $order,
            'invoice_number' => 'INV-'.$order->order_number,
            'invoice_date' => $order->created_at?->format('d M Y') ?? now()->format('d M Y'),
            'items' => $lineItems,
            'tax_summary' => $taxSummary,
            'shipping_lines' => $this->formatAddressLines($order->shippingAddress),
            'billing_lines' => $this->formatAddressLines($order->billingAddress),
            'totals' => [
                'subtotal' => MoneyService::format((int) $order->subtotal),
                'tax' => MoneyService::format((int) $order->tax),
                'shipping_cost' => MoneyService::format((int) $order->shipping_cost),
                'discount' => MoneyService::format((int) $order->discount),
                'total' => MoneyService::format((int) $order->total),
            ],
            'customer' => [
                'name' => $order->customer?->name,
                'email' => $order->customer?->email,
                'contact' => $order->customer?->contact,
                'kyc_type' => $order->customer?->kyc?->type?->value,
                'gst_number' => $order->customer?->kyc?->gst_number,
            ],
        ];
    }

    private function buildLineItems(Order $order): array
    {
        $shippingState = $order->shippingAddress?->state?->name ?? $order->shippingAddress?->state;

        return $order->items->values()->map(function ($item, int $index) use ($shippingState) {
            $product = $item->product;
            $gst = $product?->gst_tax_type ?? $product?->category?->tax_slab;
            $gstRate = $gst instanceof GstTaxCast ? $gst->percentage() : (float) $gst;
            $gstRate = $gstRate > 0 ? $gstRate : 0.0;

            $lineTotal = (int) $item->total_price;
            $taxAmount = $gstRate > 0 ? (int) round($lineTotal * ($gstRate / 100)) : 0;

            $warehouseState = null;
            $stockMeta = $item->metadata['stock_allocations'] ?? $item->metadata['stock_entries'] ?? null;
            if (is_array($stockMeta) && isset($stockMeta[0]['warehouse_state'])) {
                $warehouseState = $stockMeta[0]['warehouse_state'];
            } elseif ($item->stock?->address?->state) {
                $warehouseState = $item->stock->address->state->name ?? $item->stock->address->state;
            }

            $taxType = GstTaxCast::determineTaxType(
                $shippingState ? (string) $shippingState : null,
                $warehouseState ? (string) $warehouseState : null
            );

            return [
                'sequence' => $index + 1,
                'description' => $product?->name ?? 'Product',
                'sku' => $product?->sku ?? null,
                'quantity' => $item->quantity,
                'unit_price' => MoneyService::format((int) $item->unit_price),
                'line_total' => MoneyService::format($lineTotal),
                'gst_rate' => $gstRate,
                'gst_label' => $gst instanceof GstTaxCast ? $gst->getLabel() : ($gstRate > 0 ? $gstRate.'% GST' : '0% GST'),
                'tax_type' => $taxType instanceof GstTaxTypeCast ? $taxType->value : null,
                'tax_amount' => MoneyService::format($taxAmount),
                'line_total_including_tax' => MoneyService::format($lineTotal + $taxAmount),
                'tax_amount_raw' => $taxAmount,
            ];
        })->all();
    }

    private function buildTaxSummary(array $lineItems): array
    {
        return collect($lineItems)
            ->groupBy('gst_label')
            ->map(function (Collection $group, string $label) {
                $sum = $group->sum('tax_amount_raw');

                return [
                    'label' => $label,
                    'amount' => MoneyService::format((int) $sum),
                ];
            })
            ->values()
            ->all();
    }

    private function formatAddressLines(?Address $address): array
    {
        if (! $address) {
            return [];
        }

        $state = $address->state?->name ?? $address->state;

        $lines = [
            $address->name,
            $address->contact,
            $address->address_line_1,
            $address->address_line_2,
            trim("{$address->city}, {$state} {$address->postal_code}"),
            $address->country_code,
        ];

        return array_values(array_filter($lines));
    }

    private function filename(Order $order): string
    {
        return 'invoice-'.$order->order_number.'.pdf';
    }
}
