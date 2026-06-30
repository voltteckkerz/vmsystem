<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login') }} - {{ config('app.name', 'Laravel') }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            background-image: url('{{ asset(str_replace(' ', '%20', 'images/login background.jpg')) }}?v={{ filemtime(public_path('images/login background.jpg')) }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .login-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-footer {
            text-align: center;
            padding: 12px;
            font-size: 0.85rem;
            color: rgba(22, 24, 29, 0.65);
        }
        .login-wrapper {
            width: 100%;
            max-width: 460px;
        }
        .login-hero {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo {
            font-size: 1.15rem;
            font-weight: 400;
            color: #16181d;
            letter-spacing: 0.04em;
            margin: 0 0 18px;
        }
        .login-hero h1 {
            font-size: 4.12rem;
            font-weight: 400;
            color: #16181d;
            letter-spacing: -0.02em;
            margin: 0 0 8px;
        }
        .login-hero p.subtitle {
            color: #6b6f78;
            font-size: 1rem;
            margin: 0;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            padding: 32px 40px;
            margin-bottom: 18px;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #16181d;
            margin-bottom: 6px;
        }
        .form-label .required {
            color: #ef4444;
        }
        .input-icon-group {
            position: relative;
        }
        .input-icon-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b4bc;
            font-size: 0.95rem;
        }
        .input-icon-group .form-control {
            padding-left: 40px;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #e6e7eb;
            padding: 11px 14px;
            font-size: 0.92rem;
            background: #fcfcfd;
        }
        .form-control:focus {
            border-color: #16181d;
            box-shadow: 0 0 0 3px rgba(22, 24, 29, 0.06);
            background: #fff;
        }
        .mb-field {
            margin-bottom: 18px;
        }
        .form-check-label {
            font-size: 0.88rem;
            color: #5b5f68;
        }
        .btn-login {
            display: flex;
            margin: 0 auto;
            background: #16181d;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 28px;
            font-weight: 700;
            font-size: 0.95rem;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .btn-login:hover {
            background: #2a2d36;
            color: #fff;
        }
        .btn-login:active {
            transform: scale(0.98);
        }
        .divider-row {
            margin: 24px 0;
            border-top: 1px solid #f0f1f3;
        }
        .invalid-feedback {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-content">
        <div class="login-wrapper">
            <div class="login-hero">
                <p class="login-logo">{{ config('app.name', 'VMSYSTEM') }}</p>
                <h1>{{ __('Welcome!') }}</h1>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="login-card">
                    <div class="mb-field">
                        <label for="email" class="form-label">{{ __('Email Address') }}<span class="required">*</span></label>
                        <div class="input-icon-group">
                            <i class="bi bi-envelope"></i>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email" autofocus>
                        </div>
                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-field">
                        <label for="password" class="form-label">{{ __('Password') }}<span class="required">*</span></label>
                        <div class="input-icon-group">
                            <i class="bi bi-lock"></i>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="••••••••" required autocomplete="current-password">
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    {{ __('Sign In') }} <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <footer class="login-footer">
        &copy; {{ date('Y') }} Lembah Sari Sdn Bhd. All rights reserved.
    </footer>
</body>
</html>
