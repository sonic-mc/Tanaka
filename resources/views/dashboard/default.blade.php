@extends('layouts.app')

@section('content')
<div class="container text-center mt-5">
    <h2>👋 Welcome, {{ Auth::user()->name }}!</h2>
    <p class="lead text-muted mt-3">
        Your account has been successfully created.
    </p>

    <div class="alert alert-warning mt-4" role="alert">
        ⏳ Your account is currently <strong>awaiting admin approval</strong> and <strong>role assignment</strong>.<br>
        Once approved, you’ll be able to access your designated dashboard.
    </div>

   
</div>
@endsection
