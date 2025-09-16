@extends('layouts.guest')

@section('content')
    <h4 class="text-center mb-4">Log in to your account</h4>

    @if (session('status'))
        <div class="alert alert-success text-center">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                placeholder="Enter your email"
                value="{{ old('email') }}"
                required
                autofocus
            >
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                placeholder="Enter password"
                required
            >
            <span 
                class="position-absolute top-50 end-0 translate-middle-y me-3" 
                style="cursor:pointer;" 
                onclick="togglePassword()"
            >👁️</span>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="remember_me" class="form-check-input">
            <label for="remember_me" class="form-check-label">Remember me</label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-100">Sign in</button>

        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="text-end mt-2">
                <a href="{{ route('password.request') }}" class="text-decoration-none text-primary small">
                    Forgot your password?
                </a>
            </div>
        @endif
    </form>

    <div class="text-center mt-4">
        <small>Don't have an account? <a href="{{ route('register') }}" class="text-primary">Sign up now</a></small>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
@endsection
