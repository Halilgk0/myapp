<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- made by @hllgkx.0 -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        
        /* Mobile Responsive Styles - Sadece telefon için */
        @media (max-width: 768px) {
            /* Font boyutları */
            body {
                font-size: 12px !important;
            }
            
            h1, .h1 {
                font-size: 1.5rem !important;
            }
            
            h2, .h2 {
                font-size: 1.3rem !important;
            }
            
            h3, .h3 {
                font-size: 1.1rem !important;
            }
            
            /* Navbar */
            .navbar-brand {
                font-size: 1rem !important;
            }
            
            .nav-link {
                font-size: 12px !important;
                padding: 0.3rem 0.5rem !important;
            }
            
            .navbar {
                padding: 0.5rem 1rem !important;
            }
            
            /* Container */
            .container {
                padding: 0 0.5rem !important;
            }
            
            /* Main content */
            main {
                padding: 1rem 0 !important;
            }
            
            /* Button boyutları */
            .btn {
                padding: 0.3rem 0.6rem !important;
                font-size: 11px !important;
            }
            
            .btn-sm {
                padding: 0.2rem 0.4rem !important;
                font-size: 10px !important;
            }
            
            /* Form elementleri */
            .form-control {
                font-size: 11px !important;
                padding: 0.3rem 0.5rem !important;
            }
            
            .form-group {
                margin-bottom: 0.5rem !important;
            }
            
            label {
                font-size: 11px !important;
                margin-bottom: 0.25rem !important;
            }
            
            /* Card yapısı */
            .card {
                margin-bottom: 0.75rem !important;
            }
            
            .card-header {
                padding: 0.5rem !important;
            }
            
            .card-body {
                padding: 0.75rem !important;
            }
            
            .card-title {
                font-size: 1rem !important;
            }
            
            /* Dropdown */
            .dropdown-menu {
                font-size: 11px !important;
            }
            
            .dropdown-item {
                padding: 0.3rem 1rem !important;
                font-size: 11px !important;
            }
            
            /* Table */
            .table {
                font-size: 10px !important;
            }
            
            .table td, .table th {
                padding: 0.3rem !important;
            }
            
            /* Alert */
            .alert {
                padding: 0.5rem !important;
                font-size: 11px !important;
                margin-bottom: 0.5rem !important;
            }
            
            /* Badge */
            .badge {
                font-size: 8px !important;
                padding: 0.2em 0.4em !important;
            }
            
            /* İkonlar */
            .fas, .far, .fab {
                font-size: 12px !important;
            }
            
            /* Small text */
            small, .small {
                font-size: 9px !important;
            }
            
            .text-muted {
                font-size: 9px !important;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light shadow-sm">
            <div class="container">
                <!-- made by @hllgkx.0 -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Giriş Yap') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Çıkış Yap') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('js')
    @stack('scripts')
</body>
</html>