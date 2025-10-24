@extends('layouts.app')

@section('title', 'Edit Consultation Fee')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Fee</h5>
        <a href="{{ route('consultation_fees.index') }}" class="btn btn-sm btn-outline-secondary">Back to list</a>
    </div>
    <div class="card-body">
        <form action="{{ route('consultation_fees.update', $consultation_fee) }}" method="POST" novalidate>
            @csrf
            @method('PUT')
            @include('consultation_fees._form', ['buttonText' => 'Update'])
        </form>
    </div>
</div>
@endsection
