@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Evaluation #{{ $evaluation->id }}</h1>

    <div class="alert alert-info">
        Note: Your current routes do not include an update endpoint. To enable saving changes, add:
        <code>Route::put('/patient-evaluations/{id}', [PatientEvaluationController::class, 'update'])->name('evaluations.update');</code>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- When you add the route above, switch action to route('evaluations.update', $evaluation->id) and enable the submit --}}
    <form action="#" method="POST" onsubmit="return false;">
        @csrf
        {{-- @method('PUT') --}}

        @include('patient_evaluations._form', [
            'patients' => $patients,
            'evaluationTypes' => $evaluationTypes,
            'severityLevels' => $severityLevels,
            'riskLevels' => $riskLevels,
            'decisions' => $decisions,
            'evaluation' => $evaluation,
        ])

        <div class="mt-4">
            <button type="button" class="btn btn-primary" disabled>Save Changes</button>
            <a href="{{ route('evaluations.show', $evaluation->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
