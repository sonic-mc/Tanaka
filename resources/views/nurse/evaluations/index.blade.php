@extends('layouts.app')

@section('header')
    All Patient Evaluations
@endsection

@section('content')
@if($evaluations->isEmpty())
    <div class="alert alert-info">No evaluations found.</div>
@else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Evaluator</th>
                <th>Risk Level</th>
                <th>Notes</th>
                <th>Scores</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluations as $eval)
            <tr>
                <td>{{ $eval->created_at->format('d M Y') }}</td>
                <td>{{ $eval->patient->first_name }} {{ $eval->patient->last_name }}</td>
                <td>{{ $eval->evaluator->name ?? '—' }}</td>
                <td>
                    @if($eval->risk_level)
                        <span class="badge bg-{{ $eval->risk_level === 'severe' ? 'danger' : ($eval->risk_level === 'moderate' ? 'warning' : 'success') }}">
                            {{ ucfirst($eval->risk_level) }}
                        </span>
                    @else
                        —
                    @endif
                </td>
                <td>{{ $eval->notes ?? '—' }}</td>
                <td>
                    @if($eval->scores)
                        <ul class="list-unstyled mb-0">
                            @foreach(json_decode($eval->scores, true) as $key => $value)
                                <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                            @endforeach
                        </ul>
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
