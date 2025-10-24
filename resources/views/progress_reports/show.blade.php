@extends('layouts.app')

@section('title', 'Progress Report — ' . ($report->patient->patient_code ?? 'Report'))

@section('header')
    Progress Report Details
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1">
                {{ $report->patient->first_name ?? '' }} {{ $report->patient->last_name ?? '' }}
                <small class="text-muted">({{ $report->patient->patient_code ?? '—' }})</small>
            </h4>
            <div class="small text-muted">
                Report date: {{ optional($report->report_date)->format('Y-m-d') ?? optional($report->created_at)->format('Y-m-d') }}
                &nbsp;•&nbsp; Created: {{ optional($report->created_at)->format('Y-m-d H:i') }}
                @if($report->updated_at && $report->updated_at != $report->created_at)
                    &nbsp;•&nbsp; Updated: {{ optional($report->updated_at)->format('Y-m-d H:i') }}
                @endif
            </div>
            <div class="small text-muted mt-1">
                Clinician: {{ optional($report->clinician)->name ?? optional($report->creator)->name ?? '—' }}
                @if(optional($report->admission)->id)
                    &nbsp;•&nbsp; Admission: #{{ $report->admission->id }} ({{ optional($report->admission)->admission_date ? optional($report->admission)->admission_date->format('Y-m-d') : '—' }})
                @endif
                @if(optional($report->evaluation)->id)
                    &nbsp;•&nbsp; Evaluation ref: #{{ $report->evaluation->id }}
                @endif
            </div>
        </div>

        <div class="text-end">
            {{-- Action buttons: Edit, Compare, Export, Delete --}}
            @php
                $editRoute = Route::has('admin.progress-reports.edit') ? route('admin.progress-reports.edit', $report) : (Route::has('progress-reports.edit') ? route('progress-reports.edit', $report) : '#');
                $compareRoute = Route::has('admin.progress-reports.compare') ? route('admin.progress-reports.compare', $report->patient_id) : (Route::has('progress-reports.compare') ? route('progress-reports.compare', $report->patient_id) : '#');
                $exportRoute = Route::has('admin.progress-reports.export') ? route('admin.progress-reports.export', $report->patient_id) : (Route::has('progress-reports.export') ? route('progress-reports.export', $report->patient_id) : '#');
                $indexRoute = Route::has('admin.progress-reports.index') ? route('admin.progress-reports.index') : (Route::has('progress-reports.index') ? route('progress-reports.index') : url()->previous());
            @endphp

            <a href="{{ $compareRoute }}" class="btn btn-sm btn-outline-primary me-1">
                <i class="bi bi-graph-up"></i> Trend
            </a>

            <a href="{{ $exportRoute }}" class="btn btn-sm btn-outline-secondary me-1">
                <i class="bi bi-download"></i> Export CSV
            </a>

            <a href="{{ $editRoute }}" class="btn btn-sm btn-outline-warning me-1">
                <i class="bi bi-pencil"></i> Edit
            </a>

            <form action="{{ Route::has('admin.progress-reports.destroy') ? route('admin.progress-reports.destroy', $report) : (Route::has('progress-reports.destroy') ? route('progress-reports.destroy', $report) : '#') }}"
                  method="POST" class="d-inline-block" onsubmit="return confirm('Delete this report? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>

            <a href="{{ $indexRoute }}" class="btn btn-sm btn-light ms-2">Back</a>
        </div>
    </div>

    {{-- Top summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 modern-card">
                <div class="small text-muted">PHQ‑9</div>
                <div class="h4 mb-0">{{ $report->phq9_score ?? '—' }}</div>
                <div class="small text-muted">0–27 (lower better)</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 modern-card">
                <div class="small text-muted">GAD‑7</div>
                <div class="h4 mb-0">{{ $report->gad7_score ?? '—' }}</div>
                <div class="small text-muted">0–21 (lower better)</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 modern-card">
                <div class="small text-muted">Global Severity</div>
                <div class="h4 mb-0">{{ $report->global_severity_score ?? '—' }}</div>
                <div class="small text-muted">Normalized (site-specific)</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 modern-card">
                <div class="small text-muted">Functional Score</div>
                <div class="h4 mb-0">{{ $report->functional_score ?? '—' }}</div>
                <div class="small text-muted">Higher = better</div>
            </div>
        </div>
    </div>

    {{-- Chart area for this single report (shows scale snapshot) --}}
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="card-title">Snapshot metrics</h6>
            <div class="row">
                <div class="col-lg-9">
                    <canvas id="snapshotChart" style="height:260px;"></canvas>
                </div>
                <div class="col-lg-3">
                    <ul class="list-unstyled">
                        <li><strong>GAF:</strong> {{ $report->gaf_score ?? '—' }}</li>
                        <li><strong>HoNOS:</strong> {{ $report->honos_score ?? '—' }}</li>
                        <li><strong>BPRS:</strong> {{ $report->bprs_score ?? '—' }}</li>
                        <li><strong>CGI‑S:</strong> {{ $report->cgi_severity ?? '—' }}</li>
                        <li><strong>Risk level:</strong> <span class="badge bg-{{ $report->risk_level === 'high' ? 'danger' : ($report->risk_level === 'critical' ? 'dark' : ($report->risk_level === 'moderate' ? 'warning text-dark' : 'success')) }}">{{ ucfirst($report->risk_level) }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Narrative sections --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">Symptom Summary</div>
                <div class="card-body">
                    {!! nl2br(e($report->symptom_summary ?? 'No summary provided.')) !!}
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Clinical Observations</div>
                <div class="card-body">
                    {!! nl2br(e($report->observations ?? 'No observations recorded.')) !!}
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Treatment Plan & Medication Changes</div>
                <div class="card-body">
                    {!! nl2br(e($report->treatment_plan ?? 'No treatment plan recorded.')) !!}
                    @if($report->medication_changes)
                        <hr>
                        <h6 class="mb-2">Medication changes</h6>
                        {!! nl2br(e($report->medication_changes)) !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Risk assessment --}}
            <div class="card mb-3">
                <div class="card-header">Risk Assessment</div>
                <div class="card-body">
                    <p><strong>Level:</strong> <span class="badge bg-{{ $report->risk_level === 'high' ? 'danger' : ($report->risk_level === 'critical' ? 'dark' : ($report->risk_level === 'moderate' ? 'warning text-dark' : 'success')) }}">{{ ucfirst($report->risk_level) }}</span></p>
                    <div>{!! nl2br(e($report->risk_assessment ?? 'No risk assessment recorded.')) !!}</div>
                </div>
            </div>

            {{-- Structured metrics JSON --}}
            <div class="card mb-3">
                <div class="card-header">Structured metrics</div>
                <div class="card-body">
                    @if($report->metrics && is_array($report->metrics) && count($report->metrics))
                        <pre class="small mb-0" style="max-height:220px; overflow:auto;">{{ json_encode($report->metrics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @else
                        <div class="text-muted">No structured metrics recorded.</div>
                    @endif
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card mb-3">
                <div class="card-header">Attachments</div>
                <div class="card-body">
                    @if(!empty($report->attachments) && is_array($report->attachments))
                        <ul class="list-unstyled mb-0">
                            @foreach($report->attachments as $att)
                                @php
                                    // Att may be a URL or a storage path
                                    $url = filter_var($att, FILTER_VALIDATE_URL) ? $att : (Storage::exists($att) ? Storage::url($att) : $att);
                                @endphp
                                <li class="mb-2">
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="link-primary">
                                        <i class="bi bi-paperclip"></i> {{ basename($att) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted">No attachments</div>
                    @endif
                </div>
            </div>

            {{-- Administrative --}}
            <div class="card mb-3">
                <div class="card-header">Administrative</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Created by:</strong> {{ optional($report->creator)->name ?? '—' }}</p>
                    <p class="mb-1"><strong>Last modified by:</strong> {{ optional($report->lastModifier)->name ?? '—' }}</p>
                    <p class="mb-0"><strong>Record ID:</strong> {{ $report->id }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const snapshotCanvas = document.getElementById('snapshotChart');
    if (!snapshotCanvas || typeof Chart === 'undefined') return;

    const phq9 = {{ json_encode($report->phq9_score !== null ? (int)$report->phq9_score : null) }};
    const gad7 = {{ json_encode($report->gad7_score !== null ? (int)$report->gad7_score : null) }};
    const globalSeverity = {{ json_encode($report->global_severity_score !== null ? (float)$report->global_severity_score : null) }};
    const functional = {{ json_encode($report->functional_score !== null ? (float)$report->functional_score : null) }};
    const gaf = {{ json_encode($report->gaf_score !== null ? (float)$report->gaf_score : null) }};

    // Build dataset (only numeric, map nulls to 0 and track missing)
    const labels = ['PHQ‑9','GAD‑7','Global','Functional','GAF'];
    const raw = [phq9, gad7, globalSeverity, functional, gaf];
    const data = raw.map(v => v === null ? null : v);

    // If all null, do not render chart
    if (data.every(v => v === null)) return;

    const ctx = snapshotCanvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Score',
                data: data,
                backgroundColor: ['#f97316','#0ea5e9','#7c3aed','#10b981','#f59e0b'],
                borderRadius: 6,
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const v = ctx.parsed.y ?? ctx.parsed;
                            return `${ctx.dataset.label}: ${v === null ? '—' : v}`;
                        }
                    }
                },
                legend: { display: false }
            }
        }
    });
});
</script>
@endpush
