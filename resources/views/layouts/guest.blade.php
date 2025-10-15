<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auth Page')</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #1f293a;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .page-header {
            width: 100%;
            padding: 30px 0;
            text-align: center;
            color: #0ef;
            font-size: 1.8em;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        .page-header .icon {
            font-size: 1.5em;
        }

        .wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .container {
            position: relative;
            width: 256px;
            height: 256px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: -20px;
        }

        .container span {
            position: absolute;
            left: 0;
            width: 25px;
            height: 6px;
            background: #2c4766;
            border-radius: 8px;
            transform-origin: 128px;
            transform: scale(2.2) rotate(calc(var(--i) * (360deg / 50)));
            animation: animateBlink 3s linear infinite;
            animation-delay: calc(var(--i) * (3s / 50));
        }

        @keyframes animateBlink {
            0% { background: #0ef; }
            25% { background: #2c4766; }
        }

        .login-box {
            position: absolute;
            width: 400px;
        }

        .login-box form {
            width: 100%;
            padding: 0 50px;
        }

        h2 {
            font-size: 2em;
            color: #0ef;
            text-align: center;
        }

        .input-box {
            position: relative;
            margin: 25px 0;
        }

        .input-box input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: 2px solid #2c4766;
            outline: none;
            border-radius: 40px;
            font-size: 1em;
            color: #fff;
            padding: 0 20px;
            transition: .5s ease;
        }

        .input-box input:focus,
        .input-box input:valid {
            border-color: #0ef;
        }

        .input-box label {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            font-size: 1em;
            color: #fff;
            pointer-events: none;
            transition: .5s ease;
        }

        .input-box input:focus~label,
        .input-box input:valid~label {
            top: 1px;
            font-size: .8em;
            background: #1f293a;
            padding: 0 6px;
            color: #0ef;
        }

        .forgot-pass {
            margin: -15px 0 10px;
            text-align: center;
        }

        .forgot-pass a {
            font-size: .85em;
            color: #fff;
            text-decoration: none;
        }

        .forgot-pass a:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            height: 45px;
            background: #0ef;
            border: none;
            outline: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 1em;
            color: #1f293a;
            font-weight: 600;
        }

        .signup-link {
            margin: 20px 0 10px;
            text-align: center;
        }

        .signup-link a {
            font-size: 1em;
            color: #0ef;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header class="page-header">
        <h1>
            <i class="bi bi-hospital-fill icon"></i>
            Psych Monitoring System
        </h1>
    </header>

    <div class="wrapper">
        <div class="container">
            <div class="login-box">
                {{-- Auth page content goes here --}}
                @yield('content')
            </div>

            {{-- Animation ring --}}
            @for ($i = 0; $i < 50; $i++)
                <span style="--i:{{ $i }};"></span>
            @endfor
        </div>
    </div>
</body>
</html>
