@extends('layouts.admin')

@section('title', 'Araç Detayları')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Araç Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <dt>Plaka Numarası</dt>
                                    <dd><strong>{{ $vehicle->plate_number }}</strong></dd>
                                    
                                    <dt>Marka</dt>
                                    <dd>{{ $vehicle->brand }}</dd>
                                    
                                    <dt>Model</dt>
                                    <dd>{{ $vehicle->model }}</dd>
                                    
                                    <dt>Araç Türü</dt>
                                    <dd>{{ $vehicle->vehicle_type }}</dd>
                                    
                                    <dt>Renk</dt>
                                    <dd>{{ $vehicle->color }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt>Kapasite</dt>
                                    <dd>{{ $vehicle->capacity }} kişi</dd>
                                    
                                    <dt>Durum</dt>
                                    <dd>
                                        @if($vehicle->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Pasif</span>
                                        @endif
                                    </dd>
                                    
                                    <dt>Şoför</dt>
                                    <dd>
                                        @if($vehicle->driver)
                                            <span class="badge badge-info">{{ $vehicle->driver->name }}</span>
                                            <br><small class="text-muted">{{ $vehicle->driver->phone_number }}</small>
                                        @else
                                            <span class="badge badge-secondary">Atanmamış</span>
                                        @endif
                                    </dd>
                                    
                                    <dt>Oluşturulma Tarihi</dt>
                                    <dd>{{ $vehicle->created_at->format('d.m.Y H:i') }}</dd>
                                </dl>
                            </div>
                        </div>
                        
                        @if($vehicle->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <dt>Notlar</dt>
                                <dd>{{ $vehicle->notes }}</dd>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($vehicle->image)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Araç Resmi</h3>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ asset('storage/' . $vehicle->image) }}" 
                             alt="{{ $vehicle->plate_number }}" 
                             class="img-fluid" style="max-height: 300px;">
                    </div>
                </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- İstatistikler -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">İstatistikler</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-ticket-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Toplam Bilet</span>
                                <span class="info-box-number">{{ $vehicle->tickets()->count() }}</span>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-map-marker-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Konum Kayıtları</span>
                                <span class="info-box-number">{{ $vehicle->locations()->count() }}</span>
                            </div>
                        </div>

                        @php $lastLoc = $vehicle->current_location; @endphp
                        @if($lastLoc)
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-crosshairs"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Son Konum
                                        @if($lastLoc->recorded_at && $lastLoc->recorded_at->gt(now()->subMinutes(2)))
                                            <span class="badge badge-success ml-1" style="font-size:9px;">CANLI</span>
                                        @endif
                                    </span>
                                    <span class="info-box-number" style="font-size:13px;">
                                        {{ number_format((float) $lastLoc->latitude, 5) }}, {{ number_format((float) $lastLoc->longitude, 5) }}
                                    </span>
                                    <small class="text-muted">
                                        {{ $lastLoc->recorded_at ? $lastLoc->recorded_at->diffForHumans() : '—' }}
                                        @if($lastLoc->speed)
                                            · {{ round($lastLoc->speed) }} km/s
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endif
                        
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Son Güncelleme</span>
                                <span class="info-box-number">
                                    {{ optional($vehicle->updated_at)->diffForHumans() ?? 'Bilinmiyor' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Son Biletler -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Son Biletler</h3>
                    </div>
                    <div class="card-body">
                        @forelse($vehicle->tickets()->latest()->take(5)->get() as $ticket)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $ticket->tracking_no }}</strong>
                                <br><small class="text-muted">{{ $ticket->customer_name }}</small>
                            </div>
                            <span class="badge badge-info">{{ $ticket->tour_date->format('d.m.Y') }}</span>
                        </div>
                        @empty
                        <p class="text-muted">Henüz bilet atanmamış.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop 