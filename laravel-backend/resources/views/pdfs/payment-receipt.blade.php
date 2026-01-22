<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        @page {
            margin: 0.75in;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
        }
        .receipt-container {
            border: 2px solid #2563eb;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 22pt;
        }
        .header h2 {
            margin: 10px 0 0 0;
            font-size: 16pt;
            color: #059669;
        }
        .header p {
            margin: 5px 0;
            font-size: 9pt;
        }
        .receipt-info {
            background: #eff6ff;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .receipt-info table {
            width: 100%;
        }
        .receipt-info td {
            padding: 5px;
        }
        .receipt-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .payment-details {
            margin: 20px 0;
        }
        .payment-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-details th,
        .payment-details td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .payment-details th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .amount-box {
            background: #dcfce7;
            border: 2px solid #059669;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .amount-box p {
            margin: 5px 0;
        }
        .amount-box .amount {
            font-size: 24pt;
            font-weight: bold;
            color: #059669;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt;
            color: rgba(5, 150, 105, 0.1);
            z-index: -1;
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #2563eb;
        }
        .note {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px;
            margin: 20px 0;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="watermark">PAID</div>
    
    <div class="receipt-container">
        <div class="header">
            <h1>Academic Nexus University</h1>
            <h2>OFFICIAL RECEIPT</h2>
            <p>123 University Avenue | Academic City, ST 12345</p>
            <p>Tel: +1 (555) 123-4567 | Email: finance@academicnexus.edu</p>
        </div>

        <div class="receipt-info">
            <table>
                <tr>
                    <td>Receipt Number:</td>
                    <td><strong>{{ $receipt_number }}</strong></td>
                </tr>
                <tr>
                    <td>Payment Date:</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Student Name:</td>
                    <td>{{ $student->user->name }}</td>
                </tr>
                <tr>
                    <td>Student ID:</td>
                    <td>{{ $student->student_id }}</td>
                </tr>
                <tr>
                    <td>Invoice Number:</td>
                    <td>INV-{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</td>
                </tr>
            </table>
        </div>

        <h3>Payment Details</h3>
        <table class="payment-details">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Fee Type</td>
                    <td>{{ $invoice->feeStructure->fee_type ?? 'Tuition Fee' }}</td>
                </tr>
                <tr>
                    <td>Payment Method</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                </tr>
                <tr>
                    <td>Transaction Reference</td>
                    <td>{{ $payment->transaction_reference ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Payment Status</td>
                    <td><strong style="color: #059669;">{{ ucfirst($payment->status) }}</strong></td>
                </tr>
                @if($payment->notes)
                <tr>
                    <td>Notes</td>
                    <td>{{ $payment->notes }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="amount-box">
            <p>Amount Paid</p>
            <p class="amount">${{ number_format($payment->amount_paid, 2) }}</p>
            <p style="font-size: 9pt;">{{ ucwords(NumberFormatter::create('en', NumberFormatter::SPELLOUT)->format($payment->amount_paid)) }} Dollars</p>
        </div>

        <div class="note">
            <p><strong>Important Notes:</strong></p>
            <p>• This receipt is valid only if payment has been verified and cleared</p>
            <p>• Keep this receipt for your records</p>
            <p>• For any queries, contact the Finance Office with your receipt number</p>
            @if($invoice->balance > 0)
            <p style="color: #dc2626;"><strong>• Outstanding Balance: ${{ number_format($invoice->balance, 2) }}</strong></p>
            @endif
        </div>

        <div class="footer">
            <p><strong>This is an official computer-generated receipt and does not require a signature</strong></p>
            <p>Generated on {{ $generated_date }}</p>
            <p>Academic Nexus University - Finance Department</p>
        </div>
    </div>
</body>
</html>
