@extends('layouts.app')

@section('title', 'New Billing')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    // choose store route; ensure one of these names exists in your routes
    if (Route::has('billing.store')) {
        $storeRoute = route('billing.store');
    } elseif (Route::has('billings.store')) {
        $storeRoute = route('billings.store');
    } else {
        $storeRoute = url('/billings');
    }
@endphp

<div class="container mt-4">
    <h2 class="mb-4">New Billing (Admitted Patients)</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $storeRoute }}">
        @csrf

        <div class="mb-3">
            <label for="admission_id" class="form-label">Admitted Patient</label>

            {{-- NOTE: option value is patient_id so the server receives patient_id for validation --}}
            <select id="admission_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                <option value="">-- Select admitted patient --</option>

                @if(isset($admissions) && $admissions->isNotEmpty())
                    @foreach($admissions as $admission)
                        @php
                            $p = $admission->patient;
                            $label = trim(($p->patient_code ?? '') . ' — ' . ($p->first_name ?? '') . ' ' . ($p->last_name ?? ''));
                            $selected = (string) old('patient_id') === (string) $p->id;
                        @endphp
                        <option value="{{ $p->id }}"
                                data-admission-id="{{ $admission->id }}"
                                {{ $selected ? 'selected' : '' }}>
                            {{ $label }} (Admitted: {{ optional($admission->admission_date)->format('Y-m-d') }})
                        </option>
                    @endforeach
                @else
                    <option disabled>No active admissions found</option>
                @endif
            </select>

            @error('patient_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="form-text">Only active admissions are listed. If the patient is not admitted they will not appear here.</div>
            @enderror
        </div>

        {{-- keep admission_id as hidden if you need it server-side; JS will populate it from selected option --}}
        <input type="hidden" name="admission_id" id="hidden_admission_id" value="{{ old('admission_id') }}">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="amount" class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00" required>
                </div>
                @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" class="form-control @error('due_date') is-invalid @enderror">
                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mt-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Any additional details...">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-file-earmark-plus me-1"></i> Create Invoice
            </button>
            @if(Route::has('invoices.index'))
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            @else
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const admissionSelect = document.getElementById('admission_id'); // now is patient select with admission data
    const hiddenAdmission = document.getElementById('hidden_admission_id');

    if (!admissionSelect || !hiddenAdmission) return;

    function syncAdmission() {
        const opt = admissionSelect.selectedOptions[0];
        hiddenAdmission.value = opt ? (opt.getAttribute('data-admission-id') || '') : '';
    }

    // initial sync (handles old values)
    syncAdmission();

    admissionSelect.addEventListener('change', syncAdmission);
});
</script>
@endpush
