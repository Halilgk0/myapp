@extends('layouts.agency')

@section('title', 'Yeni Bilet Oluştur')

@section('content')
<div class="ag-page-header ag-flex ag-justify-between ag-items-center ag-mb-3" style="flex-wrap:wrap;gap:16px">
    <div>
        <h1 class="ag-page-title">Yeni Bilet Oluştur</h1>
        <p class="ag-page-subtitle">Paylaşılan turlardan bilet oluşturun</p>
    </div>
    <a href="{{ route('agency.tickets.index') }}" class="ag-btn ag-btn-secondary ag-btn-sm">
        <i data-lucide="arrow-left"></i>
        <span>Geri</span>
    </a>
</div>
<div class="container-fluid">
    <form action="{{ route('agency.tickets.store') }}" method="POST" id="ticket-create-form">
        @csrf
        <div class="row">
            <!-- Sol: Form Bölümü -->
            <div class="col-lg-8">
                <!-- Giriş Bilgileri -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sign-in-alt"></i> Giriş Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar text-primary"></i> Giriş Tarihi</label>
                                    <input type="date" class="form-control" value="{{ $defaultDate }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-clock text-info"></i> Giriş Saati</label>
                                    <input type="time" class="form-control" value="{{ $defaultTime }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-barcode text-success"></i> Voucher No *</label>
                                    <input type="text" class="form-control @error('voucher_no') is-invalid @enderror" 
                                           name="voucher_no" value="{{ old('voucher_no') }}" placeholder="Voucher numarası" required>
                                    @error('voucher_no')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tur Seçimi -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Tur Seçimi</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><i class="fas fa-route text-primary"></i> Tur Seçiniz *</label>
                            <select class="form-control @error('tour_id') is-invalid @enderror" 
                                    id="tour_id" name="tour_id" required>
                                <option value="">Tur Seçiniz</option>
                                @foreach($sharedTours as $tour)
                                    <option value="{{ $tour->id }}" 
                                            data-currency="{{ $tour->agency_currency }}"
                                            data-pickup-time="{{ $tour->pickup_time }}"
                                            data-country="{{ $tour->country }}"
                                            data-city="{{ $tour->city }}"
                                            data-name="{{ $tour->name }}"
                                            data-available-dates='{!! json_encode($tour->available_dates ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
                                            data-date-prices='{!! json_encode($tour->agency_date_prices ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'
                                            {{ old('tour_id') == $tour->id ? 'selected' : '' }}>
                                        {{ $tour->name }} - {{ $tour->country }}/{{ $tour->city }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tour_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tur Tarihi Seçimi -->
                        <div id="tour-date-section" style="display: none;">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-day text-info"></i> Tur Tarihi *</label>
                                <div class="mini-calendar">
                                    <div class="mini-cal-header">
                                        <button type="button" class="cal-nav" id="prevMonthBtn"><i class="fas fa-chevron-left"></i></button>
                                        <span class="cal-title" id="currentMonthYear">Ocak 2026</span>
                                        <button type="button" class="cal-nav" id="nextMonthBtn"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                    <div class="mini-cal-weekdays">
                                        <span>Pt</span><span>Sa</span><span>Ça</span><span>Pe</span><span>Cu</span><span>Ct</span><span>Pa</span>
                                    </div>
                                    <div class="mini-cal-grid" id="calendarGrid"></div>
                                </div>
                                <input type="hidden" id="tour_date" name="tour_date" value="{{ old('tour_date') }}" required>
                                <small class="form-text text-muted">Sadece turun açık olduğu günleri seçebilirsiniz.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Müşteri Bilgileri -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Müşteri Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-user-tie text-primary"></i> Müşteri Adı *</label>
                                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                           name="customer_name" value="{{ old('customer_name') }}" placeholder="Ad Soyad" required>
                                    @error('customer_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-phone text-success"></i> Telefon *</label>
                                    <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                                           name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+90 5xx xxx xx xx" required>
                                    @error('customer_phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-envelope text-info"></i> E-posta</label>
                                    <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                           name="customer_email" value="{{ old('customer_email') }}" placeholder="ornek@email.com">
                                    @error('customer_email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-globe text-warning"></i> Milliyet *</label>
                                    <select class="form-control @error('customer_nationality') is-invalid @enderror" 
                                            name="customer_nationality" required>
                                        <option value="">Seçiniz</option>
                                        @php
                                            $nationalities = \App\Models\Ticket::getNationalityOptions();
                                        @endphp
                                        @foreach($nationalities as $code => $name)
                                            <option value="{{ $code }}" {{ old('customer_nationality') == $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_nationality')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-hotel text-secondary"></i> Otel / Oda No</label>
                                    <input type="text" class="form-control @error('room_number') is-invalid @enderror" 
                                           name="room_number" value="{{ old('room_number') }}" placeholder="Otel adı / Oda numarası">
                                    @error('room_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_location"><i class="fas fa-map-marker-alt text-danger"></i> Alış Noktası</label>
                                    <div class="pickup-search-wrap">
                                        <input type="text" class="form-control @error('pickup_location') is-invalid @enderror"
                                               id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}"
                                               placeholder="Adres veya otel ara (servis alanı içinde)" autocomplete="off">
                                        <div class="pickup-suggestions" id="pickup-suggestions" role="listbox"></div>
                                    </div>
                                    @error('pickup_location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <div style="position:relative;">
                                        <div id="pickup-map" style="height:220px;width:100%;border:1px solid #ced4da;border-radius:4px;margin-top:8px;"></div>
                                        <div id="map-toast" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:10;background:rgba(30,30,40,.88);color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:500;pointer-events:none;opacity:0;transition:opacity .25s;white-space:nowrap;box-shadow:0 2px 12px rgba(0,0,0,.3);"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <small class="text-muted">Haritaya tıklayarak veya yukarıya yazarak seçim yapabilirsiniz.</small>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-locate-me"><i class="fas fa-location-arrow"></i> Konumumu Bul</button>
                                    </div>
                                    <input type="hidden" id="pickup_lat" name="pickup_lat" value="{{ old('pickup_lat') }}">
                                    <input type="hidden" id="pickup_lng" name="pickup_lng" value="{{ old('pickup_lng') }}">
                                    <div class="form-group mt-2 mb-0">
                                        <label for="pickup_time_input" class="mb-1"><i class="fas fa-clock text-primary"></i> Tur Saati</label>
                                        <select class="form-control form-control-sm @error('pickup_time') is-invalid @enderror"
                                                id="pickup_time_input" name="pickup_time" disabled>
                                            <option value="">Önce haritadan konum seçin</option>
                                        </select>
                                        <input type="hidden" id="pickup_time_preselect" value="{{ old('pickup_time') }}">
                                        @error('pickup_time')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Konumun bulunduğu poligonun saatleri listelenir. Opsiyonel.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-passport text-primary"></i> Pasaport Numaraları</label>
                            <textarea class="form-control @error('passport_numbers') is-invalid @enderror" 
                                      name="passport_numbers" rows="2" placeholder="Her satıra bir pasaport numarası">{{ old('passport_numbers') }}</textarea>
                            @error('passport_numbers')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Yolcu Bilgileri -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> Yolcu Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-male text-primary"></i> Yetişkin *</label>
                                    <input type="number" class="form-control passenger-count @error('adult_count') is-invalid @enderror" 
                                           id="adult_count" name="adult_count" value="{{ old('adult_count', 0) }}" min="0" required>
                                    <small class="text-muted">Birim fiyat: <span id="adult_price_display">-</span></small>
                                    @error('adult_count')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-child text-success"></i> Çocuk</label>
                                    <input type="number" class="form-control passenger-count @error('child_count') is-invalid @enderror" 
                                           id="child_count" name="child_count" value="{{ old('child_count', 0) }}" min="0">
                                    <small class="text-muted">Birim fiyat: <span id="child_price_display">-</span></small>
                                    @error('child_count')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-baby text-info"></i> Bebek</label>
                                    <input type="number" class="form-control passenger-count @error('infant_count') is-invalid @enderror" 
                                           id="infant_count" name="infant_count" value="{{ old('infant_count', 0) }}" min="0">
                                    <small class="text-muted">Birim fiyat: <span id="infant_price_display">-</span></small>
                                    @error('infant_count')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fiyat Girişi -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Fiyat</h3>
                    </div>
                    <div class="card-body">
                        <div style="display:flex !important; flex-wrap:wrap; gap:1rem;">
                            <div style="flex:1 1 200px; min-width:180px;">
                                <label class="mb-1 text-muted">Taban Fiyat (Admin Payı)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="base_total_price" value="0" readonly>
                                    <span class="input-group-text" id="base_currency_badge">TRY</span>
                                </div>
                                <small class="form-text text-muted">Tur sahibine gidecek pay (rest sonrası). Para birimi değiştirilemez.</small>
                            </div>
                            <div style="flex:1 1 200px; min-width:180px;">
                                <label class="mb-1 text-muted">Satış Fiyatı (Acenta)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control @error('sale_total_price') is-invalid @enderror"
                                           id="sale_total_price" name="sale_total_price" value="{{ old('sale_total_price') }}" placeholder="Satış tutarı">
                                    <select class="form-select" id="sale_currency_select" name="sale_currency" style="max-width:90px; border-top-left-radius:0; border-bottom-left-radius:0;">
                                        @foreach(['TRY','USD','EUR','GBP','RUB'] as $cur)
                                            <option value="{{ $cur }}" {{ old('sale_currency', 'TRY') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('sale_total_price')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Tabanın altında da olabilir; para birimini buradan seçin.</small>
                            </div>
                            <div style="flex:1 1 200px; min-width:180px;">
                                <label class="mb-1 text-muted">Rest (Opsiyonel)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           id="rest_amount" name="rest_amount" value="{{ old('rest_amount') }}" placeholder="Rest tutarı">
                                    <span class="input-group-text" id="rest_currency_badge">TRY</span>
                                    <input type="hidden" id="rest_currency_input" name="rest_currency" value="{{ old('rest_currency', 'TRY') }}">
                                </div>
                                <small class="form-text text-muted">Rest girerseniz taban payı düşer (taban para birimiyle aynıysa), rest tutarı admin geliri olur.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save"></i> Bilet Oluştur
                        </button>
                        <a href="{{ route('agency.tickets.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> İptal
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sağ: Özet -->
            <div class="col-lg-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-receipt"></i> Fiyat Özeti</h3>
                    </div>
                    <div class="card-body">
                        <div id="price-summary">
                            <p class="text-muted text-center">Tur ve tarih seçtikten sonra fiyat hesaplanacak.</p>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Taban (rest sonrası):</strong>
                            <strong id="total_price_display" class="text-primary">0.00 TRY</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <strong>Rest:</strong>
                            <strong id="rest_display" class="text-warning">0.00 TRY</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <strong>Satış:</strong>
                            <strong id="sale_price_display" class="text-success">0.00 TRY</strong>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Seçili Tur Bilgisi</h3>
                    </div>
                    <div class="card-body" id="tour-info-card">
                        <p class="text-muted text-center">Tur seçilmedi.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@push('css')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
<style>
    /* Mini Calendar */
    .mini-calendar {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        max-width: 320px;
    }
    .mini-cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #2196F3;
        color: #fff;
    }
    .cal-title {
        font-size: 14px;
        font-weight: 700;
    }
    .cal-nav {
        background: rgba(255,255,255,0.2);
        border: none;
        color: #fff;
        width: 28px;
        height: 28px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: background 0.15s;
    }
    .cal-nav:hover {
        background: rgba(255,255,255,0.4);
    }
    .mini-cal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #f5f5f5;
        border-bottom: 1px solid #e0e0e0;
        padding: 6px 8px;
    }
    .mini-cal-weekdays span {
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        color: #757575;
    }
    .mini-cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 3px;
        padding: 8px;
    }
    .cal-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.12s;
        color: #333;
        border: 1px solid #e8e8e8;
        background: #fff;
    }
    .cal-day:hover:not(.disabled):not(.empty) {
        background: #BBDEFB;
        border-color: #90CAF9;
    }
    .cal-day.selected {
        background: #2196F3;
        color: #fff;
        border-color: #1976D2;
    }
    .cal-day.today:not(.selected) {
        background: rgba(255, 235, 59, 0.25);
        border-color: #FDD835;
    }
    .cal-day.disabled {
        color: #bdbdbd;
        background: #fafafa;
        border-color: #f0f0f0;
        cursor: default;
    }
    .cal-day.empty {
        border: none;
        background: transparent;
        cursor: default;
    }

    /* ============================================== */
    /* DARK MODE STYLES FOR AGENCY TICKET CREATE */
    /* ============================================== */
    html.dark-mode .mini-calendar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html.dark-mode .mini-cal-header {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
    }

    html.dark-mode .mini-cal-weekdays {
        background: #334155 !important;
        border-color: #475569 !important;
    }

    html.dark-mode .mini-cal-weekdays span {
        color: #94a3b8 !important;
    }

    html.dark-mode .mini-cal-grid {
        background: #1e293b !important;
    }

    html.dark-mode .cal-day {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    html.dark-mode .cal-day:hover:not(.disabled):not(.selected):not(.empty) {
        background: #334155 !important;
        border-color: #3b82f6 !important;
    }

    html.dark-mode .cal-day.disabled {
        background: #0f172a !important;
        color: #475569 !important;
        border-color: #1e293b !important;
    }

    html.dark-mode .cal-day.available {
        background: #1e3a5f !important;
        border-color: #3b82f6 !important;
    }

    html.dark-mode .cal-day.selected {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }

    html.dark-mode .cal-day.today:not(.selected) {
        background: rgba(250, 204, 21, 0.2) !important;
        border-color: #eab308 !important;
    }

    /* Pickup suggestions dropdown */
    .pickup-search-wrap { position: relative; }
    .pickup-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: #fff; color: #212529; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 1050; max-height: 260px; overflow-y: auto; display: none; color-scheme: light; }
    .pickup-suggestions.show { display: block; }
    .pickup-suggestion { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f1f3f5; font-size: 13px; }
    .pickup-suggestion:last-child { border-bottom: none; }
    .pickup-suggestion:hover, .pickup-suggestion.active { background: #f0f7ff; }
    .pickup-suggestion-main { font-weight: 600; color: #212529; }
    .pickup-suggestion-sub { font-size: 11px; color: #6c757d; margin-top: 2px; }
    .pickup-suggestion-empty, .pickup-suggestion-loading { padding: 10px 12px; font-size: 12px; color: #6c757d; font-style: italic; text-align: center; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tourSelect = document.getElementById('tour_id');
    const tourDateSection = document.getElementById('tour-date-section');
    const datePickerContainer = document.getElementById('date-picker-container');
    const tourDateInput = document.getElementById('tour_date');
    const pickupTimeInput = document.getElementById('pickup_time_input');
    const tourInfoCard = document.getElementById('tour-info-card');

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let availableDates = [];
    let datePrices = {};
    let selectedDate = null;
    let currency = 'TRY'; // taban para birimi (tur fiyatı)
    let activePriceCurrency = 'TRY';
    let baseCurrency = 'TRY';
    let saleCurrency = 'TRY';
    let restCurrency = 'TRY';
    let currentBaseTotal = 0;
    let restInputEl = null;
    const saleCurrencySelect = document.getElementById('sale_currency_select');
    const restCurrencyBadge = document.getElementById('rest_currency_badge');
    const restCurrencyInput = document.getElementById('rest_currency_input');

    saleCurrency = saleCurrencySelect ? (saleCurrencySelect.value || 'TRY') : 'TRY';
    restCurrency = saleCurrency;
    document.getElementById('sale_price_display').textContent = `0.00 ${saleCurrency}`;
    document.getElementById('rest_display').textContent = `0.00 ${restCurrency}`;
    if (restCurrencyBadge) { restCurrencyBadge.textContent = restCurrency; }
    if (restCurrencyInput) { restCurrencyInput.value = restCurrency; }

    // Tur saati artık seçilen konumun bulunduğu poligonun saatlerinden dolduruluyor.
    function syncTourPickupTime() { /* no-op */ }

    tourSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        
        if (!this.value) {
            tourDateSection.style.display = 'none';
            tourInfoCard.innerHTML = '<p class="text-muted text-center">Tur seçilmedi.</p>';
            resetPricing();
            return;
        }

        // Get tour data
        availableDates = JSON.parse(option.dataset.availableDates || '[]');
        datePrices = JSON.parse(option.dataset.datePrices || '{}');
        currency = option.dataset.currency || 'TRY';
        activePriceCurrency = currency;
        baseCurrency = (currency || 'TRY').toUpperCase();
        saleCurrency = saleCurrencySelect ? (saleCurrencySelect.value || baseCurrency) : baseCurrency;
        restCurrency = saleCurrency;
        document.getElementById('base_currency_badge').textContent = baseCurrency;
        if (saleCurrencySelect) { saleCurrencySelect.value = saleCurrency; }
        if (restCurrencyBadge) { restCurrencyBadge.textContent = restCurrency; }
        if (restCurrencyInput) { restCurrencyInput.value = restCurrency; }
        document.getElementById('sale_price_display').textContent = `0.00 ${saleCurrency}`;

        // Update tour info card
        tourInfoCard.innerHTML = `
            <p><strong>${option.dataset.name}</strong></p>
            <p><small>${option.dataset.country} / ${option.dataset.city}</small></p>
            <p><i class="fas fa-clock"></i> Alış: ${option.dataset.pickupTime || '-'}</p>
            <p><i class="fas fa-calendar"></i> ${availableDates.length} gün açık</p>
        `;

        // Show date section and render calendar
        tourDateSection.style.display = 'block';
        syncTourPickupTime();
        selectedDate = null;
        tourDateInput.value = '';
        renderCalendar();
        resetPricing();
    });

    function renderCalendar() {
        const grid = document.getElementById('calendarGrid');
        const title = document.getElementById('currentMonthYear');
        if (!grid || !title) return;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;

        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const startDay = firstDay.getDay() || 7;

        const monthNames = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 
                           'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

        title.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        grid.innerHTML = '';

        // Empty cells before first day
        for (let i = 1; i < startDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'cal-day empty';
            grid.appendChild(empty);
        }

        // Days of month
        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dateObj = new Date(currentYear, currentMonth, day);
            
            const cell = document.createElement('div');
            cell.className = 'cal-day';
            cell.textContent = day;
            cell.setAttribute('data-date', dateStr);

            const isPast = dateObj < today;
            const isAvailable = availableDates.includes(dateStr);
            const isToday = dateStr === todayStr;

            if (isToday) cell.classList.add('today');
            if (selectedDate === dateStr) cell.classList.add('selected');

            if (isPast || !isAvailable) {
                cell.classList.add('disabled');
            } else {
                cell.onclick = function() {
                    grid.querySelectorAll('.cal-day').forEach(c => c.classList.remove('selected'));
                    cell.classList.add('selected');
                    selectedDate = dateStr;
                    tourDateInput.value = dateStr;
                    updatePricing();
                };
            }

            grid.appendChild(cell);
        }
    }

    // Ay değişim butonları
    document.getElementById('prevMonthBtn').onclick = function() {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    };

    document.getElementById('nextMonthBtn').onclick = function() {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar();
    };

    function resetPricing() {
        document.getElementById('adult_price_display').textContent = '-';
        document.getElementById('child_price_display').textContent = '-';
        document.getElementById('infant_price_display').textContent = '-';
        document.getElementById('total_price_display').textContent = '0.00 ' + baseCurrency;
        document.getElementById('base_total_price').value = '0.00';
        document.getElementById('sale_total_price').value = '';
        document.getElementById('sale_price_display').textContent = '0.00 ' + saleCurrency;
        document.getElementById('rest_display').textContent = '0.00 ' + restCurrency;
        if (restCurrencyBadge) { restCurrencyBadge.textContent = restCurrency; }
        if (restCurrencyInput) { restCurrencyInput.value = restCurrency; }
        document.getElementById('price-summary').innerHTML = '<p class="text-muted text-center">Tur ve tarih seçtikten sonra fiyat hesaplanacak.</p>';
        currentBaseTotal = 0;
        if (restInputEl) {
            restInputEl.value = '';
        }
    }

    function updatePricing() {
        if (!selectedDate || !datePrices[selectedDate]) {
            resetPricing();
            return;
        }

        const prices = datePrices[selectedDate];
        const adultPrice = parseFloat(prices.adult || 0);
        const childPrice = parseFloat(prices.child || 0);
        const infantPrice = parseFloat(prices.infant || 0);
        activePriceCurrency = (prices.currency || currency || 'TRY').toUpperCase();
        baseCurrency = activePriceCurrency;
        document.getElementById('base_currency_badge').textContent = baseCurrency;
        if (saleCurrencySelect) {
            saleCurrencySelect.value = saleCurrencySelect.value || baseCurrency;
            saleCurrency = saleCurrencySelect.value;
        }
        restCurrency = saleCurrency;
        if (restCurrencyBadge) { restCurrencyBadge.textContent = restCurrency; }
        if (restCurrencyInput) { restCurrencyInput.value = restCurrency; }

        document.getElementById('adult_price_display').textContent = adultPrice.toFixed(2) + ' ' + baseCurrency;
        document.getElementById('child_price_display').textContent = childPrice.toFixed(2) + ' ' + baseCurrency;
        document.getElementById('infant_price_display').textContent = infantPrice.toFixed(2) + ' ' + baseCurrency;

        calculateTotal(adultPrice, childPrice, infantPrice);
    }

    const fxPreviewUrl = "{{ route('agency.tickets.fx-preview') }}";

    function renderSummary(baseAfterRest, restVal, saleVal, note = '', adultPrice = 0, childPrice = 0, infantPrice = 0, adultCount = 0, childCount = 0, infantCount = 0) {
        document.getElementById('total_price_display').textContent = baseAfterRest.toFixed(2) + ' ' + baseCurrency;
        document.getElementById('base_total_price').value = baseAfterRest.toFixed(2);
        document.getElementById('rest_display').textContent = note
            ? `${restVal.toFixed(2)} ${restCurrency} (${note})`
            : `${restVal.toFixed(2)} ${restCurrency}`;
        document.getElementById('sale_price_display').textContent = saleVal.toFixed(2) + ' ' + saleCurrency;

        let summaryHtml = '<table class="table table-sm mb-0">';
        if (adultCount > 0) {
            summaryHtml += `<tr><td>Yetişkin (${adultCount}x)</td><td class="text-right">${(adultCount * adultPrice).toFixed(2)}</td></tr>`;
        }
        if (childCount > 0) {
            summaryHtml += `<tr><td>Çocuk (${childCount}x)</td><td class="text-right">${(childCount * childPrice).toFixed(2)}</td></tr>`;
        }
        if (infantCount > 0) {
            summaryHtml += `<tr><td>Bebek (${infantCount}x)</td><td class="text-right">${(infantCount * infantPrice).toFixed(2)}</td></tr>`;
        }
        summaryHtml += `<tr class="table-primary"><td><strong>Taban (rest sonrası)</strong></td><td class="text-right"><strong>${baseAfterRest.toFixed(2)} ${baseCurrency}</strong></td></tr>`;
        summaryHtml += `<tr class="table-warning"><td><strong>Rest</strong></td><td class="text-right"><strong>${restVal.toFixed(2)} ${restCurrency}${note ? ' (' + note + ')' : ''}</strong></td></tr>`;
        summaryHtml += `<tr class="table-success"><td><strong>Satış</strong></td><td class="text-right"><strong>${saleVal.toFixed(2)} ${saleCurrency}</strong></td></tr>`;
        document.getElementById('price-summary').innerHTML = summaryHtml;
    }

    function calculateTotal(adultPrice, childPrice, infantPrice) {
        const adultCount = parseInt(document.getElementById('adult_count').value) || 0;
        const childCount = parseInt(document.getElementById('child_count').value) || 0;
        const infantCount = parseInt(document.getElementById('infant_count').value) || 0;

        const total = (adultCount * adultPrice) + (childCount * childPrice) + (infantCount * infantPrice);
        currentBaseTotal = total;

        // Rest hesapla (taban payından düş)
        saleCurrency = saleCurrencySelect ? (saleCurrencySelect.value || baseCurrency) : baseCurrency;
        restCurrency = saleCurrency;

        const restRaw = restInputEl ? restInputEl.value : '';
        let restVal = parseFloat(restRaw);
        const restIsEmpty = restRaw === '' || restRaw === null;
        if (isNaN(restVal) || restVal < 0) {
            restVal = 0;
        }
        if (restInputEl && !restIsEmpty) {
            restInputEl.value = restVal.toFixed(2);
        }

        const saleInput = document.getElementById('sale_total_price');
        let saleVal = parseFloat(saleInput.value);
        const saleWasEmpty = saleInput.value === '' || saleInput.value === null;
        if (isNaN(saleVal)) {
            // Alan boşken yazmayı engellememek için değeri boş bırak, ekranda 0 göster
            saleVal = saleWasEmpty ? 0 : total;
            if (!saleWasEmpty) {
                saleInput.value = saleVal.toFixed(2);
            }
        }

        // Kur farklıysa backend'den önizleme çek
        const provisionalBaseAfterRest = restCurrency === baseCurrency
            ? Math.max(total - restVal, 0)
            : total;

        if (restCurrency !== baseCurrency && restVal > 0) {
            document.getElementById('price-summary').innerHTML = '<p class="text-center text-muted mb-0">Kur hesaplanıyor...</p>';
            fetch(`${fxPreviewUrl}?amount=${restVal}&from=${restCurrency}&to=${baseCurrency}`)
                .then(resp => {
                    if (!resp.ok) throw new Error('Kur alınamadı');
                    return resp.json();
                })
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    const converted = parseFloat(data.converted || restVal);
                    const baseAfter = Math.max(total - converted, 0);
                    const note = `${restVal.toFixed(2)} ${restCurrency} ≈ ${converted.toFixed(2)} ${baseCurrency}`;
                    renderSummary(baseAfter, restVal, saleVal, note, adultPrice, childPrice, infantPrice, adultCount, childCount, infantCount);
                })
                .catch(err => {
                    renderSummary(provisionalBaseAfterRest, restVal, saleVal, `kur alınamadı (${restCurrency}->${baseCurrency})`, adultPrice, childPrice, infantPrice, adultCount, childCount, infantCount);
                });
            return;
        }

        renderSummary(provisionalBaseAfterRest, restVal, saleVal, '', adultPrice, childPrice, infantPrice, adultCount, childCount, infantCount);
    }

    // Listen to passenger count changes
    document.querySelectorAll('.passenger-count').forEach(input => {
        input.addEventListener('change', function() {
            if (selectedDate && datePrices[selectedDate]) {
                const prices = datePrices[selectedDate];
                calculateTotal(
                    parseFloat(prices.adult || 0),
                    parseFloat(prices.child || 0),
                    parseFloat(prices.infant || 0)
                );
            }
        });
    });

    // Satış fiyatı manuel değişince sadece ekrana yansıt (taban altına izin ver)
    document.getElementById('sale_total_price').addEventListener('input', function() {
        let val = parseFloat(this.value);
        const isEmpty = this.value === '' || this.value === null;
        if (isNaN(val)) {
            val = 0;
            // boşken değeri temiz bırak, kullanıcıyı zorlamayalım
            if (!isEmpty) {
                this.value = val.toFixed(2);
            }
        }
        document.getElementById('sale_price_display').textContent = val.toFixed(2) + ' ' + saleCurrency;

        // Özet satırını güncelle
        if (selectedDate && datePrices[selectedDate]) {
            const prices = datePrices[selectedDate];
            calculateTotal(
                parseFloat(prices.adult || 0),
                parseFloat(prices.child || 0),
                parseFloat(prices.infant || 0)
            );
        }
    });

    // Rest değiştiğinde taban payı ve özet güncelle
    restInputEl = document.getElementById('rest_amount');
    if (restInputEl) {
        restInputEl.addEventListener('input', function() {
            let val = parseFloat(this.value);
            const isEmpty = this.value === '' || this.value === null;
            if (isNaN(val) || val < 0) {
                val = 0;
            }
            // Boş bırakma esnekliği: sadece değer varsa formatla
            if (!isEmpty) {
                this.value = val.toFixed(2);
            }

            if (selectedDate && datePrices[selectedDate]) {
                const prices = datePrices[selectedDate];
                calculateTotal(
                    parseFloat(prices.adult || 0),
                    parseFloat(prices.child || 0),
                    parseFloat(prices.infant || 0)
                );
            }
        });
    }

    if (saleCurrencySelect) {
        saleCurrencySelect.addEventListener('change', function() {
            saleCurrency = this.value || baseCurrency;
            restCurrency = saleCurrency;
            if (restCurrencyBadge) { restCurrencyBadge.textContent = restCurrency; }
            if (restCurrencyInput) { restCurrencyInput.value = restCurrency; }
            if (selectedDate && datePrices[selectedDate]) {
                const prices = datePrices[selectedDate];
                calculateTotal(
                    parseFloat(prices.adult || 0),
                    parseFloat(prices.child || 0),
                    parseFloat(prices.infant || 0)
                );
            } else {
                resetPricing();
            }
        });
    }

    // Trigger change if tour already selected (for old values)
    if (tourSelect.value) {
        tourSelect.dispatchEvent(new Event('change'));
        syncTourPickupTime();
    }
});
</script>
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var MAPBOX_TOKEN = {!! json_encode(config('services.mapbox.access_token'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    var mapEl = document.getElementById('pickup-map');
    if (!mapEl || !MAPBOX_TOKEN) return;
    mapboxgl.accessToken = MAPBOX_TOKEN;
    var inputEl = document.getElementById('pickup_location');
    var latEl = document.getElementById('pickup_lat');
    var lngEl = document.getElementById('pickup_lng');
    var locateBtn = document.getElementById('btn-locate-me');
    var map = new mapboxgl.Map({ container:'pickup-map', style:'mapbox://styles/mapbox/streets-v12', center:[28.27,36.85], zoom:10, language:'tr' });
    map.addControl(new mapboxgl.NavigationControl(),'top-right');
    var marker = null;
    var serviceGeoJson = null;
    var toastEl = document.getElementById('map-toast');
    var toastTimer = null;
    function showMapToast(msg){ if(!toastEl)return; toastEl.textContent=msg; toastEl.style.opacity='1'; clearTimeout(toastTimer); toastTimer=setTimeout(function(){ toastEl.style.opacity='0'; },2200); }
    function isTourSelected(){ var ts=document.getElementById('tour_id'); return ts && ts.value; }

    function setMarker(lat,lng){
        if(!isTourSelected()){showMapToast('Önce bir tur seçiniz');return;}
        if(!isInsideServiceAreas(lng,lat)){showMapToast('Seçilen konum, servis alanı dışında');return;}
        if(marker){marker.setLngLat([lng,lat]);}else{marker=new mapboxgl.Marker({draggable:true}).setLngLat([lng,lat]).addTo(map);marker.on('dragend',onMarkerDrag);}
        latEl.value=lat; lngEl.value=lng;
        refreshPickupTimeOptions();
    }
    function onMarkerDrag(){
        var ll=marker.getLngLat();
        if(isInsideServiceAreas(ll.lng,ll.lat)){latEl.value=ll.lat;lngEl.value=ll.lng;reverseGeocode(ll.lng,ll.lat);refreshPickupTimeOptions();}
        else{showMapToast('Seçilen konum, servis alanı dışında');var oLat=parseFloat(latEl.value),oLng=parseFloat(lngEl.value);if(!isNaN(oLat)&&!isNaN(oLng))marker.setLngLat([oLng,oLat]);}
    }
    function getPolygonTimesAt(lng,lat){
        if(!serviceGeoJson)return [];
        if(serviceGeoJson.type==='FeatureCollection'){
            var feats=serviceGeoJson.features||[];
            for(var i=0;i<feats.length;i++){
                if(feats[i]&&isInsideGeom(lng,lat,feats[i].geometry)){
                    var t=feats[i].properties&&feats[i].properties.times;
                    return Array.isArray(t)?t.slice().sort():[];
                }
            }
        }
        return [];
    }
    function refreshPickupTimeOptions(){
        var sel=document.getElementById('pickup_time_input');
        if(!sel)return;
        var preEl=document.getElementById('pickup_time_preselect');
        var pre=preEl?preEl.value:'';
        var current=sel.value||pre;
        var lat=parseFloat(latEl.value),lng=parseFloat(lngEl.value);
        sel.innerHTML='';
        function opt(v,t){var o=document.createElement('option');o.value=v;o.textContent=t;return o;}
        if(isNaN(lat)||isNaN(lng)){sel.appendChild(opt('','Önce haritadan konum seçin'));sel.disabled=true;return;}
        var times=getPolygonTimesAt(lng,lat);
        if(!times.length){sel.appendChild(opt('','Bu konumun bulunduğu alanda saat tanımlanmamış'));sel.disabled=true;return;}
        sel.disabled=false;
        sel.appendChild(opt('','-- Saat Seçiniz (opsiyonel) --'));
        times.forEach(function(t){sel.appendChild(opt(t,t));});
        if(current&&times.indexOf(current)!==-1)sel.value=current;
    }
    function reverseGeocode(lng,lat){ fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+lng+','+lat+'.json?access_token='+MAPBOX_TOKEN+'&language=tr&limit=1').then(function(r){return r.json();}).then(function(d){if(d.features&&d.features[0]&&inputEl)inputEl.value=d.features[0].place_name;}).catch(function(){}); }
    function forwardGeocode(q,cb){ fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/'+encodeURIComponent(q)+'.json?access_token='+MAPBOX_TOKEN+'&language=tr&limit=1').then(function(r){return r.json();}).then(function(d){if(d.features&&d.features[0]){var c=d.features[0].center;cb(c[0],c[1]);}}).catch(function(){}); }

    function pointInPolygon(lng,lat,coords){var ring=coords[0]||[];var inside=false;for(var i=0,j=ring.length-1;i<ring.length;j=i++){var xi=ring[i][0],yi=ring[i][1],xj=ring[j][0],yj=ring[j][1];if(((yi>lat)!==(yj>lat))&&(lng<(xj-xi)*(lat-yi)/(yj-yi)+xi))inside=!inside;}return inside;}
    function isInsideGeom(lng,lat,g){
        if(!g||!g.type)return false;
        if(g.type==='Polygon')return pointInPolygon(lng,lat,g.coordinates||[]);
        if(g.type==='MultiPolygon'){var cs=g.coordinates||[];for(var i=0;i<cs.length;i++){if(pointInPolygon(lng,lat,cs[i]))return true;}return false;}
        return false;
    }
    function isInsideServiceAreas(lng,lat){
        if(!serviceGeoJson)return true;
        if(serviceGeoJson.type==='FeatureCollection'){
            var feats=serviceGeoJson.features||[];
            if(!feats.length)return true;
            for(var i=0;i<feats.length;i++){if(feats[i]&&isInsideGeom(lng,lat,feats[i].geometry))return true;}
            return false;
        }
        if(serviceGeoJson.type==='Polygon'||serviceGeoJson.type==='MultiPolygon')return isInsideGeom(lng,lat,serviceGeoJson);
        return true;
    }
    function clearServiceLayer(){
        serviceGeoJson=null;
        if(map.getLayer('sa-fill'))map.removeLayer('sa-fill');
        if(map.getLayer('sa-line'))map.removeLayer('sa-line');
        if(map.getSource('sa-src'))map.removeSource('sa-src');
    }
    function drawServiceAreas(geo){
        clearServiceLayer();
        if(!geo)return;
        serviceGeoJson=geo;
        if(!map.isStyleLoaded()){map.on('load',function(){drawServiceAreas(geo);});return;}
        var data=geo.type==='FeatureCollection'?geo:{type:'Feature',properties:{},geometry:geo};
        map.addSource('sa-src',{type:'geojson',data:data});
        map.addLayer({id:'sa-fill',type:'fill',source:'sa-src',paint:{'fill-color':'#0d6efd','fill-opacity':0.12}});
        map.addLayer({id:'sa-line',type:'line',source:'sa-src',paint:{'line-color':'#0d6efd','line-width':2}});
        refreshPickupTimeOptions();
        loadAreaPOIs();
        try{
            var bounds=new mapboxgl.LngLatBounds();
            function addRing(r){(r||[]).forEach(function(p){bounds.extend(p);});}
            function addGeom(g){if(!g)return;if(g.type==='Polygon')addRing((g.coordinates||[])[0]);else if(g.type==='MultiPolygon')(g.coordinates||[]).forEach(function(poly){addRing(poly[0]);});}
            if(geo.type==='FeatureCollection')(geo.features||[]).forEach(function(f){if(f)addGeom(f.geometry);});
            else addGeom(geo);
            if(!bounds.isEmpty())map.fitBounds(bounds,{padding:40,maxZoom:14});
        }catch(e){}
    }
    function loadTourAreas(){
        var tourSel=document.getElementById('tour_id');
        if(!tourSel||!tourSel.value){clearServiceLayer();return;}
        var url=({!! json_encode(route('agency.tours.details',['tour'=>'__ID__']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}).replace('__ID__',tourSel.value);
        fetch(url,{headers:{'Accept':'application/json'}}).then(function(r){return r.json();}).then(function(data){
            if(!data||!data.tour)return;
            if(data.tour.service_areas)drawServiceAreas(data.tour.service_areas);
            else clearServiceLayer();
        }).catch(function(){});
    }
    var tourSel=document.getElementById('tour_id');
    if(tourSel)tourSel.addEventListener('change',loadTourAreas);
    loadTourAreas();

    map.on('click',function(e){
        if(!isTourSelected()){showMapToast('Önce bir tur seçiniz');return;}
        var lng=e.lngLat.lng,lat=e.lngLat.lat;
        if(isInsideServiceAreas(lng,lat)){setMarker(lat,lng);reverseGeocode(lng,lat);}
        else{showMapToast('Seçilen konum, servis alanı dışında');}
    });
    if(locateBtn&&navigator.geolocation){locateBtn.addEventListener('click',function(){navigator.geolocation.getCurrentPosition(function(pos){var lat=pos.coords.latitude,lng=pos.coords.longitude;map.flyTo({center:[lng,lat],zoom:15});setMarker(lat,lng);reverseGeocode(lng,lat);});});}
    // Anlık arama: yazarken servis alanı içinde kalan sonuçları input altında gösterir
    var suggestionsEl=document.getElementById('pickup-suggestions');
    var searchTimer=null,searchController=null;
    function hideSuggestions(){if(suggestionsEl){suggestionsEl.classList.remove('show');suggestionsEl.innerHTML='';}}
    function buildServiceBounds(){
        if(!serviceGeoJson)return null;
        var b=new mapboxgl.LngLatBounds();
        function addRing(r){(r||[]).forEach(function(p){b.extend(p);});}
        function addGeom(g){if(!g)return;if(g.type==='Polygon')addRing((g.coordinates||[])[0]);else if(g.type==='MultiPolygon')(g.coordinates||[]).forEach(function(p){addRing(p[0]);});}
        if(serviceGeoJson.type==='FeatureCollection')(serviceGeoJson.features||[]).forEach(function(f){if(f)addGeom(f.geometry);});
        else addGeom(serviceGeoJson);
        return b.isEmpty()?null:b;
    }
    function renderSuggestions(feats,strict){
        if(!suggestionsEl)return;
        if(!feats.length){
            var msg=strict?'Servis alanı içinde sonuç bulunamadı. Aramayı genişletin veya haritadan tıklayarak seçin.':'Sonuç bulunamadı.';
            suggestionsEl.innerHTML='<div class="pickup-suggestion-empty">'+msg+'</div>';
            suggestionsEl.classList.add('show');
            return;
        }
        suggestionsEl.innerHTML='';
        if(!strict){var note=document.createElement('div');note.className='pickup-suggestion-empty';note.style.borderBottom='1px solid #f1f3f5';note.style.fontStyle='normal';note.textContent='Bu tur için servis alanı tanımlı değil — sonuçlar filtrelenmedi.';suggestionsEl.appendChild(note);}
        feats.forEach(function(f){
            var item=document.createElement('div');
            item.className='pickup-suggestion';
            item.setAttribute('role','option');
            var p=document.createElement('div');p.className='pickup-suggestion-main';p.textContent=f.text||(f.place_name||'').split(',')[0];
            var s=document.createElement('div');s.className='pickup-suggestion-sub';s.textContent=f.place_name||'';
            item.appendChild(p);item.appendChild(s);
            item.addEventListener('mousedown',function(e){e.preventDefault();inputEl.value=f.place_name||'';map.flyTo({center:f.center,zoom:15});setMarker(f.center[1],f.center[0]);hideSuggestions();});
            suggestionsEl.appendChild(item);
        });
        suggestionsEl.classList.add('show');
    }
    function hasServiceAreaPolygons(){
        if(!serviceGeoJson)return false;
        if(serviceGeoJson.type==='FeatureCollection')return (serviceGeoJson.features||[]).length>0;
        return serviceGeoJson.type==='Polygon'||serviceGeoJson.type==='MultiPolygon';
    }
    // ===== Mapbox Tilequery POI cache =====
    var cachedPOIs=[];
    function normalizeText(s){return (s==null?'':String(s)).toLowerCase().replace(/[\u0130\u0131]/g,'i').replace(/[\u015e\u015f]/g,'s').replace(/[\u011e\u011f]/g,'g').replace(/[\u00dc\u00fc]/g,'u').replace(/[\u00d6\u00f6]/g,'o').replace(/[\u00c7\u00e7]/g,'c').trim();}
    function loadAreaPOIs(){
        var b=buildServiceBounds();
        if(!b){cachedPOIs=[];return;}
        var c=b.getCenter();
        var sw=b.getSouthWest(),ne=b.getNorthEast();
        var dx=ne.lng-sw.lng,dy=ne.lat-sw.lat;
        var radiusMeters=Math.min(50000,Math.max(3000,Math.round(Math.sqrt(dx*dx+dy*dy)*111000*0.7)));
        var url='https://api.mapbox.com/v4/mapbox.mapbox-streets-v8/tilequery/'+c.lng+','+c.lat+'.json?radius='+radiusMeters+'&limit=50&dedupe=true&geometry=point&layers=poi_label&access_token='+encodeURIComponent(MAPBOX_TOKEN);
        fetch(url).then(function(r){return r.json();}).then(function(data){
            var feats=(data&&data.features)||[];
            cachedPOIs=feats.filter(function(f){
                if(!f.properties||!f.properties.name)return false;
                var c=f.geometry&&f.geometry.coordinates;
                if(!Array.isArray(c))return false;
                return isInsideServiceAreas(c[0],c[1]);
            });
        }).catch(function(){cachedPOIs=[];});
    }
    function searchCachedPOIs(query){
        var qNorm=normalizeText(query);
        if(!qNorm||qNorm.length<2||!cachedPOIs.length)return [];
        return cachedPOIs.filter(function(p){return normalizeText(p.properties.name).indexOf(qNorm)!==-1;}).slice(0,8).map(function(p){
            var cat=p.properties.category_en||p.properties.maki||p.properties.class||'';
            return {place_name:p.properties.name+(cat?' — '+cat:''),text:p.properties.name,center:p.geometry.coordinates,_isPoi:true,_cat:cat};
        });
    }
    function instantGeocode(query){
        if(!query||query.length<2){hideSuggestions();return;}
        if(!isTourSelected()){if(suggestionsEl){suggestionsEl.innerHTML='<div class="pickup-suggestion-empty">Önce bir tur seçin</div>';suggestionsEl.classList.add('show');}return;}
        var poiResults=searchCachedPOIs(query);
        if(suggestionsEl){
            if(poiResults.length){renderSuggestions(poiResults,hasServiceAreaPolygons());}
            else{suggestionsEl.innerHTML='<div class="pickup-suggestion-loading">Aranıyor...</div>';suggestionsEl.classList.add('show');}
        }
        if(searchController){try{searchController.abort();}catch(e){}}
        searchController=(typeof AbortController!=='undefined')?new AbortController():null;
        var params='access_token='+MAPBOX_TOKEN+'&language=tr&limit=10&types=poi,address,neighborhood';
        var b=buildServiceBounds();
        if(b){params+='&bbox='+b.getWest()+','+b.getSouth()+','+b.getEast()+','+b.getNorth();var c=b.getCenter();params+='&proximity='+c.lng+','+c.lat;}
        var url='https://api.mapbox.com/geocoding/v5/mapbox.places/'+encodeURIComponent(query)+'.json?'+params;
        fetch(url,searchController?{signal:searchController.signal}:{}).then(function(r){return r.json();}).then(function(data){
            var feats=(data&&data.features)||[];
            var strict=hasServiceAreaPolygons();
            var geocodeFiltered=strict?feats.filter(function(f){var c=f.center;return Array.isArray(c)&&isInsideServiceAreas(c[0],c[1]);}):feats;
            var seen={},combined=[];
            poiResults.concat(geocodeFiltered).forEach(function(f){
                if(!f.center)return;
                var key=Math.round(f.center[0]*10000)+','+Math.round(f.center[1]*10000);
                if(seen[key])return;
                seen[key]=true;
                combined.push(f);
            });
            renderSuggestions(combined,strict);
        }).catch(function(){if(poiResults.length)renderSuggestions(poiResults,hasServiceAreaPolygons());});
    }
    if(inputEl){
        inputEl.addEventListener('input',function(){clearTimeout(searchTimer);var q=inputEl.value.trim();searchTimer=setTimeout(function(){instantGeocode(q);},250);});
        inputEl.addEventListener('focus',function(){var q=inputEl.value.trim();if(q.length>=2)instantGeocode(q);});
        inputEl.addEventListener('blur',function(){setTimeout(hideSuggestions,180);});
        inputEl.addEventListener('keydown',function(e){if(e.key==='Escape')hideSuggestions();});
    }
    var oldLat=parseFloat(latEl.value||''),oldLng=parseFloat(lngEl.value||'');
    if(!isNaN(oldLat)&&!isNaN(oldLng)){map.flyTo({center:[oldLng,oldLat],zoom:15});setMarker(oldLat,oldLng);}
    else if(inputEl&&inputEl.value){forwardGeocode(inputEl.value,function(lng,lat){map.flyTo({center:[lng,lat],zoom:15});setMarker(lat,lng);});}
});
</script>
@endpush


