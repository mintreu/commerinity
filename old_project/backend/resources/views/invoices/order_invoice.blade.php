<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order Invoice - {{ optional($order)->uuid ?? 'N/A' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        /* Basic print-friendly invoice styling */
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #333;
            background: #f6f6f6;
            margin: 0;
            padding: 20px;
        }

        .page {
            max-width: 900px;
            margin: 0 auto 24px;
            background: #fff;
            padding: 28px;
            border: 1px solid #e6e6e6;
            box-shadow: 0 0 0 rgba(0,0,0,0);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .company {
            font-size: 14px;
        }

        .company h2 {
            margin: 0 0 6px;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .invoice-meta {
            text-align: right;
            font-size: 14px;
        }

        .invoice-meta h1 {
            margin: 0;
            font-size: 26px;
        }

        .details {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .client, .ship {
            width: 48%;
            font-size: 14px;
        }

        .client .to {
            font-weight: 700;
            margin-bottom: 6px;
            color: #444;
        }

        .client h3 {
            margin: 0 0 6px;
            font-size: 16px;
        }

        .qr {
            max-width: 120px;
            max-height: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 13px;
        }

        table thead th {
            background: #f3f3f3;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #e8e8e8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table tbody td {
            padding: 10px;
            border: 1px solid #eee;
            vertical-align: top;
        }

        table tfoot td {
            padding: 8px 10px;
            border: none;
            font-size: 14px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals {
            margin-top: 10px;
            width: 100%;
            max-width: 420px;
            float: right;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px 10px;
            border: 1px solid #eee;
            background: #fff;
        }

        .totals td.label {
            background: #fafafa;
            font-weight: 700;
        }

        .notes {
            margin-top: 36px;
            font-size: 12px;
            color: #666;
            clear: both;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; border: none; margin: 0; }
        }
    </style>
</head>
<body>
@php
    // Safe money formatter helper
    if (! function_exists('format_money')) {
        function format_money($value) {
            if ($value === null) {
                return 'N/A';
            }
            if (class_exists(\App\Services\MoneyServices\Money::class)) {
                try {
                    return \App\Services\MoneyServices\Money::format($value);
                } catch (\Throwable $e) {
                    return (string) $value;
                }
            }
            return (string) $value;
        }
    }
@endphp

@php
    // Primary data fallbacks
    $companyName = $company['name'] ?? config('app.name') ?? 'N/A';
    $companyEmail = $company['email'] ?? 'N/A';
    $logoUrl = $logo ?? null;
@endphp

{{-- If multiple invoices, render each on its own "page" so printing generates separate pages --}}
@forelse(($invoices ?? collect([null])) as $invIndex => $invoice)
    <div class="page">
        <header>
            <div class="company">
                @if($logoUrl)
                    <div style="margin-bottom:10px;">
                        <img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height:64px;">
                    </div>
                @endif

                <h2>{{ $companyName }}</h2>
                <div>Email: {{ $companyEmail }}</div>
                <div>Phone: {{ $company['phone'] ?? 'N/A' }}</div>
                <div style="margin-top:6px; color:#777; font-size:12px;">
                    {{ $company['address'] ?? 'N/A' }}
                </div>
            </div>

            <div class="invoice-meta">
                <h1>INVOICE</h1>
                <div><strong>Invoice:</strong> {{ optional($invoice)->number ?? ('INV-'.(optional($order)->uuid ?? 'N/A')) }}</div>
                <div><strong>Order ID:</strong> {{ optional($order)->uuid ?? 'N/A' }}</div>
                <div><strong>Date:</strong> {{ optional($invoice)->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format(config('app.date_format', 'd/m/Y')) : ( optional($order)->created_at ? \Carbon\Carbon::parse($order->created_at)->format(config('app.date_format','d/m/Y')) : 'N/A') }}</div>
                <div><strong>Status:</strong> {{ optional(optional($order)->status)->getLabel() ?? (optional($order)->status ?? 'N/A') }}</div>
            </div>
        </header>

        <div class="details">
            <div class="client">
                <div class="to">Bill To:</div>
                <h3>{{ optional(optional($order)->customer)->name ?? (optional($order)->customer_name ?? 'N/A') }}</h3>
                <div>{{ optional(optional($order)->customer)->contact ?? (optional($order)->customer_mobile ?? 'N/A') }}</div>
                <div><a href="mailto:{{ optional(optional($order)->customer)->email ?? (optional($order)->customer_email ?? 'N/A') }}">{{ optional(optional($order)->customer)->email ?? (optional($order)->customer_email ?? 'N/A') }}</a></div>
                <div style="margin-top:6px; font-size:13px;">Billing Address: {{ optional(optional($order)->billingAddress)->full_address ?? 'N/A' }}</div>
            </div>

            <div class="ship">
                <div class="to">Ship To:</div>
                <h3>{{ optional(optional($order)->shippingAddress)->name ?? (optional($order)->customer_name ?? 'N/A') }}</h3>
                <div>{{ optional(optional($order)->shippingAddress)->contact ?? (optional($order)->customer_mobile ?? 'N/A') }}</div>
                <div style="margin-top:6px; font-size:13px;">Shipping Address: {{ optional(optional($order)->shippingAddress)->full_address ?? (optional($order)->shipping_address ?? 'N/A') }}</div>

                @if(isset($qr) && $qr)
                    <div style="margin-top:8px;">
                        <img src="{{ $qr }}" alt="QR" class="qr">
                    </div>
                @endif
            </div>
        </div>

        <div>
            <h3 style="color:#560269; margin-bottom:8px;">Products</h3>

            <table>
                <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:34%;">Description</th>
                    <th style="width:12%;">Unit Price</th>
                    <th style="width:8%;" class="text-center">Tax</th>
                    <th style="width:8%;" class="text-center">Qty</th>
                    <th style="width:10%;" class="text-right">Subtotal</th>
                    <th style="width:10%;" class="text-right">Discount</th>
                    <th style="width:8%;" class="text-right">Tax Total</th>
                    <th style="width:12%;" class="text-right">Total</th>
                </tr>
                </thead>
                <tbody>
                @php $products = optional($order)->orderProducts ?? collect(); @endphp

                @if($products->isEmpty())
                    <tr>
                        <td class="text-center" colspan="9">N/A</td>
                    </tr>
                @else
                    @foreach($products as $key => $orderProduct)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <strong>{{ optional(optional($orderProduct)->product)->name ? ucwords(optional($orderProduct->product)->name) : (optional($orderProduct)->title ?? 'N/A') }}</strong>
                                <div style="font-size:12px; color:#666;">
                                    SKU: {{ optional(optional($orderProduct)->product)->sku ?? (optional($orderProduct)->sku ?? 'N/A') }}
                                </div>
                            </td>
                            <td>{{ format_money(optional(optional($orderProduct)->product)->price ?? $orderProduct->unit_price ?? null) }}</td>
                            <td class="text-center">{{ format_money($orderProduct->tax ?? null) }}</td>
                            <td class="text-center">{{ $orderProduct->quantity ?? 'N/A' }}</td>
                            <td class="text-right">{{ format_money( (optional(optional($orderProduct)->product)->price ?? $orderProduct->unit_price ?? null) * ($orderProduct->quantity ?? 1) ) }}</td>
                            <td class="text-right">{{ format_money($orderProduct->discount ?? null) }}</td>
                            <td class="text-right">{{ format_money($orderProduct->tax ?? null) }}</td>
                            <td class="text-right">{{ format_money($orderProduct->total ?? null) }}</td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="6"></td>
                    <td colspan="2" class="label text-right">SUBTOTAL</td>
                    <td class="text-right">{{ format_money(optional($order)->subtotal ?? null) }}</td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td colspan="2" class="label text-right">DISCOUNT</td>
                    <td class="text-right">{{ format_money(optional($order)->discount ?? null) }}</td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td colspan="2" class="label text-right">TOTAL TAX</td>
                    <td class="text-right">{{ format_money(optional($order)->tax ?? null) }}</td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td colspan="2" class="label text-right"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right"><strong>{{ format_money(optional($order)->total ?? null) }}</strong></td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="notes">
            <div><strong>Invoice Notes:</strong></div>
            <div style="margin-top:6px;">
                {{ optional($invoice)->notes ?? 'N/A' }}
            </div>

            <div style="margin-top:16px; color:#444;">
                <strong>Payment Method:</strong> {{ optional(optional($invoice)->payment_method)->name ?? (optional($order)->payment_method ?? 'N/A') }}
            </div>

            <div style="margin-top:18px; font-size:12px; color:#666;">
                Thank you for your order! This invoice was generated by {{ $companyName }}.
            </div>
        </div>
    </div>

    {{-- Page break for print when multiple invoices are present --}}
    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@empty
    <div class="page">
        <header>
            <div class="company">
                <h2>{{ $companyName }}</h2>
            </div>
            <div class="invoice-meta">
                <h1>INVOICE</h1>
            </div>
        </header>

        <div style="padding:36px; text-align:center; color:#666;">
            No invoice data available.
        </div>
    </div>
@endforelse

</body>
</html>
