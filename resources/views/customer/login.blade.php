<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Müşteri Girişi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0a0f1a 0%, #0f1729 40%, #111d35 70%, #0a0f1a 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at 20% 80%, rgba(30, 64, 175, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.1) 0%, transparent 40%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(5deg); }
            66% { transform: translate(-20px, 20px) rotate(-5deg); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px -5px rgba(30, 64, 175, 0.5);
        }

        .login-logo svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .login-title {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }

        .alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert ul {
            margin: 0;
            padding-left: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s ease;
            letter-spacing: 1px;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            color: #fca5a5;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .btn-login {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -5px rgba(30, 64, 175, 0.6);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login svg {
            width: 18px;
            height: 18px;
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-footer p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
        }

        .login-footer a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .login-footer a:hover {
            color: #93c5fd;
        }

        /* Decorative elements */
        .decor-circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .decor-circle-1 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(30, 64, 175, 0.35) 0%, transparent 70%);
            top: -100px;
            right: -100px;
        }

        .decor-circle-2 {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 24px;
            }

            .login-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="decor-circle decor-circle-1"></div>
    <div class="decor-circle decor-circle-2"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
                        <path d="M13 5v2"/>
                        <path d="M13 17v2"/>
                        <path d="M13 11v2"/>
                    </svg>
                </div>
                <h1 class="login-title">Müşteri Paneli</h1>
                <p class="login-subtitle">Voucher numaranızla giriş yapın</p>
            </div>

            @if (session('message'))
                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @php
                $hasSessionError = session()->has('error');
            @endphp

            @if ($hasSessionError)
                <div class="alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (!$hasSessionError && $errors->any())
                <div class="alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <div>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.login.post') }}" id="loginForm" autocomplete="off">
                @csrf

                <div class="form-group">
                    <label for="voucher_no" class="form-label">Voucher Numarası</label>
                    <input id="voucher_no" type="text" class="form-control @error('voucher_no') is-invalid @enderror"
                           name="voucher_no" value="{{ old('voucher_no') }}" required autocomplete="off"
                           autocapitalize="characters" autocorrect="off" inputmode="text" autofocus
                           placeholder="VOU-12345">
                    @if (!$hasSessionError)
                        @error('voucher_no')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    @endif
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span id="btnText">Giriş Yap</span>
                </button>
            </form>

            <script>
                document.getElementById('loginForm').addEventListener('submit', function() {
                    var btn = document.getElementById('loginBtn');
                    var btnText = document.getElementById('btnText');
                    btn.disabled = true;
                    btnText.textContent = 'Giriş yapılıyor...';
                });
            </script>

            <div class="login-footer">
                <p>Voucher numarası biletinizin üzerinde bulunur.<br>
                Sorun yaşarsanız acentanızla iletişime geçin.</p>
            </div>
        </div>
    </div>
</body>
</html>
