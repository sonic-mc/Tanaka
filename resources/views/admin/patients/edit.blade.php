@extends('layouts.app')

@section('header')
<h2 class="fw-bold text-primary">Edit Patient Info</h2>
@endsection

@section('content')
<form method="POST" action="{{ route('patients.update', $patient->id) }}">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Personal Info --}}
        <div class="col-md-6">
            <label for="patient_code" class="form-label">Patient Code</label>
            <input type="text" name="patient_code" id="patient_code" class="form-control" value="{{ old('patient_code', $patient->patient_code) }}" required>
        </div>
        <div class="col-md-6">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control" value="{{ old('contact_number', $patient->contact_number) }}">
        </div>
        <div class="col-md-6">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name', $patient->first_name) }}" required>
        </div>
        <div class="col-md-6">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $patient->last_name) }}" required>
        </div>
        <div class="col-md-4">
            <label for="gender" class="form-label">Gender</label>
            <select name="gender" id="gender" class="form-select" required>
                @foreach(['male', 'female', 'other'] as $gender)
                    <option value="{{ $gender }}" @selected($patient->gender === $gender)>{{ ucfirst($gender) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" name="dob" id="dob" class="form-control" value="{{ old('dob', $patient->dob) }}">
        </div>
        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                @foreach(['active', 'discharged'] as $status)
                    <option value="{{ $status }}" @selected($patient->status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Admission Info --}}
        <div class="col-md-6">
            <label for="admission_date" class="form-label">Admission Date</label>
            <input type="date" name="admission_date" id="admission_date" class="form-control" value="{{ old('admission_date', $patient->admission_date) }}" required>
        </div>
        <div class="col-md-6">
            <label for="room_number" class="form-label">Room Number</label>
            <input type="text" name="room_number" id="room_number" class="form-control" value="{{ old('room_number', $patient->room_number) }}">
        </div>
        <div class="col-md-12">
            <label for="admission_reason" class="form-label">Admission Reason</label>
            <textarea name="admission_reason" id="admission_reason" class="form-control" rows="3">{{ old('admission_reason', $patient->admission_reason) }}</textarea>
        </div>

        {{-- Assignments --}}
        <div class="col-md-6">
            <label for="assigned_nurse_id" class="form-label">Assigned Nurse</label>
            <select name="assigned_nurse_id" id="assigned_nurse_id" class="form-select">
                <option value="">— Select Nurse —</option>
                @foreach($nurses as $nurse)
                    <option value="{{ $nurse->id }}" @selected($patient->assigned_nurse_id === $nurse->id)>
                        {{ $nurse->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label for="current_care_level_id" class="form-label">Care Level</label>
            <select name="current_care_level_id" id="current_care_level_id" class="form-select">
                <option value="">— Select Care Level —</option>
                @foreach($careLevels as $level)
                    <option value="{{ $level->id }}" @selected($patient->current_care_level_id === $level->id)>
                        {{ $level->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i> Update Patient
        </button>
    </div>
</form>
@endsection
