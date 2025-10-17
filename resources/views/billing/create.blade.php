@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">New Billing</h2>

    <form method="POST" action="{{ route('billing.store') }}">
        @csrf

        <div class="mb-3">
            <label for="patient_id" class="form-label">Patient</label>
            <select id="patient_id" name="patient_id" class="form-select" required>
                <option value="">-- Select patient --</option>
                @foreach($patients as $p)
                    <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>
                        {{ $p->patient_code }} — {{ $p->first_name }} {{ $p->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" class="form-control" placeholder="0.00" required>
            </div>

            <div class="col-md-6">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" class="form-control">
            </div>
        </div>

        <div class="mt-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="4" class="form-control" placeholder="Any additional details...">{{ old('notes') }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-file-earmark-plus me-1"></i> Create Invoice
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
