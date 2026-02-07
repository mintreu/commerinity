<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Invoice</title>
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
        <div class="title">Transaction Invoice</div>
        <div class="muted">Invoice generated on {{ now()->format('d M Y, H:i') }}</div>
    </div>

    <table>
        <tr>
            <th>Transaction ID</th>
            <td>{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <th>Purpose</th>
            <td>{{ $transaction->purpose }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $transaction->status->value }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td>{{ \App\Services\MoneyService::format($transaction->amount) }}</td>
        </tr>
        <tr>
            <th>Net Amount</th>
            <td>{{ \App\Services\MoneyService::format($transaction->net_amount) }}</td>
        </tr>
        <tr>
            <th>Created At</th>
            <td>{{ $transaction->created_at?->format('d M Y, H:i') }}</td>
        </tr>
    </table>
</body>
</html>
