@extends('layouts.app')

@section('content')
<h3>Register New Patient</h3>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>There were some problems with your input:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
    @csrf
    @include('patients._form', ['patient' => $patient])
    <button type="submit" class="btn btn-primary">Register</button>
    <a href="{{ route('patients.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection