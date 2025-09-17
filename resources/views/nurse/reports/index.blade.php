@extends('layouts.app')

@section('header')
    Patient Reports
@endsection

@section('content')
<table class="table table-hover">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Last Evaluation</th>
            <th>Last Progress</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($patients as $patient)
        <tr>
            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
            <td>{{ optional($patient->evaluations->last())->evaluation_date ?? '—' }}</td>
            <td>{{ optional($patient->progressReports->last())->report_date ?? '—' }}</td>
            <td>
                <a href="{{ route('nurse.reports.show', $patient) }}" class="btn btn-sm btn-outline-secondary">View</a>
                <a href="{{ route('nurse.reports.print', $patient) }}" class="btn btn-sm btn-outline-success">Print</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
