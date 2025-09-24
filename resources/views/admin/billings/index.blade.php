@extends('layouts.app')

@section('header')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">Billing & Payments</h2>
    <span class="text-muted">Manage invoices, payments, and patient balances</span>
</div>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select name="patient_id" id="patient_id" class="form-select">
                        <option value="">All Patients</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}">
                            {{ $patient->full_name }} ({{ $patient->patient_code }})
                        </option>
                        
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Invoice Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['unpaid', 'partially_paid', 'paid', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>

        {{-- Invoices Table --}}
        @if($invoices->count())
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Patient</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Issued</th>
                        <th>Due</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td><code>{{ $invoice->invoice_number }}</code></td>
                        <td>{{ $invoice->patient->name ?? '—' }}</td>
                        <td>${{ number_format($invoice->amount, 2) }}</td>
                        <td>${{ number_format($invoice->balance_due, 2) }}</td>
                        <td>
                            <span class="badge 
                                @if($invoice->status === 'paid') bg-success
                                @elseif($invoice->status === 'partially_paid') bg-warning text-dark
                                @elseif($invoice->status === 'cancelled') bg-secondary
                                @else bg-danger
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') }}</td>
                        <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}</td>
                        <td>
                            <a href="{{ route('admin.billings.show', $invoice->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.billings.pay', $invoice->id) }}" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $invoices->links() }}
        </div>
        @else
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i> No invoices found for the selected filters.
            </div>
        @endif
    </div>
</div>
@endsection
