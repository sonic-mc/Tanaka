@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Record Payment for <span class="text-muted">#{{ $invoice->invoice_number }}</span></h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Patient:</strong>
                @if($invoice->patient)
                    <strong>{{ $invoice->patient->patient_code }}</strong><br>
                    {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                @else
                    <span class="text-muted">Unknown</span>
                @endif
            </p>
            <p><strong>Balance Due:</strong> ${{ number_format($invoice->balance_due, 2) }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('invoice.payments.store', $invoice) }}">
        @csrf

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" min="0.01" max="{{ $invoice->balance_due }}"
                   id="amount" name="amount"
                   value="{{ old('amount', number_format($invoice->balance_due, 2, '.', '')) }}"
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="method" class="form-label">Payment Method</label>
            <select id="method" name="method" class="form-select" required>
                @foreach(['cash','card','mobile_money','bank_transfer'] as $method)
                    <option value="{{ $method }}" @selected(old('method') === $method)>
                        {{ ucwords(str_replace('_',' ',$method)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Transaction reference is system-generated; show as non-editable info -->
        <div class="mb-3">
            <label class="form-label">Transaction Reference</label>
            <input type="text" class="form-control" value="System generated at save" disabled>
            <div class="form-text">A unique transaction reference will be generated automatically when you save.</div>
        </div>

        <div class="mb-3">
            <label for="paid_at" class="form-label">Paid At (optional)</label>
            <input type="datetime-local" id="paid_at" name="paid_at"
                   value="{{ old('paid_at') }}" class="form-control">
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save me-1"></i> Save Payment
            </button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
        </div>
    </form>
</div>
@endsection
