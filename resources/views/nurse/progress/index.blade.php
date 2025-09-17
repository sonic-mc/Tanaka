@extends('layouts.app')

@section('header')
    Progress Monitoring
@endsection

@section('content')
<table class="table table-striped">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Date</th>
            <th>Behavior</th>
            <th>Medication</th>
            <th>Attendance</th>
            <th>Score</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reports as $report)
        <tr>
            <td>{{ $report->patient->first_name }}</td>
            <td>{{ $report->report_date }}</td>
            <td>{{ Str::limit($report->behavior_notes, 40) }}</td>
            <td>{{ Str::limit($report->medication_response, 40) }}</td>
            <td>{{ $report->attendance_days }}</td>
            <td>{{ $report->progress_score }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
