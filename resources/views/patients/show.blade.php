@extends('layouts.app')

@section('title', 'Patient Profile')

@section('content')
@php
    use App\Models\Invoice;

    try {
        if (isset($patient) && isset($patient->invoices) && $patient->invoices instanceof \Illuminate\Support\Collection) {
            $invoices = $patient->invoices;
        } elseif (isset($patient) && method_exists($patient, 'invoices')) {
            $invoices = $patient->invoices()->get();
        } else {
            $invoices = Invoice::where('patient_id', $patient->id ?? 0)->get();
        }
    } catch (\Throwable $e) {
        $invoices = Invoice::where('patient_id', $patient->id ?? 0)->get();
    }

    // Normalize gender and build a robust fallback chain for the avatar
    $g = strtolower($patient->gender ?? '');
    $avatarPath = match ($g) {
        'female' => 'images/avatars/female.svg',
        'male' => 'images/avatars/male.svg',
        default => 'images/avatars/other.svg',
    };
    // Server-side fallback if target file is missing (prevents broken src)
    if (!file_exists(public_path($avatarPath))) {
        $avatarPath = 'images/avatars/male.svg';
    }
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Patient Profile</h3>
    <div>
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Back</a>
        @if(!$patient->deleted_at)
            <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-warning">Edit</a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card mb-3">
    <div class="card-body d-flex">
        <div class="me-3">
            <img
                src="{{ asset($avatarPath) }}"
                alt="Avatar"
                width="120"
                height="120"
                class="rounded object-fit-cover"
                onerror="this.onerror=null;this.src='{{ asset('images/avatars/male.svg') }}';"
            >
        </div>
        <div>
            <p class="mb-1"><strong>Status:</strong> {{ $patient->deleted_at ? 'Archived' : 'Active' }}</p>
            <p class="mb-1"><strong>Code:</strong> {{ $patient->patient_code }}</p>
            <p class="mb-1"><strong>Name:</strong>
                {{ $patient->first_name }}{{ $patient->middle_name ? ' ' . $patient->middle_name : '' }} {{ $patient->last_name }}
            </p>
            <p class="mb-1"><strong>Gender:</strong> {{ ucfirst($patient->gender) }}</p>
            <p class="mb-1"><strong>DOB:</strong> {{ optional($patient->dob)->format('Y-m-d') }}</p>
            <p class="mb-1"><strong>Contact:</strong> {{ $patient->contact_number }}</p>
            <p class="mb-1"><strong>Email:</strong> {{ $patient->email }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Identification</div>
            <div class="card-body">
                <p class="mb-1"><strong>National ID:</strong> {{ $patient->national_id_number }}</p>
                <p class="mb-1"><strong>Passport:</strong> {{ $patient->passport_number }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Contact & Demographics</div>
            <div class="card-body">
                <p class="mb-1"><strong>Address:</strong> {{ $patient->residential_address }}</p>
                <p class="mb-1"><strong>Race:</strong> {{ $patient->race }}</p>
                <p class="mb-1"><strong>Religion:</strong> {{ $patient->religion }}</p>
                <p class="mb-1"><strong>Language:</strong> {{ $patient->language }}</p>
                {{-- <p class="mb-1"><strong>Denomination:</strong> {{ $patient->denomination }}</p> --}}
                <p class="mb-1"><strong>Marital Status:</strong> {{ $patient->marital_status }}</p>
                <p class="mb-1"><strong>Occupation:</strong> {{ $patient->occupation }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Next of Kin</div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> {{ $patient->next_of_kin_name }}</p>
                <p class="mb-1"><strong>Relationship:</strong> {{ $patient->next_of_kin_relationship }}</p>
                <p class="mb-1"><strong>Contact:</strong> {{ $patient->next_of_kin_contact_number }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $patient->next_of_kin_email }}</p>
                <p class="mb-1"><strong>Address:</strong> {{ $patient->next_of_kin_address }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Medical Information</div>
            <div class="card-body">
                {{-- <p class="mb-1"><strong>Blood Group:</strong> {{ $patient->blood_group }}</p> --}}
                <p class="mb-1"><strong>Allergies:</strong> {{ $patient->allergies }}</p>
                <p class="mb-1"><strong>Disabilities:</strong> {{ $patient->disabilities }}</p>
                <p class="mb-1"><strong>Special Diet:</strong> {{ $patient->special_diet }}</p>
                <p class="mb-1"><strong>Medical Aid Provider:</strong> {{ $patient->medical_aid_provider }}</p>
                <p class="mb-1"><strong>Medical Aid Number:</strong> {{ $patient->medical_aid_number }}</p>
                <p class="mb-1"><strong>Special Requirements:</strong> {{ $patient->special_medical_requirements }}</p>
                <p class="mb-1"><strong>Current Medications:</strong> {{ $patient->current_medications }}</p>
                <p class="mb-1"><strong>Past Medical History:</strong> {{ $patient->past_medical_history }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Audit</div>
            <div class="card-body">
                <p class="mb-1"><strong>Created At:</strong> {{ optional($patient->created_at)->format('Y-m-d H:i') }}</p>
                <p class="mb-1"><strong>Updated At:</strong> {{ optional($patient->updated_at)->format('Y-m-d H:i') }}</p>
                <p class="mb-1"><strong>Created By:</strong> {{ optional($patient->creator)->name ?? $patient->created_by }}</p>
                <p class="mb-1"><strong>Last Modified By:</strong> {{ optional($patient->lastModifier)->name ?? $patient->last_modified_by }}</p>
            </div>
        </div>
    </div>
</div>

@php
    $sessionNewInvoiceId = session('new_invoice_id');
@endphp

@if($sessionNewInvoiceId)
    @php
        $newInvoice = $invoices->firstWhere('id', $sessionNewInvoiceId) ?? Invoice::find($sessionNewInvoiceId);
    @endphp

    @if($newInvoice)
        <div class="mb-3">
            <a href="{{ route('invoices.download', $newInvoice->id) }}" target="_blank" class="btn btn-lg btn-primary">
                Download Newly Created Invoice — {{ $newInvoice->invoice_number }}
            </a>
            <span class="ms-2 text-muted small">Opens in a new tab for download; you remain on this page.</span>
        </div>
    @endif
@endif

<h5 class="mt-4">Invoices</h5>

@if($invoices->isEmpty())
    <div class="text-muted">No invoices found for this patient.</div>
@else
    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Issue Date</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                    <tr>
                        <td>{{ $inv->invoice_number }}</td>
                        <td>{{ optional($inv->issue_date)->format('Y-m-d') }}</td>
                        <td class="text-end">${{ number_format($inv->amount, 2) }}</td>
                        <td class="text-end">${{ number_format($inv->balance_due, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</td>
                        <td class="text-end">
                            <a href="{{ route('invoices.download', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                Download
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="mt-3">
    @if(!$patient->deleted_at)
        <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-danger" onclick="return confirm('Archive this patient?')">Archive</button>
        </form>
    @else
        <form action="{{ route('patients.restore', $patient->id) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-secondary" onclick="return confirm('Restore this patient?')">Restore</button>
        </form>

        <form action="{{ route('patients.force-delete', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this patient? This cannot be undone.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">Delete Permanently</button>
        </form>
    @endif

    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Back to list</a>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(session()->has('new_invoice_id'))
        (function() {
            var invoiceId = @json(session('new_invoice_id'));
            if (!invoiceId) return;

            var urlTemplate = "{{ route('invoices.download', ['invoice' => '__ID__']) }}";
            var downloadUrl = urlTemplate.replace('__ID__', encodeURIComponent(invoiceId));

            var win = window.open(downloadUrl, '_blank');
            if (win) win.focus();
        })();
    @endif
});
</script>
@endpush
