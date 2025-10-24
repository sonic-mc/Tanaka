@extends('layouts.app')

@section('header')
    Progress Trends — {{ $patient->first_name }} {{ $patient->last_name }}
@endsection

@section('content')
<div class="container">
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h5>{{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})</h5>
            <div class="small text-muted">Showing {{ count($series['labels']) }} measurement(s)</div>
        </div>
        <div>
            <a href="{{ route('admin.progress-reports.export', $patient->id) }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
            <a href="{{ route('admin.progress-reports.index', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary btn-sm">Back</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card p-3">
                <h6>PHQ‑9 / GAD‑7 / Global severity</h6>
                <div style="height:320px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h6>Quick Summary</h6>
                <ul class="list-unstyled mb-0">
                    @foreach($compare['metrics'] as $metric => $info)
                        <li class="mb-2">
                            <strong>{{ ucfirst(str_replace('_',' ',$metric)) }}:</strong>
                            <div>{{ $info['interpretation'] }}</div>
                            <div class="small text-muted">Current: {{ $info['current'] ?? '—' }} | Previous delta: {{ $info['delta_prev'] ?? '—' }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card p-3">
                <h6>Risk</h6>
                <p>{{ $compare['risk_note'] }}</p>
            </div>
        </div>
    </div>

    {{-- List of last reports --}}
    <div class="card">
        <div class="card-body">
            <h6>Recent Reports</h6>
            @foreach($reports as $r)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <div>
                        <strong>{{ $r->report_date->format('Y-m-d') }}</strong>
                        <div class="small text-muted">PHQ‑9: {{ $r->phq9_score ?? '—' }} • GAD‑7: {{ $r->gad7_score ?? '—' }}</div>
                    </div>
                    <div>
                        <a class="btn btn-sm btn-outline-info" href="{{ route('admin.progress-reports.show', $r) }}">View</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($series['labels']);
    const phq9 = @json($series['phq9']);
    const gad7 = @json($series['gad7']);
    const globalSeries = @json($series['global']);
    const functional = @json($series['functional']);

    const ctx = document.getElementById('trendChart').getContext('2d');

    new Chart(ctx, {
        data: {
            labels,
            datasets: [
                {
                    label: 'PHQ‑9',
                    data: phq9,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.08)',
                    tension: 0.25,
                    pointRadius: 4,
                },
                {
                    label: 'GAD‑7',
                    data: gad7,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14,165,233,0.08)',
                    tension: 0.25,
                    pointRadius: 4,
                },
                {
                    label: 'Global severity',
                    data: globalSeries,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.08)',
                    tension: 0.25,
                    pointRadius: 4,
                },
                {
                    label: 'Functional (higher better)',
                    data: functional,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.06)',
                    tension: 0.25,
                    pointRadius: 4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Severity (lower better)' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Function (higher better)' } }
            },
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
@endpush
@endsection
