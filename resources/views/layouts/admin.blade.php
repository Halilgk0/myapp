@php
    $currentPageTitle = trim($__env->yieldContent('page_title', $__env->yieldContent('title', 'Dashboard')));
    $sessionPartition = 'admin';
    $adminUser = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    
    <style>
        :root {
            --ad-bg: #f1f5f9;
            --ad-sidebar: #111827;
            --ad-sidebar-hover: #1f2937;
            --ad-sidebar-active: #374151;
            --ad-accent: #6366f1;
            --ad-accent-hover: #4f46e5;
            --ad-accent-light: rgba(99, 102, 241, 0.1);
            --ad-text: #1e293b;
            --ad-text-muted: #64748b;
            --ad-border: #e2e8f0;
            --ad-card: #ffffff;
            --ad-success: #10b981;
            --ad-warning: #f59e0b;
            --ad-danger: #ef4444;
            --ad-info: #06b6d4;
            --ad-radius: 10px;
            --ad-radius-sm: 6px;
            --ad-shadow: 0 1px 2px rgba(0,0,0,0.06);
            --ad-shadow-lg: 0 4px 6px -1px rgba(0,0,0,0.1);
            --sidebar-width: 230px;
            --topbar-height: 52px;
        }

        /* Tema geçiş animasyonu - renkler aniden değil yumuşak geçsin */
        html, body, *, *::before, *::after {
            transition: background-color .25s ease, color .25s ease, border-color .25s ease, box-shadow .25s ease, fill .25s ease, stroke .25s ease;
        }

        /* Dark Mode */
        html.dark-mode {
            --ad-bg: #0f172a;
            --ad-sidebar: #020617;
            --ad-sidebar-hover: #1e293b;
            --ad-sidebar-active: #334155;
            --ad-text: #e2e8f0;
            --ad-text-muted: #94a3b8;
            --ad-border: #334155;
            --ad-card: #1e293b;
            --ad-shadow: 0 1px 2px rgba(0,0,0,0.3);
            --ad-shadow-lg: 0 4px 6px -1px rgba(0,0,0,0.4);
        }

        html.dark-mode .card,
        html.dark-mode .card-body,
        html.dark-mode .card-header,
        html.dark-mode .card-footer {
            background: var(--ad-card) !important;
            color: var(--ad-text) !important;
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .table,
        html.dark-mode .table-responsive {
            background: var(--ad-card) !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .table thead,
        html.dark-mode .table thead tr,
        html.dark-mode .table thead th {
            background: #334155 !important;
            background-color: #334155 !important;
            color: var(--ad-text) !important;
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .table tbody,
        html.dark-mode .table tbody tr {
            background: var(--ad-card) !important;
            background-color: var(--ad-card) !important;
        }

        html.dark-mode .table td,
        html.dark-mode .table th {
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
            background-color: inherit !important;
        }

        html.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: rgba(255,255,255,0.03) !important;
            --bs-table-accent-bg: rgba(255,255,255,0.03) !important;
        }

        html.dark-mode .table-striped > tbody > tr:nth-of-type(even) > * {
            background-color: var(--ad-card) !important;
        }

        html.dark-mode .table-hover > tbody > tr:hover > * {
            background-color: rgba(255,255,255,0.05) !important;
            --bs-table-accent-bg: rgba(255,255,255,0.05) !important;
        }

        html.dark-mode .table-bordered {
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .table-bordered > :not(caption) > * > * {
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .form-control,
        html.dark-mode .form-select {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .form-control::placeholder {
            color: var(--ad-text-muted) !important;
        }

        html.dark-mode .btn-light {
            background: #334155 !important;
            border-color: #475569 !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .text-muted {
            color: var(--ad-text-muted) !important;
        }

        html.dark-mode .alert {
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .small-box {
            box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
        }

        html.dark-mode .ad-dropdown {
            background: var(--ad-card) !important;
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .ad-dropdown-header {
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .filter-toggle-btn,
        html.dark-mode .filters-content,
        html.dark-mode .filter-form {
            background: var(--ad-card) !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .filter-card {
            background: #334155 !important;
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .filter-select,
        html.dark-mode .filter-input {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .filter-label {
            color: var(--ad-text-muted) !important;
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
            background-color: var(--ad-card) !important;
        }

        html.dark-mode .border {
            border-color: var(--ad-border) !important;
        }

        html.dark-mode .modal-content {
            background: var(--ad-card) !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .modal-header,
        html.dark-mode .modal-footer {
            border-color: var(--ad-border) !important;
        }

        html.dark-mode strong,
        html.dark-mode b {
            color: var(--ad-text) !important;
        }

        html.dark-mode code {
            background: #334155 !important;
            color: #f472b6 !important;
        }

        html.dark-mode .badge-secondary {
            background: #475569 !important;
        }

        html.dark-mode .pagination .page-link {
            background: var(--ad-card) !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }

        html.dark-mode .pagination .page-item.active .page-link {
            background: var(--ad-accent) !important;
            border-color: var(--ad-accent) !important;
        }

        html.dark-mode .bottom-pagination-wrapper {
            background: var(--ad-card) !important;
            border-color: var(--ad-border) !important;
        }

        /* ============================================== */
        /* OPERATIONS PAGE - DARK MODE */
        /* ============================================== */
        html.dark-mode .calendar-header {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .calendar-main-title {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .calendar-month-display {
            color: var(--ad-text-muted) !important;
        }
        
        html.dark-mode .calendar-nav-btn,
        html.dark-mode .calendar-action-btn {
            background: #334155 !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .calendar-nav-btn:hover,
        html.dark-mode .calendar-action-btn:hover {
            background: #475569 !important;
            border-color: var(--ad-accent) !important;
            color: var(--ad-accent) !important;
        }
        
        html.dark-mode .day-chip {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .day-chip .dc-day-name,
        html.dark-mode .day-chip .dc-month {
            color: var(--ad-text-muted) !important;
        }
        
        html.dark-mode .day-chip .dc-day {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .day-chip:hover {
            background: #334155 !important;
            border-color: var(--ad-accent) !important;
        }
        
        html.dark-mode .day-chip.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            border-color: #2563eb !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35) !important;
        }
        
        html.dark-mode .table-title {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .table-container {
            background: var(--ad-card) !important;
        }
        
        html.dark-mode .section-collapsed {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .section-collapsed:hover {
            background: #334155 !important;
        }
        
        html.dark-mode #tickets-filters,
        html.dark-mode #vehicles-filters,
        html.dark-mode #drivers-filters,
        html.dark-mode #guides-filters {
            background: transparent !important;
        }
        
        html.dark-mode #tickets-filters label,
        html.dark-mode #vehicles-filters label,
        html.dark-mode #drivers-filters label,
        html.dark-mode #guides-filters label {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .table-row {
            background: var(--ad-card) !important;
        }
        
        html.dark-mode .table-row:hover {
            background: #334155 !important;
        }
        
        html.dark-mode .ticket-item,
        html.dark-mode .vehicle-item {
            background: var(--ad-card) !important;
        }
        
        html.dark-mode .ticket-item:hover,
        html.dark-mode .vehicle-item:hover {
            background: #334155 !important;
        }
        
        html.dark-mode .tours-header {
            color: var(--ad-text) !important;
        }
        
        /* Tours Pagination */
        html.dark-mode .tours-pagination-controls {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .tours-pagination-btn {
            background: #334155 !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .tours-pagination-btn:hover {
            background: #475569 !important;
        }
        
        html.dark-mode .tours-page-info {
            color: var(--ad-text-muted) !important;
        }
        
        html.dark-mode .tours-per-page-btn {
            background: #334155 !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .tours-per-page-btn.active,
        html.dark-mode .tours-per-page-btn:hover {
            background: var(--ad-accent) !important;
            color: white !important;
        }
        
        /* ============================================== */
        /* TICKET CREATE PAGE - MINI CALENDAR DARK MODE */
        /* ============================================== */
        html.dark-mode .mini-calendar {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .mini-cal-header {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        }
        
        html.dark-mode .mini-cal-weekdays {
            background: #334155 !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .mini-cal-weekdays span {
            color: var(--ad-text-muted) !important;
        }
        
        html.dark-mode .mini-cal-grid {
            background: #1e293b !important;
        }
        
        html.dark-mode .mini-cal-day {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .mini-cal-day:hover:not(.disabled):not(.selected) {
            background: #334155 !important;
            border-color: var(--ad-accent) !important;
        }
        
        html.dark-mode .mini-cal-day.disabled {
            background: #0f172a !important;
            color: #475569 !important;
        }
        
        html.dark-mode .mini-cal-day.available {
            background: #1e3a5f !important;
            border-color: #3b82f6 !important;
        }
        
        html.dark-mode .mini-cal-day.selected {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            color: white !important;
        }
        
        /* ============================================== */
        /* TOUR CREATE PAGE - YEAR PLANNER DARK MODE */
        /* ============================================== */
        html.dark-mode .year-planner {
            background: transparent !important;
        }
        
        html.dark-mode .yp-card {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
        }
        
        html.dark-mode .yp-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.4) !important;
        }
        
        html.dark-mode .yp-header {
            color: var(--ad-text) !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .yp-weekdays span {
            color: var(--ad-text-muted) !important;
        }
        
        html.dark-mode .yp-day {
            background: #0f172a !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .yp-day:hover:not(.out):not(.locked) {
            background: #334155 !important;
            border-color: var(--ad-accent) !important;
        }
        
        html.dark-mode .yp-day.out {
            background: #0f172a !important;
            color: #475569 !important;
        }
        
        html.dark-mode .yp-day .day-price {
            background: #334155 !important;
            color: var(--ad-accent) !important;
        }
        
        html.dark-mode .yp-day.sel .day-price {
            background: rgba(255,255,255,0.2) !important;
            color: white !important;
        }
        
        html.dark-mode .planner-menu {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.4) !important;
        }
        
        html.dark-mode #selected-dates-wrapper {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode #toggle-selected-dates {
            background: #334155 !important;
            color: var(--ad-text) !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode #toggle-selected-dates:hover {
            background: #475569 !important;
        }
        
        /* ============================================== */
        /* GENERAL DARK MODE - ADDITIONAL ELEMENTS */
        /* ============================================== */
        html.dark-mode .content-wrapper,
        html.dark-mode .content,
        html.dark-mode .main-content {
            background: var(--ad-bg) !important;
        }
        
        html.dark-mode label {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .callout {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .callout h5,
        html.dark-mode .callout p {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .nav-link {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .nav-tabs .nav-link {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .nav-tabs .nav-link.active {
            background: #334155 !important;
            border-bottom-color: #334155 !important;
        }
        
        html.dark-mode .tab-content {
            background: var(--ad-card) !important;
        }
        
        html.dark-mode .list-group-item {
            background: var(--ad-card) !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .list-group-item:hover {
            background: #334155 !important;
        }
        
        html.dark-mode h1, html.dark-mode h2, html.dark-mode h3, 
        html.dark-mode h4, html.dark-mode h5, html.dark-mode h6 {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode p {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .breadcrumb {
            background: transparent !important;
        }
        
        html.dark-mode .breadcrumb-item,
        html.dark-mode .breadcrumb-item a {
            color: var(--ad-text-muted) !important;
        }
        
        html.dark-mode .breadcrumb-item.active {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode hr {
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .dropdown-menu {
            background: var(--ad-card) !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .dropdown-item {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .dropdown-item:hover {
            background: #334155 !important;
        }
        
        html.dark-mode .dropdown-divider {
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) !important;
        }
        
        html.dark-mode select option {
            background: #1e293b !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .btn-outline-secondary,
        html.dark-mode .btn-outline-primary {
            color: var(--ad-text) !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .btn-outline-secondary:hover,
        html.dark-mode .btn-outline-primary:hover {
            background: #334155 !important;
        }
        
        html.dark-mode .input-group-text {
            background: #334155 !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .custom-select,
        html.dark-mode .custom-control-label {
            color: var(--ad-text) !important;
        }
        
        html.dark-mode .was-validated .form-control:valid,
        html.dark-mode .form-control.is-valid {
            border-color: #22c55e !important;
        }
        
        html.dark-mode .was-validated .form-control:invalid,
        html.dark-mode .form-control.is-invalid {
            border-color: #ef4444 !important;
        }
        
        /* Tour chip buttons (operations page) - force dark text */
        html.dark-mode .tour-chip-btn {
            background: #1e293b !important;
            border-color: var(--ad-border) !important;
        }
        
        html.dark-mode .tour-chip-btn:hover {
            background: #334155 !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--ad-bg);
            color: var(--ad-text);
            font-size: 13px;
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Sidebar */
        .ad-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--ad-sidebar);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .ad-sidebar-brand {
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .ad-sidebar-brand-icon {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--ad-accent) 0%, #818cf8 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .ad-sidebar-brand-icon i {
            width: 18px;
            height: 18px;
        }

        .ad-sidebar-brand-text {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }

        .ad-sidebar-brand-text small {
            display: block;
            font-weight: 400;
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            margin-top: 1px;
        }

        .ad-sidebar-nav {
            flex: 1;
            padding: 12px 8px;
            overflow-y: auto;
        }

        .ad-nav-section {
            margin-bottom: 18px;
        }

        .ad-nav-section-title {
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: rgba(255,255,255,0.35);
            padding: 0 10px;
            margin-bottom: 6px;
        }

        .ad-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: var(--ad-radius-sm);
            margin-bottom: 2px;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 12px;
        }

        .ad-nav-item:hover {
            background: var(--ad-sidebar-hover);
            color: #fff;
        }

        .ad-nav-item.active {
            background: var(--ad-accent);
            color: #fff;
        }

        .ad-nav-item i {
            width: 16px;
            height: 16px;
            stroke-width: 2;
        }

        .ad-nav-badge {
            margin-left: auto;
            background: var(--ad-danger);
            color: #fff;
            font-size: 9px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 8px;
        }

        .ad-sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .ad-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: var(--ad-radius-sm);
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.2s;
        }

        .ad-user-card:hover {
            background: rgba(255,255,255,0.1);
        }

        .ad-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ad-accent) 0%, #818cf8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 12px;
        }

        .ad-user-info {
            flex: 1;
            min-width: 0;
        }

        .ad-user-name {
            color: #fff;
            font-weight: 500;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ad-user-role {
            color: rgba(255,255,255,0.5);
            font-size: 10px;
        }

        /* Main Content */
        .ad-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Topbar */
        .ad-topbar {
            height: var(--topbar-height);
            background: var(--ad-card);
            border-bottom: 1px solid var(--ad-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .ad-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ad-menu-toggle {
            display: none;
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            border-radius: var(--ad-radius-sm);
            cursor: pointer;
            color: var(--ad-text);
        }

        .ad-menu-toggle:hover {
            background: var(--ad-bg);
        }

        .ad-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
        }

        .ad-breadcrumb a {
            color: var(--ad-text-muted);
            text-decoration: none;
        }

        .ad-breadcrumb a:hover {
            color: var(--ad-accent);
        }

        .ad-breadcrumb-sep {
            color: var(--ad-border);
        }

        .ad-breadcrumb-current {
            color: var(--ad-text);
            font-weight: 500;
        }

        .ad-topbar-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ad-topbar-btn {
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            border-radius: var(--ad-radius-sm);
            cursor: pointer;
            color: var(--ad-text-muted);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ad-topbar-btn:hover {
            background: var(--ad-bg);
            color: var(--ad-text);
        }

        /* Notifications */
        .ad-notifications-wrapper {
            position: relative;
        }

        .ad-notifications-btn {
            position: relative;
        }

        .ad-notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: var(--ad-danger);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .ad-notifications-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 320px;
            background: var(--ad-card);
            border-radius: var(--ad-radius);
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid var(--ad-border);
            z-index: 1100;
            display: none;
            overflow: hidden;
        }

        .ad-notifications-dropdown.show {
            display: block;
            animation: notificationSlideIn 0.2s ease;
        }

        @keyframes notificationSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ad-notifications-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--ad-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--ad-bg);
        }

        .ad-notifications-title {
            font-weight: 600;
            font-size: 13px;
            color: var(--ad-text);
        }

        .ad-notifications-count {
            font-size: 11px;
            color: var(--ad-accent);
            font-weight: 500;
        }

        .ad-notifications-body {
            max-height: 320px;
            overflow-y: auto;
        }

        .ad-notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            border-bottom: 1px solid var(--ad-border);
            transition: all 0.2s ease;
        }

        .ad-notification-item:last-child {
            border-bottom: none;
        }

        .ad-notification-item:hover {
            background: var(--ad-bg);
        }

        .ad-notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ad-notification-icon i {
            width: 18px;
            height: 18px;
        }

        .ad-notification-warning .ad-notification-icon {
            background: rgba(245, 158, 11, 0.1);
            color: var(--ad-warning);
        }

        .ad-notification-info .ad-notification-icon {
            background: rgba(6, 182, 212, 0.1);
            color: var(--ad-info);
        }

        .ad-notification-success .ad-notification-icon {
            background: rgba(16, 185, 129, 0.1);
            color: var(--ad-success);
        }

        .ad-notification-danger .ad-notification-icon {
            background: rgba(239, 68, 68, 0.1);
            color: var(--ad-danger);
        }

        .ad-notification-quote .ad-notification-icon {
            background: rgba(167, 139, 250, 0.1);
            color: #a78bfa;
        }

        .ad-notification-content {
            flex: 1;
            min-width: 0;
        }

        .ad-notification-text {
            font-size: 13px;
            color: var(--ad-text);
            font-weight: 500;
            line-height: 1.4;
        }

        .ad-notification-time {
            font-size: 11px;
            color: var(--ad-text-muted);
            margin-top: 2px;
        }

        .ad-notification-empty {
            padding: 32px 16px;
            text-align: center;
            color: var(--ad-text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .ad-notification-empty i {
            width: 32px;
            height: 32px;
            color: var(--ad-success);
        }

        .ad-notifications-footer {
            padding: 12px 16px;
            border-top: 1px solid var(--ad-border);
            background: var(--ad-bg);
            text-align: center;
        }

        .ad-notifications-footer a {
            font-size: 12px;
            color: var(--ad-accent);
            text-decoration: none;
            font-weight: 500;
        }

        .ad-notifications-footer a:hover {
            color: var(--ad-accent-hover);
        }

        /* Content */
        .ad-content {
            flex: 1;
            padding: 16px;
        }

        .ad-page-header {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .ad-page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--ad-text);
            margin: 0;
        }

        .ad-page-subtitle {
            color: var(--ad-text-muted);
            font-size: 12px;
            margin: 2px 0 0 0;
        }

        /* Cards */
        .ad-card {
            background: var(--ad-card);
            border-radius: var(--ad-radius);
            border: 1px solid var(--ad-border);
            box-shadow: var(--ad-shadow);
        }

        .ad-card-header {
            padding: 12px 14px;
            border-bottom: 1px solid var(--ad-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ad-card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--ad-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ad-card-body {
            padding: 14px;
        }

        .ad-card-footer {
            padding: 12px 14px;
            border-top: 1px solid var(--ad-border);
            background: var(--ad-bg);
            border-radius: 0 0 var(--ad-radius) var(--ad-radius);
        }

        /* Stats Card */
        .ad-stat-card {
            background: var(--ad-card);
            border-radius: var(--ad-radius);
            border: 1px solid var(--ad-border);
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .ad-stat-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--ad-shadow-lg);
        }

        .ad-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--ad-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ad-stat-icon i {
            width: 20px;
            height: 20px;
        }

        .ad-stat-icon-purple {
            background: rgba(99, 102, 241, 0.1);
            color: var(--ad-accent);
        }

        .ad-stat-icon-green {
            background: rgba(16, 185, 129, 0.1);
            color: var(--ad-success);
        }

        .ad-stat-icon-yellow {
            background: rgba(245, 158, 11, 0.1);
            color: var(--ad-warning);
        }

        .ad-stat-icon-red {
            background: rgba(239, 68, 68, 0.1);
            color: var(--ad-danger);
        }

        .ad-stat-icon-cyan {
            background: rgba(6, 182, 212, 0.1);
            color: var(--ad-info);
        }

        .ad-stat-content {
            flex: 1;
        }

        .ad-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--ad-text);
            line-height: 1;
        }

        .ad-stat-label {
            font-size: 11px;
            color: var(--ad-text-muted);
            margin-top: 3px;
        }

        .ad-stat-link {
            color: var(--ad-accent);
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            margin-top: 6px;
        }

        .ad-stat-link:hover {
            color: var(--ad-accent-hover);
        }

        /* Footer */
        .ad-footer {
            padding: 12px 16px;
            background: var(--ad-card);
            border-top: 1px solid var(--ad-border);
            font-size: 11px;
            color: var(--ad-text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Buttons */
        .ad-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 500;
            border-radius: var(--ad-radius-sm);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }

        .ad-btn i {
            width: 14px;
            height: 14px;
        }

        .ad-btn-primary {
            background: var(--ad-accent);
            color: #fff;
        }

        .ad-btn-primary:hover {
            background: var(--ad-accent-hover);
            color: #fff;
        }

        .ad-btn-secondary {
            background: var(--ad-bg);
            color: var(--ad-text);
            border: 1px solid var(--ad-border);
        }

        .ad-btn-secondary:hover {
            background: var(--ad-border);
            color: var(--ad-text);
        }

        .ad-btn-success {
            background: var(--ad-success);
            color: #fff;
        }

        .ad-btn-success:hover {
            background: #059669;
            color: #fff;
        }

        .ad-btn-danger {
            background: var(--ad-danger);
            color: #fff;
        }

        .ad-btn-danger:hover {
            background: #dc2626;
            color: #fff;
        }

        .ad-btn-warning {
            background: var(--ad-warning);
            color: #fff;
        }

        .ad-btn-warning:hover {
            background: #d97706;
            color: #fff;
        }

        .ad-btn-info {
            background: var(--ad-info);
            color: #fff;
        }

        .ad-btn-info:hover {
            background: #0891b2;
            color: #fff;
        }

        .ad-btn-ghost {
            background: transparent;
            color: var(--ad-text-muted);
        }

        .ad-btn-ghost:hover {
            background: var(--ad-bg);
            color: var(--ad-text);
        }

        .ad-btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .ad-btn-sm i {
            width: 14px;
            height: 14px;
        }

        .ad-btn-xs {
            padding: 4px 8px;
            font-size: 11px;
        }

        .ad-btn-xs i {
            width: 12px;
            height: 12px;
        }

        /* Tables */
        .ad-table-wrapper {
            overflow-x: auto;
        }

        .ad-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ad-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ad-text-muted);
            background: var(--ad-bg);
            border-bottom: 1px solid var(--ad-border);
        }

        .ad-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--ad-border);
            vertical-align: middle;
        }

        .ad-table tbody tr:hover {
            background: var(--ad-bg);
        }

        .ad-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .ad-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 20px;
        }

        .ad-badge-primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--ad-accent);
        }

        .ad-badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--ad-success);
        }

        .ad-badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--ad-warning);
        }

        .ad-badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--ad-danger);
        }

        .ad-badge-secondary {
            background: var(--ad-bg);
            color: var(--ad-text-muted);
        }

        .ad-badge-info {
            background: rgba(6, 182, 212, 0.1);
            color: var(--ad-info);
        }

        .ad-content .card {
            background: var(--ad-card) !important;
            border: 1px solid var(--ad-border) !important;
            border-radius: var(--ad-radius) !important;
            box-shadow: var(--ad-shadow) !important;
            color: var(--ad-text);
        }

        .ad-content .card-header {
            background: transparent !important;
            border-bottom: 1px solid var(--ad-border) !important;
            color: var(--ad-text) !important;
            padding: 12px 14px;
        }

        .ad-content .card-header .card-title,
        .ad-content .card-header h1,
        .ad-content .card-header h2,
        .ad-content .card-header h3,
        .ad-content .card-header h4,
        .ad-content .card-header h5,
        .ad-content .card-header h6 {
            color: var(--ad-text) !important;
            font-size: 13px;
            font-weight: 600;
            margin: 0;
        }

        .ad-content .card-body { color: var(--ad-text); padding: 14px; }
        .ad-content .card-footer { background: var(--ad-bg) !important; border-top: 1px solid var(--ad-border) !important; }
        .ad-content .card-primary,
        .ad-content .card-success,
        .ad-content .card-info,
        .ad-content .card-warning,
        .ad-content .card-danger { border-top: 0 !important; }

        .ad-content .btn {
            border-radius: var(--ad-radius-sm);
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .ad-content .btn-primary { background: var(--ad-accent); border-color: var(--ad-accent); }
        .ad-content .btn-primary:hover { background: var(--ad-accent-hover); border-color: var(--ad-accent-hover); }
        .ad-content .btn-success { background: var(--ad-success); border-color: var(--ad-success); }
        .ad-content .btn-warning { background: var(--ad-warning); border-color: var(--ad-warning); color: #fff; }
        .ad-content .btn-danger { background: var(--ad-danger); border-color: var(--ad-danger); }
        .ad-content .btn-info { background: var(--ad-info); border-color: var(--ad-info); }
        .ad-content .btn-light,
        .ad-content .btn-secondary,
        .ad-content .btn-outline-secondary {
            background: var(--ad-bg);
            border-color: var(--ad-border);
            color: var(--ad-text);
        }
        .ad-content .btn-light:hover,
        .ad-content .btn-secondary:hover,
        .ad-content .btn-outline-secondary:hover { background: var(--ad-border); color: var(--ad-text); }

        .ad-content .table { color: var(--ad-text); border-color: var(--ad-border); margin-bottom: 0; }
        .ad-content .table thead th { background: var(--ad-bg); color: var(--ad-text-muted); border-color: var(--ad-border); font-size: 11px; text-transform: uppercase; letter-spacing: .4px; }
        .ad-content .table td,
        .ad-content .table th { border-color: var(--ad-border); vertical-align: middle; }
        .ad-content .table-hover tbody tr:hover { background: var(--ad-bg); color: var(--ad-text); }
        .ad-content .badge { border-radius: 20px; font-size: 11px; font-weight: 600; padding: 4px 10px; }
        .ad-content .badge-light,
        .ad-content .badge-secondary { background: var(--ad-bg); color: var(--ad-text-muted); }

        html.dark-mode .ad-content .bg-white,
        html.dark-mode .ad-content .bg-light { background: var(--ad-bg) !important; color: var(--ad-text) !important; }

        .ad-content .admin-list-toolbar {
            background: var(--ad-card) !important;
            border: 1px solid var(--ad-border);
            border-radius: var(--ad-radius);
            box-shadow: var(--ad-shadow);
            padding: 12px 14px;
        }

        .ad-content .admin-list-toolbar .control-title { color: var(--ad-text); font-size: 15px; font-weight: 700; }
        .ad-content .admin-list-toolbar .control-subtitle { color: var(--ad-text-muted); font-size: 12px; }
        .ad-content .admin-list-toolbar .search-box { background: var(--ad-bg); border-color: var(--ad-border); }
        .ad-content .admin-list-toolbar .search-input { color: var(--ad-text); }
        .ad-content .admin-list-toolbar .size-selector { background: var(--ad-bg); border-color: var(--ad-border); }
        .ad-content .admin-list-toolbar .size-btn { color: var(--ad-text-muted); }
        .ad-content .admin-list-toolbar .size-btn.active { background: var(--ad-accent); color: #fff; }

        .ad-content .small-box,
        .ad-content .info-box,
        .ad-content .currency-card,
        .ad-content .table-container,
        .ad-content .filter-card {
            background: var(--ad-card) !important;
            border: 1px solid var(--ad-border) !important;
            border-radius: var(--ad-radius) !important;
            box-shadow: var(--ad-shadow);
            color: var(--ad-text);
        }

        .ad-content .small-box {
            min-height: 112px;
            overflow: hidden;
            position: relative;
        }

        .ad-content .small-box .inner { padding: 16px; position: relative; z-index: 1; }
        .ad-content .small-box .inner h3 { color: var(--ad-text); font-size: 24px; font-weight: 700; }
        .ad-content .small-box .inner p { color: var(--ad-text-muted); font-size: 12px; }
        .ad-content .small-box .icon { color: var(--ad-accent); opacity: .12; }
        .ad-content .small-box-footer { display:block; padding:8px 16px; background:var(--ad-bg); color:var(--ad-accent) !important; font-size:11px; text-decoration:none; }
        .ad-content .small-box.bg-info,
        .ad-content .small-box.bg-success,
        .ad-content .small-box.bg-warning,
        .ad-content .small-box.bg-danger { background: var(--ad-card) !important; }

        .ad-content .info-box {
            min-height: 70px;
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 12px;
        }
        .ad-content .info-box-icon { width: 42px; height: 42px; border-radius: var(--ad-radius-sm); display:flex; align-items:center; justify-content:center; margin-right:12px; color:#fff; }
        .ad-content .info-box-content { min-width:0; }
        .ad-content .info-box-text { color:var(--ad-text-muted); font-size:11px; }
        .ad-content .info-box-number { color:var(--ad-text); font-size:18px; font-weight:700; }

        .ad-content .filter-card { padding: 12px; }
        .ad-content .filter-label { color:var(--ad-text-muted); }
        .ad-content .filter-select,
        .ad-content .filter-input,
        .ad-content .form-control { background:var(--ad-card); border-color:var(--ad-border); color:var(--ad-text); }
        .ad-content .filter-icon { border-radius:var(--ad-radius-sm); }
        .ad-content .currency-card { padding: 12px; }
        .ad-content .currency-header,
        .ad-content .currency-body,
        .ad-content .currency-row { color:var(--ad-text); border-color:var(--ad-border); }
        .ad-content .currency-label { color:var(--ad-text-muted); }
        .ad-content .table-container { padding: 12px; }
        .ad-content .table-title { color:var(--ad-text); border-bottom-color:var(--ad-border); }
        .ad-content .btn-group .btn { border-radius:var(--ad-radius-sm) !important; margin-right:3px; }

        .ad-content .btn-group .btn {
            min-width: 34px;
            min-height: 32px;
            padding: 6px 9px;
            border-width: 1px !important;
            border-radius: 8px !important;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset;
        }
        .ad-content .btn-group .btn-info { background: rgba(6,182,212,.14) !important; border-color: rgba(34,211,238,.82) !important; color: #67e8f9 !important; }
        .ad-content .btn-group .btn-warning { background: rgba(245,158,11,.16) !important; border-color: rgba(251,191,36,.84) !important; color: #fcd34d !important; }
        .ad-content .btn-group .btn-danger { background: rgba(239,68,68,.14) !important; border-color: rgba(248,113,113,.82) !important; color: #fca5a5 !important; }
        .ad-content .btn-group .btn-success { background: rgba(16,185,129,.14) !important; border-color: rgba(52,211,153,.82) !important; color: #6ee7b7 !important; }
        .ad-content .btn-group .btn-primary { background: rgba(99,102,241,.14) !important; border-color: rgba(129,140,248,.82) !important; color: #a5b4fc !important; }
        .ad-content .btn-group .btn:hover { filter: brightness(1.16); }
        .ad-content .ad-table .btn,
        .ad-content table .btn {
            min-height: 32px;
            padding: 6px 9px;
            border-width: 1px !important;
            border-radius: 8px !important;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset;
        }
        .ad-content table .btn-info { background: rgba(6,182,212,.14) !important; border-color: rgba(34,211,238,.82) !important; color: #67e8f9 !important; }
        .ad-content table .btn-warning { background: rgba(245,158,11,.16) !important; border-color: rgba(251,191,36,.84) !important; color: #fcd34d !important; }
        .ad-content table .btn-danger { background: rgba(239,68,68,.14) !important; border-color: rgba(248,113,113,.82) !important; color: #fca5a5 !important; }
        .ad-content table .btn-success { background: rgba(16,185,129,.14) !important; border-color: rgba(52,211,153,.82) !important; color: #6ee7b7 !important; }
        .ad-content table .btn-primary { background: rgba(99,102,241,.14) !important; border-color: rgba(129,140,248,.82) !important; color: #a5b4fc !important; }
        .ad-content table .btn:hover { filter: brightness(1.16); }
        .ad-content .ad-table td:last-child,
        .ad-content table td:last-child { text-align: right; }
        .ad-content .ad-table td:last-child .btn-group,
        .ad-content table td:last-child .btn-group { justify-content: flex-end; display: inline-flex; }
        .ad-content .ad-table td:last-child form,
        .ad-content table td:last-child form { display: inline-flex !important; }

        .ad-content .ticket-page .btn,
        .ad-content .tour-page .btn { box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset; }

        .ad-content .tour-page .btn {
            min-height: 36px;
            padding: 8px 14px;
            border-width: 1px;
            border-style: solid;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset;
            transition: background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .ad-content .tour-page .btn-sm { min-height: 34px; padding: 7px 12px; }
        .ad-content .tour-page .btn-lg { min-height: 40px; padding: 9px 18px; font-size: 13px; }
        .ad-content .tour-page .btn-primary { background: rgba(99,102,241,.16); border-color: rgba(129,140,248,.8); color: #a5b4fc; }
        .ad-content .tour-page .btn-success { background: rgba(16,185,129,.16); border-color: rgba(52,211,153,.8); color: #6ee7b7; }
        .ad-content .tour-page .btn-warning { background: rgba(245,158,11,.16); border-color: rgba(251,191,36,.85); color: #fcd34d; }
        .ad-content .tour-page .btn-danger { background: rgba(239,68,68,.14); border-color: rgba(248,113,113,.8); color: #fca5a5; }
        .ad-content .tour-page .btn-info { background: rgba(6,182,212,.14); border-color: rgba(34,211,238,.8); color: #67e8f9; }
        .ad-content .tour-page .btn-secondary,
        .ad-content .tour-page .btn-light,
        .ad-content .tour-page .btn-outline-secondary { background: rgba(148,163,184,.12); border-color: rgba(148,163,184,.7); color: var(--ad-text); }
        .ad-content .tour-page .btn-outline-primary { background: rgba(99,102,241,.10); border-color: rgba(129,140,248,.75); color: #a5b4fc; }
        .ad-content .tour-page .btn-outline-success { background: rgba(16,185,129,.10); border-color: rgba(52,211,153,.75); color: #6ee7b7; }
        .ad-content .tour-page .btn-outline-danger { background: rgba(239,68,68,.10); border-color: rgba(248,113,113,.75); color: #fca5a5; }
        .ad-content .tour-page .btn-outline-warning { background: rgba(245,158,11,.10); border-color: rgba(251,191,36,.75); color: #fcd34d; }
        .ad-content .tour-page .btn-outline-info { background: rgba(6,182,212,.10); border-color: rgba(34,211,238,.75); color: #67e8f9; }
        .ad-content .tour-page .btn:hover { filter: brightness(1.16); box-shadow: 0 0 0 2px rgba(255,255,255,.08) inset, 0 3px 12px rgba(15,23,42,.16); }
        .ad-content .tour-page .sa-tool-btn { min-height: 34px; padding: 7px; border: 1px solid rgba(148,163,184,.35); background: rgba(148,163,184,.08); color: var(--ad-text-muted); }
        .ad-content .tour-page .sa-tool-btn.active { background: rgba(99,102,241,.22); border-color: rgba(129,140,248,.85); color: #a5b4fc; }
        .ad-content .tour-page .sa-tool-btn[data-tool="delete"]:hover,
        .ad-content .tour-page .sa-tool-btn[data-tool="delete-all"]:hover { background: rgba(239,68,68,.16); border-color: rgba(248,113,113,.8); color: #fca5a5; }

        .ad-content .tour-page .btn-primary { background: rgba(99,102,241,.16) !important; border: 1px solid rgba(129,140,248,.85) !important; color: #a5b4fc !important; }
        .ad-content .tour-page .btn-success { background: rgba(16,185,129,.16) !important; border: 1px solid rgba(52,211,153,.85) !important; color: #6ee7b7 !important; }
        .ad-content .tour-page .btn-warning { background: rgba(245,158,11,.16) !important; border: 1px solid rgba(251,191,36,.85) !important; color: #fcd34d !important; }
        .ad-content .tour-page .btn-danger { background: rgba(239,68,68,.14) !important; border: 1px solid rgba(248,113,113,.85) !important; color: #fca5a5 !important; }
        .ad-content .tour-page .btn-secondary,
        .ad-content .tour-page .btn-light { background: rgba(148,163,184,.12) !important; border: 1px solid rgba(148,163,184,.75) !important; color: var(--ad-text) !important; }
        .ad-content .tour-page .btn-outline-primary { background: rgba(99,102,241,.10) !important; border: 1px solid rgba(129,140,248,.8) !important; color: #a5b4fc !important; }
        .ad-content .tour-page .btn-outline-success { background: rgba(16,185,129,.10) !important; border: 1px solid rgba(52,211,153,.8) !important; color: #6ee7b7 !important; }
        .ad-content .tour-page .btn-outline-danger { background: rgba(239,68,68,.10) !important; border: 1px solid rgba(248,113,113,.8) !important; color: #fca5a5 !important; }
        .ad-content .tour-page .btn-outline-secondary { background: rgba(148,163,184,.10) !important; border: 1px solid rgba(148,163,184,.7) !important; color: var(--ad-text-muted) !important; }
        .ad-content .tour-page .ad-btn {
            min-height: 36px;
            padding: 8px 14px;
            border-radius: 8px;
            border-width: 1px;
            border-style: solid;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset;
        }
        .ad-content .tour-page .ad-btn-primary { background: rgba(99,102,241,.16) !important; border-color: rgba(129,140,248,.85) !important; color: #a5b4fc !important; }
        .ad-content .tour-page .ad-btn-info { background: rgba(6,182,212,.14) !important; border-color: rgba(34,211,238,.85) !important; color: #67e8f9 !important; }
        .ad-content .tour-page .ad-btn-warning { background: rgba(245,158,11,.16) !important; border-color: rgba(251,191,36,.85) !important; color: #fcd34d !important; }
        .ad-content .tour-page .ad-btn-danger { background: rgba(239,68,68,.14) !important; border-color: rgba(248,113,113,.85) !important; color: #fca5a5 !important; }
        .ad-content .tour-page .ad-btn-secondary { background: rgba(148,163,184,.12) !important; border-color: rgba(148,163,184,.75) !important; color: var(--ad-text) !important; }

        .ad-content .ticket-page .btn {
            min-height: 36px;
            padding: 8px 14px;
            border-radius: 8px;
            border-width: 1px;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset;
        }
        .ad-content .ticket-page .btn-success { background: rgba(16,185,129,.16) !important; border-color: rgba(52,211,153,.85) !important; color: #6ee7b7 !important; }
        .ad-content .ticket-page .btn-secondary,
        .ad-content .ticket-page .btn-light { background: rgba(148,163,184,.12) !important; border-color: rgba(148,163,184,.75) !important; color: var(--ad-text) !important; }
        .ad-content .ticket-page .btn-primary { background: rgba(99,102,241,.16) !important; border-color: rgba(129,140,248,.85) !important; color: #a5b4fc !important; }
        .ad-content .ticket-page .btn-warning { background: rgba(245,158,11,.16) !important; border-color: rgba(251,191,36,.85) !important; color: #fcd34d !important; }
        .ad-content .ticket-page .btn-danger { background: rgba(239,68,68,.14) !important; border-color: rgba(248,113,113,.85) !important; color: #fca5a5 !important; }
        .ad-content .ticket-page .btn:hover { filter: brightness(1.16); }

        .ad-content .tour-page .widget-user-2,
        .ad-content .tour-page .tour-action-card + .card,
        .ad-content .tour-page .tour-action-card ~ .card {
            background: var(--ad-card) !important;
            border: 1px solid var(--ad-border) !important;
            border-radius: var(--ad-radius) !important;
            box-shadow: var(--ad-shadow) !important;
            overflow: hidden;
        }
        .ad-content .tour-page .widget-user-header,
        .ad-content .tour-page .widget-user-2 .bg-gradient-primary,
        .ad-content .tour-page .bg-gradient-info {
            background: var(--ad-card) !important;
            color: var(--ad-text) !important;
            padding: 14px !important;
        }
        .ad-content .tour-page .widget-user-image { display:none; }
        .ad-content .tour-page .widget-user-username { font-size:16px; font-weight:700; color:var(--ad-text) !important; margin:0 0 3px; }
        .ad-content .tour-page .widget-user-desc { font-size:12px; color:var(--ad-text-muted) !important; margin:0; }
        .ad-content .tour-page .widget-user-2 .card-footer { background:var(--ad-card) !important; border-top:1px solid var(--ad-border); }
        .ad-content .tour-page .widget-user-2 .nav-link { padding:9px 14px; color:var(--ad-text-muted); font-size:12px; border-bottom:1px solid var(--ad-border); }
        .ad-content .tour-page .widget-user-2 .nav-link:last-child { border-bottom:0; }
        .ad-content .tour-page .callout { margin:0 0 8px; padding:10px 12px; border-left:2px solid var(--ad-accent); background:var(--ad-bg) !important; color:var(--ad-text); border-radius:6px; }
        .ad-content .tour-page .callout:last-child { margin-bottom:0; }
        .ad-content .tour-page .callout h5 { font-size:13px; margin:0 0 3px; color:var(--ad-text); }
        .ad-content .tour-page .callout p { font-size:11px; color:var(--ad-text-muted); }
        .ad-content .tour-page .widget-user-2 + .card,
        .ad-content .tour-page .widget-user-2 ~ .card { margin-top:12px; }

        .ad-content .tour-page .ad-btn,
        .ad-content .tour-page .ad-btn-sm { min-height: 32px; padding: 6px 10px; font-size: 11px; }

        /* Alerts */
        .ad-alert {
            padding: 14px 18px;
            border-radius: var(--ad-radius-sm);
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .ad-alert i {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .ad-alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .ad-alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .ad-alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .ad-alert-info {
            background: rgba(6, 182, 212, 0.1);
            color: #0e7490;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .ad-alert-close {
            margin-left: auto;
            background: transparent;
            border: none;
            cursor: pointer;
            opacity: 0.5;
            padding: 0;
        }

        .ad-alert-close:hover {
            opacity: 1;
        }

        /* Form Elements */
        .ad-form-group {
            margin-bottom: 16px;
        }

        .ad-form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--ad-text);
            margin-bottom: 6px;
        }

        .ad-form-label.required::after {
            content: ' *';
            color: var(--ad-danger);
        }

        .ad-form-input,
        .ad-form-select,
        .ad-form-textarea {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid var(--ad-border);
            border-radius: var(--ad-radius-sm);
            background: var(--ad-card);
            color: var(--ad-text);
            transition: all 0.2s;
        }

        .ad-form-input:focus,
        .ad-form-select:focus,
        .ad-form-textarea:focus {
            outline: none;
            border-color: var(--ad-accent);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* User Dropdown */
        .ad-dropdown {
            position: fixed;
            background: var(--ad-card);
            border-radius: var(--ad-radius);
            box-shadow: var(--ad-shadow-lg);
            border: 1px solid var(--ad-border);
            z-index: 1100;
            min-width: 220px;
            display: none;
        }

        .ad-dropdown.show {
            display: block;
        }

        .ad-dropdown-header {
            padding: 16px;
            border-bottom: 1px solid var(--ad-border);
        }

        .ad-dropdown-name {
            font-weight: 600;
            color: var(--ad-text);
        }

        .ad-dropdown-email {
            font-size: 12px;
            color: var(--ad-text-muted);
        }

        .ad-dropdown-body {
            padding: 8px;
        }

        .ad-dropdown-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--ad-text);
            text-decoration: none;
            border-radius: var(--ad-radius-sm);
            font-size: 13px;
        }

        .ad-dropdown-link:hover {
            background: var(--ad-bg);
            color: var(--ad-text);
        }

        .ad-dropdown-link.danger {
            color: var(--ad-danger);
        }

        .ad-dropdown-link.danger:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .ad-dropdown-link i {
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
            background: var(--ad-accent);
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
            background: var(--ad-accent);
        }

        .lang-switch-row.lang-is-tr .lang-switch-thumb {
            transform: translateX(18px);
        }

        /* Language Loading Overlay */
        .lang-loading-overlay {
            position: fixed;
            inset: 0;
            background: var(--ad-bg);
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
            color: var(--ad-text);
            opacity: 0;
            transition: opacity .4s ease;
            text-align: center;
            padding: 0 24px;
        }

        .lang-loading-text.visible {
            opacity: 1;
        }

        /* Pagination */
        .ad-pagination {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .ad-pagination-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--ad-border);
            border-radius: var(--ad-radius-sm);
            font-size: 13px;
            color: var(--ad-text);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .ad-pagination-btn:hover {
            background: var(--ad-bg);
            border-color: var(--ad-text-muted);
            color: var(--ad-text);
        }

        .ad-pagination-btn.active {
            background: var(--ad-accent);
            border-color: var(--ad-accent);
            color: #fff;
        }

        /* Empty State */
        .ad-empty {
            text-align: center;
            padding: 48px 24px;
        }

        .ad-empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            color: var(--ad-border);
        }

        .ad-empty-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--ad-text);
            margin-bottom: 8px;
        }

        .ad-empty-text {
            color: var(--ad-text-muted);
            margin-bottom: 16px;
        }

        /* Mobile Responsive */
        @media (max-width: 1024px) {
            .ad-sidebar {
                transform: translateX(-100%);
            }

            .ad-sidebar.open {
                transform: translateX(0);
            }

            .ad-main {
                margin-left: 0;
            }

            .ad-menu-toggle {
                display: flex;
            }

            .ad-sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }

            .ad-sidebar-overlay.show {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .ad-content {
                padding: 16px;
            }

            .ad-topbar {
                padding: 0 16px;
            }

            .ad-page-title {
                font-size: 20px;
            }

            .ad-breadcrumb {
                display: none;
            }

            .ad-footer {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }

        /* Utilities */
        .ad-mb-0 { margin-bottom: 0 !important; }
        .ad-mb-1 { margin-bottom: 8px !important; }
        .ad-mb-2 { margin-bottom: 16px !important; }
        .ad-mb-3 { margin-bottom: 24px !important; }
        .ad-mt-0 { margin-top: 0 !important; }
        .ad-mt-1 { margin-top: 8px !important; }
        .ad-mt-2 { margin-top: 16px !important; }
        .ad-mt-3 { margin-top: 24px !important; }
        .ad-text-muted { color: var(--ad-text-muted) !important; }
        .ad-text-success { color: var(--ad-success) !important; }
        .ad-text-danger { color: var(--ad-danger) !important; }
        .ad-text-warning { color: var(--ad-warning) !important; }
        .ad-text-right { text-align: right !important; }
        .ad-text-center { text-align: center !important; }
        .ad-flex { display: flex !important; }
        .ad-items-center { align-items: center !important; }
        .ad-justify-between { justify-content: space-between !important; }
        .ad-gap-1 { gap: 8px !important; }
        .ad-gap-2 { gap: 16px !important; }
        .ad-gap-3 { gap: 24px !important; }

        /* Sidebar scrollbar */
        .ad-sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .ad-sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        .ad-sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }

        /* Legacy wrapper override styles */
        .card {
            border-radius: var(--ad-radius) !important;
            border: 1px solid var(--ad-border) !important;
            box-shadow: var(--ad-shadow) !important;
            margin-bottom: 1rem;
        }
        
        .card-header {
            background: var(--ad-bg) !important;
            border-bottom: 1px solid var(--ad-border) !important;
            padding: 0.65rem 0.85rem !important;
            border-radius: var(--ad-radius) var(--ad-radius) 0 0 !important;
        }
        
        .card-title {
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: var(--ad-text) !important;
            margin: 0 !important;
        }
        
        .card-title i,
        .card-title .fas,
        .card-title .far,
        .card-title .fab {
            color: var(--ad-accent);
            font-size: 0.85rem;
        }
        
        .card-body {
            padding: 0.85rem !important;
        }
        
        .card-primary:not(.card-outline) > .card-header,
        .card-success:not(.card-outline) > .card-header,
        .card-info:not(.card-outline) > .card-header,
        .card-warning:not(.card-outline) > .card-header,
        .card-danger:not(.card-outline) > .card-header {
            background: var(--ad-sidebar) !important;
            color: #fff !important;
        }
        
        .card-primary:not(.card-outline) .card-title,
        .card-success:not(.card-outline) .card-title,
        .card-info:not(.card-outline) .card-title,
        .card-warning:not(.card-outline) .card-title,
        .card-danger:not(.card-outline) .card-title {
            color: #fff !important;
        }
        
        .card-outline {
            border-top: 3px solid var(--ad-accent) !important;
        }
        
        .card-primary.card-outline { border-top-color: var(--ad-accent) !important; }
        .card-success.card-outline { border-top-color: var(--ad-success) !important; }
        .card-info.card-outline { border-top-color: var(--ad-info) !important; }
        .card-warning.card-outline { border-top-color: var(--ad-warning) !important; }
        .card-danger.card-outline { border-top-color: var(--ad-danger) !important; }
        
        /* Form controls */
        .form-control {
            border-radius: var(--ad-radius-sm) !important;
            border: 1px solid var(--ad-border) !important;
            padding: 0.4rem 0.65rem !important;
            font-size: 0.8rem !important;
            transition: all 0.2s ease !important;
        }
        
        .form-control:focus {
            border-color: var(--ad-accent) !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1) !important;
        }
        
        .form-group {
            margin-bottom: 0.75rem !important;
        }
        
        .form-group label {
            font-weight: 500 !important;
            color: var(--ad-text) !important;
            font-size: 0.75rem !important;
            margin-bottom: 0.35rem !important;
        }
        
        .input-group-text {
            background: var(--ad-bg) !important;
            border: 1px solid var(--ad-border) !important;
            border-radius: var(--ad-radius-sm) !important;
            font-size: 0.8rem !important;
            padding: 0.4rem 0.65rem !important;
        }
        
        .custom-select {
            border-radius: var(--ad-radius-sm) !important;
            border: 1px solid var(--ad-border) !important;
        }
        
        .custom-select:focus {
            border-color: var(--ad-accent) !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
        
        /* Button overrides */
        .btn {
            border-radius: var(--ad-radius-sm) !important;
            font-weight: 500 !important;
            padding: 0.35rem 0.7rem !important;
            font-size: 0.75rem !important;
            transition: all 0.2s ease !important;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        
        .btn i, .btn .fas, .btn .far, .btn .fab {
            font-size: 0.8rem !important;
        }
        
        .btn-primary {
            background: var(--ad-accent) !important;
            border-color: var(--ad-accent) !important;
        }
        
        .btn-primary:hover {
            background: var(--ad-accent-hover) !important;
            border-color: var(--ad-accent-hover) !important;
        }
        
        .btn-success {
            background: var(--ad-success) !important;
            border-color: var(--ad-success) !important;
        }
        
        .btn-success:hover {
            background: #059669 !important;
            border-color: #059669 !important;
        }
        
        .btn-danger {
            background: var(--ad-danger) !important;
            border-color: var(--ad-danger) !important;
        }
        
        .btn-danger:hover {
            background: #dc2626 !important;
            border-color: #dc2626 !important;
        }
        
        .btn-warning {
            background: var(--ad-warning) !important;
            border-color: var(--ad-warning) !important;
            color: #fff !important;
        }
        
        .btn-warning:hover {
            background: #d97706 !important;
            border-color: #d97706 !important;
        }
        
        .btn-info {
            background: var(--ad-info) !important;
            border-color: var(--ad-info) !important;
        }
        
        .btn-info:hover {
            background: #0891b2 !important;
            border-color: #0891b2 !important;
        }
        
        .btn-secondary {
            background: var(--ad-bg) !important;
            border-color: var(--ad-border) !important;
            color: var(--ad-text) !important;
        }
        
        .btn-secondary:hover {
            background: var(--ad-border) !important;
            border-color: var(--ad-text-muted) !important;
        }
        
        /* Alert overrides */
        .alert {
            border-radius: var(--ad-radius-sm) !important;
            border: none !important;
            padding: 0.65rem 0.85rem !important;
            font-size: 0.8rem !important;
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
            background: rgba(6, 182, 212, 0.1) !important;
            color: #0e7490 !important;
            border: 1px solid rgba(6, 182, 212, 0.2) !important;
        }
        
        /* Table overrides */
        .table {
            margin-bottom: 0 !important;
            font-size: 0.75rem !important;
        }
        
        .table th {
            font-size: 0.65rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            color: var(--ad-text-muted) !important;
            background: var(--ad-bg) !important;
            border-bottom: 1px solid var(--ad-border) !important;
            padding: 0.5rem 0.65rem !important;
        }
        
        .table td {
            padding: 0.5rem 0.65rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid var(--ad-border) !important;
            font-size: 0.75rem !important;
        }
        
        .table-sm th, .table-sm td {
            padding: 0.35rem 0.5rem !important;
        }
        
        /* Badge overrides */
        .badge {
            font-weight: 600 !important;
            padding: 0.2rem 0.45rem !important;
            border-radius: 12px !important;
            font-size: 0.65rem !important;
        }
        
        .badge-primary, .bg-primary { background: var(--ad-accent) !important; }
        .badge-success, .bg-success { background: var(--ad-success) !important; }
        .badge-warning, .bg-warning { background: var(--ad-warning) !important; color: #fff !important; }
        .badge-danger, .bg-danger { background: var(--ad-danger) !important; }
        .badge-info, .bg-info { background: var(--ad-info) !important; }
        .badge-secondary, .bg-secondary { background: var(--ad-text-muted) !important; }
        
        /* Small box overrides */
        .small-box {
            border-radius: var(--ad-radius) !important;
            box-shadow: var(--ad-shadow) !important;
            margin-bottom: 0.75rem !important;
            overflow: hidden !important;
            min-height: auto !important;
        }
        
        .small-box .inner {
            padding: 10px 12px !important;
        }
        
        .small-box .inner h3 {
            font-weight: 700 !important;
            font-size: 1.35rem !important;
            margin: 0 0 2px 0 !important;
            white-space: nowrap !important;
        }
        
        .small-box .inner p {
            font-size: 0.7rem !important;
            margin-bottom: 0 !important;
            white-space: nowrap !important;
        }
        
        .small-box .icon {
            top: 5px !important;
            right: 8px !important;
        }
        
        .small-box .icon i {
            font-size: 40px !important;
        }
        
        .small-box-footer {
            border-radius: 0 0 var(--ad-radius) var(--ad-radius) !important;
            padding: 5px 10px !important;
            font-size: 0.65rem !important;
            display: block !important;
            text-align: center !important;
            color: rgba(255,255,255,0.8) !important;
            background: rgba(0,0,0,0.1) !important;
        }
        
        .small-box-footer:hover {
            color: #fff !important;
            background: rgba(0,0,0,0.15) !important;
        }
        
        .bg-info { background: var(--ad-info) !important; }
        .bg-success { background: var(--ad-success) !important; }
        .bg-warning { background: var(--ad-warning) !important; }
        .bg-danger { background: var(--ad-danger) !important; }
        .bg-primary { background: var(--ad-accent) !important; }
        
        /* Text colors */
        .text-primary { color: var(--ad-accent) !important; }
        .text-success { color: var(--ad-success) !important; }
        .text-danger { color: var(--ad-danger) !important; }
        .text-warning { color: var(--ad-warning) !important; }
        .text-info { color: var(--ad-info) !important; }
        .text-muted { color: var(--ad-text-muted) !important; }
        
        /* Content wrapper reset */
        .content-wrapper {
            background: transparent !important;
            min-height: auto !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        /* Global Control Panel Styles */
        .tickets-control-panel,
        .tours-control-panel,
        .vehicles-control-panel,
        .drivers-control-panel,
        .guides-control-panel {
            background: linear-gradient(135deg, var(--ad-accent) 0%, #4f46e5 100%) !important;
            border-radius: var(--ad-radius) !important;
            padding: 10px 14px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            margin-bottom: 12px !important;
        }
        
        .control-left {
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
        }
        
        .control-left .control-title {
            color: white !important;
            margin: 0 !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        
        .control-left .control-title i {
            font-size: 12px !important;
        }
        
        .control-left .control-subtitle {
            color: rgba(255,255,255,0.8) !important;
            margin: 0 !important;
            font-size: 11px !important;
        }
        
        .control-right {
            display: flex !important;
            gap: 10px !important;
            align-items: center !important;
            flex-wrap: wrap !important;
        }
        
        .control-item {
            display: flex !important;
            flex-direction: column !important;
            gap: 3px !important;
        }
        
        .control-item .control-label {
            color: rgba(255,255,255,0.7) !important;
            font-size: 9px !important;
            font-weight: 500 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.3px !important;
            margin: 0 !important;
        }
        
        .control-item .btn {
            padding: 4px 10px !important;
            font-size: 11px !important;
        }
        
        .control-item .btn i {
            font-size: 10px !important;
        }
        
        .control-item .form-control,
        .control-item .input-group-text {
            padding: 4px 8px !important;
            font-size: 11px !important;
            height: auto !important;
        }
        
        .control-item .input-group {
            min-width: 180px !important;
        }
        
        .size-selector {
            display: flex !important;
            gap: 3px !important;
        }
        
        .size-selector .size-btn {
            padding: 3px 8px !important;
            font-size: 10px !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
            background: transparent !important;
            color: white !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
        }
        
        .size-selector .size-btn:hover,
        .size-selector .size-btn.active {
            background: white !important;
            color: var(--ad-accent) !important;
            border-color: white !important;
        }
        
        .custom-pagination .pagination {
            margin: 0 !important;
            gap: 2px !important;
        }
        
        .custom-pagination .page-link {
            padding: 3px 8px !important;
            font-size: 10px !important;
            border-radius: 4px !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
            background: transparent !important;
            color: white !important;
        }
        
        .custom-pagination .page-link:hover,
        .custom-pagination .page-item.active .page-link {
            background: white !important;
            color: var(--ad-accent) !important;
            border-color: white !important;
        }
        
        @media (max-width: 992px) {
            .tickets-control-panel,
            .tours-control-panel,
            .vehicles-control-panel,
            .drivers-control-panel,
            .guides-control-panel {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            
            .control-right {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            
            .control-item .input-group {
                min-width: 100% !important;
            }
        }
        
        /* Select2 override */
        .select2-container--default .select2-selection--single {
            border-radius: var(--ad-radius-sm) !important;
            border: 1px solid var(--ad-border) !important;
            height: auto !important;
            padding: 0.4rem 0.5rem !important;
        }
        
        .select2-container--default .select2-selection--single:focus {
            border-color: var(--ad-accent) !important;
        }
        
        .select2-dropdown {
            border-radius: var(--ad-radius-sm) !important;
            border: 1px solid var(--ad-border) !important;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--ad-accent) !important;
        }
    </style>
    <style>
        .panel-info-toast { position:fixed; right:22px; bottom:22px; z-index:10050; width:min(360px,calc(100vw - 32px)); padding:14px 16px; border:1px solid rgba(129,140,248,.55); border-radius:10px; background:rgba(30,41,59,.96); color:#e2e8f0; box-shadow:0 10px 28px rgba(2,6,23,.35); animation:panelInfoToastIn .25s ease; }
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
      data-session-partition="{{ $sessionPartition }}">

    <!-- Sidebar Overlay (Mobile) -->
    <div class="ad-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="ad-sidebar" id="sidebar">
        <div class="ad-sidebar-brand">
            <div class="ad-sidebar-brand-icon">
                <i data-lucide="shield-check"></i>
            </div>
            <div class="ad-sidebar-brand-text">
                Admin Panel
                <small>{{ __('Yönetim Sistemi') }}</small>
            </div>
        </div>

        <nav class="ad-sidebar-nav">
            <div class="ad-nav-section">
                <div class="ad-nav-section-title">{{ __('Genel') }}</div>
                <a href="{{ route('admin.dashboard') }}" class="ad-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>{{ __('Dashboard') }}</span>
                </a>
            </div>

            <div class="ad-nav-section">
                <div class="ad-nav-section-title">{{ __('Operasyonlar') }}</div>
                <a href="{{ route('admin.tickets.index') }}" class="ad-nav-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                    <i data-lucide="ticket"></i>
                    <span>{{ __('Biletler') }}</span>
                </a>
                <a href="{{ route('admin.tours.index') }}" class="ad-nav-item {{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">
                    <i data-lucide="map"></i>
                    <span>{{ __('Turlar') }}</span>
                </a>
                <a href="{{ route('admin.vehicles.index') }}" class="ad-nav-item {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                    <i data-lucide="car"></i>
                    <span>{{ __('Araçlar') }}</span>
                </a>
                <a href="{{ route('admin.operations.index') }}" class="ad-nav-item {{ request()->routeIs('admin.operations.*') ? 'active' : '' }}">
                    <i data-lucide="settings-2"></i>
                    <span>{{ __('Operasyonlar') }}</span>
                </a>
            </div>

            <div class="ad-nav-section">
                <div class="ad-nav-section-title">{{ __('Kullanıcılar') }}</div>
                <a href="{{ route('admin.drivers.index') }}" class="ad-nav-item {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}">
                    <i data-lucide="user-circle"></i>
                    <span>{{ __('Şoförler') }}</span>
                </a>
                <a href="{{ route('admin.guides.index') }}" class="ad-nav-item {{ request()->routeIs('admin.guides.*') ? 'active' : '' }}">
                    <i data-lucide="users"></i>
                    <span>{{ __('Rehberler') }}</span>
                </a>
                <a href="{{ route('admin.agencies.index') }}" class="ad-nav-item {{ request()->routeIs('admin.agencies.*') ? 'active' : '' }}">
                    <i data-lucide="building-2"></i>
                    <span>{{ __('Acentalar') }}</span>
                </a>
            </div>

            <div class="ad-nav-section">
                <div class="ad-nav-section-title">{{ __('Talepler') }}</div>
                <a href="{{ route('admin.ticket-requests.index') }}" class="ad-nav-item {{ request()->routeIs('admin.ticket-requests.*') ? 'active' : '' }}">
                    <i data-lucide="inbox"></i>
                    <span>{{ __('Bilet Talepleri') }}</span>
                    @php
                        $pendingRequests = \App\Models\TicketRequest::where('status', 'pending')->count();
                    @endphp
                    @if($pendingRequests > 0)
                        <span class="ad-nav-badge">{{ $pendingRequests }}</span>
                    @endif
                </a>
            </div>

            <div class="ad-nav-section">
                <div class="ad-nav-section-title">{{ __('Finans') }}</div>
                <a href="{{ route('admin.accounting.index') }}" class="ad-nav-item {{ request()->routeIs('admin.accounting.*') ? 'active' : '' }}">
                    <i data-lucide="wallet"></i>
                    <span>{{ __('Muhasebe') }}</span>
                </a>
            </div>
        </nav>

        @if($adminUser)
        <div class="ad-sidebar-footer">
            <div class="ad-user-card" id="userCardTrigger">
                <div class="ad-user-avatar">
                    {{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}
                </div>
                <div class="ad-user-info">
                    <div class="ad-user-name">{{ $adminUser->name ?? 'Admin' }}</div>
                    <div class="ad-user-role">{{ __('Yönetici') }}</div>
                </div>
                <i data-lucide="chevron-up" style="width:16px;height:16px;color:rgba(255,255,255,0.5)"></i>
            </div>
        </div>
        @endif
    </aside>

    <!-- Main Content -->
    <main class="ad-main">
        <!-- Topbar -->
        <header class="ad-topbar">
            <div class="ad-topbar-left">
                <button class="ad-menu-toggle" id="menuToggle">
                    <i data-lucide="menu"></i>
                </button>
                <nav class="ad-breadcrumb">
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                    <span class="ad-breadcrumb-sep">/</span>
                    <span class="ad-breadcrumb-current">{{ $currentPageTitle }}</span>
                </nav>
            </div>
            <div class="ad-topbar-right">
                @php
                    $pendingTicketRequests = \App\Models\TicketRequest::where('status', 'pending')->count();
                    $pendingAgencyRequests = $adminUser
                        ? $adminUser->receivedAgencyRequests()->pending()->with('requester')->count()
                        : 0;
                    $todayTickets = \App\Models\Ticket::whereDate('tour_date', today())->where('is_active', true)->count();
                    $tomorrowTickets = \App\Models\Ticket::whereDate('tour_date', today()->addDay())->where('is_active', true)->count();
                    $primaryPendingRoute = $pendingTicketRequests > 0
                        ? route('admin.ticket-requests.index')
                        : route('admin.agencies.network');
                    $totalNotifications = $pendingTicketRequests
                        + $pendingAgencyRequests
                        + ($todayTickets > 0 ? 1 : 0)
                        + ($tomorrowTickets > 0 ? 1 : 0);
                @endphp
                
                <!-- Bildirimler -->
                <div class="ad-notifications-wrapper">
                    <button class="ad-topbar-btn ad-notifications-btn" id="notificationsToggle">
                        <i data-lucide="bell"></i>
                        @if($totalNotifications > 0)
                            <span class="ad-notification-badge">{{ $totalNotifications }}</span>
                        @endif
                    </button>
                    
                    <div class="ad-notifications-dropdown" id="notificationsDropdown">
                        <div class="ad-notifications-header">
                            <span class="ad-notifications-title">{{ __('Bildirimler') }}</span>
                            @if($totalNotifications > 0)
                                <span class="ad-notifications-count">{{ __(':count yeni', ['count' => $totalNotifications]) }}</span>
                            @endif
                        </div>
                        <div class="ad-notifications-body">
                            <div id="adInfoNotifications"></div>
                            @if($pendingTicketRequests > 0)
                                <a href="{{ route('admin.ticket-requests.index') }}" class="ad-notification-item ad-notification-warning">
                                    <div class="ad-notification-icon">
                                        <i data-lucide="inbox"></i>
                                    </div>
                                    <div class="ad-notification-content">
                                        <div class="ad-notification-text">{{ __(':count bekleyen bilet talebi', ['count' => $pendingTicketRequests]) }}</div>
                                        <div class="ad-notification-time">{{ __('Onay bekliyor') }}</div>
                                    </div>
                                </a>
                            @endif

                            @if($pendingAgencyRequests > 0)
                                <a href="{{ route('admin.agencies.network') }}" class="ad-notification-item ad-notification-warning">
                                    <div class="ad-notification-icon">
                                        <i data-lucide="link"></i>
                                    </div>
                                    <div class="ad-notification-content">
                                        <div class="ad-notification-text">{{ __(':count bekleyen acenta bağlantı isteği', ['count' => $pendingAgencyRequests]) }}</div>
                                        <div class="ad-notification-time">{{ __('Onay bekliyor') }}</div>
                                    </div>
                                </a>
                            @endif
                            
                            @if($todayTickets > 0)
                                <a href="{{ route('admin.tickets.index') }}?filter_date={{ today()->format('Y-m-d') }}" class="ad-notification-item ad-notification-info">
                                    <div class="ad-notification-icon">
                                        <i data-lucide="calendar-check"></i>
                                    </div>
                                    <div class="ad-notification-content">
                                        <div class="ad-notification-text">{{ __('Bugün :count aktif bilet', ['count' => $todayTickets]) }}</div>
                                        <div class="ad-notification-time">{{ today()->format('d.m.Y') }}</div>
                                    </div>
                                </a>
                            @endif
                            
                            @if($tomorrowTickets > 0)
                                <a href="{{ route('admin.tickets.index') }}?filter_date={{ today()->addDay()->format('Y-m-d') }}" class="ad-notification-item ad-notification-success">
                                    <div class="ad-notification-icon">
                                        <i data-lucide="calendar-clock"></i>
                                    </div>
                                    <div class="ad-notification-content">
                                        <div class="ad-notification-text">{{ __('Yarın :count aktif bilet', ['count' => $tomorrowTickets]) }}</div>
                                        <div class="ad-notification-time">{{ today()->addDay()->format('d.m.Y') }}</div>
                                    </div>
                                </a>
                            @endif
                            
                            @if($totalNotifications == 0)
                                <div class="ad-notification-empty">
                                    <i data-lucide="check-circle"></i>
                                    <span>{{ __('Yeni bildirim yok') }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="ad-notifications-footer">
                            <a href="{{ route('admin.ticket-requests.index') }}">{{ __('Tüm talepleri gör') }}</a>
                        </div>
                    </div>
                </div>
                
                @if($pendingTicketRequests > 0 || $pendingAgencyRequests > 0)
                <a href="{{ $primaryPendingRoute }}" class="ad-btn ad-btn-sm ad-btn-warning">
                    <i data-lucide="inbox"></i>
                    {{ __(':count Bekleyen', ['count' => $pendingTicketRequests + $pendingAgencyRequests]) }}
                </a>
                @endif
            </div>
        </header>

        <!-- Content -->
        <div class="ad-content">
            @if(session('success'))
                <div class="ad-alert ad-alert-success">
                    <i data-lucide="check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button class="ad-alert-close" onclick="this.parentElement.remove()">
                        <i data-lucide="x" style="width:16px;height:16px"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="ad-alert ad-alert-danger">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button class="ad-alert-close" onclick="this.parentElement.remove()">
                        <i data-lucide="x" style="width:16px;height:16px"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="ad-alert ad-alert-danger">
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
        <footer class="ad-footer">
            <span>&copy; {{ date('Y') }} Admin Panel. {{ __('Tüm hakları saklıdır.') }}</span>
            <span>v1.2.6</span>
        </footer>
    </main>

    <!-- User Dropdown -->
    @if($adminUser)
    <div class="ad-dropdown" id="userDropdown">
        <div class="ad-dropdown-header">
            <div class="ad-dropdown-name">{{ $adminUser->name }}</div>
            <div class="ad-dropdown-email">{{ $adminUser->email }}</div>
        </div>
        <div class="ad-dropdown-body">
            <!-- Dark Mode Toggle -->
            <div class="ad-dropdown-link dark-mode-toggle" id="darkModeToggle" style="cursor:pointer;">
                <i data-lucide="moon" class="dark-mode-icon-moon"></i>
                <i data-lucide="sun" class="dark-mode-icon-sun" style="display:none;"></i>
                <span class="dark-mode-text">{{ __('Karanlık Tema') }}</span>
                <div class="dark-mode-switch">
                    <div class="dark-mode-switch-thumb"></div>
                </div>
            </div>
            <!-- Language Switch -->
            <div class="ad-dropdown-link lang-switch-row {{ (auth()->user()->locale ?? 'tr') === 'tr' ? 'lang-is-tr' : '' }}" id="languageToggleRow">
                <span class="lang-symbol lang-symbol-en" title="English">🇬🇧</span>
                <div class="lang-switch" id="languageToggle" style="cursor:pointer;">
                    <div class="lang-switch-thumb"></div>
                </div>
                <span class="lang-symbol lang-symbol-tr" title="Türkçe">🇹🇷</span>
            </div>
            <div class="ad-dropdown-link" id="addressSettingTrigger" style="cursor:pointer;">
                <i data-lucide="map-pin"></i>
                <span>{{ __('Varsayılan Adres') }}</span>
            </div>
            <div style="border-top: 1px solid var(--ad-border); margin: 8px 0;"></div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <input type="hidden" name="_session_partition" value="{{ $sessionPartition }}">
                <button type="submit" class="ad-dropdown-link danger" style="width:100%;border:none;background:none;cursor:pointer">
                    <i data-lucide="log-out"></i>
                    <span>{{ __('Çıkış Yap') }}</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Language Switch Loading Overlay -->
    <div class="lang-loading-overlay" id="langLoadingOverlay">
        <div class="lang-loading-text" id="langLoadingText"></div>
    </div>

    <!-- Address Setting Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;">
                <div class="modal-header" style="background:var(--ad-primary);color:#fff;border:none;padding:16px 20px;">
                    <h5 class="modal-title" style="font-size:15px;font-weight:600;"><i data-lucide="map-pin" style="width:16px;height:16px;margin-right:6px;vertical-align:middle;"></i> {{ __('Varsayılan Adres') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Kapat') }}"></button>
                </div>
                <div class="modal-body" style="padding:16px 20px;">
                    <div class="mb-2">
                        <input type="text" class="form-control" id="addressSearchInput" placeholder="{{ __('Adres ara...') }}" style="border-radius:8px;">
                    </div>
                    <div id="address-modal-map" style="height:340px;width:100%;border-radius:8px;border:1px solid #dee2e6;"></div>
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted" id="addressDisplayText">{{ __('Haritaya tıklayarak veya arama yaparak adres seçin.') }}</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addressLocateMe"><i data-lucide="locate" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"></i> {{ __('Konumumu Bul') }}</button>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ad-border);padding:12px 20px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addressClearBtn">{{ __('Temizle') }}</button>
                    <button type="button" class="btn btn-sm btn-primary" id="addressSaveBtn">{{ __('Kaydet') }}</button>
                </div>
            </div>
        </div>
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
            });
        }

        // Notifications dropdown
        const notificationsToggle = document.getElementById('notificationsToggle');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const notificationBadge = notificationsToggle?.querySelector('.ad-notification-badge');
        
        // Bildirim durumunu yönet
        const currentNotificationCount = {{ $totalNotifications ?? 0 }};
        const storedNotificationData = localStorage.getItem('admin_notifications_seen');
        
        if (storedNotificationData && notificationBadge) {
            try {
                const data = JSON.parse(storedNotificationData);
                // Eğer kayıtlı sayı mevcut sayıyla aynıysa, badge'i gizle
                if (data.count === currentNotificationCount && data.seen === true) {
                    notificationBadge.style.display = 'none';
                }
            } catch(e) {}
        }

        if (notificationsToggle && notificationsDropdown) {
            notificationsToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown?.classList.remove('show');
                notificationsDropdown.classList.toggle('show');
                
                // Bildirimler açıldığında badge'i gizle ve localStorage'a kaydet
                if (notificationsDropdown.classList.contains('show') && notificationBadge) {
                    notificationBadge.style.display = 'none';
                    localStorage.setItem('admin_notifications_seen', JSON.stringify({
                        count: currentNotificationCount,
                        seen: true,
                        timestamp: Date.now()
                    }));
                }
            });
        }

        // Close dropdown on outside click
        document.addEventListener('click', (e) => {
            if (!userDropdown?.contains(e.target) && !userCardTrigger?.contains(e.target)) {
                userDropdown?.classList.remove('show');
            }
            if (!notificationsDropdown?.contains(e.target) && !notificationsToggle?.contains(e.target)) {
                notificationsDropdown?.classList.remove('show');
            }
        });

        // Close dropdown on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                userDropdown?.classList.remove('show');
                notificationsDropdown?.classList.remove('show');
                sidebar?.classList.remove('open');
                sidebarOverlay?.classList.remove('show');
            }
        });

        // Session partition handling
        const sessionPartition = '{{ $sessionPartition }}';
        if (sessionPartition) {
            window.__sessionPartition = sessionPartition;
            
            document.querySelectorAll('form').forEach(form => {
                if (!form.querySelector('input[name="_session_partition"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_session_partition';
                    input.value = sessionPartition;
                    form.appendChild(input);
                }
            });

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
            const csrfPartition = window.__sessionPartition || 'admin';

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

        // Address Setting Modal
        (function(){
            var trigger = document.getElementById('addressSettingTrigger');
            var modalEl = document.getElementById('addressModal');
            if (!trigger || !modalEl) return;
            var bsModal = new bootstrap.Modal(modalEl);
            trigger.addEventListener('click', function(){ bsModal.show(); });

            var MAPBOX_TOKEN = {!! json_encode(config('services.mapbox.access_token'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
            var mapObj = null, marker = null;
            var searchInput = document.getElementById('addressSearchInput');
            var displayText = document.getElementById('addressDisplayText');
            var saveBtn = document.getElementById('addressSaveBtn');
            var clearBtn = document.getElementById('addressClearBtn');
            var locateBtn = document.getElementById('addressLocateMe');
            var pendingAddr = null;

            function loadSaved(){ try { return JSON.parse(localStorage.getItem('admin_default_address')); } catch(e){ return null; } }

            modalEl.addEventListener('shown.bs.modal', function(){
                if (mapObj) { mapObj.resize(); return; }
                if (!MAPBOX_TOKEN) return;
                mapboxgl.accessToken = MAPBOX_TOKEN;
                var saved = loadSaved();
                var center = saved ? [saved.lng, saved.lat] : [28.27, 36.85];
                var zoom = saved ? 14 : 10;
                mapObj = new mapboxgl.Map({ container:'address-modal-map', style:'mapbox://styles/mapbox/streets-v12', center:center, zoom:zoom, language:'tr' });
                mapObj.addControl(new mapboxgl.NavigationControl(),'top-right');
                if (saved) {
                    marker = new mapboxgl.Marker({draggable:true}).setLngLat(center).addTo(mapObj);
                    marker.on('dragend', function(){ var ll=marker.getLngLat(); pendingAddr={lat:ll.lat,lng:ll.lng,address:''}; reverseGeo(ll.lng,ll.lat); });
                    pendingAddr = saved;
                    if (saved.address) displayText.textContent = saved.address;
                }
                mapObj.on('click', function(e){ placeMarker(e.lngLat.lat,e.lngLat.lng); reverseGeo(e.lngLat.lng,e.lngLat.lat); });
                lucide.createIcons();
            });

            function placeMarker(lat,lng){
                if(marker){marker.setLngLat([lng,lat]);}else{marker=new mapboxgl.Marker({draggable:true}).setLngLat([lng,lat]).addTo(mapObj);marker.on('dragend',function(){var ll=marker.getLngLat();pendingAddr={lat:ll.lat,lng:ll.lng,address:''};reverseGeo(ll.lng,ll.lat);});}
                pendingAddr={lat:lat,lng:lng,address:''};
            }
            function reverseGeo(lng,lat){
                fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+lng+','+lat+'.json?access_token='+MAPBOX_TOKEN+'&language=tr&limit=1').then(function(r){return r.json();}).then(function(d){
                    if(d.features&&d.features[0]){var name=d.features[0].place_name;if(pendingAddr)pendingAddr.address=name;if(displayText)displayText.textContent=name;}
                }).catch(function(){});
            }
            function forwardGeo(q){
                fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+encodeURIComponent(q)+'.json?access_token='+MAPBOX_TOKEN+'&language=tr&limit=1').then(function(r){return r.json();}).then(function(d){
                    if(d.features&&d.features[0]){var c=d.features[0].center;mapObj.flyTo({center:c,zoom:15});placeMarker(c[1],c[0]);pendingAddr.address=d.features[0].place_name;if(displayText)displayText.textContent=d.features[0].place_name;}
                }).catch(function(){});
            }

            if(searchInput){var st=null;searchInput.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();clearTimeout(st);var q=searchInput.value.trim();if(q)forwardGeo(q);}});searchInput.addEventListener('input',function(){clearTimeout(st);st=setTimeout(function(){var q=searchInput.value.trim();if(q&&q.length>3)forwardGeo(q);},800);});}
            if(locateBtn){locateBtn.addEventListener('click',function(){if(!navigator.geolocation)return;navigator.geolocation.getCurrentPosition(function(pos){var lat=pos.coords.latitude,lng=pos.coords.longitude;if(mapObj)mapObj.flyTo({center:[lng,lat],zoom:15});placeMarker(lat,lng);reverseGeo(lng,lat);});});}
            if(saveBtn){saveBtn.addEventListener('click',function(){if(pendingAddr){localStorage.setItem('admin_default_address',JSON.stringify(pendingAddr));bsModal.hide();window.dispatchEvent(new CustomEvent('admin-address-changed',{detail:pendingAddr}));}else{bsModal.hide();}});}
            if(clearBtn){clearBtn.addEventListener('click',function(){localStorage.removeItem('admin_default_address');if(marker){marker.remove();marker=null;}pendingAddr=null;if(displayText)displayText.textContent='Haritaya tıklayarak veya arama yaparak adres seçin.';window.dispatchEvent(new CustomEvent('admin-address-changed',{detail:null}));});}
        })();
    </script>
    <script>
        (function () {
            var interval = 10 * 60 * 1000;
            var STORAGE_KEY = 'admin_info_notifications';
            var MAX_ITEMS = 5;
            var toneIcon = { success: 'sparkles', quote: 'quote', info: 'info' };
            var toneClass = { success: 'ad-notification-success', quote: 'ad-notification-quote', info: 'ad-notification-info' };

            function loadQueue() {
                try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch (e) { return []; }
            }
            function saveQueue(queue) {
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(queue)); } catch (e) {}
            }
            function updateBadge(queue) {
                var toggle = document.getElementById('notificationsToggle');
                if (!toggle) return;
                var unseen = queue.filter(function (n) { return !n.seen; }).length;
                if (unseen === 0) return;
                var badge = toggle.querySelector('.ad-notification-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'ad-notification-badge';
                    toggle.appendChild(badge);
                }
                badge.textContent = {{ $totalNotifications ?? 0 }} + unseen;
                badge.style.display = '';
            }
            function renderInfoNotifications() {
                var container = document.getElementById('adInfoNotifications');
                if (!container) return;
                var body = container.closest('.ad-notifications-body');
                var emptyEl = body ? body.querySelector('.ad-notification-empty') : null;
                var queue = loadQueue();
                container.innerHTML = queue.map(function (n) {
                    var cls = toneClass[n.tone] || 'ad-notification-info';
                    var icon = toneIcon[n.tone] || 'info';
                    return '<div class="ad-notification-item ' + cls + '">' +
                        '<div class="ad-notification-icon"><i data-lucide="' + icon + '"></i></div>' +
                        '<div class="ad-notification-content">' +
                            '<div class="ad-notification-text">' + n.title + ': ' + n.message + '</div>' +
                            '<div class="ad-notification-time">Az önce</div>' +
                        '</div>' +
                    '</div>';
                }).join('');
                if (emptyEl) emptyEl.style.display = queue.length > 0 ? 'none' : '';
                updateBadge(queue);
                if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons();
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
            var bellToggle = document.getElementById('notificationsToggle');
            if (bellToggle) bellToggle.addEventListener('click', markInfoNotificationsSeen);
            setInterval(pollPanelInfo, interval);
        }());
    </script>
    @stack('js')
    @yield('js')
</body>
</html>

