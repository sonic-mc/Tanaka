@extends('layouts.app')

@section('header')
    Incident Management
@endsection

@section('content')
<div class="container">
    <ul class="nav nav-tabs mb-3" id="incidentTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#record" type="button">Record Incident</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#view" type="button">View Incidents</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#analytics" type="button">Incident Analytics</button>
        </li>
        {{-- <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#staff" type="button">Staff Insights</button>
        </li> --}}
    </ul>

    <div class="tab-content">
        <!-- Record Incident -->
        <div class="tab-pane fade show active" id="record">
            <form method="POST" action="{{ route('incidents.store') }}" class="card card-body shadow-sm">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <select name="patient_id" class="form-select" required>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Incident Date</label>
                        <input type="date" name="incident_date" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Submit Report</button>
                </div>
            </form>
        </div>

        <!-- View Incidents -->
        <div class="tab-pane fade" id="view">
            <form method="GET" action="{{ route('incidents.index') }}" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search_patient" class="form-control" placeholder="Search by patient name" value="{{ request('search_patient') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="search_reporter" class="form-control" placeholder="Search by reporter name" value="{{ request('search_reporter') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="search_date" class="form-control" placeholder="Date" value="{{ request('search_date') }}">
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Reported By</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($report->incident_date)->toDateString() }}</td>
                                <td>{{ $report->patient->first_name }} {{ $report->patient->last_name }}</td>
                                <td>{{ $report->reportedBy->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($report->description, 80) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center">No incidents found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($reports, 'links'))
                <div class="mt-3">
                    {{ $reports->withQueryString()->links() }}
                </div>
            @endif
        </div>

        <!-- Incident Analytics -->
        <div class="tab-pane fade" id="analytics">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Incidents by Month</h5>
                            <canvas id="incidentsByMonthChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Recurrence by Patient</h5>
                            <canvas id="recurrenceChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            // Convert incident periods (YYYY-MM) to "Mon YYYY" labels when possible
            $incidentPeriodLabels = $incidentStats->keys()->map(function ($p) {
                try {
                    return \Carbon\Carbon::createFromFormat('Y-m', $p)->format('M Y');
                } catch (\Exception $e) {
                    return $p; // fallback to raw key
                }
            })->values();

            // Build recurrence labels from patient names
            $recurrenceLabels = $recurrenceStats->map(function ($r) {
                $first = optional($r->patient)->first_name;
                $last  = optional($r->patient)->last_name;
                return trim(($first ?? '') . ' ' . ($last ?? '')) ?: 'Unknown Patient';
            })->values();

            $recurrenceData = $recurrenceStats->pluck('count')->values();
            $incidentsByMonthData = $incidentStats->values()->values();
        @endphp

        <script>
            const incidentsByMonthLabels = {!! $incidentPeriodLabels->toJson() !!};
            const incidentsByMonthData = {!! $incidentsByMonthData->toJson() !!};

            const recurrenceLabels = {!! $recurrenceLabels->toJson() !!};
            const recurrenceData = {!! $recurrenceData->toJson() !!};
        </script>

        <!-- Staff Insights -->
        {{-- <div class="tab-pane fade" id="staff">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Staff Involvement Overview</h5>
                    <ul class="list-group">
                        @foreach($staffStats as $user)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $user->name }} ({{ $user->role }})
                                <span class="badge bg-primary rounded-pill">{{ $user->incident_reports_count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div> --}}
    </div>
</div>
@endsection
