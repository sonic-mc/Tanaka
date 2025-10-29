@extends('layouts.app')

@section('header')
    Patient Progress Reports
@endsection

@section('content')
<div class="container">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('warning')) <div class="alert alert-warning">{{ session('warning') }}</div> @endif

    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h6 class="mb-2">Create new progress report</h6>
                <a href="{{ route('progress-reports.create') }}" class="btn btn-success w-100">New Report</a>
            </div>

            <div class="card p-3">
                <h6 class="mb-2">Quick patient selector</h6>
                <form method="GET" action="{{ route('progress-reports.index') }}">
                    <div class="mb-2">
                        <select name="patient_id" class="form-select" required>
                            <option value="">Select patient</option>
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-outline-primary w-100">View reports</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Recent progress reports</h5>
                    @forelse($reports as $r)
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div>
                                    <strong>
                                        {{ optional($r->patient)->first_name ?? 'N/A' }}
                                        {{ optional($r->patient)->last_name ?? '' }}
                                    </strong>
                                    —
                                    {{ optional($r->patient)->patient_code ?? 'N/A' }}
                                </div>
                            
                                <div class="small text-muted">
                                    {{ $r->report_date ? $r->report_date->format('Y-m-d') : '—' }}
                                    by
                                    {{ optional($r->creator)->name ?? '—' }}
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <a href="{{ route('admin.progress-reports.show', $r) }}" class="btn btn-sm btn-outline-info">View</a>
                                <a href="{{ route('admin.progress-reports.compare', $r->patient_id) }}" class="btn btn-sm btn-outline-secondary">Trend</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No reports yet.</p>
                    @endforelse

                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>

            @if(isset($selectedPatient))
                <div class="card">
                    <div class="card-body">
                        <h5>Selected patient: {{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }}</h5>

                        @if($patientReports->isEmpty())
                            <p class="text-muted">No reports found for this patient.</p>
                        @else
                            <p>
                                <a href="{{ route('admin.progress-reports.compare', $selectedPatient->id) }}" class="btn btn-primary btn-sm">Open trend & comparison</a>
                                <a href="{{ route('admin.progress-reports.export', $selectedPatient->id) }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
                            </p>

                            @foreach($patientReports as $pr)
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>{{ optional($pr->report_date)->format('Y-m-d') }}</strong>
                                                <div class="small text-muted">By: {{ optional($pr->creator)->name }}</div>
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.progress-reports.show', $pr) }}" class="btn btn-sm btn-outline-info">Details</a>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="me-3"><strong>PHQ‑9:</strong> {{ $pr->phq9_score ?? '—' }}</span>
                                            <span class="me-3"><strong>GAD‑7:</strong> {{ $pr->gad7_score ?? '—' }}</span>
                                            <span class="me-3"><strong>Global:</strong> {{ $pr->global_severity_score ?? '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
