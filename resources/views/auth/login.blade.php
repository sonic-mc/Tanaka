@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h2>Login</h2>

    @if (session('status'))
        <div style="color: #0ef; text-align:center; margin-bottom: 15px; font-size: 0.9em;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="input-box">
            <input 
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                required 
                autofocus
            >
            <label for="email">Email</label>
            @error('email')
                <div style="color: #ff6b6b; font-size: 0.8em; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="input-box" style="position: relative;">
            <input 
                type="password" 
                name="password" 
                id="password" 
                required
            >
            <label for="password">Password</label>
            @error('password')
                <div style="color: #ff6b6b; font-size: 0.8em; margin-top: 5px;">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div style="display: flex; align-items: center; gap: 6px; margin: 10px 0 20px;">
            <input type="checkbox" name="remember" id="remember_me" style="accent-color:#0ef;">
            <label for="remember_me" style="color: #fff; font-size: 0.85em;">Remember me</label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn">Sign in</button>

        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="forgot-pass" style="margin-top: 15px;">
                <a href="{{ route('password.request') }}">Forgot your password?</a>
            </div>
        @endif

        <!-- Signup link -->
        <div class="signup-link">
            <a href="{{ route('register') }}">Or...Sign up now</a>
        </div>
    </form>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
@endsection
