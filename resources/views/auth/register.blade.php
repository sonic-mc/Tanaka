@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <h2 style="margin-bottom: 15px;">Create Account</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="input-box" style="margin: 15px 0;">
            <input 
                type="text" 
                name="name" 
                id="name" 
                value="{{ old('name') }}" 
                required 
                autofocus 
                autocomplete="name"
                style="height: 40px; font-size: 0.9em;"
            >
            <label for="name" style="font-size: 0.9em;">Name</label>
            @error('name')
                <div style="color: #ff6b6b; font-size: 0.75em; margin-top: 3px;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="input-box" style="margin: 15px 0;">
            <input 
                type="email" 
                name="email" 
                id="email" 
                value="{{ old('email') }}" 
                required 
                autocomplete="username"
                style="height: 40px; font-size: 0.9em;"
            >
            <label for="email" style="font-size: 0.9em;">Email</label>
            @error('email')
                <div style="color: #ff6b6b; font-size: 0.75em; margin-top: 3px;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="input-box" style="margin: 15px 0;">
            <input 
                type="password" 
                name="password" 
                id="password" 
                required 
                autocomplete="new-password"
                style="height: 40px; font-size: 0.9em;"
            >
            <label for="password" style="font-size: 0.9em;">Password</label>
            @error('password')
                <div style="color: #ff6b6b; font-size: 0.75em; margin-top: 3px;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="input-box" style="margin: 15px 0;">
            <input 
                type="password" 
                name="password_confirmation" 
                id="password_confirmation" 
                required 
                autocomplete="new-password"
                style="height: 40px; font-size: 0.9em;"
            >
            <label for="password_confirmation" style="font-size: 0.9em;">Confirm Password</label>
            @error('password_confirmation')
                <div style="color: #ff6b6b; font-size: 0.75em; margin-top: 3px;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit -->
        <div style="display: flex; justify-content: center; margin-top: 15px;">
            <button type="submit" 
                class="btn" 
                style="width: 50%; height: 38px; font-size: 0.9em;"
            >
                Register
            </button>
        </div>

        <!-- Already registered -->
        <div class="signup-link" style="margin-top: 10px; text-align:center;">
            <a href="{{ route('login') }}">Already registered? Login here</a>
        </div>
    </form>
@endsection
