@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- İstatistikler -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\Vehicle::count() }}</h3>
                        <p>{{ __('Araç') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <a href="{{ route('admin.vehicles.index') }}" class="small-box-footer">
                        {{ __('Detaylar') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\User::where('level', 2)->count() }}</h3>
                        <p>{{ __('Şoför') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <a href="{{ route('admin.drivers.index') }}" class="small-box-footer">
                        {{ __('Detaylar') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\Ticket::count() }}</h3>
                        <p>{{ __('Bilet') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <a href="{{ route('admin.tickets.index') }}" class="small-box-footer">
                        {{ __('Detaylar') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <!-- Aktif Araç -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\Vehicle::where('is_active', true)->count() }}</h3>
                        <p>{{ __('Aktif Araç') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('admin.vehicles.index') }}" class="small-box-footer">
                        {{ __('Detaylar') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Hızlı İşlemler -->
        <div class="row">
            <div class="col-md-6">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bolt mr-1"></i> {{ __('Hızlı İşlemler') }}</h3>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-6 mb-1">
                                <a href="{{ route('admin.vehicles.create') }}" class="ad-btn ad-btn-primary ad-btn-sm w-100">
                                    <i data-lucide="plus"></i> {{ __('Yeni Araç') }}
                                </a>
                            </div>
                            <div class="col-6 mb-1">
                                <a href="{{ route('admin.drivers.create') }}" class="ad-btn ad-btn-success ad-btn-sm w-100">
                                    <i data-lucide="plus"></i> {{ __('Yeni Şoför') }}
                                </a>
                            </div>
                            <div class="col-6 mb-1">
                                <a href="{{ route('admin.tickets.create') }}" class="ad-btn ad-btn-warning ad-btn-sm w-100">
                                    <i data-lucide="plus"></i> {{ __('Yeni Bilet') }}
                                </a>
                            </div>
                            <div class="col-6 mb-1">
                                <a href="{{ route('admin.tours.create') }}" class="ad-btn ad-btn-info ad-btn-sm w-100">
                                    <i data-lucide="plus"></i> {{ __('Yeni Tur') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Son İşlemler') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="ad-table table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('İşlem') }}</th>
                                        <th>{{ __('Tarih') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $events = [];

                                        // Araçlar: eklendi + düzenlendi
                                        foreach (\App\Models\Vehicle::latest('updated_at')->take(20)->get() as $v) {
                                            $events[] = ['type'=>'vehicle','text'=>__('Araç eklendi') . ': ' . $v->plate_number,'date'=>$v->created_at,'action'=>'create'];
                                            if ($v->updated_at && $v->updated_at->gt($v->created_at->addSeconds(5))) {
                                                $events[] = ['type'=>'vehicle_edit','text'=>__('Araç düzenlendi') . ': ' . $v->plate_number,'date'=>$v->updated_at,'action'=>'edit'];
                                            }
                                        }

                                        // Biletler: eklendi + düzenlendi
                                        foreach (\App\Models\Ticket::latest('updated_at')->take(20)->get() as $t) {
                                            $events[] = ['type'=>'ticket','text'=>__('Bilet eklendi') . ': ' . ($t->voucher_no ?: $t->tracking_no),'date'=>$t->created_at,'action'=>'create'];
                                            if ($t->updated_at && $t->updated_at->gt($t->created_at->addSeconds(5))) {
                                                $events[] = ['type'=>'ticket_edit','text'=>__('Bilet düzenlendi') . ': ' . ($t->voucher_no ?: $t->tracking_no),'date'=>$t->updated_at,'action'=>'edit'];
                                            }
                                        }

                                        // Turlar: eklendi + düzenlendi
                                        foreach (\App\Models\Tour::latest('updated_at')->take(20)->get() as $tr) {
                                            $events[] = ['type'=>'tour','text'=>__('Tur eklendi') . ': ' . $tr->name,'date'=>$tr->created_at,'action'=>'create'];
                                            if ($tr->updated_at && $tr->updated_at->gt($tr->created_at->addSeconds(5))) {
                                                $events[] = ['type'=>'tour_edit','text'=>__('Tur düzenlendi') . ': ' . $tr->name,'date'=>$tr->updated_at,'action'=>'edit'];
                                            }
                                        }

                                        // Şoförler: eklendi + düzenlendi
                                        foreach (\App\Models\User::where('level',2)->latest('updated_at')->take(20)->get() as $dr) {
                                            $events[] = ['type'=>'driver','text'=>__('Şoför eklendi') . ': ' . $dr->name,'date'=>$dr->created_at,'action'=>'create'];
                                            if ($dr->updated_at && $dr->updated_at->gt($dr->created_at->addSeconds(5))) {
                                                $events[] = ['type'=>'driver_edit','text'=>__('Şoför düzenlendi') . ': ' . $dr->name,'date'=>$dr->updated_at,'action'=>'edit'];
                                            }
                                        }

                                        usort($events, function($a,$b){ return $b['date'] <=> $a['date']; });
                                        $perPage = 5;
                                        $page = max(1, (int) request('events_page', 1));
                                        $total = count($events);
                                        $totalPages = max(1, (int) ceil($total / $perPage));
                                        if ($page > $totalPages) { $page = $totalPages; }
                                        $offset = ($page - 1) * $perPage;
                                        $events = array_slice($events, $offset, $perPage);
                                    @endphp

                                    @forelse($events as $e)
                                        <tr>
                                            <td>
                                                @switch($e['type'])
                                                    @case('vehicle') <i class="fas fa-car text-info"></i> @break
                                                    @case('vehicle_edit') <i class="fas fa-car text-info"></i><i class="fas fa-pen ml-1" style="font-size:10px;color:#17a2b8;"></i> @break
                                                    @case('ticket') <i class="fas fa-ticket-alt text-warning"></i> @break
                                                    @case('ticket_edit') <i class="fas fa-ticket-alt text-warning"></i><i class="fas fa-pen ml-1" style="font-size:10px;color:#ffc107;"></i> @break
                                                    @case('tour') <i class="fas fa-plane text-primary"></i> @break
                                                    @case('tour_edit') <i class="fas fa-plane text-primary"></i><i class="fas fa-pen ml-1" style="font-size:10px;color:#007bff;"></i> @break
                                                    @case('driver') <i class="fas fa-user-tie text-success"></i> @break
                                                    @case('driver_edit') <i class="fas fa-user-tie text-success"></i><i class="fas fa-pen ml-1" style="font-size:10px;color:#28a745;"></i> @break
                                                    @default <i class="fas fa-info-circle text-muted"></i>
                                                @endswitch
                                                {{ $e['text'] }}
                                            </td>
                                            <td>{{ $e['date']->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-muted">{{ __('Henüz işlem bulunmuyor.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @php 
                                $page = (int) request('events_page', 1); 
                                $maxPagesToShow = 5;
                                $startPage = max(1, $page - floor($maxPagesToShow/2));
                                $endPage = min($totalPages ?? 1, $startPage + $maxPagesToShow - 1);
                                if ($endPage - $startPage + 1 < $maxPagesToShow) {
                                    $startPage = max(1, $endPage - $maxPagesToShow + 1);
                                }
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                                <div class="text-muted small">
                                    {{ __('Sayfa :page / :total', ['page' => $page, 'total' => $totalPages ?? 1]) }}
                                </div>
                                <nav aria-label="{{ __('Son İşlemler Sayfaları') }}">
                                    <ul class="pagination pagination-sm mb-0 modern-pagination">
                                        @if($page > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['events_page' => 1]) }}" title="{{ __('İlk') }}">
                                                    <i class="fas fa-angle-double-left"></i>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['events_page' => $page-1]) }}" title="{{ __('Önceki') }}">
                                                    <i class="fas fa-angle-left"></i>
                                                </a>
                                            </li>
                                        @endif

                                        @for($i=$startPage; isset($totalPages) && $i<=$endPage; $i++)
                                            <li class="page-item {{ $i === $page ? 'active' : '' }}">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['events_page' => $i]) }}">{{ $i }}</a>
                                            </li>
                                        @endfor

                                        @if(isset($totalPages) && $page < $totalPages)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['events_page' => $page+1]) }}" title="{{ __('Sonraki') }}">
                                                    <i class="fas fa-angle-right"></i>
                                                </a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['events_page' => $totalPages]) }}" title="{{ __('Son') }}">
                                                    <i class="fas fa-angle-double-right"></i>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kullanıcı Konumları Haritası -->
        <div class="row">
            <div class="col-12">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Kullanıcı Konumları') }}</h3>
                        <div class="card-tools">
                            <!-- made by @hllgkx.0 -->
                            <span class="live-counter mr-2" id="live-counter" style="display:none;"><span id="live-counter-num">0</span> {{ __('şoför canlı') }}</span>
                            <span class="badge bg-info mr-2" id="last-update">{{ __('Son Güncelleme') }}: -</span>
                            <button type="button" class="btn btn-tool" onclick="refreshMap()" title="{{ __('Yenile') }}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="height: 60vh;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
<!-- admin dashboard css-->
@push('css')
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
    <style>
        /* Dashboard İstatistik Kutuları - Ortalanmış */
        .small-box {
            min-height: 100px !important;
            position: relative;
        }
        .small-box .inner {
            padding: 15px 10px 10px !important;
            text-align: center !important;
            position: relative;
            z-index: 1;
        }
        .small-box .inner h3 {
            font-size: 2rem !important;
            line-height: 1 !important;
            margin-bottom: 5px !important;
        }
        .small-box .inner p {
            font-size: 0.85rem !important;
            margin-top: 5px !important;
            font-weight: 500;
        }
        .small-box .icon {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            right: auto !important;
            opacity: 0.15 !important;
            z-index: 0;
        }
        .small-box .icon i {
            font-size: 60px !important;
        }
        .small-box-footer {
            padding: 8px 10px !important;
            font-size: 0.75rem !important;
            text-align: center !important;
        }
        
        #map {
            height: 100%;
            width: 100%;
            min-height: 400px;
        }
        @keyframes dr-live-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.7); }
            70%  { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        .live-counter {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #fff; font-weight: 600; font-size: 11px;
            padding: 3px 10px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .live-counter::before {
            content: ''; display: inline-block; width: 7px; height: 7px;
            border-radius: 50%; background: #fff;
            animation: dr-live-pulse 1.4s infinite;
        }
        .info-window {
            min-width: 200px;
            padding: 10px;
        }
        .info-window h5 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }
        .info-window p {
            margin: 3px 0;
            font-size: 13px;
            color: #555;
        }

        /* Modern pagination styles */
        .modern-pagination .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            color: #495057;
            transition: all 0.2s ease;
            min-width: 32px;
            text-align: center;
        }
        .modern-pagination .page-link:hover {
            background: #f8f9fa;
            border-color: #17a2b8;
            color: #17a2b8;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .modern-pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-color: #17a2b8;
            color: #fff;
            box-shadow: 0 3px 6px rgba(23,162,184,0.3);
        }
        .modern-pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Dashboard Mobile Responsive - Sadece telefon için */
        @media (max-width: 768px) {
            /* Small box düzeni */
            .small-box {
                margin-bottom: 0.75rem !important;
            }

            .small-box .inner {
                padding: 0.75rem !important;
            }

            .small-box .inner h3 {
                font-size: 1.5rem !important;
                margin: 0 0 0.25rem 0 !important;
            }
            
            .small-box .inner p {
                font-size: 0.9rem !important;
                margin-bottom: 0 !important;
            }
            
            .small-box .icon {
                right: 0.5rem !important;
                top: 0.5rem !important;
            }
            
            .small-box .icon i {
                font-size: 3rem !important;
            }
            
            .small-box-footer {
                padding: 0.5rem !important;
                font-size: 0.8rem !important;
            }
            
            /* Dashboard grid */
            .col-lg-3 {
                margin-bottom: 0.5rem !important;
            }
            
            /* Hızlı işlemler card'ı */
            .card-body .row .col-6 {
                margin-bottom: 0.5rem !important;
            }
            
            .w-100 {
                padding: 0.4rem 0.75rem !important;
                font-size: 0.8rem !important;
            }
            
            /* Map container */
            #map {
                min-height: 300px !important;
            }
            
            /* Son işlemler tablosu */
            .table-sm {
                font-size: 9px !important;
            }
            
            .table-sm td, .table-sm th {
                padding: 0.2rem !important;
            }
            /* made by @hllgkx.0 */
            
            /* Info window mobile */
            .info-window {
                min-width: 150px !important;
                padding: 5px !important;
            }
            
            .info-window h5 {
                font-size: 12px !important;
                margin: 0 0 3px 0 !important;
            }
            
            .info-window p {
                font-size: 10px !important;
                margin: 2px 0 !important;
            }
            
            /* Badge last update */
            .badge.bg-info {
                font-size: 8px !important;
                padding: 0.2em 0.4em !important;
            }
            
            /* Card tools */
            .card-tools .btn-tool {
                padding: 0.2rem !important;
                font-size: 10px !important;
            }
        }
    </style>
@endpush
<!-- end of the css-->
<!-- admin dashboard js-->  
@push('js')
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
    <script>
        let map;
        let vehicleMarkers = {}; // id → { marker, popup, el, lastLngLat, lastUpdate }
        let customerMarkers = [];
        let activePopup = null;
        const LIVE_THRESHOLD_MS = 90 * 1000; // 90 sn içinde güncellenmiş = CANLI
        const JS_LOCALE = {!! json_encode(app()->getLocale() === 'en' ? 'en-US' : 'tr-TR', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        const I18N = {!! json_encode([
            'unknown' => __('Bilinmiyor'),
            'now' => __('şimdi'),
            'secAgo' => __('sn önce'),
            'minAgo' => __('dk önce'),
            'hourAgo' => __('sa önce'),
            'live' => __('CANLI'),
            'driver' => __('Şoför'),
            'status' => __('Durum'),
            'speed' => __('Hız'),
            'lastUpdate' => __('Son Güncelleme'),
            'ticket' => __('Bilet'),
            'tour' => __('Tur'),
            'mapboxTokenMissing' => __('Mapbox token tanımlı değil'),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

        function escHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        function createMarkerEl(type) {
            var wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;width:32px;height:32px;cursor:pointer;';
            var img = document.createElement('div');
            img.style.cssText = 'width:32px;height:32px;background-size:contain;background-repeat:no-repeat;background-position:center;transition:transform .2s ease;';
            if (type === 'vehicle') {
                img.style.backgroundImage = "url('{{ asset("img/marker-mınıbus.png") }}')";
            } else {
                img.style.backgroundImage = "url('{{ asset("img/marker-costumer.png") }}')";
            }
            wrap.appendChild(img);
            return wrap;
        }

        function setLiveBadge(el, isLive) {
            if (!el) return;
            var badge = el.querySelector('.dr-live-pin-badge');
            if (isLive) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'dr-live-pin-badge';
                    badge.style.cssText = 'position:absolute;top:-3px;right:-3px;width:10px;height:10px;border-radius:50%;background:#22c55e;border:2px solid #fff;box-shadow:0 0 0 0 rgba(34,197,94,.7);animation:dr-live-pulse 1.4s infinite;';
                    el.appendChild(badge);
                }
            } else if (badge) {
                badge.remove();
            }
        }

        function timeAgoText(ts) {
            if (!ts) return I18N.unknown;
            var d = new Date(ts);
            var diff = Math.floor((Date.now() - d.getTime()) / 1000);
            if (diff < 30) return I18N.now;
            if (diff < 60) return diff + ' ' + I18N.secAgo;
            if (diff < 3600) return Math.floor(diff / 60) + ' ' + I18N.minAgo;
            if (diff < 86400) return Math.floor(diff / 3600) + ' ' + I18N.hourAgo;
            return d.toLocaleString(JS_LOCALE);
        }

        function initMap() {
            mapboxgl.accessToken = {!! json_encode(config('services.mapbox.access_token'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
            if (!mapboxgl.accessToken) {
                document.getElementById('map').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#999"><p>' + I18N.mapboxTokenMissing + '</p></div>';
                return;
            }

            var defaultCenter = [35.0, 39.0];
            var defaultZoom = 6;
            try {
                var savedAddr = JSON.parse(localStorage.getItem('admin_default_address'));
                if (savedAddr && savedAddr.lng && savedAddr.lat) {
                    defaultCenter = [savedAddr.lng, savedAddr.lat];
                    defaultZoom = 13;
                }
            } catch(e){}

            map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: defaultCenter,
                zoom: defaultZoom,
                language: {!! json_encode(app()->getLocale(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
            });

            map.addControl(new mapboxgl.NavigationControl(), 'top-right');

            map.on('load', function () {
                fetchVehicleLocations();
                setInterval(fetchVehicleLocations, 10000); // 10 sn poll
                setInterval(refreshLiveBadges, 5000);      // CANLI rozetini periyodik tazele
            });
        }

        function clearMarkers(arr) {
            if (Array.isArray(arr)) {
                arr.forEach(function (m) { m.remove(); });
                arr.length = 0;
            } else {
                Object.values(arr).forEach(function (e) { e.marker.remove(); });
                Object.keys(arr).forEach(function (k) { delete arr[k]; });
            }
        }

        function isLive(lastUpdate) {
            if (!lastUpdate) return false;
            return (Date.now() - new Date(lastUpdate).getTime()) <= LIVE_THRESHOLD_MS;
        }

        function refreshLiveBadges() {
            var liveCount = 0;
            Object.values(vehicleMarkers).forEach(function (entry) {
                var live = isLive(entry.lastUpdate);
                setLiveBadge(entry.el, live);
                if (live) liveCount++;
            });
            var counter = document.getElementById('live-counter');
            var num = document.getElementById('live-counter-num');
            if (counter && num) {
                if (liveCount > 0) {
                    num.textContent = liveCount;
                    counter.style.display = '';
                } else {
                    counter.style.display = 'none';
                }
            }
        }

        function buildVehiclePopupHtml(vehicle) {
            var live = isLive(vehicle.last_update);
            var liveDot = live ? '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#22c55e;margin-right:6px;animation:dr-live-pulse 1.4s infinite;"></span>' + I18N.live : '';
            return '<div class="info-window">' +
                '<h5>' + escHtml(vehicle.plate_number) +
                (live ? ' <span style="font-size:10px;color:#16a34a;font-weight:700;margin-left:6px;">' + liveDot + '</span>' : '') +
                '</h5>' +
                '<p><strong>' + I18N.driver + ':</strong> ' + escHtml(vehicle.driver_name || '-') + '</p>' +
                '<p><strong>' + I18N.status + ':</strong> ' + escHtml(vehicle.status || '-') + '</p>' +
                (vehicle.speed != null ? '<p><strong>' + I18N.speed + ':</strong> ' + Math.round(vehicle.speed) + ' km/s</p>' : '') +
                '<p><strong>' + I18N.lastUpdate + ':</strong> ' + timeAgoText(vehicle.last_update) + '</p>' +
                '</div>';
        }

        function fetchVehicleLocations() {
            fetch('{{ route("admin.vehicles.locations") }}')
                .then(function (r) { return r.json(); })
                .then(function (vehicles) {
                    var seenIds = {};
                    vehicles.forEach(function (vehicle) {
                        if (!vehicle.lat || !vehicle.lng) return;
                        var lng = parseFloat(vehicle.lng);
                        var lat = parseFloat(vehicle.lat);
                        seenIds[vehicle.id] = true;

                        var existing = vehicleMarkers[vehicle.id];
                        if (existing) {
                            // Mevcut marker'ı yumuşakça güncelle
                            existing.marker.setLngLat([lng, lat]);
                            existing.popup.setHTML(buildVehiclePopupHtml(vehicle));
                            existing.lastLngLat = [lng, lat];
                            existing.lastUpdate = vehicle.last_update;
                            setLiveBadge(existing.el, isLive(vehicle.last_update));
                        } else {
                            // Yeni marker oluştur
                            var el = createMarkerEl('vehicle');
                            var popup = new mapboxgl.Popup({ offset: 25, maxWidth: '260px' })
                                .setHTML(buildVehiclePopupHtml(vehicle));
                            var marker = new mapboxgl.Marker(el)
                                .setLngLat([lng, lat])
                                .setPopup(popup)
                                .addTo(map);
                            vehicleMarkers[vehicle.id] = {
                                marker: marker, popup: popup, el: el,
                                lastLngLat: [lng, lat], lastUpdate: vehicle.last_update,
                            };
                            setLiveBadge(el, isLive(vehicle.last_update));
                        }
                    });

                    // Geri dönmeyen araçları sil
                    Object.keys(vehicleMarkers).forEach(function (id) {
                        if (!seenIds[id]) {
                            vehicleMarkers[id].marker.remove();
                            delete vehicleMarkers[id];
                        }
                    });

                    refreshLiveBadges();
                    fetchCustomerLocations();
                })
                .catch(function (err) { console.error('Vehicle locations error:', err); });
        }

        function fetchCustomerLocations() {
            fetch('/api/tickets/locations')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    clearMarkers(customerMarkers);

                    if (data.success && data.customers) {
                        data.customers.forEach(function (customer) {
                            var lng = parseFloat(customer.longitude);
                            var lat = parseFloat(customer.latitude);
                            if (isNaN(lat) || isNaN(lng)) return;

                            var el = createMarkerEl('customer');
                            var popup = new mapboxgl.Popup({ offset: 25, maxWidth: '260px' })
                                .setHTML(
                                    '<div class="info-window">' +
                                    '<h5>' + escHtml(customer.ticket.customer_name) + '</h5>' +
                                    '<p><strong>' + I18N.ticket + ':</strong> ' + escHtml(customer.ticket.tracking_no) + '</p>' +
                                    '<p><strong>' + I18N.tour + ':</strong> ' + escHtml(customer.ticket.tour_name) + '</p>' +
                                    '<p><strong>' + I18N.lastUpdate + ':</strong> ' + new Date(customer.updated_at).toLocaleString(JS_LOCALE) + '</p>' +
                                    '</div>'
                                );

                            var marker = new mapboxgl.Marker(el)
                                .setLngLat([lng, lat])
                                .setPopup(popup)
                                .addTo(map);

                            customerMarkers.push(marker);
                        });
                    }

                    document.getElementById('last-update').textContent = I18N.lastUpdate + ': ' + new Date().toLocaleTimeString(JS_LOCALE);
                })
                .catch(function (err) { console.error('Customer locations error:', err); });
        }

        function refreshMap() {
            fetchVehicleLocations();
        }

        window.addEventListener('load', initMap);
    </script>
@endpush
<!-- end of the js-->
<!-- end of the code-->