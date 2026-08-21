@extends('layouts.driver')

@section('title', 'Araç Bilgisi')

@section('content')
<div class="mb-4">
    <h1 style="font-size:22px;font-weight:700;color:var(--dr-text);margin:0 0 4px">
        <i data-lucide="car" style="width:22px;height:22px;display:inline-block;vertical-align:-3px;margin-right:6px;color:var(--dr-accent)"></i>
        Araç Detayları
    </h1>
    <p style="color:var(--dr-text-muted);font-size:13px;margin:0">Size atanmış aracın bilgileri aşağıda görüntülenmektedir.</p>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="clipboard-list" style="width:16px;height:16px"></i> Araç Bilgileri
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold">Plaka</td>
                                <td><span class="badge bg-primary">{{ $vehicle->plate_number }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Marka</td>
                                <td>{{ $vehicle->brand }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Model</td>
                                <td>{{ $vehicle->model }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Araç Tipi</td>
                                <td>{{ $vehicle->vehicle_type }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Renk</td>
                                <td>{{ $vehicle->color }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Kapasite</td>
                                <td>{{ $vehicle->capacity }} kişilik</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold">Durum</td>
                                <td>
                                    <span class="badge bg-{{ $vehicle->is_active ? 'success' : 'danger' }}">
                                        {{ $vehicle->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Çevrimiçi</td>
                                <td>
                                    <span class="badge bg-{{ $vehicle->isOnline() ? 'success' : 'danger' }}">
                                        {{ $vehicle->isOnline() ? 'Çevrimiçi' : 'Çevrimdışı' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Son Konum</td>
                                <td>
                                    @if($vehicle->getCurrentLocationAttribute())
                                        {{ $vehicle->getCurrentLocationAttribute()->created_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Konum bilgisi yok</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Toplam Bilet</td>
                                <td>{{ $vehicle->tickets->count() }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Aktif Bilet</td>
                                <td>{{ $vehicle->tickets->where('is_active', true)->count() }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Konum Kaydı</td>
                                <td>{{ $vehicle->locations->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($vehicle->notes)
                <div class="mt-3">
                    <h5 class="fw-semibold" style="font-size:14px">Notlar</h5>
                    <p class="text-muted mb-0">{{ $vehicle->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($vehicle->image)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="image" style="width:16px;height:16px"></i> Araç Fotoğrafı
                </h3>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $vehicle->image) }}"
                     alt="Araç Fotoğrafı"
                     class="img-fluid"
                     style="max-height:300px;border-radius:var(--dr-radius-sm)">
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        @if($vehicle->getCurrentLocationAttribute())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="map-pin" style="width:16px;height:16px"></i> Mevcut Konum
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Koordinatlar</span>
                        <span class="info-box-number" style="font-size:0.85rem">
                            {{ number_format($vehicle->getCurrentLocationAttribute()->latitude, 6) }},
                            {{ number_format($vehicle->getCurrentLocationAttribute()->longitude, 6) }}
                        </span>
                        <span class="info-box-text">
                            <small class="text-muted">
                                {{ $vehicle->getCurrentLocationAttribute()->created_at->format('H:i:s') }}
                            </small>
                        </span>
                    </div>
                </div>

                @if($vehicle->getCurrentLocationAttribute()->speed)
                <div class="mt-3 small text-muted">
                    <i data-lucide="gauge" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;margin-right:3px"></i>
                    Hız: {{ number_format($vehicle->getCurrentLocationAttribute()->speed, 1) }} km/h
                </div>
                @endif

                @if($vehicle->getCurrentLocationAttribute()->heading)
                <div class="mt-1 small text-muted">
                    <i data-lucide="compass" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;margin-right:3px"></i>
                    Yön: {{ $vehicle->getCurrentLocationAttribute()->heading }}°
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($vehicle->tickets->where('is_active', true)->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="ticket" style="width:16px;height:16px"></i> Aktif Biletler
                </h3>
            </div>
            <div class="card-body">
                @foreach($vehicle->tickets->where('is_active', true)->take(3) as $ticket)
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-ticket-alt"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $ticket->customer_name }}</span>
                        <span class="info-box-number" style="font-size:0.85rem">{{ $ticket->tracking_no }}</span>
                        <span class="info-box-text">
                            <small class="text-muted">
                                {{ $ticket->tour_name }} &bull; {{ $ticket->tour_date }}
                            </small>
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
