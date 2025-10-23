@php
    $isEdit = isset($evaluation) && $evaluation;
@endphp

<div class="col-12">
    <div class="card border-0 bg-light-subtle">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Patient <span class="text-danger">*</span></label>
                    <select name="patient_id" id="patient_id" class="form-select" required>
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                @selected(old('patient_id', $selectedPatientId ?? ($evaluation->patient_id ?? null)) == $patient->id)>
                                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Start typing below to quickly find a patient.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quick Patient Search</label>
                    <div class="input-group">
                        <input type="text" id="patient_search" class="form-control" placeholder="Code, name, ID, passport">
                        <button type="button" id="patient_search_btn" class="btn btn-outline-secondary">Search</button>
                    </div>
                    <div id="patient_search_results" class="list-group mt-2" style="max-height: 200px; overflow:auto; display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-3">
    <label class="form-label">Evaluation Date <span class="text-danger">*</span></label>
    <input type="date" name="evaluation_date" class="form-control"
           value="{{ old('evaluation_date', optional($evaluation->evaluation_date ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
</div>

<div class="col-md-3">
    <label class="form-label">Evaluation Type <span class="text-danger">*</span></label>
    @php $etype = old('evaluation_type', $evaluation->evaluation_type ?? 'initial'); @endphp
    <select name="evaluation_type" class="form-select" required>
        <option value="initial" @selected($etype==='initial')>Initial</option>
        <option value="follow-up" @selected($etype==='follow-up')>Follow-up</option>
        <option value="emergency" @selected($etype==='emergency')>Emergency</option>
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Decision <span class="text-danger">*</span></label>
    @php $dec = old('decision', $evaluation->decision ?? 'outpatient'); @endphp
    <select name="decision" id="decision" class="form-select" required>
        <option value="admit" @selected($dec==='admit')>Admit</option>
        <option value="outpatient" @selected($dec==='outpatient')>Outpatient</option>
        <option value="refer" @selected($dec==='refer')>Refer</option>
        <option value="monitor" @selected($dec==='monitor')>Monitor</option>
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Requires Admission? <span class="text-danger">*</span></label>
    @php $reqAdm = (bool) old('requires_admission', $evaluation->requires_admission ?? false); @endphp
    <select name="requires_admission" id="requires_admission" class="form-select" required>
        <option value="0" @selected(!$reqAdm)>No</option>
        <option value="1" @selected($reqAdm)>Yes</option>
    </select>
</div>

<div class="col-12">
    <label class="form-label">Admission Trigger Notes</label>
    <textarea name="admission_trigger_notes" id="admission_trigger_notes" rows="2" class="form-control"
              placeholder="Why admission was recommended (if applicable)">{{ old('admission_trigger_notes', $evaluation->admission_trigger_notes ?? '') }}</textarea>
</div>

<div class="col-12">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Presenting Complaints</label>
            <textarea name="presenting_complaints" rows="3" class="form-control">{{ old('presenting_complaints', $evaluation->presenting_complaints ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Clinical Observations</label>
            <textarea name="clinical_observations" rows="3" class="form-control">{{ old('clinical_observations', $evaluation->clinical_observations ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Diagnosis</label>
            <textarea name="diagnosis" rows="3" class="form-control">{{ old('diagnosis', $evaluation->diagnosis ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Recommendations</label>
            <textarea name="recommendations" rows="3" class="form-control">{{ old('recommendations', $evaluation->recommendations ?? '') }}</textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const requiresAdmission = document.getElementById('requires_admission');
    const admissionNotes = document.getElementById('admission_trigger_notes');
    const decision = document.getElementById('decision');

    function toggleAdmissionNotes() {
        const need = requiresAdmission.value === '1' || decision.value === 'admit';
        admissionNotes.closest('.col-12').style.display = need ? '' : 'none';
        if (!need) admissionNotes.value = '';
    }
    requiresAdmission.addEventListener('change', toggleAdmissionNotes);
    decision.addEventListener('change', toggleAdmissionNotes);
    toggleAdmissionNotes();

    // Patient quick search
    const searchInput = document.getElementById('patient_search');
    const searchBtn = document.getElementById('patient_search_btn');
    const resultsBox = document.getElementById('patient_search_results');
    const selectPatient = document.getElementById('patient_id');

    async function runSearch() {
        const q = searchInput.value.trim();
        if (!q) { resultsBox.style.display = 'none'; resultsBox.innerHTML = ''; return; }
        const url = new URL('{{ route('patients.lookup') }}', window.location.origin);
        url.searchParams.set('q', q);
        try {
            const res = await fetch(url);
            const json = await res.json();
            const items = json.data || [];
            if (!items.length) {
                resultsBox.innerHTML = '<div class="list-group-item text-muted">No matches</div>';
                resultsBox.style.display = 'block';
                return;
            }
            resultsBox.innerHTML = '';
            items.forEach(item => {
                const a = document.createElement('button');
                a.type = 'button';
                a.className = 'list-group-item list-group-item-action';
                a.textContent = item.label;
                a.addEventListener('click', () => {
                    // Ensure selected option exists in select
                    let opt = [...selectPatient.options].find(o => o.value === String(item.id));
                    if (!opt) {
                        opt = new Option(item.label, item.id, true, true);
                        selectPatient.add(opt);
                    }
                    selectPatient.value = String(item.id);
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                });
                resultsBox.appendChild(a);
            });
            resultsBox.style.display = 'block';
        } catch (e) {
            console.error(e);
        }
    }

    searchBtn.addEventListener('click', runSearch);
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); runSearch(); }
    });
});
</script>
@endpush
