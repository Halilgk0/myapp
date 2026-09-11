@extends('layouts.admin')

@section('title', __('Bilet Detayı') . ' - ' . $ticket->tracking_no)

@push('css')
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css">
    <style>
        .info-item { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:10px 0; border-bottom:1px solid var(--ad-border); }
        .info-item:last-child { border-bottom:none; }
        .info-label { font-size:13px; color:var(--ad-text-muted); }
        .info-value { font-size:14px; color:var(--ad-text); text-align:right; overflow-wrap:anywhere; }
        .price-row { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:10px 0; border-bottom:1px solid var(--ad-border); font-size:14px; }
        .price-row:last-child { border-bottom:none; }
        .price-total { margin-top:8px; padding-top:14px; border-top:2px solid var(--ad-border); }
        #ticket-pickup-map { height:320px; width:100%; }
    </style>
@endpush

@section('content')
<div class="ad-page-header d-flex justify-content-between align-items-center" style="flex-wrap:wrap;gap:16px">
    <div>
        <h1 class="ad-page-title">{{ __('Bilet Detayı') }}</h1>
        <p class="ad-page-subtitle" style="font-family:monospace;font-size:14px">{{ $ticket->tracking_no }}</p>
    </div>
    <div class="d-flex" style="gap:8px">
        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="ad-btn ad-btn-warning ad-btn-sm"><i data-lucide="edit-2"></i><span>{{ __('Düzenle') }}</span></a>
        <a href="{{ route('admin.tickets.index') }}" class="ad-btn ad-btn-secondary ad-btn-sm"><i data-lucide="arrow-left"></i><span>{{ __('Geri') }}</span></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="ad-card mb-3">
            <div class="ad-card-header d-flex justify-content-between align-items-center">
                <h3 class="ad-card-title"><i data-lucide="info"></i>{{ __('Bilet Bilgileri') }}</h3>
                @if($ticket->is_active)<span class="ad-badge ad-badge-success">{{ __('Aktif') }}</span>@else<span class="ad-badge ad-badge-danger">{{ __('Pasif') }}</span>@endif
            </div>
            <div class="ad-card-body"><div class="row g-3">
                <div class="col-md-6">
                    <div class="info-item"><span class="info-label">{{ __('Takip No') }}</span><span class="info-value" style="font-family:monospace">{{ $ticket->tracking_no }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Voucher No') }}</span><span class="info-value">{{ $ticket->voucher_no ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Giriş Tarihi') }}</span><span class="info-value">{{ $ticket->entry_date ? $ticket->entry_date->format('d.m.Y') : '-' }}</span></div>
                </div>
                <div class="col-md-6">
                    <div class="info-item"><span class="info-label">{{ __('Giriş Saati') }}</span><span class="info-value">{{ $ticket->entry_time ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Satış Acentası') }}</span><span class="info-value">{{ $ticket->sales_agency ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Oluşturulma') }}</span><span class="info-value">{{ $ticket->created_at->format('d.m.Y H:i') }}</span></div>
                </div>
            </div></div>
        </div>

        <div class="ad-card mb-3">
            <div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="map-pin"></i>{{ __('Tur Bilgileri') }}</h3></div>
            <div class="ad-card-body"><div class="row g-3">
                <div class="col-md-6">
                    <div class="info-item"><span class="info-label">{{ __('Tur Adı') }}</span><span class="info-value" style="font-weight:600">{{ $ticket->tour_name ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Ülke') }}</span><span class="info-value">{{ $ticket->tour_country ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Şehir') }}</span><span class="info-value">{{ $ticket->tour_region ?: '-' }}</span></div>
                </div>
                <div class="col-md-6">
                    <div class="info-item"><span class="info-label">{{ __('Tur Tarihi') }}</span><span class="info-value" style="font-weight:600">{{ $ticket->tour_date ? $ticket->tour_date->format('d.m.Y') : '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Alış Saati') }}</span><span class="info-value">{{ $ticket->pickup_time ? \Carbon\Carbon::parse($ticket->pickup_time)->format('H:i') : '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Alış Noktası') }}</span><span class="info-value">{{ $ticket->pickup_location ?: '-' }}</span></div>
                </div>
            </div></div>
        </div>

        <div class="ad-card mb-3">
            <div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="map"></i>{{ __('Alış Yeri Haritası') }}</h3></div>
            <div class="ad-card-body" style="padding:0">
                @if($ticket->location)<div id="ticket-pickup-map" aria-label="{{ __('Alış yeri haritası') }}"></div>@else<p class="text-muted p-3 mb-0">{{ __('Bu bilet için koordinat bilgisi bulunmuyor.') }}</p>@endif
            </div>
        </div>

        <div class="ad-card mb-3">
            <div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="user"></i>{{ __('Müşteri Bilgileri') }}</h3></div>
            <div class="ad-card-body"><div class="row g-3">
                <div class="col-md-6">
                    <div class="info-item"><span class="info-label">{{ __('Ad Soyad') }}</span><span class="info-value" style="font-weight:600">{{ $ticket->customer_name ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Telefon') }}</span><span class="info-value">{{ $ticket->customer_phone ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('E-posta') }}</span><span class="info-value">{{ $ticket->customer_email ?: '-' }}</span></div>
                </div>
                <div class="col-md-6">
                    <div class="info-item"><span class="info-label">{{ __('Milliyet') }}</span><span class="info-value">{{ $ticket->nationality_name ?: $ticket->customer_nationality ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Otel / Oda') }}</span><span class="info-value">{{ $ticket->room_number ?: '-' }}</span></div>
                    <div class="info-item"><span class="info-label">{{ __('Pasaport No') }}</span><span class="info-value">{{ $ticket->passport_numbers ?: '-' }}</span></div>
                </div>
            </div>@if($ticket->notes)<div class="info-item mt-2"><span class="info-label">{{ __('Notlar') }}</span><span class="info-value">{{ $ticket->notes }}</span></div>@endif</div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ad-card mb-3">
            <div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="users"></i>{{ __('Yolcular') }}</h3></div>
            <div class="ad-card-body" style="padding:0"><div class="table-responsive"><table class="ad-table" style="font-size:13px"><thead><tr><th>{{ __('Tip') }}</th><th>{{ __('Adet') }}</th><th>{{ __('Birim') }}</th><th>{{ __('Toplam') }}</th></tr></thead><tbody>
                @forelse($ticket->passengers as $passenger)<tr><td>{{ $passenger->passenger_type_label }}</td><td>{{ $passenger->quantity }}</td><td>{{ $passenger->formatted_price_per_person }}</td><td style="font-weight:600">{{ $passenger->formatted_total_price }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted" style="padding:24px">{{ __('Yolcu bilgisi yok') }}</td></tr>@endforelse
            </tbody></table></div></div>
        </div>

        <div class="ad-card mb-3" style="border-left:4px solid var(--ad-success)">
            <div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="calculator"></i>{{ __('Fiyat Özeti') }}</h3></div>
            <div class="ad-card-body">
                @php
                    $baseCurr = strtoupper($ticket->base_currency ?? $ticket->currency ?? 'TRY');
                    $saleCurr = strtoupper($ticket->sale_currency ?? $ticket->currency ?? 'TRY');
                    $restCurr = strtoupper($ticket->rest_adjustment_currency ?? $baseCurr);
                    $restAmount = (float) ($ticket->rest_adjustment_amount ?? 0);
                    $baseTotal = ($ticket->adult_count * $ticket->adult_price) + ($ticket->child_count * $ticket->child_price) + ($ticket->infant_count * $ticket->infant_price);
                    if ($baseTotal <= 0) $baseTotal = (float) ($ticket->passengers?->sum('total_price') ?: $ticket->total_price);
                @endphp
                <div class="price-row"><span>{{ __('Taban Fiyat') }}</span><strong>{{ number_format($baseTotal, 2) }} {{ $baseCurr }}</strong></div>
                <div class="price-row"><span>{{ __('Rest (Admin Geliri)') }}</span><strong class="text-warning">{{ number_format($restAmount, 2) }} {{ $restAmount > 0 ? $restCurr : $baseCurr }}</strong></div>
                <div class="price-row"><span>{{ __('Admin Payı') }}</span><strong>{{ number_format($ticket->owner_share_amount ?? $baseTotal, 2) }} {{ $baseCurr }}</strong></div>
                <div class="price-row price-total"><span>{{ __('Satış Fiyatı') }}</span><strong class="text-success" style="font-size:18px">{{ number_format($ticket->total_price, 2) }} {{ $saleCurr }}</strong></div>
            </div>
        </div>

        @if($ticket->vehicle)<div class="ad-card mb-3"><div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="truck"></i>{{ __('Araç Bilgisi') }}</h3></div><div class="ad-card-body">
            <div class="info-item"><span class="info-label">{{ __('Plaka') }}</span><span class="info-value" style="font-weight:600">{{ $ticket->vehicle->plate_number }}</span></div>
            <div class="info-item"><span class="info-label">{{ __('Model') }}</span><span class="info-value">{{ $ticket->vehicle->brand }} {{ $ticket->vehicle->model }}</span></div>
            @if($ticket->vehicle->driver)<div class="info-item"><span class="info-label">{{ __('Şoför') }}</span><span class="info-value">{{ $ticket->vehicle->driver->name }}</span></div>@endif
        </div></div>@endif

        <div class="ad-card"><div class="ad-card-header"><h3 class="ad-card-title"><i data-lucide="flag"></i>{{ __('Bilet Durumu') }}</h3></div><div class="ad-card-body">
            <div class="info-item"><span class="info-label">{{ __('Durum') }}</span><span class="info-value">@if($ticket->is_active)<span class="ad-badge ad-badge-success">{{ __('Aktif') }}</span>@else<span class="ad-badge ad-badge-danger">{{ __('Pasif') }}</span>@endif</span></div>
            @if($ticket->is_route_start || !is_null($ticket->route_order))<div class="info-item"><span class="info-label">{{ __('Rota Sırası') }}</span><span class="info-value">@if($ticket->is_route_start)<span class="ad-badge ad-badge-success">{{ __('Başlangıç') }}</span>@else<span class="ad-badge ad-badge-primary">{{ __(':n. durak', ['n' => $ticket->route_order]) }}</span>@endif</span></div>@endif
            <div class="info-item"><span class="info-label">{{ __('Son Güncelleme') }}</span><span class="info-value">{{ $ticket->updated_at->format('d.m.Y H:i') }}</span></div>
        </div></div>
    </div>
</div>
@endsection

@if($ticket->location)
@push('js')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var token = {!! json_encode(config('services.mapbox.access_token'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    if (!token || typeof mapboxgl === 'undefined') return;
    mapboxgl.accessToken = token;
    var location = {!! json_encode(['lat' => $ticket->location->latitude, 'lng' => $ticket->location->longitude], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    var map = new mapboxgl.Map({container:'ticket-pickup-map',style:'mapbox://styles/mapbox/streets-v12',center:[location.lng,location.lat],zoom:14,interactive:false});
    new mapboxgl.Marker({color:'#ef4444'}).setLngLat([location.lng,location.lat]).addTo(map);
});
</script>
@endpush
@endif

@push('js')
<script>lucide.createIcons();</script>
@endpush
