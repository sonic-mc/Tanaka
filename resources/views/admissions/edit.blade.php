@extends('layouts.app')

@section('title', 'Edit Admission')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Admission</h5>
            <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        <div class="card-body">
            <form action="{{ route('admissions.update', $admission) }}" method="POST" class="row g-3">
                @csrf @method('PUT')

                <div class="col-md-6">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select id="patient_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ (old('patient_id', $admission->patient_id) == $p->id) ? 'selected' : '' }}>
                                {{ $p->patient_code ?? '' }} — {{ $p->first_name }} {{ $p->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="evaluation_id" class="form-label">Evaluation (if applicable)</label>
                    <select id="evaluation_id" name="evaluation_id" class="form-select">
                        <option value="">-- Optional --</option>
                        @foreach($evaluations as $eval)
                            <option value="{{ $eval->id }}" {{ (old('evaluation_id', $admission->evaluation_id) == $eval->id) ? 'selected' : '' }}>
                                {{ optional($eval->evaluation_date)->format('Y-m-d') }} — {{ ucfirst($eval->decision) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="admission_date" class="form-label">Admission Date</label>
                    <input type="date" id="admission_date" name="admission_date" value="{{ old('admission_date', optional($admission->admission_date)->format('Y-m-d')) }}" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="room_number" class="form-label">Room Number</label>
                    <input type="text" id="room_number" name="room_number" value="{{ old('room_number', $admission->room_number) }}" class="form-control">
                </div>

                <div class="col-md-4">
                    <label for="care_level_id" class="form-label">Care Level (ID)</label>
                    <input type="number" id="care_level_id" name="care_level_id" value="{{ old('care_level_id', $admission->care_level_id) }}" class="form-control">
                </div>

                <div class="col-12">
                    <label for="admission_reason" class="form-label">Admission Reason</label>
                    <textarea id="admission_reason" name="admission_reason" rows="4" class="form-control">{{ old('admission_reason', $admission->admission_reason) }}</textarea>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="active" {{ old('status', $admission->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="discharged" {{ old('status', $admission->status) == 'discharged' ? 'selected' : '' }}>Discharged</option>
                        <option value="transferred" {{ old('status', $admission->status) == 'transferred' ? 'selected' : '' }}>Transferred</option>
                        <option value="deceased" {{ old('status', $admission->status) == 'deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-primary">Update Admission</button>
                    <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
