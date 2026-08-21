@extends('layouts.admin')

@section('title', 'Rehber Detayı')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rehber Bilgileri</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.guides.edit', $guide) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Düzenle
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Ad Soyad:</strong>
                            <p class="text-muted">{{ $guide->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>E-posta:</strong>
                            <p class="text-muted">{{ $guide->email }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Telefon:</strong>
                            <p class="text-muted">{{ $guide->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Durum:</strong>
                            <p class="text-muted">
                                @if($guide->status == 'Aktif')
                                    <span class="badge badge-success">{{ $guide->status }}</span>
                                @elseif($guide->status == 'İzinli')
                                    <span class="badge badge-warning">{{ $guide->status }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $guide->status }}</span>
                                @endif
                                <!-- made by @hllgkx.0 -->
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Rehber Belgesi No:</strong>
                            <p class="text-muted">{{ $guide->license_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Belge Geçerlilik Tarihi:</strong>
                            <p class="text-muted">{{ $guide->license_expiry ? $guide->license_expiry->format('d.m.Y') : '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <strong>İşe Başlama Tarihi:</strong>
                            <p class="text-muted">{{ $guide->hire_date ? $guide->hire_date->format('d.m.Y') : '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Maaş:</strong>
                            <p class="text-muted">{{ $guide->salary ? number_format($guide->salary, 2) . ' TL' : '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Adres:</strong>
                            <p class="text-muted">{{ $guide->address ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Desteklenen Milliyetler:</strong>
                            <p class="text-muted">
                                @if($guide->supported_nationalities)
                                    @foreach($guide->supported_nationalities as $nationality)
                                        <span class="badge badge-info mr-1">
                                            {{ \App\Models\Guide::getNationalityOptions()[$nationality] ?? $nationality }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Belirtilmemiş</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($guide->notes)
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Notlar:</strong>
                                <p class="text-muted">{{ $guide->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Atanmış Şoförler</h3>
                </div>
                <div class="card-body">
                    @if($guide->drivers->count() > 0)
                        @foreach($guide->drivers as $driver)
                            <!-- made by @hllgkx.0 -->
                            <div class="mb-3 p-2 border rounded">
                                <strong>{{ $driver->name }}</strong><br>
                                <small class="text-muted">{{ $driver->email }}</small><br>
                                @if($driver->vehicle)
                                    <span class="badge badge-primary">
                                        {{ $driver->vehicle->plate_number }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Araç Atanmamış</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Bu rehbere henüz şoför atanmamıştır.</p>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">İstatistikler</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">Toplam Şoför</span>
                                    <span class="info-box-number">{{ $guide->drivers->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-success">
                                <div class="info-box-content">
                                    <span class="info-box-text">Aktif Şoför</span>
                                    <span class="info-box-number">{{ $guide->drivers->where('status', 'Aktif')->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
<!-- rehber detayı css-->
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
<!-- end of the code-->