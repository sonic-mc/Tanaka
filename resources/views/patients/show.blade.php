@extends('layouts.app')

@section('content')
<h3>Patient Profile</h3>

<div class="card mb-3">
    <div class="card-body d-flex">
        <div class="me-3">
            @if($patient->photo)
                <img src="{{ asset('storage/'.$patient->photo) }}" alt="Photo" width="120" height="120" class="rounded object-fit-cover">
            @else
                <div class="bg-secondary rounded d-inline-block" style="width:120px;height:120px;"></div>
            @endif
        </div>
        <div>
            <p class="mb-1"><strong>Status:</strong> {{ $patient->deleted_at ? 'Archived' : 'Active' }}</p>
            <p class="mb-1"><strong>Code:</strong> {{ $patient->patient_code }}</p>
            <p class="mb-1"><strong>Name:</strong> {{ $patient->full_name }}</p>
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
                <p><strong>National ID:</strong> {{ $patient->national_id_number }}</p>
                <p><strong>Passport:</strong> {{ $patient->passport_number }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Contact & Demographics</div>
            <div class="card-body">
                <p><strong>Address:</strong> {{ $patient->residential_address }}</p>
                <p><strong>Race:</strong> {{ $patient->race }}</p>
                <p><strong>Religion:</strong> {{ $patient->religion }}</p>
                <p><strong>Language:</strong> {{ $patient->language }}</p>
                <p><strong>Denomination:</strong> {{ $patient->denomination }}</p>
                <p><strong>Marital Status:</strong> {{ $patient->marital_status }}</p>
                <p><strong>Occupation:</strong> {{ $patient->occupation }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Next of Kin</div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $patient->next_of_kin_name }}</p>
                <p><strong>Relationship:</strong> {{ $patient->next_of_kin_relationship }}</p>
                <p><strong>Contact:</strong> {{ $patient->next_of_kin_contact_number }}</p>
                <p><strong>Email:</strong> {{ $patient->next_of_kin_email }}</p>
                <p><strong>Address:</strong> {{ $patient->next_of_kin_address }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">Medical Information</div>
            <div class="card-body">
                <p><strong>Blood Group:</strong> {{ $patient->blood_group }}</p>
                <p><strong>Allergies:</strong> {{ $patient->allergies }}</p>
                <p><strong>Disabilities:</strong> {{ $patient->disabilities }}</p>
                <p><strong>Special Diet:</strong> {{ $patient->special_diet }}</p>
                <p><strong>Medical Aid Provider:</strong> {{ $patient->medical_aid_provider }}</p>
                <p><strong>Medical Aid Number:</strong> {{ $patient->medical_aid_number }}</p>
                <p><strong>Special Requirements:</strong> {{ $patient->special_medical_requirements }}</p>
                <p><strong>Current Medications:</strong> {{ $patient->current_medications }}</p>
                <p><strong>Past Medical History:</strong> {{ $patient->past_medical_history }}</p>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Audit</div>
            <div class="card-body">
                <p><strong>Created At:</strong> {{ $patient->created_at }}</p>
                <p><strong>Updated At:</strong> {{ $patient->updated_at }}</p>
                <p><strong>Created By:</strong> {{ optional($patient->creator)->name ?? $patient->created_by }}</p>
                <p><strong>Last Modified By:</strong> {{ optional($patient->lastModifier)->name ?? $patient->last_modified_by }}</p>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    @if(!$patient->deleted_at)
        <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-warning">Edit</a>
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
    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Back</a>
</div>
@endsection
