@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Invoices</h2>
        <a href="{{ route('billing.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Billing
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Patient</th>
                    <th scope="col" class="text-end">Amount</th>
                    <th scope="col" class="text-end">Balance</th>
                    <th scope="col">Status</th>
                    <th scope="col">Issue Date</th>
                    <th scope="col">Due Date</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>
                            @if($invoice->patient)
                                <strong>{{ $invoice->patient->patient_code }}</strong><br>
                                {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($invoice->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($invoice->balance_due, 2) }}</td>
                        <td>
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
                        </td>
                        <td>{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
                        <td>{{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No invoices found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
