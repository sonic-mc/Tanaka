@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<h3>Reassign / Edit Assignment</h3>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>There were some problems with your input:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('nurse-assignments.update', $assignment->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            @include('nurse_assignments._form', [
                'admissions' => $admissions,
                'nurses' => $nurses,
                'assignment' => $assignment,
                'prefillAdmissionId' => $assignment->admission_id
            ])
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
                <a href="{{ route('nurse-assignments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
