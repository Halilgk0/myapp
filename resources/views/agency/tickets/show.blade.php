@extends('layouts.agency')

@section('title', 'Bilet Detayı - ' . $ticket->tracking_no)

@section('content')
<div class="ag-page-header ag-flex ag-justify-between ag-items-center" style="flex-wrap:wrap;gap:16px">
    <div>
        <h1 class="ag-page-title">Bilet Detayı</h1>
        <p class="ag-page-subtitle" style="font-family:monospace;font-size:14px">{{ $ticket->tracking_no }}</p>
    </div>
    <div class="ag-flex ag-gap-1">
        <a href="{{ route('agency.tickets.edit', $ticket) }}" class="ag-btn ag-btn-warning ag-btn-sm">
            <i data-lucide="edit-2"></i>
            <span>Düzenle</span>
        </a>
        <a href="{{ route('agency.tickets.index') }}" class="ag-btn ag-btn-secondary ag-btn-sm">
            <i data-lucide="arrow-left"></i>
            <span>Geri</span>
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Ticket Info -->
        <div class="ag-card ag-mb-3">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="info"></i>
                    Bilet Bilgileri
                </h3>
                @if($ticket->is_active)
                <span class="ag-badge ag-badge-success">Aktif</span>
                @else
                <span class="ag-badge ag-badge-danger">Pasif</span>
                @endif
            </div>
            <div class="ag-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Takip No</span>
                            <span class="info-value" style="font-family:monospace">{{ $ticket->tracking_no }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Voucher No</span>
                            <span class="info-value">{{ $ticket->voucher_no ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Giriş Tarihi</span>
                            <span class="info-value">{{ $ticket->entry_date ? \Carbon\Carbon::parse($ticket->entry_date)->format('d.m.Y') : '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Giriş Saati</span>
                            <span class="info-value">{{ $ticket->entry_time ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Satış Acentası</span>
                            <span class="info-value">{{ $ticket->sales_agency ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Oluşturulma</span>
                            <span class="info-value">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tour Info -->
        <div class="ag-card ag-mb-3">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="map-pin"></i>
                    Tur Bilgileri
                </h3>
            </div>
            <div class="ag-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Tur Adı</span>
                            <span class="info-value" style="font-weight:600">{{ $ticket->tour_name ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ülke</span>
                            <span class="info-value">{{ $ticket->tour_country ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Şehir</span>
                            <span class="info-value">{{ $ticket->tour_region ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Tur Tarihi</span>
                            <span class="info-value" style="font-weight:600">{{ $ticket->tour_date ? $ticket->tour_date->format('d.m.Y') : '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Alış Saati</span>
                            <span class="info-value">{{ $ticket->pickup_time ? \Carbon\Carbon::parse($ticket->pickup_time)->format('H:i') : '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Alış Noktası</span>
                            <span class="info-value">{{ $ticket->pickup_location ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="ag-card ag-mb-3">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="user"></i>
                    Müşteri Bilgileri
                </h3>
            </div>
            <div class="ag-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Ad Soyad</span>
                            <span class="info-value" style="font-weight:600">{{ $ticket->customer_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Telefon</span>
                            <span class="info-value">{{ $ticket->customer_phone }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">E-posta</span>
                            <span class="info-value">{{ $ticket->customer_email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <span class="info-label">Milliyet</span>
                            <span class="info-value">{{ $ticket->nationality_name ?? $ticket->customer_nationality ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Otel / Oda</span>
                            <span class="info-value">{{ $ticket->room_number ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Pasaport No</span>
                            <span class="info-value">{{ $ticket->passport_numbers ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Passengers -->
        <div class="ag-card ag-mb-3">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="users"></i>
                    Yolcular
                </h3>
            </div>
            <div class="ag-card-body" style="padding:0">
                <table class="ag-table" style="font-size:13px">
                    <thead>
                        <tr>
                            <th>Tip</th>
                            <th class="ag-text-center">Adet</th>
                            <th class="ag-text-right">Birim</th>
                            <th class="ag-text-right">Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ticket->passengers as $passenger)
                        <tr>
                            <td>
                                @if($passenger->passenger_type === 'adult')
                                Yetişkin
                                @elseif($passenger->passenger_type === 'child')
                                Çocuk
                                @else
                                Bebek
                                @endif
                            </td>
                            <td class="ag-text-center">{{ $passenger->quantity }}</td>
                            <td class="ag-text-right">{{ number_format($passenger->unit_price, 2) }}</td>
                            <td class="ag-text-right" style="font-weight:600">{{ number_format($passenger->total_price, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="ag-text-center ag-text-muted" style="padding:24px">Yolcu bilgisi yok</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Price Summary -->
        <div class="ag-card ag-mb-3" style="border-left:4px solid var(--ag-success)">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="calculator"></i>
                    Fiyat Özeti
                </h3>
            </div>
            <div class="ag-card-body">
                @php
                    $baseCurr = strtoupper($ticket->base_currency ?? $ticket->currency ?? 'TRY');
                    $saleCurr = strtoupper($ticket->sale_currency ?? $ticket->currency ?? 'TRY');
                    $restCurr = strtoupper($ticket->rest_adjustment_currency ?? $ticket->currency ?? $baseCurr);
                    $restConverted = $ticket->rest_converted_amount;
                    $restNote = null;
                    if ($restConverted !== null && $restCurr !== $baseCurr) {
                        $restNote = number_format($ticket->rest_adjustment_amount ?? 0, 2) . ' ' . $restCurr .
                            ' ≈ ' . number_format($restConverted, 2) . ' ' . $baseCurr;
                    }
                    $baseTotal = ($ticket->adult_count * $ticket->adult_price) 
                               + ($ticket->child_count * $ticket->child_price) 
                               + ($ticket->infant_count * $ticket->infant_price);
                    if ($baseTotal <= 0) {
                        $passengerTotal = (float) ($ticket->passengers?->sum('total_price') ?? 0);
                        $baseTotal = $passengerTotal > 0 ? $passengerTotal : (float) ($ticket->total_price ?? 0);
                    }
                @endphp
                <div class="price-row">
                    <span>Taban Fiyat</span>
                    <span style="font-weight:600">{{ number_format($baseTotal, 2) }} {{ $baseCurr }}</span>
                </div>
                @if(($ticket->rest_adjustment_amount ?? 0) > 0)
                <div class="price-row">
                    <span>Rest (Admin Geliri)</span>
                    <span class="ag-text-warning" style="font-weight:600">{{ number_format($ticket->rest_adjustment_amount, 2) }} {{ $restCurr }}</span>
                </div>
                @if($restNote)
                <div class="ag-text-muted" style="font-size:11px;margin-bottom:8px">{{ $restNote }}</div>
                @endif
                @endif
                <div class="price-row">
                    <span>Admin Payı</span>
                    <span style="font-weight:600">{{ number_format($ticket->owner_share_amount ?? $baseTotal, 2) }} {{ $baseCurr }}</span>
                </div>
                <div class="price-row price-total">
                    <span>Satış Fiyatı</span>
                    <span class="ag-text-success" style="font-weight:700;font-size:18px">{{ number_format($ticket->total_price, 2) }} {{ $saleCurr }}</span>
                </div>
            </div>
        </div>

        <!-- Vehicle Info -->
        @if($ticket->vehicle)
        <div class="ag-card">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="truck"></i>
                    Araç Bilgisi
                </h3>
            </div>
            <div class="ag-card-body">
                <div class="info-item">
                    <span class="info-label">Plaka</span>
                    <span class="info-value" style="font-weight:600">{{ $ticket->vehicle->plate_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Model</span>
                    <span class="info-value">{{ $ticket->vehicle->model ?? '-' }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('css')
<style>
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--ag-border);
}
.info-item:last-child {
    border-bottom: none;
}
.info-label {
    font-size: 13px;
    color: var(--ag-text-muted);
}
.info-value {
    font-size: 14px;
    color: var(--ag-text);
}
.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--ag-border);
    font-size: 14px;
}
.price-row:last-child {
    border-bottom: none;
}
.price-row.price-total {
    margin-top: 8px;
    padding-top: 14px;
    border-top: 2px solid var(--ag-border);
    border-bottom: none;
}
</style>
@endpush

@push('js')
<script>
    lucide.createIcons();
</script>
@endpush
