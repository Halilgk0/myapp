<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Sorgula</title>
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
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(99, 102, 241, 0.1) 0%, transparent 40%);
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
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.4);
        }

        .login-logo svg {
            width: 36px;
            height: 36px;
            color: white;
        }

        .login-title {
            color: #fff;
            font-size: 26px;
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
            align-items: center;
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

        .alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 16px 48px 16px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.2s ease;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0;
            text-transform: none;
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

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
        }

        .input-icon svg {
            width: 20px;
            height: 20px;
        }

        .invalid-feedback {
            color: #fca5a5;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .btn-login {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login svg {
            width: 20px;
            height: 20px;
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-footer p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
            line-height: 1.6;
        }

        .decor-circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .decor-circle-1 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
            top: -100px;
            right: -100px;
        }

        .decor-circle-2 {
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
        }

        /* Ticket decoration */
        .ticket-decoration {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }

        .ticket-decoration::before,
        .ticket-decoration::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            border-radius: 50%;
        }

        .ticket-decoration::before {
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .ticket-decoration::after {
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 24px;
            }
            
            .login-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="decor-circle decor-circle-1"></div>
    <div class="decor-circle decor-circle-2"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="ticket-decoration"></div>
            
            <div class="login-header">
                <div class="login-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
                        <path d="M13 5v2"/>
                        <path d="M13 17v2"/>
                        <path d="M13 11v2"/>
                    </svg>
                </div>
                <h1 class="login-title">Bilet Sorgula</h1>
                <p class="login-subtitle">Bilet numaranızı girerek seyahat bilgilerinize ulaşın</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('tickets.login.post') }}" method="post">
                @csrf
                
                <div class="form-group">
                    <label for="tracking_no" class="form-label">Bilet Numarası</label>
                    <div class="input-wrapper">
                        <input type="text" name="tracking_no" id="tracking_no" 
                               class="form-control @error('tracking_no') is-invalid @enderror" 
                               placeholder="Örn: ABC123XYZ" 
                               value="{{ old('tracking_no') }}" 
                               required
                               autocomplete="off">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </span>
                    </div>
                    @error('tracking_no')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <button type="submit" class="btn-login">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Biletimi Sorgula
                </button>
            </form>

            <div class="login-footer">
                <p>Bilet numaranızı bilmiyorsanız lütfen<br>acentenizle iletişime geçiniz.</p>
            </div>
        </div>
    </div>
</body>
</html>
