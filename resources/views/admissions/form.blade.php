<div class="mb-3">
    <label for="patient_id" class="form-label">Patient</label>
    <select name="patient_id" class="form-select" required>
        <option value="">-- Select Patient --</option>
        @foreach($patients as $patient)
            <option value="{{ $patient->id }}" {{ (old('patient_id') ?? $admission->patient_id ?? '') == $patient->id ? 'selected' : '' }}>
                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="evaluation_id" class="form-label">Evaluation (if any)</label>
    <select name="evaluation_id" class="form-select">
        <option value="">-- Optional --</option>
        @foreach($evaluations as $eval)
            <option value="{{ $eval->id }}" {{ (old('evaluation_id') ?? $admission->evaluation_id ?? '') == $eval->id ? 'selected' : '' }}>
                {{ $eval->evaluation_date }} - {{ $eval->decision }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="admission_date" class="form-label">Admission Date</label>
    <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date') ?? $admission->admission_date ?? '' }}" required>
</div>

<div class="mb-3">
    <label for="admission_reason" class="form-label">Reason</label>
    <textarea name="admission_reason" class="form-control">{{ old('admission_reason') ?? $admission->admission_reason ?? '' }}</textarea>
</div>

<div class="mb-3">
    <label for="room_number" class="form-label">Room Number</label>
    <input type="text" name="room_number" class="form-control" value="{{ old('room_number') ?? $admission->room_number ?? '' }}">
</div>

<div class="mb-3">
    <label for="patient_id" class="form-label">Patient</label>
    <select name="patient_id" class="form-select" required>
        <option value="">-- Select Patient --</option>
        @foreach($patients as $patient)
            <option value="{{ $patient->id }}" {{ (old('patient_id') ?? $admission->patient_id ?? '') == $patient->id ? 'selected' : '' }}>
                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="evaluation_id" class="form-label">Evaluation (if applicable)</label>
    <select name="evaluation_id" class="form-select">
        <option value="">-- Optional --</option>
        @foreach($evaluations as $eval)
            <option value="{{ $eval->id }}" {{ (old('evaluation_id') ?? $admission->evaluation_id ?? '') == $eval->id ? 'selected' : '' }}>
                {{ $eval->evaluation_date }} - {{ ucfirst($eval->decision) }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="admission_date" class="form-label">Admission Date</label>
    <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date') ?? $admission->admission_date ?? '' }}" required>
</div>

<div class="mb-3">
    <label for="admission_reason" class="form-label">Admission Reason</label>
    <textarea name="admission_reason" class="form-control">{{ old('admission_reason') ?? $admission->admission_reason ?? '' }}</textarea>
</div>

<div class="mb-3">
    <label for="room_number" class="form-label">Room Number</label>
    <input type="text" name="room_number" class="form-control" value="{{ old('room_number') ?? $admission->room_number ?? '' }}">
</div>

<div class="mb-3">
    <label for="care_level_id" class="form-label">Care Level ID</label>
    <input type="number" name="care_level_id" class="form-control" value="{{ old('care_level_id') ?? $admission->care_level_id ?? '' }}">
</div>

@if(isset($admission))
<div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select name="status" class="form-select" required>
        <option value="active" {{ $admission->status == 'active' ? 'selected' : '' }}>Active</option>
        <option value="discharged" {{ $admission->status == 'discharged' ? 'selected' : '' }}>Discharged</option>
        <option value="transferred" {{ $admission->status == 'transferred' ? 'selected' : '' }}>Transferred</option>
        <option value="deceased" {{ $admission->status == 'deceased' ? 'selected' : '' }}>Deceased</option>
    </select>
</div>
@endif
