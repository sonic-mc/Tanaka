@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Patient Evaluations</h1>

    <div class="mb-3">
        <a href="{{ route('evaluations.create') }}" class="btn btn-primary">New Evaluation</a>
    </div>

    <form method="GET" class="card card-body mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label>Patient</label>
                <select name="patient_id" class="form-control">
                    <option value="">All</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" @selected(request('patient_id') == $p->id)>
                            {{ $p->patient_code }} — {{ trim($p->first_name . ' ' . ($p->middle_name ? $p->middle_name . ' ' : '') . $p->last_name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Type</label>
                <select name="evaluation_type" class="form-control">
                    <option value="">All</option>
                    @foreach($filters['evaluation_types'] as $t)
                        <option value="{{ $t }}" @selected(request('evaluation_type') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Severity</label>
                <select name="severity_level" class="form-control">
                    <option value="">All</option>
                    @foreach($filters['severity_levels'] as $s)
                        <option value="{{ $s }}" @selected(request('severity_level') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Risk</label>
                <select name="risk_level" class="form-control">
                    <option value="">All</option>
                    @foreach($filters['risk_levels'] as $r)
                        <option value="{{ $r }}" @selected(request('risk_level') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label>From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-1">
                <label>To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-md-4 mt-3">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search patient/diagnosis/complaints">
            </div>
            <div class="col-md-2 mt-3">
                <button class="btn btn-outline-secondary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>

    @if ($evaluations->count())
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Risk</th>
                        <th>Decision</th>
                        <th>Priority</th>
                        <th>Requires Admission</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($evaluations as $e)
                    <tr>
                        <td>{{ $e->evaluation_date?->format('Y-m-d') }}</td>
                        <td>
                            {{ $e->patient?->patient_code }} — {{ $e->patient?->full_name }}
                        </td>
                        <td>{{ ucfirst($e->evaluation_type) }}</td>
                        <td>{{ ucfirst($e->severity_level) }}</td>
                        <td>{{ ucfirst($e->risk_level) }}</td>
                        <td>{{ ucfirst($e->decision) }}</td>
                        <td>{{ $e->priority_score ?? '—' }}</td>
                        <td>
                            @if($e->requires_admission)
                                <span class="badge bg-danger">Yes</span>
                            @else
                                <span class="badge bg-success">No</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('evaluations.show', $e->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="{{ route('evaluations.edit', $e->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $evaluations->links() }}
    @else
        <p class="text-muted">No evaluations found.</p>
    @endif
</div>
@endsection
