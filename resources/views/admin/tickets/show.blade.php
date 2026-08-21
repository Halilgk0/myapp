@extends('layouts.admin')

@section('title', 'Bilet Detayları')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <!-- Bilet Bilgileri -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tur Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <dt>Bilet Numarası</dt>
                                    <dd><strong>{{ $ticket->tracking_no }}</strong></dd>

                                    <dt>Voucher No</dt>
                                    <dd>{{ $ticket->voucher_no ?: 'Belirtilmemiş' }}</dd>

                                    <dt>Müşteri Adı</dt>
                                    <dd>{{ $ticket->customer_name }}</dd>

                                    <dt>Müşteri Telefonu</dt>
                                    <dd>{{ $ticket->customer_phone }}</dd>

                                    @if($ticket->customer_nationality)
                                    <dt>Müşteri Milliyeti</dt>
                                    <dd>
                                        <span class="badge badge-primary">{{ $ticket->nationality_name }}</span>
                                    </dd>
                                    @endif

                                    <dt>Tur Adı</dt>
                                    <dd>{{ $ticket->tour_name }}</dd>

                                    <dt>Tur Tarihi</dt>
                                    <dd>{{ $ticket->tour_date->format('d.m.Y') }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt>Alınış Saati</dt>
                                    <dd>{{ $ticket->pickup_time ? \Carbon\Carbon::parse($ticket->pickup_time)->format('H:i') : '-' }}</dd>
                                    
                                    <dt>Alınış Yeri</dt>
                                    <dd>{{ $ticket->pickup_location }}</dd>
                                    
                                    <dt>Oda Numarası</dt>
                                    <dd>{{ $ticket->room_number ?: 'Belirtilmemiş' }}</dd>
                                    
                                    <dt>Satış Acentası</dt>
                                    <dd>{{ $ticket->sales_agency }}</dd>
                                    
                                    <dt>Tur Ülkesi</dt>
                                    <dd>{{ $ticket->tour_country }}</dd>
                                    
                                    <dt>Tur Bölgesi</dt>
                                    <dd>{{ $ticket->tour_region }}</dd>
                                </dl>
                            </div>
                        </div>
                        
                        @if($ticket->passport_numbers)
                        <div class="row mt-3">
                            <div class="col-12">
                                <dt>Pasaport Numaraları</dt>
                                <dd>{{ $ticket->passport_numbers }}</dd>
                            </div>
                        </div>
                        @endif
                        
                        @if($ticket->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <dt>Notlar</dt>
                                <dd>{{ $ticket->notes }}</dd>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Yolcu Bilgileri -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Yolcu Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $passengerCurr = strtoupper($ticket->base_currency ?? $ticket->currency ?? 'TRY');
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Yolcu Tipi</th>
                                        <th>Adet</th>
                                        <th>Kişi Başı Fiyat</th>
                                        <th>Toplam</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ticket->passengers as $passenger)
                                    <tr>
                                        <td>{{ $passenger->passenger_type_label }}</td>
                                        <td>{{ $passenger->quantity }}</td>
                                        <td>{{ $passenger->formatted_price_per_person }} {{ $passengerCurr }}</td>
                                        <td>{{ $passenger->formatted_total_price }} {{ $passengerCurr }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th colspan="3">Toplam Yolcu</th>
                                        <th>{{ $ticket->total_passengers }} kişi</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Fiyat Özeti -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calculator"></i> Fiyat Özeti</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $baseCurr = strtoupper($ticket->base_currency ?? $ticket->currency ?? 'TRY');
                            $saleCurr = strtoupper($ticket->sale_currency ?? $ticket->currency ?? 'TRY');
                            $restCurr = strtoupper($ticket->rest_adjustment_currency ?? $baseCurr);
                            $restAmount = (float) ($ticket->rest_adjustment_amount ?? 0);
                            $restDisplayCurr = $restAmount > 0 ? $restCurr : $baseCurr;
                            $currClass = function ($c) {
                                return match($c) {
                                    'TRY' => 'text-success',
                                    'EUR' => 'text-primary',
                                    'USD' => 'text-danger',
                                    'GBP' => 'text-info',
                                    'RUB' => 'text-warning',
                                    default => 'text-secondary',
                                };
                            };
                            // Taban fiyat (rest öncesi)
                            $baseTotal = ($ticket->adult_count * $ticket->adult_price) 
                                       + ($ticket->child_count * $ticket->child_price) 
                                       + ($ticket->infant_count * $ticket->infant_price);
                            if ($baseTotal <= 0) {
                                $passengerTotal = (float) ($ticket->passengers?->sum('total_price') ?? 0);
                                $baseTotal = $passengerTotal > 0 ? $passengerTotal : (float) ($ticket->total_price ?? 0);
                            }
                            $restConverted = $ticket->rest_converted_amount;
                            $restNote = null;
                            if ($restAmount > 0 && $restConverted !== null && $restCurr !== $baseCurr) {
                                $restNote = number_format($restAmount, 2) . ' ' . $restCurr .
                                    ' ≈ ' . number_format($restConverted, 2) . ' ' . $baseCurr;
                            }
                        @endphp
                        <table class="table table-sm mb-0">
                            <tr>
                                <th>Toplam Fiyat (Taban):</th>
                                <td class="text-right {{ $currClass($baseCurr) }}">
                                    <strong>{{ number_format($baseTotal, 2) }} {{ $baseCurr }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Rest (Admin Geliri):</th>
                                <td class="text-right">
                                    <strong class="text-warning">
                                        {{ number_format($restAmount, 2) }} {{ $restDisplayCurr }}
                                    </strong>
                                    @if($restAmount > 0 && $restNote)
                                        <div class="text-muted small">{{ $restNote }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Araç Bilgileri -->
                @if($ticket->vehicle)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Araç Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <dl>
                            <dt>Plaka</dt>
                            <dd>{{ $ticket->vehicle->plate_number }}</dd>
                            
                            <dt>Marka/Model</dt>
                            <dd>{{ $ticket->vehicle->brand }} {{ $ticket->vehicle->model }}</dd>
                            
                            <dt>Kapasite</dt>
                            <dd>{{ $ticket->vehicle->capacity }} kişi</dd>
                            
                            @if($ticket->vehicle->driver)
                            <dt>Şoför</dt>
                            <dd>{{ $ticket->vehicle->driver->name }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
                @endif

                <!-- Bilet Durumu -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bilet Durumu</h3>
                    </div>
                    <div class="card-body">
                        <dl>
                            <dt>Durum</dt>
                            <dd>
                                @if($ticket->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Pasif</span>
                                @endif
                            </dd>

                            @if($ticket->is_route_start || !is_null($ticket->route_order))
                                <dt>Rota Sırası</dt>
                                <dd>
                                    @if($ticket->is_route_start)
                                        <span class="badge badge-success"><i class="fas fa-flag"></i> Başlangıç</span>
                                    @else
                                        <span class="badge badge-primary">{{ $ticket->route_order }}. durak</span>
                                    @endif
                                    @if($ticket->route_optimized_at)
                                        <br><small class="text-muted">Optimize edildi: {{ \Carbon\Carbon::parse($ticket->route_optimized_at)->format('d.m.Y H:i') }}</small>
                                    @endif
                                </dd>
                            @endif

                            <dt>Oluşturulma Tarihi</dt>
                            <dd>{{ $ticket->created_at->format('d.m.Y H:i') }}</dd>

                            <dt>Son Güncelleme</dt>
                            <dd>{{ $ticket->updated_at->format('d.m.Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop 
<!-- end of the code-->