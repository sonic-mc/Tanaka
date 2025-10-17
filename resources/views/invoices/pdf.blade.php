<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 24mm 18mm; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #222;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            border-bottom: 2px solid #ccc;
            padding-bottom: 8px;
        }

        .header .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .header .meta {
            text-align: right;
            font-size: 12px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h3 {
            font-size: 14px;
            margin-bottom: 6px;
            color: #444;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            color: #555;
            width: 30%;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            background: #eee;
            border: 1px solid #ccc;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .payments-table th, .payments-table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 11px;
        }

        .payments-table th {
            background: #f6f6f6;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            color: #777;
        }

        .notes {
            font-style: italic;
            color: #666;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ config('app.name', 'Hospital Billing System') }}</div>
        <div class="meta">
            <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
            <div><strong>Status:</strong> <span class="badge">{{ strtoupper(str_replace('_',' ', $invoice->status)) }}</span></div>
        </div>
    </div>

    <div class="section">
        <h3>Invoice Details</h3>
        <table class="info-table">
            <tr>
                <td class="label">Issue Date:</td>
                <td>{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td class="label">Due Date:</td>
                <td>{{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Created By:</td>
                <td>{{ $invoice->creator->name ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Patient Information</h3>
        <table class="info-table">
            <tr>
                <td class="label">Patient:</td>
                <td>
                    @if($invoice->patient)
                        {{ $invoice->patient->patient_code }} — {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                    @else
                        Unknown
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Contact:</td>
                <td>{{ $invoice->patient->contact_number ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Billing Summary</h3>
        <table class="info-table">
            <tr>
                <td class="label">Total Amount:</td>
                <td class="text-right">{{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Balance Due:</td>
                <td class="text-right">{{ number_format($invoice->balance_due, 2) }}</td>
            </tr>
        </table>

        @if($invoice->notes)
            <div class="notes">Note: {{ $invoice->notes }}</div>
        @endif
    </div>

    <div class="section">
        <h3>Payments</h3>
        @if($invoice->payments->isEmpty())
            <div class="notes">No payments recorded.</div>
        @else
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Paid At</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $p)
                        <tr>
                            <td>{{ optional($p->paid_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $p->method)) }}</td>
                            <td>{{ $p->transaction_ref ?? '—' }}</td>
                            <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
