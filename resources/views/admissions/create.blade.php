@extends('layouts.app')
@section('content')
<h3>{{ isset($admission) ? 'Edit Admission' : 'New Admission' }}</h3>
<form action="{{ isset($admission) ? route('admissions.update', $admission) : route('admissions.store') }}" method="POST">
    @csrf
    @if(isset($admission)) @method('PUT') @endif
    @include('admissions.form')
    <button type="submit" class="btn btn-primary">{{ isset($admission) ? 'Update' : 'Create' }}</button>
</form>
@endsection
