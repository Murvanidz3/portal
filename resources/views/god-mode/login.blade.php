<!DOCTYPE html>
<html lang="ka">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>God Mode - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --god-bg: #0a0a0a;
            --god-surface: #141414;
            --god-border: #2a2a2a;
            --god-text: #f5f5f5;
            --god-text-muted: #888;
            --god-primary: #dc2626;
            --god-primary-hover: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--god-bg);
            color: var(--god-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-box {
            background: var(--god-surface);
            border: 1px solid var(--god-border);
            border-radius: 16px;
            padding: 40px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo i {
            font-size: 48px;
            color: var(--god-primary);
            margin-bottom: 15px;
            display: block;
        }

        .login-logo h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-logo p {
            color: var(--god-text-muted);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            color: var(--god-text-muted);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            background: var(--god-bg);
            border: 1px solid var(--god-border);
            border-radius: 8px;
            color: var(--god-text);
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--god-primary);
        }

        .form-input.error {
            border-color: var(--god-primary);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .form-check input {
            width: 18px;
            height: 18px;
            accent-color: var(--god-primary);
        }

        .form-check label {
            font-size: 13px;
            color: var(--god-text-muted);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: var(--god-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .login-btn:hover {
            background: var(--god-primary-hover);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .error-message {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid var(--god-primary);
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--god-primary);
        }

        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .warning-box i {
            color: #f59e0b;
            font-size: 18px;
            margin-top: 2px;
        }

        .warning-box p {
            font-size: 12px;
            color: var(--god-text-muted);
            line-height: 1.5;
        }

        /* Pulse animation for logo */
        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .login-logo i {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <i class="fas fa-shield-halved"></i>
                <h1>GOD MODE</h1>
                <p>Super Admin Access</p>
            </div>

            @if ($errors->any())
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('god.login.submit') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">მომხმარებელი / Email</label>
                    <input type="text" name="username" class="form-input @error('username') error @enderror"
                        value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label class="form-label">პაროლი</label>
                    <input type="password" name="password" class="form-input @error('password') error @enderror"
                        required autocomplete="current-password">
                </div>

                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">დამიმახსოვრე</label>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-key"></i> შესვლა
                </button>
            </form>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <p>
                    <strong>გაფრთხილება:</strong> ეს არის უმაღლესი დონის კონტროლის პანელი.
                    ყველა მოქმედება ლოგირდება და მონიტორინგი ხდება.
                </p>
            </div>
        </div>
    </div>
</body>

</html>