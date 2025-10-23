@php
    $isEdit = isset($assignment);
@endphp

<div class="col-md-6">
    <label class="form-label">Nurse <span class="text-danger">*</span></label>
    <select name="nurse_id" class="form-select" required>
        <option value="">-- Choose Nurse --</option>
        @foreach($nurses as $nurse)
            <option value="{{ $nurse->id }}" @selected(old('nurse_id', $assignment->nurse_id ?? null) == $nurse->id)>
                {{ $nurse->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-6">
    <label class="form-label">Patient (Active Admission) <span class="text-danger">*</span></label>
    <select name="admission_id" class="form-select" required>
        <option value="">-- Choose Patient --</option>
        @foreach($admissions as $admission)
            <option value="{{ $admission->id }}" @selected(old('admission_id', $prefillAdmissionId ?? ($assignment->admission_id ?? null)) == $admission->id)>
                {{ $admission->patient->first_name }} {{ $admission->patient->last_name }}
                ({{ $admission->patient->patient_code }})
                @if($admission->room_number) - Room {{ $admission->room_number }} @endif
            </option>
        @endforeach
    </select>
    <div class="form-text">Only active admissions are listed.</div>
</div>

<div class="col-md-4">
    <label class="form-label">Shift</label>
    @php $shift = old('shift', $assignment->shift ?? '') @endphp
    <select name="shift" class="form-select">
        <option value="">— Optional —</option>
        <option value="morning" @selected($shift==='morning')>Morning</option>
        <option value="evening" @selected($shift==='evening')>Evening</option>
        <option value="night" @selected($shift==='night')>Night</option>
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Assigned Date</label>
    <input type="date" name="assigned_date" class="form-control"
           value="{{ old('assigned_date', optional($assignment->assigned_date ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
</div>

<div class="col-md-4">
    <label class="form-label">Notes</label>
    <input type="text" name="notes" class="form-control" placeholder="Optional notes"
           value="{{ old('notes', $assignment->notes ?? '') }}">
</div>