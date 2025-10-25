<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number ?? '#'.$invoice->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 12px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; vertical-align: top; }
        th { background: #f6f6f6; }
        .meta td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->invoice_number ?? '#'.$invoice->id }}</h1>

    <table class="meta">
        <tr>
            <td><strong>Patient:</strong></td>
            <td>{{ $patient->full_name ?? ($patient->first_name.' '.$patient->last_name) }}</td>
        </tr>
        <tr>
            <td><strong>Patient Code:</strong></td>
            <td>{{ $patient->patient_code }}</td>
        </tr>
        <tr>
            <td><strong>Issue Date:</strong></td>
            <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m-d') : '—' }}</td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong></td>
            <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '—' }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>{{ ucfirst((string)$invoice->status) }}</td>
        </tr>
    </table>

    <h2>Amount</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 160px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Invoice Amount</td>
                <td>{{ number_format((float)$invoice->amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Balance Due</strong></td>
                <td><strong>{{ number_format((float)$invoice->balance_due, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if(!empty($invoice->notes))
        <h2>Notes</h2>
        <p style="white-space: pre-wrap;">{{ $invoice->notes }}</p>
    @endif

    <p style="margin-top: 24px; font-size: 11px; color: #666;">
        Generated on {{ now()->format('Y-m-d H:i') }}.
    </p>
</body>
</html>
