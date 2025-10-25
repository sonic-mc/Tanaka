@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Patient Grading</h3>
        <div class="text-muted">Browse grading derived from clinical evaluations</div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between" style="min-height: 300px;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Patients by Severity</h6>
                        <small class="text-muted">Latest Evaluation</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-success">Mild: {{ $severityCounts['mild'] }}</span>
                        <span class="badge bg-warning text-dark">Moderate: {{ $severityCounts['moderate'] }}</span>
                        <span class="badge bg-danger">Severe: {{ $severityCounts['severe'] }}</span>
                        <span class="badge bg-dark">Critical: {{ $severityCounts['critical'] }}</span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <canvas id="severityBar" style="max-height: 200px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column justify-content-between" style="min-height: 300px;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Patients by Risk</h6>
                        <small class="text-muted">Latest Evaluation</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-success">Low: {{ $riskCounts['low'] }}</span>
                        <span class="badge bg-warning text-dark">Medium: {{ $riskCounts['medium'] }}</span>
                        <span class="badge bg-danger">High: {{ $riskCounts['high'] }}</span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <canvas id="riskPie" style="max-height: 200px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Summary badges -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Patients by Severity (latest evaluation)</h6>
                    <small class="text-muted">Totals</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-success">Mild: {{ $severityCounts['mild'] }}</span>
                    <span class="badge bg-warning text-dark">Moderate: {{ $severityCounts['moderate'] }}</span>
                    <span class="badge bg-danger">Severe: {{ $severityCounts['severe'] }}</span>
                    <span class="badge bg-dark">Critical: {{ $severityCounts['critical'] }}</span>
                </div>
                <div class="mt-3">
                    <canvas id="severityBar"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Patients by Risk (latest evaluation)</h6>
                    <small class="text-muted">Totals</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-success">Low: {{ $riskCounts['low'] }}</span>
                    <span class="badge bg-warning text-dark">Medium: {{ $riskCounts['medium'] }}</span>
                    <span class="badge bg-danger">High: {{ $riskCounts['high'] }}</span>
                </div>
                <div class="mt-3">
                    <canvas id="riskPie"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Decision</th>
                    <th>Severity</th>
                    <th>Risk</th>
                    <th>Priority</th>
                    <th>Psychiatrist</th>
                    <th class="text-end" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $eval)
                    @php
                        $sev = strtolower($eval->severity_level ?? 'mild');
                        $sevClass = match($sev) {
                            'critical' => 'bg-dark',
                            'severe' => 'bg-danger',
                            'moderate' => 'bg-warning text-dark',
                            default => 'bg-success',
                        };
                        $risk = strtolower($eval->risk_level ?? 'low');
                        $riskClass = match($risk) {
                            'high' => 'bg-danger',
                            'medium' => 'bg-warning text-dark',
                            default => 'bg-success',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $eval->patient?->first_name }} {{ $eval->patient?->last_name }}</div>
                            <div class="text-muted small">{{ $eval->patient?->patient_code }}</div>
                        </td>
                        <td>{{ optional($eval->evaluation_date)->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($eval->decision) }}</td>
                        <td><span class="badge {{ $sevClass }}">{{ ucfirst($sev) }}</span></td>
                        <td><span class="badge {{ $riskClass }}">{{ ucfirst($risk) }}</span></td>
                        <td>{{ $eval->priority_score ?? '—' }}</td>
                        <td>{{ $eval->psychiatrist?->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('grading.show', $eval) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <form class="d-inline" action="{{ route('grading.recalculate', $eval) }}" method="POST" onsubmit="return confirm('Recalculate and save grading now?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Recalculate</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No evaluations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($evaluations->hasPages())
        <div class="card-footer">
            {{ $evaluations->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<!-- Chart.js CDN (v4) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" integrity="sha256-Cv3psZ2/2NzmNo0+fD3oDg1o0G3E9dkXAhFQ8v4u3Xg=" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const severityCfg = @json($chartData['severity']);
    const riskCfg = @json($chartData['risk']);

    // Severity bar chart
    const ctxSeverity = document.getElementById('severityBar').getContext('2d');
    new Chart(ctxSeverity, {
        type: 'bar',
        data: {
            labels: severityCfg.labels,
            datasets: [{
                label: 'Patients',
                data: severityCfg.data,
                backgroundColor: severityCfg.colors,
                borderColor: '#e5e7eb',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });

    // Risk pie chart
    const ctxRisk = document.getElementById('riskPie').getContext('2d');
    new Chart(ctxRisk, {
        type: 'pie',
        data: {
            labels: riskCfg.labels,
            datasets: [{
                data: riskCfg.data,
                backgroundColor: riskCfg.colors,
                borderColor: '#ffffff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>
@endpush
