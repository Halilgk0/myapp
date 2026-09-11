@extends('layouts.admin')

@section('title', __('Tur Detayları'))

@section('content')
    <div class="container-fluid tour-page">
        <div class="row">
            <div class="col-md-8">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Tur Bilgileri') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <!-- made by @hllgkx.0 -->
                                    <dt>{{ __('Tur Adı') }}</dt>
                                    <dd><strong>{{ $tour->name }}</strong></dd>

                                    <dt>{{ __('Ülke/Şehir') }}</dt>
                                    <dd>{{ $tour->country }} / {{ $tour->city }}</dd>



                                    <dt>{{ __('Para Birimi') }}</dt>
                                    <dd>{{ $tour->currency }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt>{{ __('Durum') }}</dt>
                                    <dd>
                                        <span class="badge {{ $tour->status_badge }}">{{ __($tour->status) }}</span>
                                    </dd>

                                    <dt>{{ __('Maksimum Kapasite') }}</dt>
                                    <dd>
                                        @if($tour->max_capacity)
                                            <span class="badge badge-secondary">{{ __(':count kişi', ['count' => $tour->max_capacity]) }}</span>
                                        @else
                                            <span class="text-muted">{{ __('Sınırsız') }}</span>
                                        @endif
                                    </dd>

                                    <dt>{{ __('Kayıt Tarihi') }}</dt>
                                    <dd>{{ $tour->created_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</dd>

                                    <dt>{{ __('Son Güncelleme') }}</dt>
                                    <dd>{{ $tour->updated_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</dd>
                                </dl>
                            </div>
                        </div>

                        @if($tour->description)
                        <div class="row">
                            <div class="col-12">
                                <dt>{{ __('Açıklama') }}</dt>
                                <dd>{{ $tour->description }}</dd>
                            </div>
                        </div>
                        @endif
<!-- made by @hllgkx.0 -->

                        <!-- Servis Alanları (Poligon) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="ad-card mb-3">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-draw-polygon"></i> {{ __('Servis Alanları') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="service-area-map-show" style="height: 320px; width: 100%; border:1px solid #ced4da; border-radius:4px;"></div>
                                        @if(empty($tour->service_areas))
                                            <small class="text-muted d-block mt-2">{{ __('Bu tur için servis alanı tanımlanmamış.') }}</small>
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
                                        <span class="info-box-text">{{ __('Yetişkin Fiyatı') }}</span>
                                        <span class="info-box-number">{{ $sym }}{{ number_format($maxAdult, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-child"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ __('Çocuk Fiyatı') }}</span>
                                        <span class="info-box-number">{{ $sym }}{{ number_format($maxChild, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-baby"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ __('Bebek Fiyatı') }}</span>
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
                                    <strong>{{ __('Notlar') }}:</strong> {{ $tour->notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <!-- son biletler yeri -->
                @if($tour->tickets->count() > 0)
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-ticket-alt"></i> {{ __('Son Biletler') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="ad-table table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Takip No') }}</th>
                                        <th>{{ __('Müşteri') }}</th>
                                        <th>{{ __('Tur Tarihi') }}</th>
                                        <th>{{ __('Toplam Fiyat') }}</th>
                                        <th>{{ __('Durum') }}</th>
                                        <th>{{ __('İşlemler') }}</th>
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
                                                    <span class="badge badge-success">{{ __('Aktif') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('Pasif') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.tickets.show', $ticket) }}"
                                                   class="btn btn-sm btn-info" title="{{ __('Görüntüle') }}">
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
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('İstatistikler') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-ticket-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Toplam Bilet') }}</span>
                                <span class="info-box-number">{{ $tour->total_tickets }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Aktif Bilet') }}</span>
                                <span class="info-box-number">{{ $tour->active_tickets }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- hızlı işlemler bölümü -->
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Hızlı İşlemler') }}</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> {{ __('Düzenle') }}
                        </a>

                        <a href="{{ route('admin.tickets.create') }}?tour_id={{ $tour->id }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-plus"></i> {{ __('Bu Tur İçin Bilet Oluştur') }}
                        </a>

                        <form action="{{ route('admin.tours.destroy', $tour) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('{{ __('Bu turu silmek istediğinizden emin misiniz?') }}')">
                                <i class="fas fa-trash"></i> {{ __('Sil') }}
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
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var token = {!! json_encode(config('services.mapbox.access_token'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    var el = document.getElementById('service-area-map-show');
    if (!el || !token || typeof mapboxgl === 'undefined') return;
    mapboxgl.accessToken = token;
    var timesLabel = {!! json_encode(__('Saatler'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    var map = new mapboxgl.Map({ container: el, style: 'mapbox://styles/mapbox/streets-v12', center: [35, 39], zoom: 5, language: {!! json_encode(app()->getLocale(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!} });
    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
    var data = {!! json_encode($tour->service_areas ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    map.on('load', function(){
        if (!data) return;
        var features = [];
        function addFeature(geometry, times) {
            if (geometry) features.push({ type: 'Feature', geometry: geometry, properties: { times: (times || []).slice().sort().join(', ') } });
        }
        if (data.type === 'FeatureCollection') (data.features || []).forEach(function(f){ addFeature(f.geometry, f.properties && f.properties.times); });
        else if (data.type === 'Feature') addFeature(data.geometry, data.properties && data.properties.times);
        else if (data.type === 'Polygon' || data.type === 'MultiPolygon') addFeature(data, []);
        if (!features.length) return;
        map.addSource('service-areas', { type: 'geojson', data: { type: 'FeatureCollection', features: features } });
        map.addLayer({ id: 'service-areas-fill', type: 'fill', source: 'service-areas', paint: { 'fill-color': '#007bff', 'fill-opacity': 0.15 } });
        map.addLayer({ id: 'service-areas-line', type: 'line', source: 'service-areas', paint: { 'line-color': '#007bff', 'line-width': 2 } });
        var bounds = new mapboxgl.LngLatBounds();
        features.forEach(function(feature){
            var rings = feature.geometry.type === 'Polygon' ? feature.geometry.coordinates : feature.geometry.coordinates.flat();
            rings.forEach(function(ring){ ring.forEach(function(coord){ bounds.extend(coord); }); });
        });
        map.fitBounds(bounds, { padding: 35, maxZoom: 12 });
        map.on('click', 'service-areas-fill', function(e){
            var times = e.features[0].properties.times;
            if (times) new mapboxgl.Popup().setLngLat(e.lngLat).setHTML('<strong>' + timesLabel + ':</strong><br>' + times).addTo(map);
        });
        map.on('mouseenter', 'service-areas-fill', function(){ map.getCanvas().style.cursor = 'pointer'; });
        map.on('mouseleave', 'service-areas-fill', function(){ map.getCanvas().style.cursor = ''; });
    });
});
</script>
@endpush