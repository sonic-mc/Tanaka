@extends('layouts.app')

@section('title', 'New Admission')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">New Admission</h5>
            <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>

        <div class="card-body">
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

            <form action="{{ route('admissions.store') }}" method="POST" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select id="patient_id" name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                        <option value="">-- Select patient (not currently admitted) --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ (old('patient_id') == $p->id) ? 'selected' : '' }}>
                                {{ $p->patient_code ?? '' }} — {{ $p->first_name }} {{ $p->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Only patients without an active admission are listed.</div>
                </div>

                <div class="col-md-6">
                    <label for="evaluation_id" class="form-label">Evaluation (if any)</label>
                    <select id="evaluation_id" name="evaluation_id" class="form-select">
                        <option value="">-- Optional --</option>
                        @foreach($evaluations as $eval)
                            <option value="{{ $eval->id }}" {{ (old('evaluation_id') == $eval->id) ? 'selected' : '' }}>
                                {{ optional($eval->evaluation_date)->format('Y-m-d') }} — {{ ucfirst($eval->decision) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="admission_date" class="form-label">Admission Date</label>
                    <input type="date" id="admission_date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" class="form-control @error('admission_date') is-invalid @enderror" required>
                    @error('admission_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="room_number" class="form-label">Room Number</label>
                    <input type="text" id="room_number" name="room_number" value="{{ old('room_number') }}" class="form-control @error('room_number') is-invalid @enderror">
                    @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="care_level_id" class="form-label">Care Level (ID)</label>
                    <input type="number" id="care_level_id" name="care_level_id" value="{{ old('care_level_id') }}" class="form-control @error('care_level_id') is-invalid @enderror">
                    @error('care_level_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="admission_reason" class="form-label">Reason for admission</label>
                    <textarea id="admission_reason" name="admission_reason" rows="4" class="form-control @error('admission_reason') is-invalid @enderror">{{ old('admission_reason') }}</textarea>
                    @error('admission_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-success">Create Admission</button>
                    <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
