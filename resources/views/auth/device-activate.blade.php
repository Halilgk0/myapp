<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cihaz Aktivasyonu - Araç Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            max-width: 400px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Cihaz Hesabınızı Aktifleştirin</h4>
                    </div>

                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <div class="text-center mb-4">
                            <p class="mb-2">Hesap: <strong>{{ $email }}</strong></p>
                            <p class="text-muted">Hesabınızı aktifleştirmek için yeni bir şifre belirleyin.</p>
                        </div>

                        <form method="POST" action="{{ route('device.activate') }}">
                            @csrf
                            <input type="hidden" name="activation_token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="password" class="form-label">Yeni Şifre (en az 8 karakter)</label>
                                <div class="input-group mb-1">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                           name="password" required autocomplete="new-password" autofocus>
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="form-text">Büyük harf, küçük harf, rakam ve özel karakter içermelidir.</div>
                            </div>

                            <div class="mb-3">
                                <label for="password-confirm" class="form-label mt-3">Şifreyi Onayla</label>
                                <div class="input-group mb-3">
                                    <input id="password-confirm" type="password" class="form-control" 
                                           name="password_confirmation" required autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Hesabı Aktifleştir ve Giriş Yap
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="mb-0">Aktivasyon bağlantınız mı geçersiz oldu? 
                                    <a href="{{ route('device.activation.resend.form') }}">Yeni bağlantı isteyin</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
        
        // Add password strength indicator
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.createElement('div');
        passwordStrength.className = 'password-strength mt-2';
        passwordInput.parentNode.insertBefore(passwordStrength, passwordInput.nextSibling);
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check length
            if (password.length >= 8) strength++;
            // Check for uppercase
            if (/[A-Z]/.test(password)) strength++;
            // Check for numbers
            if (/[0-9]/.test(password)) strength++;
            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update strength indicator
            let strengthText = '';
            let strengthClass = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Zayıf';
                    strengthClass = 'text-danger';
                    break;
                case 2:
                    strengthText = 'Orta';
                    strengthClass = 'text-warning';
                    break;
                case 3:
                    strengthText = 'Güçlü';
                    strengthClass = 'text-info';
                    break;
                case 4:
                    strengthText = 'Çok Güçlü';
                    strengthClass = 'text-success';
                    break;
            }
            
            passwordStrength.innerHTML = `Şifre Gücü: <span class="${strengthClass}">${strengthText}</span>`;
        });
    </script>
</body>
</html>
