@php
    $currentPageTitle = trim($__env->yieldContent('page_title', $__env->yieldContent('title', 'Ana Sayfa')));
    $notificationUser = auth()->user();
    $incomingRequests = collect();
    $recentActivities = collect();
    $sessionPartition = 'agency';

    if ($notificationUser) {
        $incomingRequests = $notificationUser->receivedAgencyRequests()
            ->pending()
            ->with('requester')
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = $notificationUser->sentAgencyRequests()
            ->with('target')
            ->latest()
            ->take(5)
            ->get();

        $pendingSettlements = \App\Models\SettlementRequest::where('agency_user_id', $notificationUser->id)
            ->where('status', \App\Models\SettlementRequest::STATUS_PENDING)
            ->latest()
            ->take(5)
            ->get();
    } else {
        $pendingSettlements = collect();
    }

    $notificationCount = $incomingRequests->count() + $pendingSettlements->count();
    $notifications = collect();

    foreach ($incomingRequests as $req) {
        $notifications->push([
            'type' => 'incoming_agency',
            'title' => optional($req->requester)->name ?? 'Bilinmeyen Kullanıcı',
            'message' => 'Acenta bağlantısı için onay bekliyor.',
            'time' => optional($req->created_at)?->diffForHumans() ?? '',
            'request_id' => $req->id,
        ]);
    }

    foreach ($recentActivities as $req) {
        $statusLabel = match ($req->status) {
            \App\Models\AgencyConnectionRequest::STATUS_ACCEPTED => 'Kabul edildi',
            \App\Models\AgencyConnectionRequest::STATUS_REJECTED => 'Reddedildi',
            default => 'Beklemede'
        };

        $notifications->push([
            'type' => 'activity',
            'title' => optional($req->target)->name ?? 'Bilinmeyen Kullanıcı',
            'message' => 'Gönderilen istek: ' . $statusLabel,
            'time' => optional($req->created_at)?->diffForHumans() ?? '',
            'request_id' => null,
        ]);
    }

    foreach ($pendingSettlements as $settlement) {
        $ticketCount = count($settlement->ticket_ids ?? []);
        $fallbackTxCount = count($settlement->transaction_ids ?? []);
        $notifications->push([
            'type' => 'settlement_pending',
            'title' => 'Mutabakat #' . $settlement->id,
            'message' => (($ticketCount > 0 ? $ticketCount : $fallbackTxCount)) . ' bilet için onay bekleniyor.',
            'time' => optional($settlement->created_at)?->diffForHumans() ?? '',
            'settlement_id' => $settlement->id,
        ]);
    }
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acenta Paneli')</title>

    <!-- Google Fonts - DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    
    <style>
        :root {
            --ag-bg: #f8fafc;
            --ag-sidebar: #0f172a;
            --ag-sidebar-hover: #1e293b;
            --ag-sidebar-active: #334155;
            --ag-accent: #3b82f6;
            --ag-accent-hover: #2563eb;
            --ag-text: #1e293b;
            --ag-text-muted: #64748b;
            --ag-border: #e2e8f0;
            --ag-card: #ffffff;
            --ag-success: #10b981;
            --ag-warning: #f59e0b;
            --ag-danger: #ef4444;
            --ag-radius: 12px;
            --ag-radius-sm: 8px;
            --ag-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
            --ag-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --sidebar-width: 260px;
            --sidebar-collapsed: 72px;
            --topbar-height: 64px;
        }

        /* Tema geçiş animasyonu - renkler aniden değil yumuşak geçsin */
        html, body, *, *::before, *::after {
            transition: background-color .25s ease, color .25s ease, border-color .25s ease, box-shadow .25s ease, fill .25s ease, stroke .25s ease;
        }

        /* Dark Mode */
        html.dark-mode {
            --ag-bg: #0f172a;
            --ag-sidebar: #020617;
            --ag-sidebar-hover: #1e293b;
            --ag-sidebar-active: #334155;
            --ag-text: #e2e8f0;
            --ag-text-muted: #94a3b8;
            --ag-border: #334155;
            --ag-card: #1e293b;
            --ag-shadow: 0 1px 3px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.2);
            --ag-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.4), 0 4px 6px -2px rgba(0,0,0,0.3);
        }

        html.dark-mode .card,
        html.dark-mode .card-body,
        html.dark-mode .card-header,
        html.dark-mode .card-footer {
            background: var(--ag-card) !important;
            color: var(--ag-text) !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .table,
        html.dark-mode .table-responsive {
            background: var(--ag-card) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .table thead,
        html.dark-mode .table thead tr,
        html.dark-mode .table thead th {
            background: #334155 !important;
            background-color: #334155 !important;
            color: var(--ag-text) !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .table tbody,
        html.dark-mode .table tbody tr {
            background: var(--ag-card) !important;
            background-color: var(--ag-card) !important;
        }

        html.dark-mode .table td,
        html.dark-mode .table th {
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
            background-color: inherit !important;
        }

        html.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(255,255,255,0.03) !important;
            --bs-table-accent-bg: rgba(255,255,255,0.03) !important;
        }

        html.dark-mode .table-striped > tbody > tr:nth-of-type(even) > * {
            background-color: var(--ag-card) !important;
        }

        html.dark-mode .table-hover > tbody > tr:nth-of-type(odd) > *:hover,
        html.dark-mode .table-hover > tbody > tr:hover > * {
            background-color: rgba(255,255,255,0.05) !important;
            --bs-table-accent-bg: rgba(255,255,255,0.05) !important;
        }

        html.dark-mode .table-bordered {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .table-bordered > :not(caption) > * > * {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .form-control,
        html.dark-mode .form-select {
            background: #1e293b !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .form-control::placeholder {
            color: var(--ag-text-muted) !important;
        }

        html.dark-mode .btn-light {
            background: #334155 !important;
            border-color: #475569 !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .text-muted {
            color: var(--ag-text-muted) !important;
        }

        html.dark-mode .alert {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .ag-dropdown {
            background: var(--ag-card) !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .ag-dropdown-header,
        html.dark-mode .ag-user-dropdown-header {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .ag-stat-card {
            background: var(--ag-card) !important;
        }

        /* Additional dark mode overrides */
        html.dark-mode .p-0,
        html.dark-mode .p-1,
        html.dark-mode .p-2,
        html.dark-mode .p-3,
        html.dark-mode .p-4,
        html.dark-mode .p-5 {
            background-color: inherit !important;
        }

        html.dark-mode .bg-white,
        html.dark-mode .bg-light {
            background-color: var(--ag-card) !important;
        }

        html.dark-mode .border {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .modal-content {
            background: var(--ag-card) !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .modal-header,
        html.dark-mode .modal-footer {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode strong,
        html.dark-mode b {
            color: var(--ag-text) !important;
        }

        html.dark-mode code {
            background: #334155 !important;
            color: #f472b6 !important;
        }

        html.dark-mode .badge-secondary {
            background: #475569 !important;
        }

        html.dark-mode .pagination .page-link {
            background: var(--ag-card) !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .pagination .page-item.active .page-link {
            background: var(--ag-accent) !important;
            border-color: var(--ag-accent) !important;
        }

        html.dark-mode .bottom-pagination-wrapper {
            background: var(--ag-card) !important;
            border-color: var(--ag-border) !important;
        }

        /* Additional dark mode overrides for Agency */
        html.dark-mode .content-wrapper,
        html.dark-mode .content,
        html.dark-mode .main-content {
            background: var(--ag-bg) !important;
        }

        html.dark-mode label {
            color: var(--ag-text) !important;
        }

        html.dark-mode .callout {
            background: #1e293b !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .callout h5,
        html.dark-mode .callout p {
            color: var(--ag-text) !important;
        }

        html.dark-mode .nav-link {
            color: var(--ag-text) !important;
        }

        html.dark-mode .nav-tabs .nav-link {
            background: #1e293b !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .nav-tabs .nav-link.active {
            background: #334155 !important;
            border-bottom-color: #334155 !important;
        }

        html.dark-mode .tab-content {
            background: var(--ag-card) !important;
        }

        html.dark-mode .list-group-item {
            background: var(--ag-card) !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .list-group-item:hover {
            background: #334155 !important;
        }

        html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, 
        html.dark-mode h4, html.dark-mode h5, html.dark-mode h6 {
            color: var(--ag-text) !important;
        }

        html.dark-mode p {
            color: var(--ag-text) !important;
        }

        html.dark-mode .breadcrumb {
            background: transparent !important;
        }

        html.dark-mode .breadcrumb-item,
        html.dark-mode .breadcrumb-item a {
            color: var(--ag-text-muted) !important;
        }

        html.dark-mode .breadcrumb-item.active {
            color: var(--ag-text) !important;
        }

        html.dark-mode hr {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .dropdown-menu {
            background: var(--ag-card) !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .dropdown-item {
            color: var(--ag-text) !important;
        }

        html.dark-mode .dropdown-item:hover {
            background: #334155 !important;
        }

        html.dark-mode .dropdown-divider {
            border-color: var(--ag-border) !important;
        }

        html.dark-mode input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) !important;
        }

        html.dark-mode select option {
            background: #1e293b !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .btn-outline-secondary,
        html.dark-mode .btn-outline-primary {
            color: var(--ag-text) !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .btn-outline-secondary:hover,
        html.dark-mode .btn-outline-primary:hover {
            background: #334155 !important;
        }

        html.dark-mode .input-group-text {
            background: #334155 !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .custom-select,
        html.dark-mode .custom-control-label {
            color: var(--ag-text) !important;
        }

        /* Mini Calendar Dark Mode for Agency */
        html.dark-mode .mini-calendar {
            background: #1e293b !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .mini-cal-header {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        }

        html.dark-mode .mini-cal-weekdays {
            background: #334155 !important;
            border-color: var(--ag-border) !important;
        }

        html.dark-mode .mini-cal-weekdays span {
            color: var(--ag-text-muted) !important;
        }

        html.dark-mode .mini-cal-grid {
            background: #1e293b !important;
        }

        html.dark-mode .cal-day {
            background: #0f172a !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }

        html.dark-mode .cal-day:hover:not(.disabled):not(.selected):not(.empty) {
            background: #334155 !important;
            border-color: var(--ag-accent) !important;
        }

        html.dark-mode .cal-day.disabled {
            background: #0f172a !important;
            color: #475569 !important;
            border-color: #1e293b !important;
        }

        html.dark-mode .cal-day.selected {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            color: white !important;
            border-color: #3b82f6 !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--ag-bg);
            color: var(--ag-text);
            font-size: 14px;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Sidebar */
        .ag-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--ag-sidebar);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .ag-sidebar-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .ag-sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--ag-accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .ag-sidebar-brand-text {
            color: #fff;
            font-weight: 600;
            font-size: 16px;
        }

        .ag-sidebar-brand-text small {
            display: block;
            font-weight: 400;
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            margin-top: 2px;
        }

        .ag-sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .ag-nav-section {
            margin-bottom: 24px;
        }

        .ag-nav-section-title {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.35);
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .ag-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: var(--ag-radius-sm);
            margin-bottom: 4px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .ag-nav-item:hover {
            background: var(--ag-sidebar-hover);
            color: #fff;
        }

        .ag-nav-item.active {
            background: var(--ag-accent);
            color: #fff;
        }

        .ag-nav-item i {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .ag-sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .ag-user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: var(--ag-radius-sm);
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.2s;
        }

        .ag-user-card:hover {
            background: rgba(255,255,255,0.1);
        }

        .ag-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--ag-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }

        .ag-user-info {
            flex: 1;
            min-width: 0;
        }

        .ag-user-name {
            color: #fff;
            font-weight: 500;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ag-user-role {
            color: rgba(255,255,255,0.5);
            font-size: 11px;
        }

        /* Main Content */
        .ag-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Topbar */
        .ag-topbar {
            height: var(--topbar-height);
            background: var(--ag-card);
            border-bottom: 1px solid var(--ag-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .ag-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .ag-menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: var(--ag-radius-sm);
            cursor: pointer;
            color: var(--ag-text);
        }

        .ag-menu-toggle:hover {
            background: var(--ag-bg);
        }

        .ag-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .ag-breadcrumb a {
            color: var(--ag-text-muted);
            text-decoration: none;
        }

        .ag-breadcrumb a:hover {
            color: var(--ag-accent);
        }

        .ag-breadcrumb-sep {
            color: var(--ag-border);
        }

        .ag-breadcrumb-current {
            color: var(--ag-text);
            font-weight: 500;
        }

        .ag-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ag-topbar-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: var(--ag-radius-sm);
            cursor: pointer;
            color: var(--ag-text-muted);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ag-topbar-btn:hover {
            background: var(--ag-bg);
            color: var(--ag-text);
        }

        .ag-notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 18px;
            height: 18px;
            background: var(--ag-danger);
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Content */
        .ag-content {
            flex: 1;
            padding: 24px;
        }

        .ag-page-header {
            margin-bottom: 24px;
        }

        .ag-page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--ag-text);
            margin: 0 0 4px 0;
        }

        .ag-page-subtitle {
            color: var(--ag-text-muted);
            font-size: 14px;
            margin: 0;
        }

        /* Cards */
        .ag-card {
            background: var(--ag-card);
            border-radius: var(--ag-radius);
            border: 1px solid var(--ag-border);
            box-shadow: var(--ag-shadow);
        }

        .ag-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--ag-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ag-card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--ag-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ag-card-body {
            padding: 20px;
        }

        .ag-card-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--ag-border);
            background: var(--ag-bg);
            border-radius: 0 0 var(--ag-radius) var(--ag-radius);
        }

        /* Footer */
        .ag-footer {
            padding: 16px 24px;
            background: var(--ag-card);
            border-top: 1px solid var(--ag-border);
            font-size: 12px;
            color: var(--ag-text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Buttons */
        .ag-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--ag-radius-sm);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }

        .ag-btn i {
            width: 16px;
            height: 16px;
        }

        .ag-btn-primary {
            background: var(--ag-accent);
            color: #fff;
        }

        .ag-btn-primary:hover {
            background: var(--ag-accent-hover);
            color: #fff;
        }

        .ag-btn-secondary {
            background: var(--ag-bg);
            color: var(--ag-text);
            border: 1px solid var(--ag-border);
        }

        .ag-btn-secondary:hover {
            background: var(--ag-border);
            color: var(--ag-text);
        }

        .ag-btn-success {
            background: var(--ag-success);
            color: #fff;
        }

        .ag-btn-success:hover {
            background: #059669;
            color: #fff;
        }

        .ag-btn-danger {
            background: var(--ag-danger);
            color: #fff;
        }

        .ag-btn-danger:hover {
            background: #dc2626;
            color: #fff;
        }

        .ag-btn-warning {
            background: var(--ag-warning);
            color: #fff;
        }

        .ag-btn-warning:hover {
            background: #d97706;
            color: #fff;
        }

        .ag-btn-ghost {
            background: transparent;
            color: var(--ag-text-muted);
        }

        .ag-btn-ghost:hover {
            background: var(--ag-bg);
            color: var(--ag-text);
        }

        .ag-btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .ag-btn-sm i {
            width: 14px;
            height: 14px;
        }

        .ag-btn-xs {
            padding: 4px 8px;
            font-size: 11px;
        }

        .ag-btn-xs i {
            width: 12px;
            height: 12px;
        }

        /* Form Elements */
        .ag-form-group {
            margin-bottom: 16px;
        }

        .ag-form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--ag-text);
            margin-bottom: 6px;
        }

        .ag-form-label.required::after {
            content: ' *';
            color: var(--ag-danger);
        }

        .ag-form-input,
        .ag-form-select,
        .ag-form-textarea {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid var(--ag-border);
            border-radius: var(--ag-radius-sm);
            background: var(--ag-card);
            color: var(--ag-text);
            transition: all 0.2s;
        }

        .ag-form-input:focus,
        .ag-form-select:focus,
        .ag-form-textarea:focus {
            outline: none;
            border-color: var(--ag-accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .ag-form-input::placeholder {
            color: var(--ag-text-muted);
        }

        .ag-form-hint {
            font-size: 12px;
            color: var(--ag-text-muted);
            margin-top: 4px;
        }

        .ag-form-error {
            font-size: 12px;
            color: var(--ag-danger);
            margin-top: 4px;
        }

        /* Tables */
        .ag-table-wrapper {
            overflow-x: auto;
        }

        .ag-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ag-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ag-text-muted);
            background: var(--ag-bg);
            border-bottom: 1px solid var(--ag-border);
        }

        .ag-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--ag-border);
            vertical-align: middle;
        }

        .ag-table tbody tr:hover {
            background: var(--ag-bg);
        }

        .ag-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .ag-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 20px;
        }

        .ag-badge-primary {
            background: rgba(59, 130, 246, 0.1);
            color: var(--ag-accent);
        }

        .ag-badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--ag-success);
        }

        .ag-badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--ag-warning);
        }

        .ag-badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--ag-danger);
        }

        .ag-badge-secondary {
            background: var(--ag-bg);
            color: var(--ag-text-muted);
        }

        .ag-badge-info {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
        }

        /* Alerts */
        .ag-alert {
            padding: 14px 18px;
            border-radius: var(--ag-radius-sm);
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .ag-alert i {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .ag-alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .ag-alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .ag-alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .ag-alert-info {
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .ag-alert-close {
            margin-left: auto;
            background: transparent;
            border: none;
            cursor: pointer;
            opacity: 0.5;
            padding: 0;
        }

        .ag-alert-close:hover {
            opacity: 1;
        }

        /* Stats Card */
        .ag-stat-card {
            background: var(--ag-card);
            border-radius: var(--ag-radius);
            border: 1px solid var(--ag-border);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .ag-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--ag-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ag-stat-icon i {
            width: 24px;
            height: 24px;
        }

        .ag-stat-icon-blue {
            background: rgba(59, 130, 246, 0.1);
            color: var(--ag-accent);
        }

        .ag-stat-icon-green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--ag-success);
        }

        .ag-stat-icon-yellow {
            background: rgba(245, 158, 11, 0.1);
            color: var(--ag-warning);
        }

        .ag-stat-icon-red {
            background: rgba(239, 68, 68, 0.1);
            color: var(--ag-danger);
        }

        .ag-stat-content {
            flex: 1;
        }

        .ag-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--ag-text);
            line-height: 1;
        }

        .ag-stat-label {
            font-size: 13px;
            color: var(--ag-text-muted);
            margin-top: 4px;
        }

        /* Empty State */
        .ag-empty {
            text-align: center;
            padding: 48px 24px;
        }

        .ag-empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            color: var(--ag-border);
        }

        .ag-empty-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--ag-text);
            margin-bottom: 8px;
        }

        .ag-empty-text {
            color: var(--ag-text-muted);
            margin-bottom: 16px;
        }

        /* Dropdown / Popover */
        .ag-dropdown {
            position: fixed;
            background: var(--ag-card);
            border-radius: var(--ag-radius);
            box-shadow: var(--ag-shadow-lg);
            border: 1px solid var(--ag-border);
            z-index: 1100;
            min-width: 280px;
            display: none;
        }

        .ag-dropdown.show {
            display: block;
        }

        .ag-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--ag-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ag-dropdown-title {
            font-weight: 600;
            font-size: 14px;
        }

        .ag-dropdown-body {
            max-height: 320px;
            overflow-y: auto;
        }

        .ag-dropdown-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--ag-border);
        }

        .ag-dropdown-item:last-child {
            border-bottom: none;
        }

        .ag-dropdown-empty {
            padding: 32px 16px;
            text-align: center;
            color: var(--ag-text-muted);
        }

        .ag-dropdown-item.ag-info-item { border-left: 3px solid rgba(6, 182, 212, .55); }
        .ag-dropdown-item.ag-success-item { border-left: 3px solid rgba(52, 211, 153, .6); }
        .ag-dropdown-item.ag-quote-item { border-left: 3px solid rgba(167, 139, 250, .6); }

        /* User Dropdown */
        .ag-user-dropdown {
            min-width: 220px;
        }

        .ag-user-dropdown-header {
            padding: 16px;
            border-bottom: 1px solid var(--ag-border);
        }

        .ag-user-dropdown-name {
            font-weight: 600;
            color: var(--ag-text);
        }

        .ag-user-dropdown-email {
            font-size: 12px;
            color: var(--ag-text-muted);
        }

        .ag-user-dropdown-body {
            padding: 8px;
        }

        .ag-user-dropdown-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--ag-text);
            text-decoration: none;
            border-radius: var(--ag-radius-sm);
            font-size: 13px;
        }

        .ag-user-dropdown-link:hover {
            background: var(--ag-bg);
            color: var(--ag-text);
        }

        .ag-user-dropdown-link.danger {
            color: var(--ag-danger);
        }

        .ag-user-dropdown-link.danger:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .ag-user-dropdown-link i {
            width: 18px;
            height: 18px;
        }

        /* Dark Mode Toggle */
        .dark-mode-toggle {
            justify-content: flex-start;
        }

        .dark-mode-toggle .dark-mode-text {
            flex: 1;
        }

        .dark-mode-switch {
            width: 40px;
            height: 22px;
            background: #cbd5e1;
            border-radius: 11px;
            position: relative;
            transition: background 0.3s ease;
        }

        .dark-mode-switch-thumb {
            width: 18px;
            height: 18px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        html.dark-mode .dark-mode-switch {
            background: var(--ag-accent);
        }

        html.dark-mode .dark-mode-switch-thumb {
            transform: translateX(18px);
        }

        html.dark-mode .dark-mode-icon-moon {
            display: none !important;
        }

        html.dark-mode .dark-mode-icon-sun {
            display: block !important;
            color: #fbbf24;
        }

        /* Language Switch */
        .lang-switch-row {
            justify-content: center;
            gap: 10px;
            cursor: default;
        }

        .lang-symbol {
            font-size: 17px;
            line-height: 1;
            opacity: .45;
            filter: grayscale(60%);
            transition: opacity 0.3s ease, filter 0.3s ease;
        }

        .lang-switch-row.lang-is-tr .lang-symbol-tr {
            opacity: 1;
            filter: none;
        }

        .lang-switch-row:not(.lang-is-tr) .lang-symbol-en {
            opacity: 1;
            filter: none;
        }

        .lang-switch {
            width: 40px;
            height: 22px;
            background: #cbd5e1;
            border-radius: 11px;
            position: relative;
            flex-shrink: 0;
            transition: background 0.3s ease;
        }

        .lang-switch-thumb {
            width: 18px;
            height: 18px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .lang-switch-row.lang-is-tr .lang-switch {
            background: var(--ag-accent);
        }

        .lang-switch-row.lang-is-tr .lang-switch-thumb {
            transform: translateX(18px);
        }

        /* Language Loading Overlay */
        .lang-loading-overlay {
            position: fixed;
            inset: 0;
            background: var(--ag-bg);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .lang-loading-overlay.show {
            display: flex;
        }

        .lang-loading-text {
            font-size: 20px;
            font-weight: 600;
            color: var(--ag-text);
            opacity: 0;
            transition: opacity .4s ease;
            text-align: center;
            padding: 0 24px;
        }

        .lang-loading-text.visible {
            opacity: 1;
        }

        /* Pagination */
        .ag-pagination {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .ag-pagination-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--ag-border);
            border-radius: var(--ag-radius-sm);
            font-size: 13px;
            color: var(--ag-text);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .ag-pagination-btn:hover {
            background: var(--ag-bg);
            border-color: var(--ag-text-muted);
            color: var(--ag-text);
        }

        .ag-pagination-btn.active {
            background: var(--ag-accent);
            border-color: var(--ag-accent);
            color: #fff;
        }

        .ag-pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .ag-sidebar {
                transform: translateX(-100%);
            }

            .ag-sidebar.open {
                transform: translateX(0);
            }

            .ag-main {
                margin-left: 0;
            }

            .ag-menu-toggle {
                display: flex;
            }

            .ag-sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }

            .ag-sidebar-overlay.show {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .ag-content {
                padding: 16px;
            }

            .ag-topbar {
                padding: 0 16px;
            }

            .ag-page-title {
                font-size: 20px;
            }

            .ag-breadcrumb {
                display: none;
            }

            .ag-card-header,
            .ag-card-body,
            .ag-card-footer {
                padding: 14px 16px;
            }

            .ag-table th,
            .ag-table td {
                padding: 10px 12px;
            }

            .ag-footer {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }

        /* Utilities */
        .ag-mb-0 { margin-bottom: 0 !important; }
        .ag-mb-1 { margin-bottom: 8px !important; }
        .ag-mb-2 { margin-bottom: 16px !important; }
        .ag-mb-3 { margin-bottom: 24px !important; }
        .ag-mt-0 { margin-top: 0 !important; }
        .ag-mt-1 { margin-top: 8px !important; }
        .ag-mt-2 { margin-top: 16px !important; }
        .ag-mt-3 { margin-top: 24px !important; }
        .ag-text-muted { color: var(--ag-text-muted) !important; }
        .ag-text-success { color: var(--ag-success) !important; }
        .ag-text-danger { color: var(--ag-danger) !important; }
        .ag-text-warning { color: var(--ag-warning) !important; }
        .ag-text-right { text-align: right !important; }
        .ag-text-center { text-align: center !important; }
        .ag-flex { display: flex !important; }
        .ag-items-center { align-items: center !important; }
        .ag-justify-between { justify-content: space-between !important; }
        .ag-gap-1 { gap: 8px !important; }
        .ag-gap-2 { gap: 16px !important; }

        /* Hide scrollbar for sidebar nav */
        .ag-sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .ag-sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        .ag-sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        /* Legacy wrapper override styles - modern look */
        .card {
            border-radius: var(--ag-radius) !important;
            border: 1px solid var(--ag-border) !important;
            box-shadow: var(--ag-shadow) !important;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: var(--ag-bg) !important;
            border-bottom: 1px solid var(--ag-border) !important;
            padding: 1rem 1.25rem !important;
            border-radius: var(--ag-radius) var(--ag-radius) 0 0 !important;
        }
        
        .card-title {
            font-size: 0.95rem !important;
            font-weight: 600 !important;
            color: var(--ag-text) !important;
            margin: 0 !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .card-title i,
        .card-title .fas,
        .card-title .far,
        .card-title .fab {
            color: var(--ag-accent);
        }
        
        .card-body {
            padding: 1.25rem !important;
        }
        
        .card-primary:not(.card-outline) > .card-header,
        .card-success:not(.card-outline) > .card-header,
        .card-info:not(.card-outline) > .card-header,
        .card-warning:not(.card-outline) > .card-header,
        .card-danger:not(.card-outline) > .card-header,
        .card-secondary:not(.card-outline) > .card-header {
            background: var(--ag-sidebar) !important;
            color: #fff !important;
        }
        
        .card-primary:not(.card-outline) .card-title,
        .card-success:not(.card-outline) .card-title,
        .card-info:not(.card-outline) .card-title,
        .card-warning:not(.card-outline) .card-title,
        .card-danger:not(.card-outline) .card-title,
        .card-secondary:not(.card-outline) .card-title {
            color: #fff !important;
        }
        
        .card-primary:not(.card-outline) .card-title i,
        .card-success:not(.card-outline) .card-title i,
        .card-info:not(.card-outline) .card-title i,
        .card-warning:not(.card-outline) .card-title i,
        .card-danger:not(.card-outline) .card-title i,
        .card-secondary:not(.card-outline) .card-title i {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .card-outline {
            border-top: 3px solid var(--ag-accent) !important;
        }
        
        .card-primary.card-outline {
            border-top-color: var(--ag-accent) !important;
        }
        
        .card-success.card-outline {
            border-top-color: var(--ag-success) !important;
        }
        
        .card-info.card-outline {
            border-top-color: #17a2b8 !important;
        }
        
        .card-warning.card-outline {
            border-top-color: var(--ag-warning) !important;
        }
        
        .card-danger.card-outline {
            border-top-color: var(--ag-danger) !important;
        }
        
        /* Form controls override */
        .form-control {
            border-radius: var(--ag-radius-sm) !important;
            border: 1px solid var(--ag-border) !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.9rem !important;
            transition: all 0.2s ease !important;
        }
        
        .form-control:focus {
            border-color: var(--ag-accent) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        .form-group label {
            font-weight: 500 !important;
            color: var(--ag-text) !important;
            font-size: 0.875rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        .form-group label i {
            margin-right: 6px;
        }
        
        .input-group-text {
            background: var(--ag-bg) !important;
            border: 1px solid var(--ag-border) !important;
            border-radius: var(--ag-radius-sm) !important;
            font-size: 0.875rem !important;
        }
        
        .custom-select {
            border-radius: var(--ag-radius-sm) !important;
            border: 1px solid var(--ag-border) !important;
        }
        
        .custom-select:focus {
            border-color: var(--ag-accent) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        /* Button overrides */
        .btn {
            border-radius: var(--ag-radius-sm) !important;
            font-weight: 500 !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.875rem !important;
            transition: all 0.2s ease !important;
        }
        
        .btn-lg {
            padding: 0.75rem 1.5rem !important;
            font-size: 1rem !important;
        }
        
        .btn-primary {
            background: var(--ag-accent) !important;
            border-color: var(--ag-accent) !important;
        }
        
        .btn-primary:hover {
            background: var(--ag-accent-hover) !important;
            border-color: var(--ag-accent-hover) !important;
        }
        
        .btn-success {
            background: var(--ag-success) !important;
            border-color: var(--ag-success) !important;
        }
        
        .btn-success:hover {
            background: #059669 !important;
            border-color: #059669 !important;
        }
        
        .btn-danger {
            background: var(--ag-danger) !important;
            border-color: var(--ag-danger) !important;
        }
        
        .btn-danger:hover {
            background: #dc2626 !important;
            border-color: #dc2626 !important;
        }
        
        .btn-secondary {
            background: var(--ag-bg) !important;
            border-color: var(--ag-border) !important;
            color: var(--ag-text) !important;
        }
        
        .btn-secondary:hover {
            background: var(--ag-border) !important;
            border-color: var(--ag-text-muted) !important;
        }
        
        /* Alert overrides */
        .alert {
            border-radius: var(--ag-radius-sm) !important;
            border: none !important;
            padding: 1rem 1.25rem !important;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #065f46 !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #991b1b !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
        }
        
        .alert-warning {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #92400e !important;
            border: 1px solid rgba(245, 158, 11, 0.2) !important;
        }
        
        .alert-info {
            background: rgba(59, 130, 246, 0.1) !important;
            color: #1e40af !important;
            border: 1px solid rgba(59, 130, 246, 0.2) !important;
        }
        
        /* Table overrides */
        .table {
            margin-bottom: 0 !important;
        }
        
        .table th {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: var(--ag-text-muted) !important;
            background: var(--ag-bg) !important;
            border-bottom: 1px solid var(--ag-border) !important;
            padding: 0.75rem 1rem !important;
        }
        
        .table td {
            padding: 0.875rem 1rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid var(--ag-border) !important;
        }
        
        .table-sm th,
        .table-sm td {
            padding: 0.5rem 0.75rem !important;
        }
        
        .table-primary {
            background: rgba(59, 130, 246, 0.08) !important;
        }
        
        .table-success {
            background: rgba(16, 185, 129, 0.08) !important;
        }
        
        .table-warning {
            background: rgba(245, 158, 11, 0.08) !important;
        }
        
        /* Badge overrides */
        .badge {
            font-weight: 600 !important;
            padding: 0.35rem 0.65rem !important;
            border-radius: 20px !important;
            font-size: 0.75rem !important;
        }
        
        .badge-primary, .bg-primary {
            background: var(--ag-accent) !important;
        }
        
        .badge-success, .bg-success {
            background: var(--ag-success) !important;
        }
        
        .badge-warning, .bg-warning {
            background: var(--ag-warning) !important;
            color: #fff !important;
        }
        
        .badge-danger, .bg-danger {
            background: var(--ag-danger) !important;
        }
        
        .badge-info, .bg-info {
            background: #17a2b8 !important;
        }
        
        .badge-secondary, .bg-secondary {
            background: var(--ag-text-muted) !important;
        }
        
        /* Remove old content-wrapper padding conflicts */
        .content-wrapper {
            background: transparent !important;
            min-height: auto !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        /* Text color helpers */
        .text-primary {
            color: var(--ag-accent) !important;
        }
        
        .text-success {
            color: var(--ag-success) !important;
        }
        
        .text-danger {
            color: var(--ag-danger) !important;
        }
        
        .text-warning {
            color: var(--ag-warning) !important;
        }
        
        .text-info {
            color: #17a2b8 !important;
        }
        
        .text-muted {
            color: var(--ag-text-muted) !important;
        }
    </style>
    <style>
        .panel-info-toast { position:fixed; right:22px; bottom:22px; z-index:10050; width:min(360px,calc(100vw - 32px)); padding:14px 16px; border:1px solid rgba(96,165,250,.55); border-radius:10px; background:rgba(15,23,42,.96); color:#e2e8f0; box-shadow:0 10px 28px rgba(2,6,23,.35); animation:panelInfoToastIn .25s ease; }
        .panel-info-toast.success { border-color:rgba(52,211,153,.6); }
        .panel-info-toast.quote { border-color:rgba(167,139,250,.6); }
        .panel-info-toast strong { display:block; margin-bottom:4px; font-size:13px; color:#f8fafc; }
        .panel-info-toast span { font-size:12px; color:#cbd5e1; line-height:1.45; }
        .panel-info-toast button { position:absolute; top:7px; right:8px; border:0; background:transparent; color:#94a3b8; cursor:pointer; font-size:16px; }
        @keyframes panelInfoToastIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    </style>
    @stack('css')
</head>
<body data-user-id="{{ auth()->id() }}"
      data-user-level="{{ auth()->user()?->level }}"
      data-session-ping="{{ auth()->check() ? route('session.ping') : '' }}"
      data-session-partition="{{ $sessionPartition }}">

    <!-- Sidebar Overlay (Mobile) -->
    <div class="ag-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="ag-sidebar" id="sidebar">
        <div class="ag-sidebar-brand">
            <div class="ag-sidebar-brand-icon">
                <i data-lucide="building-2"></i>
            </div>
            <div class="ag-sidebar-brand-text">
                Acenta Panel
                <small>Yönetim Sistemi</small>
            </div>
        </div>

        <nav class="ag-sidebar-nav">
            <div class="ag-nav-section">
                <div class="ag-nav-section-title">Ana Menü</div>
                <a href="{{ route('agency.agencies.index') }}" class="ag-nav-item {{ request()->routeIs('agency.agencies.*') ? 'active' : '' }}">
                    <i data-lucide="users"></i>
                    <span>Acentalar</span>
                </a>
                <a href="{{ route('agency.tours.index') }}" class="ag-nav-item {{ request()->routeIs('agency.tours.*') ? 'active' : '' }}">
                    <i data-lucide="map"></i>
                    <span>Turlar</span>
                </a>
                <a href="{{ route('agency.tickets.index') }}" class="ag-nav-item {{ request()->routeIs('agency.tickets.*') ? 'active' : '' }}">
                    <i data-lucide="ticket"></i>
                    <span>Biletler</span>
                </a>
            </div>

            <div class="ag-nav-section">
                <div class="ag-nav-section-title">Finans</div>
                <a href="{{ route('agency.accounting.index') }}" class="ag-nav-item {{ request()->routeIs('agency.accounting.*') ? 'active' : '' }}">
                    <i data-lucide="wallet"></i>
                    <span>Muhasebe</span>
                </a>
            </div>
        </nav>

        @if($notificationUser)
        <div class="ag-sidebar-footer">
            <div class="ag-user-card" id="userCardTrigger">
                <div class="ag-user-avatar">
                    {{ strtoupper(substr($notificationUser->name ?? 'U', 0, 1)) }}
                </div>
                <div class="ag-user-info">
                    <div class="ag-user-name">{{ $notificationUser->name ?? 'Kullanıcı' }}</div>
                    <div class="ag-user-role">Sokak Acentası</div>
                </div>
                <i data-lucide="chevron-up" style="width:16px;height:16px;color:rgba(255,255,255,0.5)"></i>
            </div>
        </div>
        @endif
    </aside>

    <!-- Main Content -->
    <main class="ag-main">
        <!-- Topbar -->
        <header class="ag-topbar">
            <div class="ag-topbar-left">
                <button class="ag-menu-toggle" id="menuToggle">
                    <i data-lucide="menu"></i>
                </button>
                <nav class="ag-breadcrumb">
                    <a href="{{ route('agency.tickets.index') }}">Panel</a>
                    <span class="ag-breadcrumb-sep">/</span>
                    <span class="ag-breadcrumb-current">{{ $currentPageTitle }}</span>
                </nav>
            </div>
            <div class="ag-topbar-right">
                <button class="ag-topbar-btn" id="notificationToggle">
                    <i data-lucide="bell"></i>
                    @if($notificationCount > 0)
                        <span class="ag-notification-badge">{{ $notificationCount }}</span>
                    @endif
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="ag-content">
            @if(session('success'))
                <div class="ag-alert ag-alert-success">
                    <i data-lucide="check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button class="ag-alert-close" onclick="this.parentElement.remove()">
                        <i data-lucide="x" style="width:16px;height:16px"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="ag-alert ag-alert-danger">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button class="ag-alert-close" onclick="this.parentElement.remove()">
                        <i data-lucide="x" style="width:16px;height:16px"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="ag-alert ag-alert-danger">
                    <i data-lucide="alert-triangle"></i>
                    <div>
                        <ul style="margin:0;padding-left:16px">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="ag-footer">
            <span>&copy; {{ date('Y') }} Acenta Paneli. Tüm hakları saklıdır.</span>
            <span>v1.2.6</span>
        </footer>
    </main>

    <!-- Notification Dropdown -->
    <div class="ag-dropdown" id="notificationDropdown">
        <div class="ag-dropdown-header">
            <span class="ag-dropdown-title">Bildirimler</span>
            <span class="ag-text-muted" style="font-size:12px">{{ $notifications->count() }} bildirim</span>
        </div>
        <div class="ag-dropdown-body">
            <div id="agInfoNotifications"></div>
            @forelse($notifications as $note)
                <div class="ag-dropdown-item">
                    <div class="ag-flex ag-justify-between ag-items-center ag-mb-1">
                        <strong style="font-size:13px">{{ $note['title'] }}</strong>
                        <small class="ag-text-muted">{{ $note['time'] }}</small>
                    </div>
                    <p class="ag-text-muted ag-mb-1" style="font-size:12px;margin:0">{{ $note['message'] }}</p>
                    @if($note['type'] === 'incoming_agency' && $note['request_id'])
                        <div class="ag-flex ag-gap-1 ag-mt-1">
                            <form action="{{ route('agencies.requests.accept', $note['request_id']) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="ag-btn ag-btn-success ag-btn-xs">
                                    <i data-lucide="check"></i> Kabul
                                </button>
                            </form>
                            <form action="{{ route('agencies.requests.reject', $note['request_id']) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="ag-btn ag-btn-danger ag-btn-xs">
                                    <i data-lucide="x"></i> Reddet
                                </button>
                            </form>
                        </div>
                    @endif
                    @if($note['type'] === 'settlement_pending' && !empty($note['settlement_id']))
                        <div class="ag-flex ag-gap-1 ag-mt-1">
                            <form action="{{ route('agency.accounting.settlements.approve', $note['settlement_id']) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="ag-btn ag-btn-success ag-btn-xs">
                                    <i data-lucide="check"></i> Onayla
                                </button>
                            </form>
                            <form action="{{ route('agency.accounting.settlements.reject', $note['settlement_id']) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="ag-btn ag-btn-danger ag-btn-xs">
                                    <i data-lucide="x"></i> Reddet
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="ag-dropdown-empty">
                    <i data-lucide="bell-off" style="width:32px;height:32px;margin-bottom:8px;opacity:0.3"></i>
                    <p style="margin:0">Yeni bildirim yok</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- User Dropdown -->
    @if($notificationUser)
    <div class="ag-dropdown ag-user-dropdown" id="userDropdown">
        <div class="ag-user-dropdown-header">
            <div class="ag-user-dropdown-name">{{ $notificationUser->name }}</div>
            <div class="ag-user-dropdown-email">{{ $notificationUser->email }}</div>
        </div>
        <div class="ag-user-dropdown-body">
            <!-- Dark Mode Toggle -->
            <div class="ag-user-dropdown-link dark-mode-toggle" id="darkModeToggle" style="cursor:pointer;">
                <i data-lucide="moon" class="dark-mode-icon-moon"></i>
                <i data-lucide="sun" class="dark-mode-icon-sun" style="display:none;"></i>
                <span class="dark-mode-text">Karanlık Tema</span>
                <div class="dark-mode-switch">
                    <div class="dark-mode-switch-thumb"></div>
                </div>
            </div>
            <!-- Language Switch -->
            <div class="ag-user-dropdown-link lang-switch-row {{ (auth()->user()->locale ?? 'tr') === 'tr' ? 'lang-is-tr' : '' }}" id="languageToggleRow">
                <span class="lang-symbol lang-symbol-en" title="English">🇬🇧</span>
                <div class="lang-switch" id="languageToggle" style="cursor:pointer;">
                    <div class="lang-switch-thumb"></div>
                </div>
                <span class="lang-symbol lang-symbol-tr" title="Türkçe">🇹🇷</span>
            </div>
            <div style="border-top: 1px solid var(--ag-border); margin: 8px 0;"></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <input type="hidden" name="_session_partition" value="{{ $sessionPartition }}">
                <button type="submit" class="ag-user-dropdown-link danger" style="width:100%;border:none;background:none;cursor:pointer">
                    <i data-lucide="log-out"></i>
                    <span>Çıkış Yap</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Language Switch Loading Overlay -->
    <div class="lang-loading-overlay" id="langLoadingOverlay">
        <div class="lang-loading-text" id="langLoadingText"></div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Sidebar toggle
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

        // Notification dropdown
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationDropdown = document.getElementById('notificationDropdown');

        if (notificationToggle && notificationDropdown) {
            notificationToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const rect = notificationToggle.getBoundingClientRect();
                notificationDropdown.style.top = (rect.bottom + 8) + 'px';
                notificationDropdown.style.right = (window.innerWidth - rect.right) + 'px';
                notificationDropdown.style.left = 'auto';
                notificationDropdown.classList.toggle('show');
                userDropdown?.classList.remove('show');
            });
        }

        // User dropdown
        const userCardTrigger = document.getElementById('userCardTrigger');
        const userDropdown = document.getElementById('userDropdown');

        if (userCardTrigger && userDropdown) {
            userCardTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const rect = userCardTrigger.getBoundingClientRect();
                userDropdown.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                userDropdown.style.left = (rect.left) + 'px';
                userDropdown.style.top = 'auto';
                userDropdown.style.right = 'auto';
                userDropdown.classList.toggle('show');
                notificationDropdown?.classList.remove('show');
            });
        }

        // Close dropdowns on outside click
        document.addEventListener('click', (e) => {
            if (!notificationDropdown?.contains(e.target) && !notificationToggle?.contains(e.target)) {
                notificationDropdown?.classList.remove('show');
            }
            if (!userDropdown?.contains(e.target) && !userCardTrigger?.contains(e.target)) {
                userDropdown?.classList.remove('show');
            }
        });

        // Close dropdowns on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                notificationDropdown?.classList.remove('show');
                userDropdown?.classList.remove('show');
                sidebar?.classList.remove('open');
                sidebarOverlay?.classList.remove('show');
            }
        });

        // Session partition handling
        const sessionPartition = '{{ $sessionPartition }}';
        if (sessionPartition) {
            window.__sessionPartition = sessionPartition;
            
            // Add to all forms
            document.querySelectorAll('form').forEach(form => {
                if (!form.querySelector('input[name="_session_partition"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_session_partition';
                    input.value = sessionPartition;
                    form.appendChild(input);
                }
            });

            // Watch for new forms
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            if (node.tagName === 'FORM') {
                                if (!node.querySelector('input[name="_session_partition"]')) {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = '_session_partition';
                                    input.value = sessionPartition;
                                    node.appendChild(input);
                                }
                            } else if (node.querySelectorAll) {
                                node.querySelectorAll('form').forEach(form => {
                                    if (!form.querySelector('input[name="_session_partition"]')) {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = '_session_partition';
                                        input.value = sessionPartition;
                                        form.appendChild(input);
                                    }
                                });
                            }
                        }
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

        // Refresh CSRF token before non-GET form submit to reduce 419 errors on stale pages
        (function () {
            let csrfRefreshPromise = null;
            const csrfRefreshUrl = '{{ route('csrf.refresh') }}';
            const csrfPartition = window.__sessionPartition || 'agency';

            function setTokenEverywhere(token) {
                if (!token) return;
                document.querySelectorAll('input[name="_token"]').forEach((input) => {
                    input.value = token;
                });
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
            }

            function refreshCsrfToken() {
                if (!csrfRefreshPromise) {
                    csrfRefreshPromise = fetch(csrfRefreshUrl, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-Session-Partition': csrfPartition,
                            'Accept': 'application/json'
                        }
                    })
                    .then((r) => (r.ok ? r.json() : null))
                    .then((data) => {
                        if (data && data.token) {
                            setTokenEverywhere(data.token);
                            return data.token;
                        }
                        return null;
                    })
                    .finally(() => {
                        csrfRefreshPromise = null;
                    });
                }
                return csrfRefreshPromise;
            }

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                const method = (form.method || 'GET').toUpperCase();
                if (method === 'GET' || form.dataset.csrfRefreshing === '1') return;

                event.preventDefault();
                form.dataset.csrfRefreshing = '1';
                refreshCsrfToken().finally(() => {
                    form.submit();
                });
            }, true);
        })();

        // Dark Mode Toggle
        (function() {
            const darkModeKey = 'darkMode';
            const html = document.documentElement;
            
            // Check saved preference
            const savedDarkMode = localStorage.getItem(darkModeKey);
            if (savedDarkMode === 'true') {
                html.classList.add('dark-mode');
            }
            
            // Toggle handler
            const darkModeToggle = document.getElementById('darkModeToggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function() {
                    html.classList.toggle('dark-mode');
                    const isDark = html.classList.contains('dark-mode');
                    localStorage.setItem(darkModeKey, isDark);

                    // Re-initialize Lucide icons for the toggle
                    lucide.createIcons();
                });
            }
        })();

        // Language Switch - success toast (shown once, right after the reload that applied it)
        (function () {
            var FLAG = 'langSwitchNotice';
            var pending = sessionStorage.getItem(FLAG);
            if (!pending) return;
            sessionStorage.removeItem(FLAG);
            var old = document.querySelector('.panel-info-toast');
            if (old) old.remove();
            var toast = document.createElement('div');
            toast.className = 'panel-info-toast success';
            toast.innerHTML = '<button type="button" aria-label="Kapat">&times;</button><strong>{{ __('Dil Değiştirildi') }}</strong><span>{{ __('Dil başarıyla değiştirildi.') }}</span>';
            toast.querySelector('button').addEventListener('click', function () { toast.remove(); });
            document.body.appendChild(toast);
            setTimeout(function () { if (toast.isConnected) toast.remove(); }, 8000);
        })();

        // Language Switch
        (function () {
            var row = document.getElementById('languageToggleRow');
            var switchEl = document.getElementById('languageToggle');
            var overlay = document.getElementById('langLoadingOverlay');
            var textEl = document.getElementById('langLoadingText');
            if (!row || !switchEl || !overlay || !textEl) return;

            var phraseSets = {
                tr: [
                    ['Çaylar demleniyor...', 'Her şey hazırlanıyor...'],
                    ['Simitler fırınlanıyor...', 'Son dokunuşlar yapılıyor...'],
                    ['Kahve telveyle demleniyor...', 'Neredeyse hazır...'],
                    ['Misafir odası hazırlanıyor...', 'Birazdan buyurun...'],
                    ['Lokumlar tepsiye diziliyor...', 'Her şey yoluna giriyor...'],
                    ['Nazar boncuğu takılıyor...', 'İşte oldu...']
                ],
                en: [
                    ['Brewing the coffee...', 'Getting everything ready...'],
                    ['Toasting the bagels...', 'Putting on the finishing touches...'],
                    ['Warming up the kettle...', 'Almost there...'],
                    ['Setting the table...', 'Just a moment more...'],
                    ['Preheating the oven...', 'Everything is coming together...'],
                    ['Fluffing the pillows...', 'All set...']
                ]
            };

            function pickPhrases(locale) {
                var sets = phraseSets[locale] || phraseSets.tr;
                return sets[Math.floor(Math.random() * sets.length)];
            }

            function playSequence(locale, onDone) {
                var seq = pickPhrases(locale);
                overlay.classList.add('show');
                var i = 0;
                function showNext() {
                    textEl.textContent = seq[i];
                    textEl.classList.remove('visible');
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () { textEl.classList.add('visible'); });
                    });
                    setTimeout(function () {
                        textEl.classList.remove('visible');
                        setTimeout(function () {
                            i++;
                            if (i < seq.length) {
                                showNext();
                            } else {
                                onDone();
                            }
                        }, 400);
                    }, 1200);
                }
                showNext();
            }

            var switching = false;
            switchEl.addEventListener('click', function (e) {
                e.stopPropagation();
                if (switching) return;
                switching = true;
                var targetLocale = row.classList.contains('lang-is-tr') ? 'en' : 'tr';

                var sequenceDone = new Promise(function (resolve) {
                    playSequence(targetLocale, resolve);
                });

                var saveRequest = fetch('{{ route('language.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ locale: targetLocale })
                });

                Promise.all([sequenceDone, saveRequest]).then(function () {
                    sessionStorage.setItem('langSwitchNotice', '1');
                    window.location.reload();
                }).catch(function () {
                    sessionStorage.setItem('langSwitchNotice', '1');
                    window.location.reload();
                });
            });
        })();
    </script>
    <script>
        (function () {
            var interval = 10 * 60 * 1000;
            var STORAGE_KEY = 'agency_info_notifications';
            var MAX_ITEMS = 5;
            var toneClass = { success: 'ag-success-item', quote: 'ag-quote-item', info: 'ag-info-item' };

            function loadQueue() {
                try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch (e) { return []; }
            }
            function saveQueue(queue) {
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(queue)); } catch (e) {}
            }
            function updateBadge(queue) {
                var toggle = document.getElementById('notificationToggle');
                if (!toggle) return;
                var unseen = queue.filter(function (n) { return !n.seen; }).length;
                var total = {{ $notificationCount ?? 0 }} + unseen;
                var badge = toggle.querySelector('.ag-notification-badge');
                if (total > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'ag-notification-badge';
                        toggle.appendChild(badge);
                    }
                    badge.textContent = total;
                    badge.style.display = '';
                } else if (badge) {
                    badge.style.display = 'none';
                }
            }
            function renderInfoNotifications() {
                var container = document.getElementById('agInfoNotifications');
                if (!container) return;
                var body = container.closest('.ag-dropdown-body');
                var emptyEl = body ? body.querySelector('.ag-dropdown-empty') : null;
                var queue = loadQueue();
                container.innerHTML = queue.map(function (n) {
                    var cls = toneClass[n.tone] || 'ag-info-item';
                    return '<div class="ag-dropdown-item ' + cls + '">' +
                        '<div class="ag-flex ag-justify-between ag-items-center ag-mb-1">' +
                            '<strong style="font-size:13px">' + n.title + '</strong>' +
                            '<small class="ag-text-muted">Az önce</small>' +
                        '</div>' +
                        '<p class="ag-text-muted ag-mb-1" style="font-size:12px;margin:0">' + n.message + '</p>' +
                    '</div>';
                }).join('');
                if (emptyEl) emptyEl.style.display = queue.length > 0 ? 'none' : '';
                updateBadge(queue);
            }
            function addInfoNotification(note) {
                var queue = loadQueue();
                queue.unshift({ title: note.title, message: note.message, tone: note.tone || 'info', seen: false });
                queue = queue.slice(0, MAX_ITEMS);
                saveQueue(queue);
                renderInfoNotifications();
            }
            function markInfoNotificationsSeen() {
                var queue = loadQueue();
                var changed = false;
                queue.forEach(function (n) { if (!n.seen) { n.seen = true; changed = true; } });
                if (changed) { saveQueue(queue); renderInfoNotifications(); }
            }

            function showPanelInfoToast(note) {
                var old = document.querySelector('.panel-info-toast');
                if (old) old.remove();
                var toast = document.createElement('div');
                toast.className = 'panel-info-toast ' + (note.tone || 'info');
                toast.innerHTML = '<button type="button" aria-label="Kapat">&times;</button><strong>' + note.title + '</strong><span>' + note.message + '</span>';
                toast.querySelector('button').addEventListener('click', function () { toast.remove(); });
                document.body.appendChild(toast);
                setTimeout(function () { if (toast.isConnected) toast.remove(); }, 12000);
            }
            function pollPanelInfo() {
                fetch('{{ route('info.notification') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (response) { return response.ok ? response.json() : null; })
                    .then(function (note) {
                        if (note && note.message) {
                            showPanelInfoToast(note);
                            addInfoNotification(note);
                        }
                    })
                    .catch(function () {});
            }

            renderInfoNotifications();
            var bellToggle = document.getElementById('notificationToggle');
            if (bellToggle) bellToggle.addEventListener('click', markInfoNotificationsSeen);
            setInterval(pollPanelInfo, interval);
        }());
    </script>
    @stack('js')
    @yield('js')
</body>
</html>

