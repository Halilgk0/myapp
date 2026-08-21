@extends('layouts.admin')

@section('title', 'Tur Detayları')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tur Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <!-- made by @hllgkx.0 -->
                                    <dt>Tur Adı</dt>
                                    <dd><strong>{{ $tour->name }}</strong></dd>
                                    
                                    <dt>Ülke/Şehir</dt>
                                    <dd>{{ $tour->country }} / {{ $tour->city }}</dd>
                                    
                                    
                                    
                                    <dt>Para Birimi</dt>
                                    <dd>{{ $tour->currency }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt>Durum</dt>
                                    <dd>
                                        <span class="badge {{ $tour->status_badge }}">{{ $tour->status }}</span>
                                    </dd>
                                    
                                    <dt>Maksimum Kapasite</dt>
                                    <dd>
                                        @if($tour->max_capacity)
                                            <span class="badge badge-secondary">{{ $tour->max_capacity }} kişi</span>
                                        @else
                                            <span class="text-muted">Sınırsız</span>
                                        @endif
                                    </dd>
                                    
                                    <dt>Kayıt Tarihi</dt>
                                    <dd>{{ $tour->created_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</dd>
                                    
                                    <dt>Son Güncelleme</dt>
                                    <dd>{{ $tour->updated_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</dd>
                                </dl>
                            </div>
                        </div>

                        @if($tour->description)
                        <div class="row">
                            <div class="col-12">
                                <dt>Açıklama</dt>
                                <dd>{{ $tour->description }}</dd>
                            </div>
                        </div>
                        @endif
<!-- made by @hllgkx.0 -->
                        
                        <!-- Servis Alanları (Poligon) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-info card-outline">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-draw-polygon"></i> Servis Alanları</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="service-area-map-show" style="height: 320px; width: 100%; border:1px solid #ced4da; border-radius:4px;"></div>
                                        @if(empty($tour->service_areas))
                                            <small class="text-muted d-block mt-2">Bu tur için servis alanı tanımlanmamış.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- fiyat girme yeri-->
                        <div class="row">
                            @php
                                $currency = $tour->currency ?? 'TRY';
                                $sym = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : '₺');
                                $maxAdult = 0; $maxChild = 0; $maxInfant = 0;
                                if (!empty($tour->date_prices) && is_array($tour->date_prices)) {
                                    foreach ($tour->date_prices as $d => $p) {
                                        if (is_array($p)) {
                                            $maxAdult = max($maxAdult, (float)($p['adult'] ?? 0));
                                            $maxChild = max($maxChild, (float)($p['child'] ?? 0));
                                            $maxInfant = max($maxInfant, (float)($p['infant'] ?? 0));
                                        }
                                    }
                                }
                            @endphp
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-user"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Yetişkin Fiyatı</span>
                                        <span class="info-box-number">{{ $sym }}{{ number_format($maxAdult, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-child"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Çocuk Fiyatı</span>
                                        <span class="info-box-number">{{ $sym }}{{ number_format($maxChild, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-baby"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Bebek Fiyatı</span>
                                        <span class="info-box-number">{{ $sym }}{{ number_format($maxInfant, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- notlar yeri -->
                        @if($tour->notes)
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Notlar:</strong> {{ $tour->notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <!-- son biletler yeri -->
                @if($tour->tickets->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-ticket-alt"></i> Son Biletler
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Takip No</th>
                                        <th>Müşteri</th>
                                        <th>Tur Tarihi</th>
                                        <th>Toplam Fiyat</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tour->tickets as $ticket)
                                        <tr>
                                            <td>{{ $ticket->tracking_no }}</td>
                                            <td>{{ $ticket->customer_name }}</td>
                                            <td>{{ $ticket->tour_date->format('d.m.Y') }}</td>
                                            <td>{{ $ticket->formatted_total_price }}</td>
                                            <!-- made by @hllgkx.0 -->
                                            <td>
                                                @if($ticket->is_active)
                                                    <span class="badge badge-success">Aktif</span>
                                                @else
                                                    <span class="badge badge-danger">Pasif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.tickets.show', $ticket) }}" 
                                                   class="btn btn-sm btn-info" title="Görüntüle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <!-- sağ kolon -->
            <div class="col-md-4">
                <!-- istatistikler bölümü-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">İstatistikler</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-ticket-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Toplam Bilet</span>
                                <span class="info-box-number">{{ $tour->total_tickets }}</span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Aktif Bilet</span>
                                <span class="info-box-number">{{ $tour->active_tickets }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- hızlı işlemler bölümü -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Hızlı İşlemler</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> Düzenle
                        </a>
                        
                        <a href="{{ route('admin.tickets.create') }}?tour_id={{ $tour->id }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-plus"></i> Bu Tur İçin Bilet Oluştur
                        </a>
                        
                        <form action="{{ route('admin.tours.destroy', $tour) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Bu turu silmek istediğinizden emin misiniz?')">
                                <i class="fas fa-trash"></i> Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop 
<!-- end of the code -->
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var GMAPS_API_KEY = @json(config('services.google.maps_api_key', env('GOOGLE_MAPS_API_KEY'))) || 'AIzaSyBB5xkVxJJtkagn08AjRfE9pP3BqA8PvjM';
    function loadGMaps(cb){
        if (window.google && window.google.maps) return cb();
        if (!GMAPS_API_KEY) { console.error('Google Maps API anahtarı tanımlı değil'); return; }
        if (window.__gmapsShowLoading) { (window.__gmapsShowCallbacks = window.__gmapsShowCallbacks || []).push(cb); return; }
        window.__gmapsShowLoading = true; window.__gmapsShowCallbacks = [cb];
        var s = document.createElement('script');
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(GMAPS_API_KEY) + '&libraries=geometry&language=tr';
        s.async = true; s.defer = true;
        s.onload = function(){ var cbs = window.__gmapsShowCallbacks || []; cbs.forEach(function(f){ try{f();}catch(e){} }); window.__gmapsShowCallbacks = []; };
        document.head.appendChild(s);
    }
    loadGMaps(function(){
        var el = document.getElementById('service-area-map-show');
        if (!el) return;
        var map = new google.maps.Map(el, {
            center: {lat: 39.0, lng: 35.0},
            zoom: 5,
            streetViewControl: false,
            mapTypeControl: false,
            fullscreenControl: true
        });
        var data = @json($tour->service_areas ?? null);
        if (data) {
            var bounds = new google.maps.LatLngBounds();
            var pointsCount = 0;
            function drawPolygon(rings, times){
                var paths = (rings || []).map(function(ring){
                    return ring.map(function(coord){
                        var lat = parseFloat(coord[1]);
                        var lng = parseFloat(coord[0]);
                        var p = { lat: lat, lng: lng };
                        pointsCount++;
                        bounds.extend(p);
                        return p;
                    });
                });
                if (!paths.length) return;
                var poly = new google.maps.Polygon({
                    paths: paths,
                    strokeColor: '#007bff',
                    strokeOpacity: 0.9,
                    strokeWeight: 2,
                    fillColor: '#007bff',
                    fillOpacity: 0.15,
                    clickable: !!(times && times.length)
                });
                poly.setMap(map);
                if (times && times.length) {
                    var info = new google.maps.InfoWindow({
                        content: '<div style="font-size:13px;"><strong>Saatler:</strong><br>' + times.map(function(t){
                            return '<span style="display:inline-block;margin:2px 4px 2px 0;padding:2px 6px;background:#e7f1ff;color:#0d6efd;border-radius:3px;font-weight:500;">' + t + '</span>';
                        }).join('') + '</div>'
                    });
                    poly.addListener('click', function(e){
                        info.setPosition(e.latLng);
                        info.open(map);
                    });
                }
            }
            if (data.type === 'FeatureCollection' && Array.isArray(data.features)) {
                data.features.forEach(function(feat){
                    if (!feat || !feat.geometry) return;
                    var times = (feat.properties && Array.isArray(feat.properties.times)) ? feat.properties.times.slice().sort() : [];
                    if (feat.geometry.type === 'Polygon') {
                        drawPolygon(feat.geometry.coordinates, times);
                    } else if (feat.geometry.type === 'MultiPolygon') {
                        feat.geometry.coordinates.forEach(function(poly){ drawPolygon(poly, times); });
                    }
                });
            } else if (data.type === 'Polygon') {
                drawPolygon(data.coordinates, []);
            } else if (data.type === 'MultiPolygon') {
                data.coordinates.forEach(function(poly){ drawPolygon(poly, []); });
            }
            if (pointsCount > 0) {
                try { map.fitBounds(bounds); } catch(e) {}
            }
        }
    });
});
</script>
@endpush