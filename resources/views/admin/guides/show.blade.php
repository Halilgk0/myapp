@extends('layouts.admin')

@section('title', __('Rehber Detayı'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="ad-card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Rehber Bilgileri') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.guides.edit', $guide) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> {{ __('Düzenle') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Ad Soyad') }}:</strong>
                            <p class="text-muted">{{ $guide->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('E-posta') }}:</strong>
                            <p class="text-muted">{{ $guide->email }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Telefon') }}:</strong>
                            <p class="text-muted">{{ $guide->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Durum') }}:</strong>
                            <p class="text-muted">
                                @if($guide->status == 'Aktif')
                                    <span class="badge badge-success">{{ __('Aktif') }}</span>
                                @elseif($guide->status == 'İzinli')
                                    <span class="badge badge-warning">{{ __('İzinli') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __($guide->status) }}</span>
                                @endif
                                <!-- made by @hllgkx.0 -->
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Rehber Belgesi No') }}:</strong>
                            <p class="text-muted">{{ $guide->license_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Belge Geçerlilik Tarihi') }}:</strong>
                            <p class="text-muted">{{ $guide->license_expiry ? $guide->license_expiry->format('d.m.Y') : '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('İşe Başlama Tarihi') }}:</strong>
                            <p class="text-muted">{{ $guide->hire_date ? $guide->hire_date->format('d.m.Y') : '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Maaş') }}:</strong>
                            <p class="text-muted">{{ $guide->salary ? number_format($guide->salary, 2) . ' TL' : '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <strong>{{ __('Adres') }}:</strong>
                            <p class="text-muted">{{ $guide->address ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <strong>{{ __('Desteklenen Milliyetler') }}:</strong>
                            <p class="text-muted">
                                @if($guide->supported_nationalities)
                                    @foreach($guide->supported_nationalities as $nationality)
                                        <span class="badge badge-info mr-1">
                                            {{ \App\Models\Guide::getNationalityOptions()[$nationality] ?? $nationality }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">{{ __('Belirtilmemiş') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($guide->notes)
                        <div class="row">
                            <div class="col-md-12">
                                <strong>{{ __('Notlar') }}:</strong>
                                <p class="text-muted">{{ $guide->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="ad-card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Atanmış Şoförler') }}</h3>
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
                                    <span class="badge badge-secondary">{{ __('Araç Atanmamış') }}</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">{{ __('Bu rehbere henüz şoför atanmamıştır.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('İstatistikler') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Toplam Şoför') }}</span>
                                    <span class="info-box-number">{{ $guide->drivers->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-success">
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Aktif Şoför') }}</span>
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