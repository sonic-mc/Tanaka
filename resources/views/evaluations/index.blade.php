@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Patient Evaluations</h3>
        <div class="text-muted">Review, filter, and manage evaluations</div>
    </div>

    <a href="{{ route('evaluations.create') }}" class="btn btn-primary">
        <i class="bi bi-clipboard-plus me-1"></i> New Evaluation
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-3">
                <input type="text" name="q" class="form-control" placeholder="Search patient (code/name/ID/passport)" value="{{ $filters['q'] ?? '' }}">
            </div>
            <div class="col-6 col-md-2">
                <select name="type" class="form-select">
                    @php $type = $filters['type'] ?? '' @endphp
                    <option value="">All Types</option>
                    <option value="initial" {{ $type==='initial'?'selected':'' }}>Initial</option>
                    <option value="follow-up" {{ $type==='follow-up'?'selected':'' }}>Follow-up</option>
                    <option value="emergency" {{ $type==='emergency'?'selected':'' }}>Emergency</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="decision" class="form-select">
                    @php $decision = $filters['decision'] ?? '' @endphp
                    <option value="">All Decisions</option>
                    <option value="admit" {{ $decision==='admit'?'selected':'' }}>Admit</option>
                    <option value="outpatient" {{ $decision==='outpatient'?'selected':'' }}>Outpatient</option>
                    <option value="refer" {{ $decision==='refer'?'selected':'' }}>Refer</option>
                    <option value="monitor" {{ $decision==='monitor'?'selected':'' }}>Monitor</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}" placeholder="From">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}" placeholder="To">
            </div>
            <div class="col-6 col-md-1">
                <select name="status" class="form-select">
                    @php $status = $filters['status'] ?? 'active' @endphp
                    <option value="active" {{ $status==='active'?'selected':'' }}>Active</option>
                    <option value="trashed" {{ $status==='trashed'?'selected':'' }}>Archived</option>
                    <option value="all" {{ $status==='all'?'selected':'' }}>All</option>
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
                    <th>Type</th>
                    <th>Decision</th>
                    <th>Severity</th>
                    <th>Risk</th>
                    <th>Priority</th>
                    <th>Psychiatrist</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 260px;">Actions</th>
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
                    <tr @if($eval->deleted_at) class="table-warning" @endif>
                        <td>
                            <div class="fw-semibold">{{ $eval->patient?->first_name }} {{ $eval->patient?->last_name }}</div>
                            <div class="text-muted small">{{ $eval->patient?->patient_code }}</div>
                        </td>
                        <td>{{ optional($eval->evaluation_date)->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($eval->evaluation_type) }}</td>
                        <td>{{ ucfirst($eval->decision) }}</td>
                        <td><span class="badge {{ $sevClass }}">{{ ucfirst($sev) }}</span></td>
                        <td><span class="badge {{ $riskClass }}">{{ ucfirst($risk) }}</span></td>
                        <td>{{ $eval->priority_score !== null ? $eval->priority_score : '—' }}</td>
                        <td>{{ $eval->psychiatrist?->name ?? '—' }}</td>
                        <td>{{ $eval->deleted_at ? 'Archived' : 'Active' }}</td>
                        <td class="text-end">
                            <a href="{{ route('evaluations.show', $eval->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @if(!$eval->deleted_at)
                                <a href="{{ route('evaluations.edit', $eval->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('evaluations.destroy', $eval->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this evaluation?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Archive</button>
                                </form>
                            @else
                                <form action="{{ route('evaluations.restore', $eval->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Restore this evaluation?')">
                                    @csrf
                                    <button class="btn btn-sm btn-secondary">Restore</button>
                                </form>
                                <form action="{{ route('evaluations.force-delete', $eval->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this evaluation? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete Permanently</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted">No evaluations found.</td></tr>
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
