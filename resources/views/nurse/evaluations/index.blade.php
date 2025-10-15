@extends('layouts.app')

@section('header')
    Patient Evaluations
@endsection

@section('content')

<ul class="nav nav-tabs mb-3" id="evalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">
            All Evaluations
        </button>
    </li>
    @if(auth()->user()->hasRole('psychiatrist'))
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="conduct-tab" data-bs-toggle="tab" data-bs-target="#conduct" type="button" role="tab">
            Conduct Evaluation
        </button>
    </li>
    @endif
</ul>

<div class="tab-content" id="evalTabsContent">

    {{-- Tab: All Evaluations --}}
    <div class="tab-pane fade show active" id="list" role="tabpanel">
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
                        @if(auth()->user()->hasRole('psychiatrist'))
                            <th>Actions</th>
                        @endif
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
                            @if($eval->scores && is_array($eval->scores))
                            <ul class="list-unstyled mb-0">
                                @foreach($eval->scores as $key => $value)
                                    <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                                @endforeach
                            </ul>
                        @else
                            —
                        @endif
                        
                        </td>
    
                        @if(auth()->user()->hasRole('psychiatrist'))
                        <td>
                            <a href="{{ route('evaluations.edit', $eval->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('evaluations.destroy', $eval->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this evaluation?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    

    {{-- Tab: Conduct New Evaluation (Psychiatrists only) --}}
    @if(auth()->user()->hasRole('psychiatrist'))
    <div class="tab-pane fade" id="conduct" role="tabpanel">

        <h5 class="mb-3">New Patient Evaluation</h5>

        <form method="POST" action="{{ route('evaluations.store') }}" class="row g-3">
            @csrf

            {{-- Select Patient --}}
            <div class="col-md-6">
                <label for="patient_id" class="form-label">Select Patient</label>
                <select name="patient_id" id="patient_id" class="form-select" required>
                    <option value="">— Select Patient —</option>
                    @foreach($allPatients as $patient)
                        <option value="{{ $patient->id }}">
                            {{ $patient->patient_code }} — {{ $patient->first_name }} {{ $patient->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Risk Level --}}
            <div class="col-md-6">
                <label for="risk_level" class="form-label">Risk Level</label>
                <select name="risk_level" id="risk_level" class="form-select" required>
                    <option value="">— Select Risk —</option>
                    <option value="mild">Mild</option>
                    <option value="moderate">Moderate</option>
                    <option value="severe">Severe</option>
                </select>
            </div>

            {{-- Notes --}}
            <div class="col-md-12">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Write evaluation notes..."></textarea>
            </div>

            {{-- Example Structured Scores --}}
            <div class="col-md-6">
                <label for="score_stress" class="form-label">Stress Score</label>
                <input type="number" min="0" max="10" name="scores[stress]" id="score_stress" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="score_mood" class="form-label">Mood Score</label>
                <input type="number" min="0" max="10" name="scores[mood]" id="score_mood" class="form-control">
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Save Evaluation</button>
            </div>
        </form>
    </div>
    @endif

</div>

@endsection
