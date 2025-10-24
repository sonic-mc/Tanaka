@extends('layouts.app')

@section('title', 'Care Level Details')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Care Level Details</h5>
            <div>
                <a href="{{ route('care_levels.edit', $care_level) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="{{ route('care_levels.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="card-body">
            <h6>{{ $care_level->name }}</h6>
            <p class="text-muted">{{ $care_level->description ?? 'No description provided.' }}</p>
        </div>
    </div>
</div>
@endsection
