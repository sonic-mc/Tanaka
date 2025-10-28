@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Patient Evaluation</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

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

    <form action="{{ route('evaluations.store') }}" method="POST">
        @csrf

        @include('patient_evaluations._form', [
            'patients' => $patients,
            'evaluationTypes' => $evaluationTypes,
            'severityLevels' => $severityLevels,
            'riskLevels' => $riskLevels,
            'decisions' => $decisions,
        ])

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Evaluation</button>
            <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
