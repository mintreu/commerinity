{{-- resources/views/invoices/transaction_invoice.blade.php --}}
@php
    use Mintreu\LaravelMoney\LaravelMoney;
    use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
    use Illuminate\Support\Str;

    $t = $transaction;
    $transactionable = $t->transactionable ?? null;

    // Customer information
    $customer = $transactionable->customer ?? $transactionable->walletable ?? null;
    $entityName = $customer->name ?? $transactionable->name ?? 'Guest Customer';
    $entityEmail = $customer->email ?? null;
    $entityPhone = $customer->phone ?? $customer->mobile ?? null;

    // Handle address - it's a model with properties
    $addressModel = $customer->address ?? null;
    $entityAddress = null;
    if ($addressModel && is_object($addressModel)) {
        $addressParts = array_filter([
            $addressModel->title ?? null,
            $addressModel->landmark ?? null,
            $addressModel->city ?? null,
            $addressModel->postal_code ?? null,
        ]);
        $entityAddress = implode(', ', $addressParts);

        // Get contact from address if not available from customer
        if (!$entityPhone && $addressModel->person_mobile) {
            $entityPhone = $addressModel->person_mobile;
        }
        if (!$entityEmail && $addressModel->person_email) {
            $entityEmail = $addressModel->person_email;
        }
        if (!$entityName || $entityName === 'Guest Customer') {
            $entityName = $addressModel->person_name ?? $entityName;
        }
    }

    // Formatting
    $formatMoney = fn($amount) => LaravelMoney::format($amount);
    $createdAt = optional($t->created_at)->format('d M Y') ?? 'N/A';
    $createdTime = optional($t->created_at)->format('h:i A') ?? '';

    // Status colors
    $statusColors = [
        TransactionStatusCast::COMPLETED->value => ['bg' => '#d4edda', 'text' => '#155724'],
        TransactionStatusCast::FAILED->value => ['bg' => '#f8d7da', 'text' => '#721c24'],
        TransactionStatusCast::CANCELLED->value => ['bg' => '#e2e3e5', 'text' => '#383d41'],
        TransactionStatusCast::REFUNDED->value => ['bg' => '#d1ecf1', 'text' => '#0c5460'],
        TransactionStatusCast::PENDING->value => ['bg' => '#fff3cd', 'text' => '#856404'],
    ];

    $statusValue = is_object($t->status) ? $t->status->value : (string)$t->status;
    $statusColor = $statusColors[$statusValue] ?? ['bg' => '#f5f5f5', 'text' => '#495057'];
    $statusLabel = is_object($t->status) && method_exists($t->status, 'getLabel') ? $t->status->getLabel() : ucfirst($statusValue);

    $typeLabel = is_object($t->type) && method_exists($t->type, 'getLabel') ? $t->type->getLabel() : 'Payment';
    $paymentMethod = optional($t->integration)->name ?? 'Online Payment';
    $description = $transactionable->title ?? $transactionable->description ?? $t->purpose ?? 'Transaction Payment';

    // Amount in words - Indian system
    function convertNumberToWords($number) {
        if ($number == 0) return 'Zero';

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];

        if ($number < 10) return $ones[$number];
        if ($number < 20) return $teens[$number - 10];
        if ($number < 100) return trim($tens[floor($number / 10)] . ' ' . $ones[$number % 10]);
        if ($number < 1000) return trim($ones[floor($number / 100)] . ' Hundred ' . convertNumberToWords($number % 100));
        if ($number < 100000) return trim(convertNumberToWords(floor($number / 1000)) . ' Thousand ' . convertNumberToWords($number % 1000));
        if ($number < 10000000) return trim(convertNumberToWords(floor($number / 100000)) . ' Lakh ' . convertNumberToWords($number % 100000));
        return trim(convertNumberToWords(floor($number / 10000000)) . ' Crore ' . convertNumberToWords($number % 10000000));
    }

    $amountValue = floor($t->amount / 100);
    $amountInWords = $amountValue > 0 ? convertNumberToWords($amountValue) . ' Rupees Only' : 'Zero Rupees Only';
@endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt - {{ $t->uuid }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
        }

        .container {
            width: 180mm;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 0;
        }

        .header {
            text-align: center;
            padding: 10px 15px;
            background: #f0f0f0;
            border-bottom: 2px solid #000;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .company-details {
            font-size: 8pt;
            color: #333;
            line-height: 1.4;
        }

        .receipt-title {
            text-align: center;
            padding: 8px;
            background: #fff;
            border-bottom: 2px solid #000;
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 6px 10px;
            font-size: 9pt;
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }

        .info-grid td:nth-child(2n) {
            border-right: none;
        }

        .info-grid tr:last-child td {
            border-bottom: 1px solid #000;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            width: 25%;
        }

        .info-value {
            color: #000;
            width: 25%;
            font-weight: 600;
        }

        .amount-section {
            background: #f9f9f9;
            padding: 15px;
            border-bottom: 2px solid #000;
            text-align: center;
        }

        .amount-label {
            font-size: 9pt;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .amount-value {
            font-size: 24pt;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 8px;
        }

        .amount-words {
            font-size: 9pt;
            color: #333;
            font-style: italic;
            padding: 8px;
            background: #fff;
            border: 1px dashed #ccc;
            margin-top: 8px;
        }

        .customer-section {
            padding: 12px 15px;
            border-bottom: 1px solid #000;
            background: #fafafa;
        }

        .customer-label {
            font-size: 9pt;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .customer-info {
            font-size: 10pt;
            line-height: 1.6;
            color: #000;
        }

        .customer-info strong {
            display: block;
            font-size: 11pt;
            margin-bottom: 2px;
        }

        .transaction-details {
            padding: 12px 15px;
            border-bottom: 1px solid #000;
        }

        .details-label {
            font-size: 9pt;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .details-content {
            font-size: 10pt;
            color: #000;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 8px;
        }

        .footer {
            padding: 10px 15px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            background: #f9f9f9;
        }

        .footer strong {
            display: block;
            margin-bottom: 5px;
            color: #000;
        }

        .small-text {
            font-size: 8pt;
            color: #999;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Company Header -->
    <div class="header">
        <div class="company-name">{{ $company['name'] ?? config('app.name') }}</div>
        <div class="company-details">
            {{ $company['address'] ?? '' }}
            @if(isset($company['email']))
                | Email: {{ $company['email'] }}
            @endif
            @if(isset($company['phone']))
                | Phone: {{ $company['phone'] }}
            @endif
        </div>
    </div>

    <!-- Receipt Title -->
    <div class="receipt-title">
        Payment Receipt
    </div>

    <!-- Transaction Info Grid -->
    <table class="info-grid">
        <tr>
            <td class="info-label">Receipt No:</td>
            <td class="info-value">{{ $t->uuid }}</td>
            <td class="info-label">Date & Time:</td>
            <td class="info-value">{{ $createdAt }} {{ $createdTime }}</td>
        </tr>
        <tr>
            <td class="info-label">Payment Mode:</td>
            <td class="info-value">{{ $paymentMethod }}</td>
            <td class="info-label">Transaction Type:</td>
            <td class="info-value">{{ $typeLabel }}</td>
        </tr>
        <tr>
            <td class="info-label">Provider Ref ID:</td>
            <td class="info-value">{{ $t->provider_transaction_id ?? 'N/A' }}</td>
            <td class="info-label">Status:</td>
            <td class="info-value">
                <span class="status-badge" style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                    {{ $statusLabel }}
                </span>
            </td>
        </tr>
    </table>

    <!-- Amount Section -->
    <div class="amount-section">
        <div class="amount-label">Amount Paid</div>
        <div class="amount-value">{{ $formatMoney($t->amount) }}</div>
        <div class="amount-words">
            In Words: <strong style="font-style: normal;">{{ $amountInWords }}</strong>
        </div>
    </div>

    <!-- Customer Details -->
    <div class="customer-section">
        <div class="customer-label">Paid By</div>
        <div class="customer-info">
            <strong>{{ $entityName }}</strong>
            @if($entityPhone)
                Phone: {{ $entityPhone }}
            @endif
            @if($entityEmail)
                | Email: {{ $entityEmail }}
            @endif
            @if($entityAddress)
                <br>{{ Str::limit($entityAddress, 90) }}
            @endif
        </div>
    </div>

    <!-- Transaction Details -->
    <div class="transaction-details">
        <div class="details-label">Transaction Details</div>
        <div class="details-content">
            <strong>{{ Str::limit($description, 100) }}</strong>
            @if($t->purpose)
                <br><span class="small-text">Purpose: {{ $t->purpose }}</span>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <strong>This is a computer-generated receipt</strong>
        <div class="small-text">
            For any queries, please contact: {{ $company['email'] ?? 'support@'.request()->getHost() }}
            <br>Keep this receipt for your records
        </div>
    </div>
</div>
</body>
</html>
