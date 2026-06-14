<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --bg-gradient-1: #1e1b4b;
            --bg-gradient-2: #312e81;
            --bg-gradient-3: #4338ca;
            --text-main: #ffffff;
            --text-muted: #a5b4fc;
            --card-bg: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.1);
            --input-bg: rgba(0, 0, 0, 0.2);
            --input-border: rgba(255, 255, 255, 0.15);
            --input-focus: rgba(99, 102, 241, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-gradient-1), var(--bg-gradient-2), var(--bg-gradient-3));
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: var(--text-main);
            overflow: hidden;
            position: relative;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Abstract Background Shapes */
        .shape {
            position: absolute;
            filter: blur(80px);
            z-index: 0;
            border-radius: 50%;
            animation: float 20s infinite ease-in-out alternate;
        }
        
        .shape-1 {
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: #818cf8;
            opacity: 0.4;
        }

        .shape-2 {
            bottom: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: #c084fc;
            opacity: 0.3;
            animation-delay: -5s;
        }

        .shape-3 {
            bottom: 20%;
            left: 20%;
            width: 300px;
            height: 300px;
            background: #38bdf8;
            opacity: 0.3;
            animation-delay: -10s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 50px) scale(1.1); }
            100% { transform: translate(-50px, -20px) scale(0.9); }
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideUp {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .logo {
            display: block;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-muted);
            transition: color 0.3s;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--input-focus);
            background: rgba(0, 0, 0, 0.3);
        }

        .form-input:focus + .form-label {
            color: #fff;
        }

        .form-error {
            color: #fca5a5;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-top: 8px;
            cursor: pointer;
        }

        .checkbox-container input {
            display: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--input-border);
            border-radius: 4px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .checkbox-container input:checked ~ .checkmark {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkmark::after {
            content: "";
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            display: none;
        }

        .checkbox-container input:checked ~ .checkmark::after {
            display: block;
        }

        .checkbox-text {
            font-size: 14px;
            color: var(--text-muted);
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 32px;
        }

        .forgot-link {
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #fff;
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Status Message */
        .status-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="login-container">
        <a href="{{ route('home') ?? '/' }}" class="logo">{{ config('app.name', 'Laravel') }}</a>
        <p class="subtitle">Welcome back! Please enter your details.</p>

        @if (session('status'))
            <div class="status-message">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" type="email" class="form-input" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Enter your email">
                @if ($errors->has('email'))
                    <span class="form-error">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" type="password" class="form-input" name="password" required autocomplete="current-password" placeholder="••••••••">
                @if ($errors->has('password'))
                    <span class="form-error">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" id="remember_me">
                    <span class="checkmark"></span>
                    <span class="checkbox-text">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="actions">
                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif

                <button type="submit" class="btn-submit">
                    {{ __('Sign in') }}
                </button>
            </div>
        </form>
    </div>

</body>
</html>
