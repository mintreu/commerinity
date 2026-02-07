<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Affiliate Disbursement Invoice</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Affiliate Disbursement</div>
        <div class="muted">Period: {{ $payout->period_start }} to {{ $payout->period_end }}</div>
    </div>

    <table>
        <tr>
            <th>Payout ID</th>
            <td>{{ $payout->uuid }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $payout->status->value }}</td>
        </tr>
        <tr>
            <th>Total PV</th>
            <td>{{ $payout->pv }}</td>
        </tr>
        <tr>
            <th>Total BV</th>
            <td>{{ $payout->bv }}</td>
        </tr>
        <tr>
            <th>Gross Amount</th>
            <td>{{ \App\Services\MoneyService::format($payout->gross_amount) }}</td>
        </tr>
        <tr>
            <th>Platform Fee</th>
            <td>{{ \App\Services\MoneyService::format($payout->platform_fee) }}</td>
        </tr>
        <tr>
            <th>Platform Fee GST</th>
            <td>{{ \App\Services\MoneyService::format($payout->platform_fee_gst) }}</td>
        </tr>
        <tr>
            <th>TDS</th>
            <td>{{ \App\Services\MoneyService::format($payout->tds_amount) }}</td>
        </tr>
        <tr>
            <th>TCS</th>
            <td>{{ \App\Services\MoneyService::format($payout->tcs_amount) }}</td>
        </tr>
        <tr>
            <th>Net Amount</th>
            <td>{{ \App\Services\MoneyService::format($payout->net_amount) }}</td>
        </tr>
        <tr>
            <th>Paid At</th>
            <td>{{ $payout->paid_at?->format('d M Y, H:i') }}</td>
        </tr>
    </table>
</body>
</html>
