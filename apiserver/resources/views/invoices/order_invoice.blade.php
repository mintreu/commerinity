<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #222; }
        .container { width: 100%; }
        .row { display: block; width: 100%; }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        th { background: #f6f6f6; text-align: left; }
        .right { text-align: right; }
        .summary { margin-top: 16px; }
        .summary td { border: none; padding: 4px 0; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="title">Invoice</div>
        <div class="muted">Invoice #: {{ $invoice_number }}</div>
        <div class="muted">Invoice Date: {{ $invoice_date }}</div>
    </div>

    <table>
        <tr>
            <th>Billing Address</th>
            <th>Shipping Address</th>
        </tr>
        <tr>
            <td>
                @foreach ($billing_lines as $line)
                    <div>{{ $line }}</div>
                @endforeach
                @if(!empty($customer['gst_number']))
                    <div class="small">GSTIN: {{ $customer['gst_number'] }}</div>
                @endif
            </td>
            <td>
                @foreach ($shipping_lines as $line)
                    <div>{{ $line }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>SKU</th>
            <th class="right">Qty</th>
            <th class="right">Unit Price</th>
            <th class="right">Tax</th>
            <th class="right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item['sequence'] }}</td>
                <td>
                    <div>{{ $item['description'] }}</div>
                    <div class="small muted">{{ $item['gst_label'] }} @if($item['tax_type']) ({{ $item['tax_type'] }}) @endif</div>
                </td>
                <td>{{ $item['sku'] }}</td>
                <td class="right">{{ $item['quantity'] }}</td>
                <td class="right">{{ $item['unit_price'] }}</td>
                <td class="right">{{ $item['tax_amount'] }}</td>
                <td class="right">{{ $item['line_total_including_tax'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="right">Subtotal:</td>
            <td class="right">{{ $totals['subtotal'] }}</td>
        </tr>
        <tr>
            <td class="right">Shipping:</td>
            <td class="right">{{ $totals['shipping_cost'] }}</td>
        </tr>
        <tr>
            <td class="right">Tax:</td>
            <td class="right">{{ $totals['tax'] }}</td>
        </tr>
        <tr>
            <td class="right">Discount:</td>
            <td class="right">{{ $totals['discount'] }}</td>
        </tr>
        <tr>
            <td class="right"><strong>Total:</strong></td>
            <td class="right"><strong>{{ $totals['total'] }}</strong></td>
        </tr>
    </table>

    @if (!empty($tax_summary))
        <table>
            <tr>
                <th colspan="2">Tax Summary</th>
            </tr>
            @foreach ($tax_summary as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="right">{{ $row['amount'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
</body>
</html>
