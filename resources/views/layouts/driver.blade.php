@php
    $currentPageTitle = trim($__env->yieldContent('page_title', $__env->yieldContent('title', 'Ana Sayfa')));
    $driverUser = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Şoför Paneli')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        :root {
            --dr-bg: #f8fafc;
            --dr-sidebar: #0f172a;
            --dr-sidebar-hover: #1e293b;
            --dr-sidebar-active: #334155;
            --dr-accent: #3b82f6;
            --dr-accent-hover: #2563eb;
            --dr-text: #1e293b;
            --dr-text-muted: #64748b;
            --dr-border: #e2e8f0;
            --dr-card: #ffffff;
            --dr-success: #10b981;
            --dr-warning: #f59e0b;
            --dr-danger: #ef4444;
            --dr-radius: 12px;
            --dr-radius-sm: 8px;
            --dr-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
            --dr-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --sidebar-width: 240px;
            --topbar-height: 60px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dr-bg);
            color: var(--dr-text);
            font-size: 14px;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .dr-sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--dr-sidebar);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .dr-sidebar-brand {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .dr-sidebar-brand-icon {
            width: 38px; height: 38px;
            background: var(--dr-accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .dr-sidebar-brand-text {
            color: #fff;
            font-weight: 600;
            font-size: 15px;
        }

        .dr-sidebar-brand-text small {
            display: block;
            font-weight: 400;
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            margin-top: 1px;
        }

        .dr-sidebar-nav {
            flex: 1;
            padding: 14px 10px;
            overflow-y: auto;
        }

        .dr-nav-section { margin-bottom: 20px; }

        .dr-nav-section-title {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.35);
            padding: 0 12px;
            margin-bottom: 6px;
        }

        .dr-nav-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 14px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: var(--dr-radius-sm);
            margin-bottom: 3px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 13.5px;
        }

        .dr-nav-item:hover { background: var(--dr-sidebar-hover); color: #fff; }
        .dr-nav-item.active { background: var(--dr-accent); color: #fff; }

        .dr-nav-item i[data-lucide] { width: 19px; height: 19px; stroke-width: 2; }

        .dr-sidebar-footer {
            padding: 14px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .dr-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--dr-radius-sm);
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.2s;
        }

        .dr-user-card:hover { background: rgba(255,255,255,0.1); }

        .dr-user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--dr-accent);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            flex-shrink: 0;
        }

        .dr-user-info { flex: 1; min-width: 0; }

        .dr-user-name {
            color: #fff;
            font-weight: 500;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dr-user-role {
            color: rgba(255,255,255,0.5);
            font-size: 11px;
        }

        /* ── Main ── */
        .dr-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* ── Topbar ── */
        .dr-topbar {
            height: var(--topbar-height);
            background: var(--dr-card);
            border-bottom: 1px solid var(--dr-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dr-topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .dr-menu-toggle {
            display: none;
            width: 38px; height: 38px;
            border: none;
            background: transparent;
            border-radius: var(--dr-radius-sm);
            cursor: pointer;
            color: var(--dr-text);
            align-items: center; justify-content: center;
        }

        .dr-menu-toggle:hover { background: var(--dr-bg); }

        .dr-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .dr-breadcrumb a {
            color: var(--dr-text-muted);
            text-decoration: none;
        }

        .dr-breadcrumb a:hover { color: var(--dr-accent); }

        .dr-breadcrumb-sep { color: var(--dr-border); }

        .dr-breadcrumb-current {
            color: var(--dr-text);
            font-weight: 500;
        }

        /* ── Content ── */
        .dr-content {
            flex: 1;
            padding: 24px;
        }

        /* ── Footer ── */
        .dr-footer {
            padding: 14px 24px;
            background: var(--dr-card);
            border-top: 1px solid var(--dr-border);
            font-size: 12px;
            color: var(--dr-text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── User dropdown ── */
        .dr-user-dropdown {
            position: fixed;
            background: var(--dr-card);
            border-radius: var(--dr-radius);
            box-shadow: var(--dr-shadow-lg);
            border: 1px solid var(--dr-border);
            z-index: 1100;
            min-width: 210px;
            display: none;
        }

        .dr-user-dropdown.show { display: block; }

        .dr-user-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--dr-border);
        }

        .dr-user-dropdown-name {
            font-weight: 600;
            color: var(--dr-text);
        }

        .dr-user-dropdown-email {
            font-size: 12px;
            color: var(--dr-text-muted);
        }

        .dr-user-dropdown-body { padding: 8px; }

        .dr-user-dropdown-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--dr-text);
            text-decoration: none;
            border-radius: var(--dr-radius-sm);
            font-size: 13px;
        }

        .dr-user-dropdown-link:hover { background: var(--dr-bg); color: var(--dr-text); }

        .dr-user-dropdown-link.danger { color: var(--dr-danger); }
        .dr-user-dropdown-link.danger:hover { background: rgba(239,68,68,0.1); }

        .dr-user-dropdown-link i[data-lucide] { width: 18px; height: 18px; }

        /* ── Overrides (match agency / admin cards, badges, etc.) ── */
        .card {
            border-radius: var(--dr-radius) !important;
            border: 1px solid var(--dr-border) !important;
            box-shadow: var(--dr-shadow) !important;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: var(--dr-bg) !important;
            border-bottom: 1px solid var(--dr-border) !important;
            padding: 1rem 1.25rem !important;
            border-radius: var(--dr-radius) var(--dr-radius) 0 0 !important;
        }

        .card-title {
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            color: var(--dr-text) !important;
            margin: 0 !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i,
        .card-title .fas,
        .card-title .far,
        .card-title .fab { color: var(--dr-accent); }

        .card-body { padding: 1.25rem !important; }

        .form-control {
            border-radius: var(--dr-radius-sm) !important;
            border: 1px solid var(--dr-border) !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.9rem !important;
            transition: all 0.2s ease !important;
        }

        .form-control:focus {
            border-color: var(--dr-accent) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        label {
            font-weight: 500 !important;
            color: var(--dr-text) !important;
            font-size: 0.875rem !important;
        }

        .btn {
            border-radius: var(--dr-radius-sm) !important;
            font-weight: 500 !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.875rem !important;
            transition: all 0.2s ease !important;
        }

        .btn-primary {
            background: var(--dr-accent) !important;
            border-color: var(--dr-accent) !important;
        }

        .btn-primary:hover {
            background: var(--dr-accent-hover) !important;
            border-color: var(--dr-accent-hover) !important;
        }

        .btn-success {
            background: var(--dr-success) !important;
            border-color: var(--dr-success) !important;
        }

        .btn-danger {
            background: var(--dr-danger) !important;
            border-color: var(--dr-danger) !important;
        }

        .badge {
            font-weight: 600 !important;
            padding: 0.35rem 0.65rem !important;
            border-radius: 20px !important;
            font-size: 0.75rem !important;
        }

        .badge-primary, .bg-primary { background: var(--dr-accent) !important; }
        .badge-success, .bg-success { background: var(--dr-success) !important; }
        .badge-warning, .bg-warning { background: var(--dr-warning) !important; color: #fff !important; }
        .badge-danger, .bg-danger { background: var(--dr-danger) !important; }
        .badge-info, .bg-info { background: #17a2b8 !important; }
        .badge-secondary, .bg-secondary { background: var(--dr-text-muted) !important; }

        .table { margin-bottom: 0 !important; }

        .table th {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: var(--dr-text-muted) !important;
            background: var(--dr-bg) !important;
            border-bottom: 1px solid var(--dr-border) !important;
            padding: 0.75rem 1rem !important;
        }

        .table td {
            padding: 0.875rem 1rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid var(--dr-border) !important;
        }

        .alert {
            border-radius: var(--dr-radius-sm) !important;
            border: none !important;
            padding: 1rem 1.25rem !important;
        }

        .alert-success {
            background: rgba(16,185,129,0.1) !important;
            color: #065f46 !important;
            border: 1px solid rgba(16,185,129,0.2) !important;
        }

        .alert-danger {
            background: rgba(239,68,68,0.1) !important;
            color: #991b1b !important;
            border: 1px solid rgba(239,68,68,0.2) !important;
        }

        .alert-warning {
            background: rgba(245,158,11,0.1) !important;
            color: #92400e !important;
            border: 1px solid rgba(245,158,11,0.2) !important;
        }

        .text-primary { color: var(--dr-accent) !important; }
        .text-success { color: var(--dr-success) !important; }
        .text-danger { color: var(--dr-danger) !important; }
        .text-warning { color: var(--dr-warning) !important; }
        .text-muted { color: var(--dr-text-muted) !important; }

        .container-fluid { padding: 0 !important; }

        .info-box {
            display: flex;
            min-height: 72px;
            background: var(--dr-card);
            border: 1px solid var(--dr-border);
            border-radius: var(--dr-radius);
            margin-bottom: 0.75rem;
            overflow: hidden;
            box-shadow: var(--dr-shadow);
        }

        .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: #fff;
        }

        .info-box-content {
            flex: 1;
            padding: 0.6rem 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .info-box-text {
            font-size: 0.8rem;
            color: var(--dr-text-muted);
            line-height: 1.3;
        }

        .info-box-number {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dr-text);
        }

        /* ── Sidebar scrollbar ── */
        .dr-sidebar-nav::-webkit-scrollbar { width: 4px; }
        .dr-sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .dr-sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* ── Mobile responsive ── */
        @media (max-width: 1024px) {
            .dr-sidebar { transform: translateX(-100%); }
            .dr-sidebar.open { transform: translateX(0); }
            .dr-main { margin-left: 0; }
            .dr-menu-toggle { display: flex; }

            .dr-sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }

            .dr-sidebar-overlay.show { display: block; }
        }

        @media (max-width: 640px) {
            .dr-content { padding: 16px; }
            .dr-topbar { padding: 0 16px; }
            .dr-breadcrumb { display: none; }
            .dr-footer { flex-direction: column; gap: 6px; text-align: center; }
        }
    </style>
    @stack('css')
</head>
<body>
    <div class="dr-sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="dr-sidebar" id="sidebar">
        <div class="dr-sidebar-brand">
            <div class="dr-sidebar-brand-icon">
                <i data-lucide="truck"></i>
            </div>
            <div class="dr-sidebar-brand-text">
                Şoför Paneli
                <small>Araç Takip Sistemi</small>
            </div>
        </div>

        <nav class="dr-sidebar-nav">
            <div class="dr-nav-section">
                <div class="dr-nav-section-title">Genel</div>
                <a href="{{ route('driver.dashboard') }}" class="dr-nav-item {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Ana Sayfa</span>
                </a>
                <a href="{{ route('driver.tickets') }}" class="dr-nav-item {{ request()->routeIs('driver.tickets') ? 'active' : '' }}">
                    <i data-lucide="ticket"></i>
                    <span>Biletler</span>
                </a>
            </div>
            <div class="dr-nav-section">
                <div class="dr-nav-section-title">Araç & Profil</div>
                <a href="{{ route('driver.vehicle') }}" class="dr-nav-item {{ request()->routeIs('driver.vehicle') ? 'active' : '' }}">
                    <i data-lucide="car"></i>
                    <span>Araç Bilgisi</span>
                </a>
                <a href="{{ route('driver.profile') }}" class="dr-nav-item {{ request()->routeIs('driver.profile') ? 'active' : '' }}">
                    <i data-lucide="user"></i>
                    <span>Profil</span>
                </a>
            </div>
        </nav>

        @if($driverUser)
        <div class="dr-sidebar-footer">
            <div class="dr-user-card" id="userCardTrigger">
                <div class="dr-user-avatar">
                    {{ strtoupper(substr($driverUser->name ?? 'Ş', 0, 1)) }}
                </div>
                <div class="dr-user-info">
                    <div class="dr-user-name">{{ $driverUser->name ?? 'Şoför' }}</div>
                    <div class="dr-user-role">Şoför</div>
                </div>
                <i data-lucide="chevron-up" style="width:14px;height:14px;color:rgba(255,255,255,0.5)"></i>
            </div>
        </div>
        @endif
    </aside>

    <main class="dr-main">
        <header class="dr-topbar">
            <div class="dr-topbar-left">
                <button class="dr-menu-toggle" id="menuToggle">
                    <i data-lucide="menu"></i>
                </button>
                <nav class="dr-breadcrumb">
                    <a href="{{ route('driver.dashboard') }}">Şoför</a>
                    <span class="dr-breadcrumb-sep">/</span>
                    <span class="dr-breadcrumb-current">{{ $currentPageTitle }}</span>
                </nav>
            </div>
        </header>

        <div class="dr-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i data-lucide="check-circle" style="width:18px;height:18px;display:inline-block;vertical-align:text-bottom;margin-right:6px"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;display:inline-block;vertical-align:text-bottom;margin-right:6px"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            @endif

            @yield('content')
        </div>

        <footer class="dr-footer">
            <span>&copy; {{ date('Y') }} Şoför Paneli</span>
        </footer>
    </main>

    @if($driverUser)
    <div class="dr-user-dropdown" id="userDropdown">
        <div class="dr-user-dropdown-header">
            <div class="dr-user-dropdown-name">{{ $driverUser->name }}</div>
            <div class="dr-user-dropdown-email">{{ $driverUser->email }}</div>
        </div>
        <div class="dr-user-dropdown-body">
            <a href="{{ route('driver.profile') }}" class="dr-user-dropdown-link">
                <i data-lucide="settings"></i>
                <span>Profil Ayarları</span>
            </a>
            <div style="border-top:1px solid var(--dr-border);margin:6px 0"></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dr-user-dropdown-link danger" style="width:100%;border:none;background:none;cursor:pointer">
                    <i data-lucide="log-out"></i>
                    <span>Çıkış Yap</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menuToggle');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('show');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('show');
            });
        }

        const userCardTrigger = document.getElementById('userCardTrigger');
        const userDropdown = document.getElementById('userDropdown');

        if (userCardTrigger && userDropdown) {
            userCardTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const rect = userCardTrigger.getBoundingClientRect();
                userDropdown.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                userDropdown.style.left = rect.left + 'px';
                userDropdown.style.top = 'auto';
                userDropdown.style.right = 'auto';
                userDropdown.classList.toggle('show');
            });
        }

        document.addEventListener('click', (e) => {
            if (userDropdown && !userDropdown.contains(e.target) && !userCardTrigger?.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    </script>
    @stack('js')
</body>
</html>
