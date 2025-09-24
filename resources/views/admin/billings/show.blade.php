@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">Invoice #{{ $invoice->invoice_number }}</h2>
    <span class="text-muted">Patient: {{ $invoice->patient->name ?? '—' }}</span>
</div>
@endsection

@section('content')
<div class="row g-4">
    {{-- Invoice Summary --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Invoice Details</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</li>
                    <li class="list-group-item"><strong>Balance Due:</strong> ${{ number_format($invoice->balance_due, 2) }}</li>
                    <li class="list-group-item"><strong>Status:</strong>
                        <span class="badge 
                            @if($invoice->status === 'paid') bg-success
                            @elseif($invoice->status === 'partially_paid') bg-warning text-dark
                            @elseif($invoice->status === 'cancelled') bg-secondary
                            @else bg-danger
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                        </span>
                    </li>
                    <li class="list-group-item"><strong>Issued:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') }}</li>
                    <li class="list-group-item"><strong>Due:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}</li>
                    <li class="list-group-item"><strong>Notes:</strong> {{ $invoice->notes ?? '—' }}</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Payment History --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Payment History</h5>
                @if($invoice->payments->count())
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Ref</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td><span class="badge bg-info text-dark">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span></td>
                            <td>{{ $payment->transaction_ref ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <div class="alert alert-info">No payments recorded yet.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Record Payment --}}
    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Record Payment</h5>
                <form method="POST" action="{{ route('admin.billing.pay', $invoice->id) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="method" class="form-label">Method</label>
                            <select name="method" id="method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="transaction_ref" class="form-label">Transaction Ref</label>
                            <input type="text" name="transaction_ref" id="transaction_ref" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-outline-success w-100">
                                <i class="bi bi-cash-coin me-1"></i> Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
