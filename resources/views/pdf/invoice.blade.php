<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body { font-family: Arial; position: relative; }

        .header {
            text-align: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .invoice-box {
            margin-top: 20px;
        }

        .watermark {
            position: fixed;
            top: 40%;
            left: 20%;
            font-size: 80px;
            opacity: 0.1;
            transform: rotate(-30deg);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
    </style>
</head>
<body>

@if($billing->status == 'paid')
    <div class="watermark">PAID</div>
@endif

<div class="header">
    <h2>School Invoice</h2>
    <p><strong>Invoice No:</strong> {{ $billing->invoice_no }}</p>
</div>

<div class="invoice-box">
    <p><strong>School:</strong> {{ $school->name }}</p>
    <p><strong>Email:</strong> {{ $school->email }}</p>
    <p><strong>Month:</strong> {{ $billing->billing_month->format('F Y') }}</p>
</div>

<table>
    <tr>
        <th>Description</th>
        <th>Value</th>
    </tr>

    <tr>
        <td>Student Count</td>
        <td>{{ $billing->student_count }}</td>
    </tr>

    <tr>
        <td>Price Per Student</td>
        <td>{{ $billing->price_per_student }} Tk</td>
    </tr>

    <tr>
        <td><strong>Total</strong></td>
        <td><strong>{{ $billing->total_amount }} Tk</strong></td>
    </tr>

    <tr>
        <td>Status</td>
        <td>{{ ucfirst($billing->status) }}</td>
    </tr>
</table>

</body>
</html>