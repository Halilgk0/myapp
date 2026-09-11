<!DOCTYPE html>
<html lang="{{ $t['html_lang'] }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $t['title_my_ticket'] }} — {{ $ticket->customer_name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @if($mapboxToken)
        <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css">
    @endif
    <style>
        :root {
            --c-bg: #f4f7fb;
            --c-card: #ffffff;
            --c-text: #1e293b;
            --c-muted: #64748b;
            --c-accent: #0d6efd;
            --c-success: #16a34a;
            --c-warning: #f59e0b;
            --c-border: #e2e8f0;
            --c-radius: 12px;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--c-bg);
            color: var(--c-text);
            font-size: 14px;
        }

        /* Üst bar */
        .c-topbar {
            background: linear-gradient(135deg, #1e3a8a, #0d6efd);
            color: #fff;
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .c-topbar h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }
        .c-topbar small { opacity: 0.85; font-size: 11px; }
        .c-logout {
            background: rgba(255,255,255,0.18);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 6px 12px;
            border-radius: 18px;
            font-size: 12px;
            text-decoration: none;
        }
        .c-logout:hover { background: rgba(255,255,255,0.28); color: #fff; }

        /* Ana içerik */
        .c-container {
            max-width: 720px;
            margin: 0 auto;
            padding: 14px;
        }

        .c-section {
            background: var(--c-card);
            border-radius: var(--c-radius);
            padding: 16px 16px 14px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .c-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--c-muted);
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .c-section-title i { color: var(--c-accent); }

        .c-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid var(--c-border);
            font-size: 13px;
        }
        .c-row:last-child { border-bottom: none; }
        .c-row-label { color: var(--c-muted); }
        .c-row-value { font-weight: 600; text-align: right; max-width: 60%; word-break: break-word; }

        .c-big-num {
            font-size: 22px;
            font-weight: 700;
            color: var(--c-accent);
        }

        /* ETA kutu */
        .c-eta-box {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff;
            border-radius: var(--c-radius);
            padding: 16px;
            margin-bottom: 12px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(22,163,74,0.3);
        }
        .c-eta-box.is-stale {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            box-shadow: 0 4px 14px rgba(75,85,99,0.3);
        }
        .c-eta-box .eta-label {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .c-eta-box .eta-num {
            font-size: 28px;
            font-weight: 800;
            margin: 4px 0;
        }
        .c-eta-box .eta-dist { font-size: 12px; opacity: 0.9; }
        .c-eta-pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: pulse 1.4s infinite;
            margin-right: 4px;
            vertical-align: middle;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.75); }
        }

        /* Harita */
        .c-map-card {
            background: var(--c-card);
            border-radius: var(--c-radius);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 12px;
            position: relative;
        }
        #customer-map { width: 100%; height: 320px; }
        .c-map-empty {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: var(--c-muted);
            text-align: center;
            padding: 20px;
        }

        /* Şoför kart */
        .c-driver-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 0;
        }
        .c-driver-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #06b6d4);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .c-driver-info { flex: 1; min-width: 0; }
        .c-driver-name {
            font-weight: 700;
            font-size: 15px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .c-driver-meta { color: var(--c-muted); font-size: 12px; margin-top: 2px; }
        .c-call-btn {
            background: var(--c-success);
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(22,163,74,0.4);
            flex-shrink: 0;
        }
        .c-call-btn:hover { background: #15803d; color: #fff; }

        /* Şoför yok */
        .c-no-driver {
            background: rgba(245,158,11,0.1);
            border: 1px solid rgba(245,158,11,0.3);
            color: #92400e;
            padding: 12px 14px;
            border-radius: var(--c-radius);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Mapbox popup */
        .mapboxgl-popup-content {
            border-radius: 10px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 13px;
        }

        /* Marker'lar */
        .c-marker {
            width: 32px;
            height: 32px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 2px solid #fff;
            box-shadow: 0 3px 8px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .c-marker.is-driver { background: #dc3545; }
        .c-marker.is-pickup { background: #0d6efd; }
        .c-marker span {
            transform: rotate(45deg);
            color: #fff;
            font-size: 14px;
        }
        .c-marker.is-live::after {
            content: '';
            position: absolute;
            top: -4px;
            right: -4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            border: 2px solid #fff;
            transform: rotate(45deg);
            animation: pulse 1.4s infinite;
        }

        @media (max-width: 480px) {
            #customer-map { height: 260px; }
            .c-container { padding: 10px; }
            .c-section { padding: 14px 14px 12px; }
        }
    </style>
</head>
<body>
    <div class="c-topbar">
        <div>
            <h1>{{ $t['title_my_ticket'] }}</h1>
            <small>{{ $ticket->voucher_no ?: $ticket->tracking_no }}</small>
        </div>
        <form method="POST" action="{{ route('customer.logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="c-logout">
                <i class="fas fa-sign-out-alt"></i> {{ $t['btn_logout'] }}
            </button>
        </form>
    </div>

    <div class="c-container">

        {{-- ETA kutusu (canlı güncellenecek) --}}
        <div class="c-eta-box is-stale" id="c-eta-box">
            <div class="eta-label">{{ $t['eta_label'] }}</div>
            <div class="eta-num" id="c-eta-num">
                <span class="c-eta-pulse"></span> {{ $t['eta_connecting'] }}
            </div>
            <div class="eta-dist" id="c-eta-dist">{{ $t['eta_loading'] }}</div>
        </div>

        {{-- Harita --}}
        <div class="c-map-card">
            @if($mapboxToken)
                <div id="customer-map"></div>
            @else
                <div class="c-map-empty">
                    <i class="fas fa-map" style="font-size:38px;opacity:0.3;margin-bottom:10px"></i>
                    <p style="margin:0;font-size:13px">{{ $t['map_unavailable'] }}</p>
                </div>
            @endif
        </div>

        {{-- Şoför bilgileri --}}
        <div class="c-section">
            <div class="c-section-title"><i class="fas fa-user-tie"></i> {{ $t['section_driver'] }}</div>
            @if($driver)
                <div class="c-driver-card">
                    <div class="c-driver-avatar">
                        {{ mb_strtoupper(mb_substr($driver->name, 0, 1)) }}
                    </div>
                    <div class="c-driver-info">
                        <p class="c-driver-name">{{ $driver->name }}</p>
                        <div class="c-driver-meta">
                            @if($vehicle)
                                <i class="fas fa-car"></i> {{ $vehicle->plate_number }}
                                @if($vehicle->brand) · {{ $vehicle->brand }} {{ $vehicle->model }} @endif
                            @endif
                        </div>
                    </div>
                    @if($driver->phone_number)
                        <a href="tel:{{ $driver->phone_number }}" class="c-call-btn" title="{{ $t['call_driver_title'] }}">
                            <i class="fas fa-phone"></i>
                        </a>
                    @endif
                </div>
            @else
                <div class="c-no-driver">
                    <i class="fas fa-clock"></i>
                    <span>{{ $t['no_driver'] }}</span>
                </div>
            @endif
        </div>

        {{-- Bilet bilgileri --}}
        <div class="c-section">
            <div class="c-section-title"><i class="fas fa-ticket-alt"></i> {{ $t['section_ticket'] }}</div>
            <div class="c-row">
                <span class="c-row-label">{{ $t['row_tour'] }}</span>
                <span class="c-row-value">{{ $ticket->tour_name ?: $ticket->tour?->name ?: '—' }}</span>
            </div>
            <div class="c-row">
                <span class="c-row-label">{{ $t['row_tour_date'] }}</span>
                <span class="c-row-value">{{ $ticket->tour_date ? \Carbon\Carbon::parse($ticket->tour_date)->locale($locale)->translatedFormat('d M Y, l') : '—' }}</span>
            </div>
            @if($ticket->pickup_time)
                <div class="c-row">
                    <span class="c-row-label">{{ $t['row_pickup_time'] }}</span>
                    <span class="c-row-value">{{ \Carbon\Carbon::parse($ticket->pickup_time)->format('H:i') }}</span>
                </div>
            @endif
            @if($ticket->pickup_location)
                <div class="c-row">
                    <span class="c-row-label">{{ $t['row_pickup_loc'] }}</span>
                    <span class="c-row-value">{{ $ticket->pickup_location }}</span>
                </div>
            @endif
            @if($ticket->room_number)
                <div class="c-row">
                    <span class="c-row-label">{{ $t['row_room'] }}</span>
                    <span class="c-row-value">{{ $ticket->room_number }}</span>
                </div>
            @endif
            <div class="c-row">
                <span class="c-row-label">{{ $t['row_passengers'] }}</span>
                <span class="c-row-value">
                    @php $totalPax = (int) $ticket->total_passengers; @endphp
                    {{ $totalPax > 0 ? ($totalPax . ' ' . $t['passenger_word']) : $t['no_passengers'] }}
                </span>
            </div>
            @if((float) $ticket->rest > 0)
                <div class="c-row">
                    <span class="c-row-label">{{ $t['row_rest'] }}</span>
                    <span class="c-row-value" style="color: var(--c-warning); font-weight: 700;">
                        {{ number_format((float) $ticket->rest, 2, ',', '.') }} {{ $ticket->currency ?: 'TRY' }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Müşteri bilgileri --}}
        <div class="c-section">
            <div class="c-section-title"><i class="fas fa-user"></i> {{ $t['section_customer'] }}</div>
            <div class="c-row">
                <span class="c-row-label">{{ $t['row_name'] }}</span>
                <span class="c-row-value">{{ $ticket->customer_name }}</span>
            </div>
            @if($ticket->customer_phone)
                <div class="c-row">
                    <span class="c-row-label">{{ $t['row_phone'] }}</span>
                    <span class="c-row-value">{{ $ticket->customer_phone }}</span>
                </div>
            @endif
            @if($ticket->customer_email)
                <div class="c-row">
                    <span class="c-row-label">{{ $t['row_email'] }}</span>
                    <span class="c-row-value">{{ $ticket->customer_email }}</span>
                </div>
            @endif
        </div>

    </div>

    @if($mapboxToken)
    @php
        $jsI18n = [
            'eta_not_started'    => $t['eta_not_started'],
            'eta_driver_waiting' => $t['eta_driver_waiting'],
            'eta_no_driver'      => $t['eta_no_driver'],
            'eta_km_away'        => $t['eta_km_away'],
            'eta_live'           => $t['eta_live'],
            'eta_last_known'     => $t['eta_last_known'],
            'eta_calculating'    => $t['eta_calculating'],
            'eta_location'       => $t['eta_location'],
            'popup_pickup'       => $t['popup_pickup'],
            'popup_driver'       => $t['popup_driver'],
        ];
    @endphp
    <script>
        window.CUSTOMER_I18N = {!! json_encode($jsI18n, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
    <script>
    (function(){
        var T = window.CUSTOMER_I18N || {};
        mapboxgl.accessToken = {!! json_encode($mapboxToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        var pickupLat = {!! json_encode($ticket->location?->latitude ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        var pickupLng = {!! json_encode($ticket->location?->longitude ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        var pickupName = {!! json_encode($ticket->pickup_location ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        var liveUrl = {!! json_encode(route('customer.live'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

        var defaultCenter = [28.2722, 36.8500]; // Marmaris
        if (pickupLat && pickupLng) defaultCenter = [parseFloat(pickupLng), parseFloat(pickupLat)];

        var map = new mapboxgl.Map({
            container: 'customer-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: defaultCenter,
            zoom: 13,
            language: {!! json_encode($locale ?? 'en', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
        });
        map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

        var pickupMarker = null;
        var driverMarker = null;

        if (pickupLat && pickupLng) {
            var pickupEl = document.createElement('div');
            pickupEl.className = 'c-marker is-pickup';
            pickupEl.innerHTML = '<span><i class="fas fa-map-marker-alt"></i></span>';
            pickupMarker = new mapboxgl.Marker(pickupEl)
                .setLngLat([parseFloat(pickupLng), parseFloat(pickupLat)])
                .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<strong>' + T.popup_pickup + '</strong><br>' + pickupName))
                .addTo(map);
        }

        function updateEta(data) {
            var box = document.getElementById('c-eta-box');
            var num = document.getElementById('c-eta-num');
            var dist = document.getElementById('c-eta-dist');

            if (!data.driver_location) {
                box.classList.add('is-stale');
                num.innerHTML = '<i class="fas fa-clock"></i> ' + T.eta_not_started;
                dist.textContent = data.driver_name ? data.driver_name + ' ' + T.eta_driver_waiting : T.eta_no_driver;
                return;
            }

            if (data.eta_text) {
                box.classList.remove('is-stale');
                num.innerHTML = '<span class="c-eta-pulse"></span> ' + data.eta_text;
                var distKm = data.distance_meters ? (data.distance_meters / 1000).toFixed(1) + ' ' + T.eta_km_away : '';
                dist.textContent = distKm + ' · ' + (data.is_live ? T.eta_live : T.eta_last_known);
            } else {
                box.classList.add('is-stale');
                num.innerHTML = '<i class="fas fa-clock"></i> ' + T.eta_calculating;
                dist.textContent = T.eta_location + ': ' + data.driver_location.lat.toFixed(4) + ', ' + data.driver_location.lng.toFixed(4);
            }
        }

        function updateDriverMarker(data) {
            if (!data.driver_location) {
                if (driverMarker) { driverMarker.remove(); driverMarker = null; }
                return;
            }
            var lngLat = [data.driver_location.lng, data.driver_location.lat];
            if (!driverMarker) {
                var el = document.createElement('div');
                el.className = 'c-marker is-driver' + (data.is_live ? ' is-live' : '');
                el.style.position = 'relative';
                el.innerHTML = '<span><i class="fas fa-car"></i></span>';
                driverMarker = new mapboxgl.Marker(el)
                    .setLngLat(lngLat)
                    .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<strong>' + (data.driver_name || T.popup_driver) + '</strong>'))
                    .addTo(map);
                if (pickupMarker) {
                    var b = new mapboxgl.LngLatBounds();
                    b.extend(lngLat);
                    b.extend([parseFloat(pickupLng), parseFloat(pickupLat)]);
                    try { map.fitBounds(b, { padding: 70, maxZoom: 15 }); } catch(e){}
                } else {
                    map.flyTo({ center: lngLat, zoom: 14 });
                }
            } else {
                driverMarker.setLngLat(lngLat);
                var el = driverMarker.getElement();
                el.classList.toggle('is-live', !!data.is_live);
            }
        }

        function drawRoute(data) {
            if (map.getLayer('c-route')) map.removeLayer('c-route');
            if (map.getSource('c-route')) map.removeSource('c-route');
            if (!data.has_route_geometry || !data.route_geometry) return;
            map.addSource('c-route', { type: 'geojson', data: { type: 'Feature', geometry: data.route_geometry } });
            map.addLayer({
                id: 'c-route', type: 'line', source: 'c-route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#0d6efd', 'line-width': 5, 'line-opacity': 0.85 }
            });
        }

        function poll() {
            fetch(liveUrl, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r){ return r.json(); })
            .then(function(data){
                updateEta(data);
                updateDriverMarker(data);
                if (map.isStyleLoaded()) drawRoute(data);
                else map.once('load', function(){ drawRoute(data); });
            })
            .catch(function(){ /* sessiz geç */ });
        }

        map.on('load', function(){
            poll();
            setInterval(poll, 15000);
        });
    })();
    </script>
    @endif
</body>
</html>
