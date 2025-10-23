@extends('layouts.app')

@section('content')
<h3>Edit Patient</h3>

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

<form action="{{ route('patients.update', $patient) }}" method="POST" enctype="multipart/form-data" class="mt-3">
    @csrf
    @method('PUT')
    @include('patients._form', ['patient' => $patient])
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('patients.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
