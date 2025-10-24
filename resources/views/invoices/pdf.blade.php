<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#222; }
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .brand { font-weight:700; font-size:18px; }
        .meta { text-align:right; }
        .table { width:100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding:8px; }
        .table th { background: #f7f7f7; }
        .totals { margin-top: 20px; width:100%; }
        .totals .label { text-align:right; padding-right:10px; }
        .small { font-size:11px; color:#666; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">Pysc Hospital</div>
            <div class="small">Address line 1<br/>Contact: 000-000-000</div>
        </div>
        <div class="meta">
            <div><strong>Invoice</strong></div>
            <div>#{{ $invoice->invoice_number }}</div>
            <div class="small">Issued: {{ optional($invoice->issue_date)->format('Y-m-d') }}</div>
            <div class="small">Due: {{ optional($invoice->due_date)->format('Y-m-d') }}</div>
        </div>
    </div>

    <div>
        <strong>Bill To:</strong><br/>
        {{ $patient->first_name }} {{ $patient->middle_name ? $patient->middle_name . ' ' : '' }}{{ $patient->last_name }}<br/>
        {{ $patient->contact_number ?? '' }}<br/>
        {{ $patient->email ?? '' }}
    </div>

    <table class="table" style="margin-top:15px;">
        <thead>
            <tr>
                <th style="width:70%;">Description</th>
                <th style="width:15%;">Qty</th>
                <th style="width:15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->notes ?? 'Consultation Fee' }}</td>
                <td style="text-align:center">1</td>
                <td style="text-align:right">${{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label" style="width:85%"><strong>Total:</strong></td>
            <td style="text-align:right;"><strong>${{ number_format($invoice->amount, 2) }}</strong></td>
        </tr>
        <tr>
            <td class="label"><strong>Balance Due:</strong></td>
            <td style="text-align:right;"><strong>${{ number_format($invoice->balance_due, 2) }}</strong></td>
        </tr>
    </table>

    @if($invoice->notes)
    <div style="margin-top:20px;">
        <strong>Notes</strong>
        <div class="small">{!! nl2br(e($invoice->notes)) !!}</div>
    </div>
    @endif

    <div style="position:fixed; bottom:20px; width:100%; text-align:center;" class="small">
        Thank you for choosing My Clinic.
    </div>
</body>
</html>
