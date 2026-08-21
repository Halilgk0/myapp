@extends('layouts.driver')

@section('title', 'Ana Sayfa')

@push('css')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
<style>
    .dr-map-container {
        background: var(--dr-card);
        border: 1px solid var(--dr-border);
        border-radius: var(--dr-radius);
        box-shadow: var(--dr-shadow);
        min-height: min(68vh, 600px);
        overflow: hidden;
        position: relative;
    }

    #driver-map {
        width: 100%;
        height: min(68vh, 600px);
    }

    .dr-map-no-token {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dr-text-muted);
        text-align: center;
        padding: 2rem;
        min-height: min(68vh, 600px);
    }

    .dr-map-no-token-inner { max-width: 340px; }

    .dr-today-panel {
        background: var(--dr-card);
        border: 1px solid var(--dr-border);
        border-radius: var(--dr-radius);
        box-shadow: var(--dr-shadow);
        height: 100%;
        min-height: min(68vh, 600px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .dr-today-head {
        background: var(--dr-sidebar);
        color: #fff;
        padding: 16px 18px;
        flex-shrink: 0;
    }

    .dr-today-head h2 {
        font-size: 14px;
        margin: 0 0 4px;
    }

    .dr-today-body {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
    }

    .dr-today-body::-webkit-scrollbar { width: 5px; }
    .dr-today-body::-webkit-scrollbar-track { background: transparent; }
    .dr-today-body::-webkit-scrollbar-thumb { background: var(--dr-border); border-radius: 4px; }

    .dr-ticket-card {
        border: 1px solid var(--dr-border);
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 10px;
        background: var(--dr-bg);
        transition: border-color 0.15s, box-shadow 0.15s;
        cursor: pointer;
    }

    .dr-ticket-card:last-child { margin-bottom: 0; }

    .dr-ticket-card:hover,
    .dr-ticket-card.dr-ticket-active {
        border-color: var(--dr-accent);
        box-shadow: 0 4px 12px rgba(59,130,246,0.12);
    }

    .dr-ticket-meta {
        font-size: 12px;
        color: var(--dr-text-muted);
        line-height: 1.55;
    }

    .dr-ticket-rest {
        font-weight: 700;
        color: var(--dr-success);
    }

    .dr-empty-tickets {
        text-align: center;
        padding: 48px 20px;
        color: var(--dr-text-muted);
    }

    .dr-warn-box {
        background: rgba(245,158,11,0.08);
        border: 1px solid rgba(245,158,11,0.2);
        border-radius: var(--dr-radius-sm);
        padding: 14px 16px;
        color: #92400e;
        font-size: 13px;
    }

    .dr-marker {
        width: 32px;
        height: 32px;
        border-radius: 50% 50% 50% 0;
        background: var(--dr-accent, #3b82f6);
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        cursor: pointer;
        border: 2px solid #fff;
    }

    .dr-marker span {
        transform: rotate(45deg);
        color: #fff;
        font-weight: 700;
        font-size: 12px;
    }

    .mapboxgl-popup-content {
        border-radius: 10px !important;
        padding: 14px 16px !important;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        min-width: 200px;
    }

    .mapboxgl-popup-content strong {
        font-size: 14px;
        display: block;
        margin-bottom: 6px;
    }

    .mapboxgl-popup-close-button {
        font-size: 18px;
        right: 6px;
        top: 4px;
    }

    @media (max-width: 991.98px) {
        .dr-today-panel { min-height: auto; margin-top: 16px; }
        .dr-map-container, #driver-map, .dr-map-no-token { min-height: 38vh; }
        #driver-map { height: 38vh; }
    }

    /* Rota başlat barı */
    .dr-route-bar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; padding: 10px 14px; margin-bottom: 12px;
        background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff;
        border-radius: var(--dr-radius-sm, 8px); flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(13,110,253,.25);
    }
    .dr-route-bar.no-plan { background: #fff; color: var(--dr-text-muted); border: 1px dashed #ced4da; box-shadow: none; }
    .dr-route-bar .rb-info { font-size: 13px; line-height: 1.4; }
    .dr-route-bar .rb-info strong { font-size: 14px; }
    .dr-route-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .dr-btn-go { background: #198754; color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(25,135,84,.3); }
    .dr-btn-go:hover { background: #157347; color: #fff; }
    .dr-btn-go.is-active { background: #dc3545; box-shadow: 0 2px 6px rgba(220,53,69,.3); }
    .dr-btn-go.is-active:hover { background: #b02a37; }
    .dr-btn-nav { background: rgba(255,255,255,.18); color: #fff; border: 1px solid rgba(255,255,255,.35); padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .dr-btn-nav:hover { background: rgba(255,255,255,.28); color: #fff; text-decoration: none; }

    .dr-order-pill { background: #0d6efd; color: #fff; font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 999px; margin-right: 6px; }
    .dr-order-pill.is-start { background: #198754; }
    .dr-order-pill.is-unset { background: #adb5bd; }

    /* Sıralı pin */
    .dr-marker.is-start { background: #198754; }
    .dr-marker.is-current { background: #dc3545; box-shadow: 0 0 0 4px rgba(220,53,69,.25), 0 2px 8px rgba(0,0,0,.3); }

    /* Canlı takip butonu (haritanın sağ alt köşesinde) */
    .dr-live-btn {
        position: absolute; right: 10px; bottom: 10px; z-index: 5;
        background: #198754; color: #fff; border: none;
        padding: 7px 12px; font-size: 12px; font-weight: 600;
        border-radius: 20px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        transition: background .15s, transform .12s;
    }
    .dr-live-btn:hover { background: #157347; transform: translateY(-1px); }
    .dr-live-btn.is-live {
        background: #dc3545; padding-left: 10px;
    }
    .dr-live-btn.is-live:hover { background: #b02a37; }
    .dr-live-btn .pulse {
        display: inline-block; width: 8px; height: 8px; border-radius: 50%;
        background: #fff; animation: dr-pulse 1.2s infinite;
    }
    @keyframes dr-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.7); }
    }
    .dr-live-status {
        position: absolute; right: 10px; bottom: 50px; z-index: 5;
        background: rgba(0,0,0,0.7); color: #fff; padding: 4px 10px;
        border-radius: 12px; font-size: 11px; max-width: 220px;
        display: none;
    }
    .dr-live-status.show { display: block; }

    /* Devre dışı durum (gelecek/geçmiş günler) */
    .dr-live-btn.is-disabled {
        background: #6c757d; cursor: not-allowed; opacity: 0.7;
    }
    .dr-live-btn.is-disabled:hover { background: #6c757d; transform: none; }

    /* Konum izni / GPS bekleme overlay'i */
    .dr-permission-overlay {
        position: absolute; inset: 0; z-index: 10;
        background: rgba(15, 23, 42, 0.65);
        display: none; align-items: center; justify-content: center;
        padding: 20px;
    }
    .dr-permission-overlay.show { display: flex; }
    .dr-permission-card {
        background: #fff; border-radius: 12px; padding: 22px 24px;
        max-width: 380px; width: 100%; text-align: center;
        box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    }
    html.dark-mode .dr-permission-card { background: #1e293b; color: #e2e8f0; }
    .dr-permission-card h4 { margin: 0 0 8px; font-size: 16px; font-weight: 700; }
    .dr-permission-card p { margin: 0 0 14px; font-size: 13px; color: #64748b; line-height: 1.5; }
    html.dark-mode .dr-permission-card p { color: #94a3b8; }
    .dr-permission-steps { text-align: left; font-size: 13px; margin: 12px 0; }
    .dr-permission-step { display: flex; align-items: center; gap: 10px; padding: 6px 0; color: #475569; }
    html.dark-mode .dr-permission-step { color: #cbd5e1; }
    .dr-permission-step .step-num {
        width: 22px; height: 22px; border-radius: 50%;
        background: #e2e8f0; color: #64748b;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .dr-permission-step.is-active .step-num { background: #0d6efd; color: #fff; animation: dr-pulse 1s infinite; }
    .dr-permission-step.is-done .step-num { background: #198754; color: #fff; }
    .dr-permission-step.is-done .step-num::before { content: '\f00c'; font-family: 'Font Awesome 5 Free'; font-weight: 900; }
    .dr-permission-step.is-done .step-num span { display: none; }
    .dr-permission-actions { display: flex; gap: 8px; margin-top: 14px; justify-content: center; }
    .dr-permission-actions button {
        padding: 7px 16px; border-radius: 6px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .dr-permission-cancel { background: #e2e8f0; color: #475569; }
    .dr-permission-cancel:hover { background: #cbd5e1; }
    html.dark-mode .dr-permission-cancel { background: #334155; color: #e2e8f0; }

    /* Sefer başladı toast'ı */
    .dr-trip-toast {
        position: absolute; top: 12px; left: 50%; transform: translateX(-50%);
        background: #198754; color: #fff; padding: 10px 18px;
        border-radius: 24px; font-size: 13px; font-weight: 600;
        box-shadow: 0 4px 16px rgba(25,135,84,0.4); z-index: 8;
        display: none; align-items: center; gap: 8px;
        animation: dr-toast-in .3s ease;
    }
    .dr-trip-toast.show { display: inline-flex; }
    @keyframes dr-toast-in {
        from { opacity: 0; transform: translate(-50%, -10px); }
        to   { opacity: 1; transform: translate(-50%, 0); }
    }

    /* Gün sekmeleri */
    .dr-day-tabs {
        display: flex; gap: 4px; flex-wrap: wrap;
        padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.12);
    }
    .dr-day-tab {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18);
        color: #fff; font-size: 11px; font-weight: 600;
        padding: 5px 10px; border-radius: 14px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all .15s;
    }
    .dr-day-tab:hover { background: rgba(255,255,255,0.2); }
    .dr-day-tab.active { background: #fff; color: var(--dr-sidebar, #1f2937); }
    .dr-day-tab.has-route::before {
        content: ""; display: inline-block; width: 6px; height: 6px;
        border-radius: 50%; background: #22d3ee;
    }
    .dr-day-tab.active.has-route::before { background: #0d6efd; }
    .dr-day-tab-count {
        font-size: 10px; background: rgba(255,255,255,0.15); padding: 1px 5px; border-radius: 8px;
    }
    .dr-day-tab.active .dr-day-tab-count { background: rgba(13,110,253,0.15); color: #0d6efd; }

    /* Salt-okunur uyarı (gelecek günler için) */
    .dr-readonly-banner {
        background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.3);
        color: #92400e; font-size: 12px;
        padding: 8px 12px; border-radius: 8px; margin-bottom: 10px;
        display: flex; align-items: center; gap: 6px;
    }
    html.dark-mode .dr-readonly-banner {
        background: rgba(245,158,11,0.08); color: #fbbf24;
    }

    /* Karanlık modda Mapbox popup */
    html.dark-mode .mapboxgl-popup-content {
        background: #1e293b !important; color: #e2e8f0 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,.5) !important;
    }
    html.dark-mode .mapboxgl-popup-content strong { color: #f1f5f9 !important; }
    html.dark-mode .mapboxgl-popup-anchor-top .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-top-left .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-top-right .mapboxgl-popup-tip { border-bottom-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-bottom-left .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-bottom-right .mapboxgl-popup-tip { border-top-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-anchor-left .mapboxgl-popup-tip { border-right-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-anchor-right .mapboxgl-popup-tip { border-left-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-close-button { color: #cbd5e1 !important; }
</style>
@endpush

@section('content')
    @php
        $todayTickets = collect($ticketsByDay[$today]['tickets'] ?? []);
        $hasRoutePlan = $todayTickets->whereNotNull('route_order')->isNotEmpty();
        $startTicket = $todayTickets->firstWhere('is_route_start', true);
        $totalStops = $todayTickets->whereNotNull('route_order')->count();
        $allTickets = collect($ticketsByDay)->flatMap(fn($d) => $d['tickets']);
    @endphp

    {{-- Sefere Çık barı (yalnızca bugünün rotası için) --}}
    @if(!$todayTickets->isEmpty())
        <div class="dr-route-bar @if(!$hasRoutePlan) no-plan @endif" id="dr-route-bar" data-active-day="{{ $today }}">
            <div class="rb-info">
                @if($hasRoutePlan)
                    <strong><i class="fas fa-route"></i> Bugünün rotası hazır</strong><br>
                    <span style="opacity:.9;font-size:12px">{{ $totalStops }} durak — başlangıç:
                        {{ $startTicket ? $startTicket->customer_name : '—' }}</span>
                @else
                    <strong><i class="fas fa-info-circle"></i> Bugün için rota planı yok</strong><br>
                    <span style="font-size:12px">Yöneticinizden rota oluşturmasını isteyin.</span>
                @endif
            </div>
            @if($hasRoutePlan)
                <div class="dr-route-actions">
                    <button type="button" class="dr-btn-go" id="dr-go-btn">
                        <i class="fas fa-play"></i> <span>Sefere Çık</span>
                    </button>
                    <a href="#" class="dr-btn-nav" id="dr-nav-link" target="_blank" rel="noopener">
                        <i class="fas fa-directions"></i> Navigasyon Aç
                    </a>
                </div>
            @endif
        </div>
    @endif

    <div class="row g-3 align-items-stretch">
        {{-- Harita --}}
        <div class="col-lg-8">
            <div class="dr-map-container" style="position:relative;">
                @if($mapboxToken)
                    <div id="driver-map"></div>
                    @if($vehicle)
                        <div class="dr-live-status" id="dr-live-status"></div>
                        <div class="dr-trip-toast" id="dr-trip-toast">
                            <i class="fas fa-check-circle"></i> <span>Sefer başladı</span>
                        </div>
                        <button type="button" class="dr-live-btn" id="dr-live-btn" title="Canlı konum takibini başlat">
                            <span class="dr-live-icon"><i class="fas fa-play"></i></span>
                            <span class="dr-live-label">Sefere Başla</span>
                        </button>
                        <div class="dr-permission-overlay" id="dr-permission-overlay">
                            <div class="dr-permission-card">
                                <h4><i class="fas fa-map-marker-alt text-primary"></i> Konum Erişimi</h4>
                                <p id="dr-permission-msg">Sefere başlamak için cihazınızın konum bilgilerine ihtiyacımız var.</p>
                                <div class="dr-permission-steps">
                                    <div class="dr-permission-step" id="dr-step-permit">
                                        <span class="step-num"><span>1</span></span>
                                        <span>Tarayıcı konum izni isteyecek — <strong>İzin Ver</strong>'e basın</span>
                                    </div>
                                    <div class="dr-permission-step" id="dr-step-gps">
                                        <span class="step-num"><span>2</span></span>
                                        <span>Cihazınızın <strong>GPS / Konum Servisi</strong>'ni açık tutun</span>
                                    </div>
                                    <div class="dr-permission-step" id="dr-step-locate">
                                        <span class="step-num"><span>3</span></span>
                                        <span>Konumunuz alındığında sefer otomatik başlayacak</span>
                                    </div>
                                </div>
                                <div class="dr-permission-actions">
                                    <button type="button" class="dr-permission-cancel" id="dr-permission-cancel">İptal</button>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="dr-map-no-token">
                        <div class="dr-map-no-token-inner">
                            <i data-lucide="map-pin" style="width:48px;height:48px;opacity:0.3;margin-bottom:12px"></i>
                            <p class="fw-semibold mb-1" style="font-size:15px;color:var(--dr-text)">Harita</p>
                            <p class="mb-0 small">Harita yüklenemedi. Mapbox API anahtarı tanımlı değil.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Atanan biletler (gün sekmeli) --}}
        <div class="col-lg-4">
            <div class="dr-today-panel">
                <div class="dr-today-head">
                    <h2>
                        <i data-lucide="calendar-days" style="width:16px;height:16px;display:inline-block;vertical-align:-2px;margin-right:6px"></i>
                        Atanan Biletler
                    </h2>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1" style="font-size:12px;opacity:0.85">
                        @if($vehicle)
                            <span class="badge bg-light text-dark" style="font-size:11px">{{ $vehicle->plate_number }}</span>
                        @endif
                        <span style="font-size:11px;">{{ $allTickets->count() }} bilet (bugün ve sonrası)</span>
                    </div>
                    @if(count($ticketsByDay) > 0)
                        <div class="dr-day-tabs mt-2" id="dr-day-tabs">
                            @foreach($ticketsByDay as $day)
                                @php
                                    $isToday = $day['date'] === $today;
                                    $dayCarbon = \Carbon\Carbon::parse($day['date']);
                                    $label = $isToday ? 'Bugün' : ($dayCarbon->isTomorrow() ? 'Yarın' : $dayCarbon->locale('tr')->translatedFormat('d M'));
                                @endphp
                                <button type="button" class="dr-day-tab @if($isToday) active @endif @if($day['has_route']) has-route @endif"
                                        data-date="{{ $day['date'] }}"
                                        data-is-today="{{ $isToday ? '1' : '0' }}">
                                    {{ $label }}
                                    <span class="dr-day-tab-count">{{ count($day['tickets']) }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="dr-today-body">
                    @if(!$vehicle)
                        <div class="dr-warn-box">
                            <i data-lucide="alert-triangle" style="width:16px;height:16px;display:inline-block;vertical-align:-2px;margin-right:4px"></i>
                            Size atanmış bir araç yok. Bilet atamaları için yöneticinizle iletişime geçin.
                        </div>
                    @elseif($allTickets->isEmpty())
                        <div class="dr-empty-tickets">
                            <i data-lucide="ticket" style="width:40px;height:40px;opacity:0.25;margin-bottom:10px"></i>
                            <p class="mb-0 small">Size atanmış bilet bulunmuyor.</p>
                        </div>
                    @else
                        @foreach($ticketsByDay as $day)
                            @foreach($day['tickets'] as $ticket)
                            <div class="dr-ticket-card"
                                 data-ticket-id="{{ $ticket->id }}"
                                 data-date="{{ $day['date'] }}"
                                 @if($ticket->location)
                                     data-lat="{{ $ticket->location->latitude }}"
                                     data-lng="{{ $ticket->location->longitude }}"
                                 @endif
                                 @if(!is_null($ticket->route_order))
                                     data-order="{{ $ticket->route_order }}"
                                 @endif
                                 @if($ticket->is_route_start)
                                     data-is-start="1"
                                 @endif>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="small fw-semibold text-primary text-truncate" title="{{ $ticket->tracking_no }}">
                                        @if($ticket->is_route_start)
                                            <span class="dr-order-pill is-start" title="Başlangıç noktası">S</span>
                                        @elseif(!is_null($ticket->route_order))
                                            <span class="dr-order-pill">{{ $ticket->route_order }}</span>
                                        @endif
                                        {{ $ticket->tracking_no }}
                                    </span>
                                    @if($ticket->pickup_time)
                                        <span class="badge bg-secondary fw-normal" style="font-size:11px">
                                            <i class="fas fa-clock me-1" style="font-size:10px"></i>{{ $ticket->pickup_time->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="fw-semibold mb-1" style="font-size:14px">{{ $ticket->customer_name }}</div>
                                <div class="dr-ticket-meta mb-2">
                                    <i data-lucide="users" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;margin-right:3px"></i>
                                    {{ $ticket->passenger_count_text }}
                                    @if($ticket->passenger_breakdown && $ticket->passenger_breakdown !== 'Yolcu Bilgisi Yok')
                                        <span class="d-block mt-1">{{ $ticket->passenger_breakdown }}</span>
                                    @endif
                                </div>
                                @if($ticket->passport_numbers)
                                    <div class="small mb-2 text-break" style="max-height:4rem;overflow-y:auto">
                                        <span class="text-muted">İsim:</span>
                                        {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $ticket->passport_numbers)), 120) }}
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center small border-top pt-2 mt-1">
                                    <span class="text-muted">Alınacak rest</span>
                                    <span class="dr-ticket-rest">
                                        {{ number_format((float) $ticket->rest, 2, ',', '.') }} {{ $ticket->currency }}
                                    </span>
                                </div>
                                <div class="dr-ticket-meta mt-2 mb-0">
                                    <div>
                                        <i data-lucide="map-pin" style="width:12px;height:12px;display:inline-block;vertical-align:-2px;margin-right:3px"></i>
                                        {{ $ticket->pickup_location }}
                                    </div>
                                    @if($ticket->room_number)
                                        <div class="mt-1">
                                            <i data-lucide="door-open" style="width:12px;height:12px;display:inline-block;vertical-align:-2px;margin-right:3px"></i>
                                            Oda: {{ $ticket->room_number }}
                                        </div>
                                    @endif
                                    @if($ticket->tour_name)
                                        <div class="mt-1">
                                            <i data-lucide="route" style="width:12px;height:12px;display:inline-block;vertical-align:-2px;margin-right:3px"></i>
                                            {{ $ticket->tour_name }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@if($mapboxToken)
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    mapboxgl.accessToken = @json($mapboxToken);

    const defaultCenter = [28.2722, 36.8500]; // Marmaris
    const defaultZoom = 12;

    const map = new mapboxgl.Map({
        container: 'driver-map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: defaultCenter,
        zoom: defaultZoom,
        language: 'tr',
    });

    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
    map.addControl(new mapboxgl.GeolocateControl({
        positionOptions: { enableHighAccuracy: true },
        trackUserLocation: true,
        showUserHeading: true,
    }), 'top-right');

    const allCards = document.querySelectorAll('.dr-ticket-card');
    const todayStr = @json($today);
    let activeDay = todayStr;
    let dayMarkers = {}; // date → [{marker, popup, card, lngLat, isStart, order, el}]

    function buildMarkersForDay(dayStr) {
        // Eski marker'ları temizle
        if (dayMarkers[dayStr]) {
            dayMarkers[dayStr].forEach(m => m.marker.remove());
        }
        dayMarkers[dayStr] = [];
        let bounds = new mapboxgl.LngLatBounds();
        let hasCoords = false;

        allCards.forEach((card, index) => {
            if (card.dataset.date !== dayStr) return;
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            hasCoords = true;
            const lngLat = [lng, lat];
            bounds.extend(lngLat);

            const isStart = card.dataset.isStart === '1';
            const orderRaw = card.dataset.order;
            const order = orderRaw ? parseInt(orderRaw, 10) : NaN;

            const el = document.createElement('div');
            el.className = 'dr-marker' + (isStart ? ' is-start' : '');
            const labelText = isStart ? 'S' : (isNaN(order) ? (index + 1) : order);
            el.innerHTML = '<span>' + labelText + '</span>';

            const customerName = (card.querySelector('.fw-semibold[style]') || {}).textContent?.trim() || '';
            const pickupLocation = (card.querySelector('.dr-ticket-meta:last-child div') || {}).textContent?.trim() || '';
            const timeEl = card.querySelector('.badge.bg-secondary');
            const time = timeEl ? timeEl.textContent.trim() : '';
            const restEl = card.querySelector('.dr-ticket-rest');
            const rest = restEl ? restEl.textContent.trim() : '';

            const popup = new mapboxgl.Popup({ offset: 25, maxWidth: '260px' })
                .setHTML(
                    '<strong>' + escapeHtml(customerName) + '</strong>' +
                    (time ? '<div>🕐 ' + escapeHtml(time) + '</div>' : '') +
                    (pickupLocation ? '<div>📍 ' + escapeHtml(pickupLocation) + '</div>' : '') +
                    (rest ? '<div style="margin-top:6px;font-weight:600;color:#16a34a">Rest: ' + escapeHtml(rest) + '</div>' : '')
                );

            const marker = new mapboxgl.Marker(el)
                .setLngLat(lngLat).setPopup(popup).addTo(map);

            const entry = { marker, popup, card, lngLat, isStart, order, el };
            dayMarkers[dayStr].push(entry);

            // Card click handler — bir kez bağlansın
            if (!card.dataset.clickBound) {
                card.dataset.clickBound = '1';
                card.addEventListener('click', () => {
                    document.querySelectorAll('.dr-ticket-card').forEach(c => c.classList.remove('dr-ticket-active'));
                    card.classList.add('dr-ticket-active');
                    map.flyTo({ center: lngLat, zoom: 15, duration: 800 });
                    Object.values(dayMarkers).flat().forEach(m => m.popup.remove());
                    popup.addTo(map);
                });
            }
        });

        if (hasCoords) {
            try {
                if (dayMarkers[dayStr].length === 1) {
                    map.flyTo({ center: dayMarkers[dayStr][0].lngLat, zoom: 15 });
                } else {
                    map.fitBounds(bounds, { padding: 60, maxZoom: 15 });
                }
            } catch (e) {}
        }
    }

    function clearRouteLine() {
        if (map.getLayer('dr-route-line')) map.removeLayer('dr-route-line');
        if (map.getSource('dr-route')) map.removeSource('dr-route');
    }

    function getOrderedStopsForDay(dayStr) {
        const list = (dayMarkers[dayStr] || []).filter(m => m.isStart || !isNaN(m.order));
        list.sort((a, b) => (a.isStart ? 0 : a.order) - (b.isStart ? 0 : b.order));
        return list;
    }

    function drawRouteForDay(dayStr) {
        clearRouteLine();
        const ordered = getOrderedStopsForDay(dayStr);
        if (ordered.length < 2) return;
        const coords = ordered.map(m => m.lngLat[0] + ',' + m.lngLat[1]).join(';');
        const url = 'https://api.mapbox.com/directions/v5/mapbox/driving-traffic/' + coords
                  + '?geometries=geojson&overview=full&access_token=' + encodeURIComponent(mapboxgl.accessToken);
        fetch(url).then(r => r.json()).then(data => {
            if (!data.routes || !data.routes[0]) return;
            // Geçerlilik kontrolü — kullanıcı bu arada başka güne geçtiyse çizme
            if (activeDay !== dayStr) return;
            map.addSource('dr-route', { type: 'geojson', data: { type: 'Feature', geometry: data.routes[0].geometry } });
            map.addLayer({
                id: 'dr-route-line', type: 'line', source: 'dr-route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#0d6efd', 'line-width': 5, 'line-opacity': 0.85 },
            });
        }).catch(() => {});
    }

    function selectDay(dayStr) {
        activeDay = dayStr;

        // Tabs
        document.querySelectorAll('#dr-day-tabs .dr-day-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.date === dayStr);
        });

        // Bilet kartlarını filtrele
        allCards.forEach(c => {
            c.style.display = c.dataset.date === dayStr ? '' : 'none';
        });

        // Eski tüm marker'ları kaldır → o günün marker'larını yeniden çiz
        Object.values(dayMarkers).flat().forEach(m => m.marker.remove());
        dayMarkers = {};
        clearRouteLine();
        if (startedMarker) { startedMarker.remove(); startedMarker = null; }

        if (map.isStyleLoaded()) {
            buildMarkersForDay(dayStr);
            drawRouteForDay(dayStr);
        } else {
            map.on('load', () => { buildMarkersForDay(dayStr); drawRouteForDay(dayStr); });
        }

        // Sefere Çık barı: yalnızca bugün seçili iken aktif
        const bar = document.getElementById('dr-route-bar');
        if (bar) {
            const isToday = dayStr === todayStr;
            bar.style.display = isToday ? '' : 'none';
        }

        // Salt-okunur banner gelecek günlerde göster
        showReadonlyBanner(dayStr !== todayStr);

        // Sefere Başla butonu — yalnızca bugün
        if (typeof updateLiveBtnAvailability === 'function') updateLiveBtnAvailability();
    }

    function showReadonlyBanner(show) {
        let banner = document.getElementById('dr-readonly-banner');
        const body = document.querySelector('.dr-today-body');
        if (!body) return;
        if (show) {
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'dr-readonly-banner';
                banner.className = 'dr-readonly-banner';
                banner.innerHTML = '<i data-lucide="eye" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;margin-right:4px"></i> İleri tarihteki rota — yalnızca görüntüleme.';
                body.insertBefore(banner, body.firstChild);
            }
        } else if (banner) {
            banner.remove();
        }
    }

    // ===== Canlı konum takibi (Sefere Başla butonu) =====
    const liveBtn = document.getElementById('dr-live-btn');
    const liveStatus = document.getElementById('dr-live-status');
    const liveLabel = liveBtn ? liveBtn.querySelector('.dr-live-label') : null;
    const liveIcon = liveBtn ? liveBtn.querySelector('.dr-live-icon') : null;
    const permOverlay = document.getElementById('dr-permission-overlay');
    const permCancelBtn = document.getElementById('dr-permission-cancel');
    const permMsg = document.getElementById('dr-permission-msg');
    const stepPermit = document.getElementById('dr-step-permit');
    const stepGps = document.getElementById('dr-step-gps');
    const stepLocate = document.getElementById('dr-step-locate');
    const tripToast = document.getElementById('dr-trip-toast');
    const locationUrl = @json(route('driver.location.update'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let watchId = null;
    let liveMarker = null;
    let lastSentAt = 0;
    let tripStarted = false;
    const SEND_INTERVAL_MS = 10000;

    function setLiveStatus(text, show) {
        if (!liveStatus) return;
        if (text != null) liveStatus.textContent = text;
        liveStatus.classList.toggle('show', !!show);
    }

    function setStep(el, state) {
        if (!el) return;
        el.classList.remove('is-active', 'is-done');
        if (state) el.classList.add('is-' + state);
    }

    function showPermissionOverlay(show) {
        if (permOverlay) permOverlay.classList.toggle('show', !!show);
    }

    function showTripToast() {
        if (!tripToast) return;
        tripToast.classList.add('show');
        setTimeout(() => tripToast.classList.remove('show'), 3500);
    }

    function updateLiveBtnAvailability() {
        if (!liveBtn) return;
        const isToday = activeDay === todayStr;
        if (watchId !== null) return; // takip çalışıyorken müdahale etme
        if (isToday) {
            liveBtn.classList.remove('is-disabled');
            liveBtn.disabled = false;
            liveBtn.title = 'Canlı konum takibini başlat';
            if (liveLabel) liveLabel.textContent = 'Sefere Başla';
        } else {
            liveBtn.classList.add('is-disabled');
            liveBtn.disabled = false; // tıklayınca uyarı verebilelim
            liveBtn.title = 'Sefer yalnızca bugünün rotasıyla başlatılabilir';
            if (liveLabel) liveLabel.textContent = 'Yalnızca bugün';
        }
    }

    function sendLocation(lat, lng, accuracy, speed, heading) {
        return fetch(locationUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                latitude: lat, longitude: lng,
                accuracy: accuracy, speed: speed, heading: heading,
            }),
        }).catch(() => {});
    }

    function requestPermissionAndStart() {
        if (!navigator.geolocation) {
            alert('Cihazınız konum servisini desteklemiyor.');
            return;
        }
        if (watchId !== null) return;

        // Overlay'i aç, ilk adımı aktif et
        showPermissionOverlay(true);
        setStep(stepPermit, 'active');
        setStep(stepGps, '');
        setStep(stepLocate, '');
        if (permMsg) permMsg.textContent = 'Tarayıcınız konum izni soracak. İzin verdikten sonra GPS\'inizi açık tutun.';

        // İzin durumu sorgulanabiliyorsa kontrol edelim
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then((res) => {
                if (res.state === 'granted') setStep(stepPermit, 'done');
            }).catch(() => {});
        }

        if (liveBtn) liveBtn.disabled = true;

        watchId = navigator.geolocation.watchPosition(function (pos) {
            // İlk konum geldi → sefer başladı
            if (!tripStarted) {
                tripStarted = true;
                setStep(stepPermit, 'done');
                setStep(stepGps, 'done');
                setStep(stepLocate, 'done');
                setTimeout(() => {
                    showPermissionOverlay(false);
                    showTripToast();
                }, 600);
            }

            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            const accuracy = pos.coords.accuracy ?? null;
            const speedMs = pos.coords.speed ?? null;
            const speedKmh = (speedMs != null && !isNaN(speedMs)) ? Math.max(0, speedMs * 3.6) : null;
            const heading = pos.coords.heading ?? null;
            const lngLat = [lng, lat];

            if (!liveMarker) {
                const el = document.createElement('div');
                el.className = 'dr-marker is-current';
                el.innerHTML = '<span>📍</span>';
                liveMarker = new mapboxgl.Marker(el).setLngLat(lngLat).addTo(map);
                map.flyTo({ center: lngLat, zoom: 15 });
            } else {
                liveMarker.setLngLat(lngLat);
            }

            const statusParts = [
                'Canlı: ' + lat.toFixed(5) + ', ' + lng.toFixed(5),
            ];
            if (accuracy != null) statusParts.push('±' + Math.round(accuracy) + 'm');
            if (speedKmh != null) statusParts.push(Math.round(speedKmh) + ' km/s');
            setLiveStatus(statusParts.join(' • '), true);

            if (liveBtn) {
                liveBtn.classList.add('is-live');
                liveBtn.classList.remove('is-disabled');
                liveBtn.disabled = false;
            }
            if (liveLabel) liveLabel.textContent = 'Seferi Bitir';
            if (liveIcon) liveIcon.innerHTML = '<span class="pulse"></span>';

            const now = Date.now();
            if (now - lastSentAt >= SEND_INTERVAL_MS) {
                lastSentAt = now;
                sendLocation(lat, lng, accuracy, speedKmh, heading);
            }
        }, function (err) {
            // İzin reddi veya GPS kapalı vs
            const denied = err.code === 1; // PERMISSION_DENIED
            const unavailable = err.code === 2; // POSITION_UNAVAILABLE
            const timeout = err.code === 3;
            stopLiveTracking();
            if (denied) {
                if (permMsg) permMsg.textContent = 'Konum izni reddedildi. Tarayıcı ayarlarından izni açıp tekrar deneyin.';
                setStep(stepPermit, '');
                showPermissionOverlay(true);
            } else if (unavailable) {
                if (permMsg) permMsg.textContent = 'Konum alınamıyor. Cihazınızın GPS / Konum Servisi açık olduğundan emin olun.';
                setStep(stepGps, 'active');
                showPermissionOverlay(true);
            } else if (timeout) {
                alert('Konum zaman aşımına uğradı. Açık bir alanda tekrar deneyin.');
            } else {
                alert('Konum alınamadı: ' + (err.message || 'bilinmeyen hata'));
            }
        }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 20000 });
    }

    function stopLiveTracking() {
        if (watchId !== null) {
            try { navigator.geolocation.clearWatch(watchId); } catch (e) {}
            watchId = null;
        }
        if (liveMarker) { liveMarker.remove(); liveMarker = null; }
        setLiveStatus('', false);
        tripStarted = false;
        showPermissionOverlay(false);
        if (liveBtn) {
            liveBtn.classList.remove('is-live');
            liveBtn.disabled = false;
        }
        if (liveLabel) liveLabel.textContent = 'Sefere Başla';
        if (liveIcon) liveIcon.innerHTML = '<i class="fas fa-play"></i>';
        updateLiveBtnAvailability();
    }

    if (liveBtn) {
        liveBtn.addEventListener('click', () => {
            if (watchId !== null) {
                if (confirm('Seferi bitirmek istediğinizden emin misiniz?')) stopLiveTracking();
                return;
            }
            // Yalnızca bugün
            if (activeDay !== todayStr) {
                alert('Sefer yalnızca bugünün rotası ile başlatılabilir. Lütfen "Bugün" sekmesini seçin.');
                return;
            }
            requestPermissionAndStart();
        });
    }

    if (permCancelBtn) {
        permCancelBtn.addEventListener('click', () => {
            stopLiveTracking();
        });
    }

    // Sayfa kapatılırken takibi durdur
    window.addEventListener('beforeunload', () => {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
    });

    // Tab handlers
    document.querySelectorAll('#dr-day-tabs .dr-day-tab').forEach(t => {
        t.addEventListener('click', () => selectDay(t.dataset.date));
    });

    // Varsayılan: bugün varsa bugün, yoksa ilk (en yakın) gün
    const allTabs = document.querySelectorAll('#dr-day-tabs .dr-day-tab');
    let defaultDay = todayStr;
    const todayTab = Array.from(allTabs).find(t => t.dataset.date === todayStr);
    if (!todayTab && allTabs.length > 0) defaultDay = allTabs[0].dataset.date;
    if (allTabs.length > 0) selectDay(defaultDay);

    // Google Maps deep link (native nav app açar)
    function buildGoogleMapsUrl(originLngLat, ordered) {
        if (!ordered.length) return '#';
        var params = ['api=1'];
        if (originLngLat) {
            params.push('origin=' + originLngLat[1] + ',' + originLngLat[0]);
        }
        // Hedef = son durak; ara duraklar = waypoints
        var dest = ordered[ordered.length - 1].lngLat;
        params.push('destination=' + dest[1] + ',' + dest[0]);
        if (ordered.length > 1) {
            var waypoints = ordered.slice(0, -1).map(function (m) {
                return m.lngLat[1] + ',' + m.lngLat[0];
            }).join('|');
            params.push('waypoints=' + waypoints);
        }
        params.push('travelmode=driving');
        params.push('dir_action=navigate');
        return 'https://www.google.com/maps/dir/?' + params.join('&');
    }

    // Sefere Çık butonu + Navigasyon Aç linkini güncelle
    var goBtn = document.getElementById('dr-go-btn');
    var navLink = document.getElementById('dr-nav-link');
    var startedMarker = null; // şoförün şu anki konumu

    function updateNavLink(originLngLat) {
        if (!navLink) return;
        var ordered = getOrderedStopsForDay(todayStr);
        navLink.href = buildGoogleMapsUrl(originLngLat, ordered);
    }

    // Sayfa yüklenince linki ön-doldur (origin yok, native maps "Geçerli Konum"u kullanır)
    updateNavLink(null);

    if (goBtn) {
        goBtn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Cihazınız konum servisini desteklemiyor.');
                return;
            }
            var label = goBtn.querySelector('span');
            var originalText = label ? label.textContent : 'Sefere Çık';
            if (label) label.textContent = 'Konum alınıyor...';
            goBtn.disabled = true;

            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude, lng = pos.coords.longitude;
                var origin = [lng, lat];
                // Şoför pinini koy/güncelle
                if (startedMarker) {
                    startedMarker.setLngLat(origin);
                } else {
                    var pe = document.createElement('div');
                    pe.className = 'dr-marker is-current';
                    pe.innerHTML = '<span>📍</span>';
                    startedMarker = new mapboxgl.Marker(pe).setLngLat(origin).addTo(map);
                }
                // Rotayı şoförün konumundan başlatarak çiz (her zaman bugünün rotası)
                if (activeDay !== todayStr) selectDay(todayStr);
                var ordered = getOrderedStopsForDay(todayStr);
                var fullCoords = [origin].concat(ordered.map(function (m) { return m.lngLat; }))
                    .map(function (c) { return c[0] + ',' + c[1]; }).join(';');
                var url = 'https://api.mapbox.com/directions/v5/mapbox/driving-traffic/' + fullCoords
                        + '?geometries=geojson&overview=full&access_token=' + encodeURIComponent(mapboxgl.accessToken);
                fetch(url).then(function (r) { return r.json(); }).then(function (data) {
                    if (!data.routes || !data.routes[0]) return;
                    if (map.getLayer('dr-route-line')) map.removeLayer('dr-route-line');
                    if (map.getSource('dr-route')) map.removeSource('dr-route');
                    map.addSource('dr-route', { type: 'geojson', data: { type: 'Feature', geometry: data.routes[0].geometry } });
                    map.addLayer({
                        id: 'dr-route-line', type: 'line', source: 'dr-route',
                        layout: { 'line-join': 'round', 'line-cap': 'round' },
                        paint: { 'line-color': '#dc3545', 'line-width': 5, 'line-opacity': 0.9 },
                    });
                    // Tüm rotayı kapsayan zoom
                    var b = new mapboxgl.LngLatBounds();
                    b.extend(origin);
                    ordered.forEach(function (m) { b.extend(m.lngLat); });
                    try { map.fitBounds(b, { padding: 60, maxZoom: 14 }); } catch (e) { }
                }).catch(function () { });
                updateNavLink(origin);
                if (label) label.textContent = 'Seferde';
                goBtn.classList.add('is-active');
                goBtn.disabled = false;
            }, function (err) {
                if (label) label.textContent = originalText;
                goBtn.disabled = false;
                alert('Konum alınamadı: ' + (err.message || 'bilinmeyen hata'));
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endif
@endpush
