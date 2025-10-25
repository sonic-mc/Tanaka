@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Patient Grading</h3>
        <div class="text-muted">Browse grading derived from clinical evaluations</div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-3">
                <input type="text" name="q" class="form-control" placeholder="Search patient (code/name)" value="{{ $filters['q'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                @php $severity = $filters['severity'] ?? '' @endphp
                <select name="severity" class="form-select">
                    <option value="">All Severities</option>
                    <option value="mild" @selected($severity==='mild')>Mild</option>
                    <option value="moderate" @selected($severity==='moderate')>Moderate</option>
                    <option value="severe" @selected($severity==='severe')>Severe</option>
                    <option value="critical" @selected($severity==='critical')>Critical</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                @php $risk = $filters['risk'] ?? '' @endphp
                <select name="risk" class="form-select">
                    <option value="">All Risks</option>
                    <option value="low" @selected($risk==='low')>Low</option>
                    <option value="medium" @selected($risk==='medium')>Medium</option>
                    <option value="high" @selected($risk==='high')>High</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                @php $decision = $filters['decision'] ?? '' @endphp
                <select name="decision" class="form-select">
                    <option value="">All Decisions</option>
                    <option value="admit" @selected($decision==='admit')>Admit</option>
                    <option value="outpatient" @selected($decision==='outpatient')>Outpatient</option>
                    <option value="refer" @selected($decision==='refer')>Refer</option>
                    <option value="monitor" @selected($decision==='monitor')>Monitor</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-grid">
                <button class="btn btn-outline-secondary">Apply</button>
            </div>
        </form>
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
