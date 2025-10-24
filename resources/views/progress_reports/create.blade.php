@extends('layouts.app')

@section('header') New Progress Report @endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.progress-reports.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ old('patient_id')==$p->id ? 'selected' : '' }}>
                                    {{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Report date</label>
                        <input type="date" name="report_date" class="form-control" value="{{ old('report_date', date('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">PHQ‑9 total</label>
                        <input type="number" name="phq9_score" class="form-control" min="0" max="27" value="{{ old('phq9_score') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">GAD‑7 total</label>
                        <input type="number" name="gad7_score" class="form-control" min="0" max="21" value="{{ old('gad7_score') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Global severity (norm)</label>
                        <input type="number" step="0.1" name="global_severity_score" class="form-control" value="{{ old('global_severity_score') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Functional score</label>
                        <input type="number" step="0.1" name="functional_score" class="form-control" value="{{ old('functional_score') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Symptom summary</label>
                        <textarea name="symptom_summary" class="form-control">{{ old('symptom_summary') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Treatment Plan</label>
                        <textarea name="treatment_plan" class="form-control">{{ old('treatment_plan') }}</textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button class="btn btn-success">Save Report</button>
                        <a href="{{ route('admin.progress-reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
