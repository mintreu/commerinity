<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Invoice</title>

    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f8f8f8;
        }

        .invoice-box {
            background: #fff;
            max-width: 900px;
            margin: auto;
            padding: 40px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .company-info {
            text-align: left;
        }

        .company-info h2 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 14px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            margin: 0;
            font-size: 28px;
            color: #222;
        }

        .invoice-title p {
            margin: 4px 0;
            font-size: 14px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h3 {
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 10px;
            color: #444;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 8px;
            font-size: 14px;
            vertical-align: top;
        }

        .info-table td.label {
            font-weight: 600;
            color: #555;
            width: 25%;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }

        .product-table th {
            background-color: #f4f4f4;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .totals {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 6px 8px;
            font-size: 14px;
        }

        .totals td.label {
            text-align: right;
            font-weight: 600;
            color: #333;
            width: 80%;
        }

        .totals td.value {
            text-align: right;
            width: 20%;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 40px;
        }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="header">
        <div class="company-info">
            <h2>Company Name</h2>
            <p>Address Line 1</p>
            <p>City, State, ZIP</p>
            <p>Email: info@example.com</p>
            <p>Phone: +1 (000) 000-0000</p>
        </div>

        <div class="invoice-title">
            <h1>INVOICE</h1>
            <p><strong>Invoice No:</strong> N/A</p>
            <p><strong>Order No:</strong> N/A</p>
            <p><strong>Date:</strong> N/A</p>
            <p><strong>Status:</strong> N/A</p>
        </div>
    </div>

    <div class="section">
        <h3>Billing Information</h3>
        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td>N/A</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>N/A</td>
            </tr>
            <tr>
                <td class="label">Phone:</td>
                <td>N/A</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td>N/A</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Shipping Information</h3>
        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td>N/A</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>N/A</td>
            </tr>
            <tr>
                <td class="label">Phone:</td>
                <td>N/A</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td>N/A</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Order Items</h3>
        <table class="product-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td>N/A</td>
                <td>N/A</td>
                <td>N/A</td>
                <td>N/A</td>
                <td>N/A</td>
            </tr>
            </tbody>
        </table>
    </div>

    <table class="totals">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="value">N/A</td>
        </tr>
        <tr>
            <td class="label">Discount:</td>
            <td class="value">N/A</td>
        </tr>
        <tr>
            <td class="label">Tax:</td>
            <td class="value">N/A</td>
        </tr>
        <tr>
            <td class="label">Shipping:</td>
            <td class="value">N/A</td>
        </tr>
        <tr>
            <td class="label">Total:</td>
            <td class="value"><strong>N/A</strong></td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for your order!</p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
    </div>
</div>
</body>
</html>
