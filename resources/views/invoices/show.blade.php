@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Invoice <span class="text-muted">#{{ $invoice->invoice_number }}</span></h2>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Patient</h5>
            <p class="mb-2">
                @if($invoice->patient)
                    <strong>{{ $invoice->patient->patient_code }}</strong><br>
                    {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                @else
                    <span class="text-muted">Unknown</span>
                @endif
            </p>

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</p>
                    <p><strong>Balance Due:</strong> ${{ number_format($invoice->balance_due, 2) }}</p>
                </div>
                <div class="col-md-6">
                    <p>
                        <strong>Status:</strong>
                        <span class="badge 
                            @switch($invoice->status)
                                @case('paid') bg-success @break
                                @case('partially_paid') bg-warning text-dark @break
                                @case('unpaid') bg-danger @break
                                @case('cancelled') bg-secondary @break
                                @default bg-light text-dark
                            @endswitch
                        ">
                            {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                        </span>
                    </p>
                    <p><strong>Issue Date:</strong> {{ optional($invoice->issue_date)->format('Y-m-d') }}</p>
                    <p><strong>Due Date:</strong> {{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</p>
                </div>
            </div>

            @if($invoice->notes)
                <div class="mt-3">
                    <strong>Notes:</strong>
                    <p class="text-muted">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <h4 class="mb-3">Payments</h4>
    @if($invoice->payments->isEmpty())
        <div class="alert alert-info">No payments recorded.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Paid At</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $p)
                        <tr>
                            <td>{{ optional($p->paid_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $p->method)) }}</td>
                            <td>{{ $p->transaction_ref ?? '—' }}</td>
                            <td class="text-end">${{ number_format($p->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-success">Download PDF</a>
        @if($invoice->status !== 'paid')
            <a href="{{ route('payments.create', $invoice) }}" class="btn btn-success">
                <i class="bi bi-cash-coin me-1"></i> Record Payment
            </a>
        @endif
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>
</div>
@endsection
