@php
    // Props:
    // $patients, $evaluationTypes, $severityLevels, $riskLevels, $decisions
    // Optional: $evaluation (for edit)
@endphp

<div class="space-y-6">
    <div>
        <label for="patient_id">Patient</label>
        <select name="patient_id" id="patient_id" class="form-control" required>
            <option value="">-- Select patient --</option>
            @foreach($patients as $p)
                <option value="{{ $p->id }}"
                    @selected(old('patient_id', $evaluation->patient_id ?? '') == $p->id)>
                    {{ $p->patient_code }} — {{ trim($p->first_name . ' ' . ($p->middle_name ? $p->middle_name . ' ' : '') . $p->last_name) }}
                </option>
            @endforeach
        </select>
        @error('patient_id')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="evaluation_date">Evaluation Date</label>
            <input type="date" id="evaluation_date" name="evaluation_date" class="form-control"
                   value="{{ old('evaluation_date', optional($evaluation->evaluation_date ?? now())->format('Y-m-d')) }}" required>
            @error('evaluation_date')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="evaluation_type">Type</label>
            <select name="evaluation_type" id="evaluation_type" class="form-control" required>
                @foreach($evaluationTypes as $type)
                    <option value="{{ $type }}" @selected(old('evaluation_type', $evaluation->evaluation_type ?? 'initial') === $type)>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
            @error('evaluation_type')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="priority_score">Priority Score (1–10)</label>
            <input type="number" min="1" max="10" id="priority_score" name="priority_score" class="form-control"
                   value="{{ old('priority_score', $evaluation->priority_score ?? '') }}">
            @error('priority_score')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label for="severity_level">Severity</label>
            <select name="severity_level" id="severity_level" class="form-control" required>
                @foreach($severityLevels as $level)
                    <option value="{{ $level }}" @selected(old('severity_level', $evaluation->severity_level ?? 'mild') === $level)>
                        {{ ucfirst($level) }}
                    </option>
                @endforeach
            </select>
            @error('severity_level')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="risk_level">Risk</label>
            <select name="risk_level" id="risk_level" class="form-control" required>
                @foreach($riskLevels as $risk)
                    <option value="{{ $risk }}" @selected(old('risk_level', $evaluation->risk_level ?? 'low') === $risk)>
                        {{ ucfirst($risk) }}
                    </option>
                @endforeach
            </select>
            @error('risk_level')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label for="decision">Decision</label>
            <select name="decision" id="decision" class="form-control" required>
                @foreach($decisions as $d)
                    <option value="{{ $d }}" @selected(old('decision', $evaluation->decision ?? 'outpatient') === $d)>
                        {{ ucfirst($d) }}
                    </option>
                @endforeach
            </select>
            @error('decision')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
    </div>

    <div>
        <label for="presenting_complaints">Presenting Complaints</label>
        <textarea id="presenting_complaints" name="presenting_complaints" rows="3" class="form-control">{{ old('presenting_complaints', $evaluation->presenting_complaints ?? '') }}</textarea>
        @error('presenting_complaints')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div>
        <label for="clinical_observations">Clinical Observations</label>
        <textarea id="clinical_observations" name="clinical_observations" rows="3" class="form-control">{{ old('clinical_observations', $evaluation->clinical_observations ?? '') }}</textarea>
        @error('clinical_observations')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div>
        <label for="diagnosis">Diagnosis</label>
        <textarea id="diagnosis" name="diagnosis" rows="3" class="form-control">{{ old('diagnosis', $evaluation->diagnosis ?? '') }}</textarea>
        @error('diagnosis')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div>
        <label for="recommendations">Recommendations</label>
        <textarea id="recommendations" name="recommendations" rows="3" class="form-control">{{ old('recommendations', $evaluation->recommendations ?? '') }}</textarea>
        @error('recommendations')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="form-check mt-3">
        <input type="checkbox" class="form-check-input" id="requires_admission" name="requires_admission"
               value="1" @checked(old('requires_admission', $evaluation->requires_admission ?? false))>
        <label class="form-check-label" for="requires_admission">Requires Admission</label>
        @error('requires_admission')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div id="admission_notes_group" class="mt-2" style="display: none;">
        <label for="admission_trigger_notes">Admission Trigger Notes</label>
        <textarea id="admission_trigger_notes" name="admission_trigger_notes" rows="2" class="form-control">{{ old('admission_trigger_notes', $evaluation->admission_trigger_notes ?? '') }}</textarea>
        @error('admission_trigger_notes')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script>
    function shouldAutoRequireAdmission() {
        const decision = document.getElementById('decision')?.value;
        const severity = document.getElementById('severity_level')?.value;
        const risk = document.getElementById('risk_level')?.value;
        const score = parseInt(document.getElementById('priority_score')?.value || '0', 10);

        return (decision === 'admit') || (severity === 'critical') || (risk === 'high') || (score >= 8);
    }

    function updateAdmissionUI() {
        const checkbox = document.getElementById('requires_admission');
        const group = document.getElementById('admission_notes_group');

        if (!checkbox || !group) return;

        if (checkbox.checked) {
            group.style.display = 'block';
        } else {
            group.style.display = shouldAutoRequireAdmission() ? 'block' : 'none';
        }
    }

    ['decision', 'severity_level', 'risk_level', 'priority_score', 'requires_admission']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', updateAdmissionUI);
        });

    document.addEventListener('DOMContentLoaded', updateAdmissionUI);
</script>
@endpush
