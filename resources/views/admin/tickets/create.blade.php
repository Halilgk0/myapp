@extends('layouts.admin')

@section('title', 'Yeni Bilet Oluştur')

@section('content')
    <div class="container-fluid">
        <form action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticket-create-form">
            @csrf
<div class="row">
                <!-- Sol: Form Bölümü -->
                <div class="col-lg-8">
                    <!-- Giriş Bilgileri Card -->
                    <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                                <i class="fas fa-sign-in-alt"></i> Giriş Bilgileri
                </h3>
            </div>
            <div class="card-body">
                    <div class="row">
                                <div class="col-md-4">
                            <div class="form-group">
                                        <label for="entry_date"><i class="fas fa-calendar text-primary"></i> Giriş Tarihi *</label>
                                <input type="date" class="form-control @error('entry_date') is-invalid @enderror" 
                                       id="entry_date" name="entry_date" value="{{ old('entry_date', $defaultDate) }}" required readonly>
                                @error('entry_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Otomatik olarak bugünün tarihi</small>
                            </div>
                                </div>
                                <div class="col-md-4">
                            <div class="form-group">
                                        <label for="entry_time"><i class="fas fa-clock text-info"></i> Giriş Saati *</label>
                                <input type="time" class="form-control @error('entry_time') is-invalid @enderror" 
                                       id="entry_time" name="entry_time" value="{{ old('entry_time', $defaultTime) }}" required readonly>
                                @error('entry_time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Otomatik olarak şu anki saat</small>
                            </div>
                                </div>
                                <div class="col-md-4">
                            <div class="form-group">
                                        <label for="voucher_no"><i class="fas fa-barcode text-success"></i> Voucher No *</label>
                                <input type="text" class="form-control @error('voucher_no') is-invalid @enderror"
                                               id="voucher_no" name="voucher_no" value="{{ old('voucher_no') }}"
                                               placeholder="Voucher numarası girin"
                                               data-check-url="{{ route('admin.tickets.check-voucher') }}"
                                               required>
                                <span class="voucher-check-msg form-text" id="voucher-check-msg" style="font-size:12px;display:none;"></span>
                                @error('voucher_no')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                    <!-- Tur Seçimi Card -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-map-marked-alt"></i> Tur Seçimi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="tour_id"><i class="fas fa-route text-primary"></i> Tur Seçiniz *</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="tour_search" placeholder="Tur adına göre ara">
                                </div>
                                <select class="form-control @error('tour_id') is-invalid @enderror" 
                                        id="tour_id" name="tour_id" required onchange="var ds=document.getElementById('tour-date-selection'); if(ds){ ds.style.display=this.value?'block':'none'; } window.recalcTicketPricing && window.recalcTicketPricing(); window.refreshCalendarAvailability && window.refreshCalendarAvailability(); window.syncTourPickupTime && window.syncTourPickupTime();">
                                    <option value="">Tur Seçiniz</option>
                                    @foreach($tours as $tour)
                                        <option value="{{ $tour->id }}" 
                                                data-currency="{{ $tour->currency }}"
                                                data-pickup-time="{{ $tour->pickup_time }}"
                                                data-country="{{ $tour->country }}"
                                                data-city="{{ $tour->city }}"
                                                data-district="{{ $tour->district }}"
                                                data-name="{{ $tour->name }}"
                                                data-available-dates='@json($tour->available_dates ?? [])'
                                                data-date-prices='@json($tour->date_prices ?? new \stdClass())'
                                                {{ old('tour_id') == $tour->id ? 'selected' : '' }}>
                                            {{ $tour->name }} - {{ $tour->country }}/{{ $tour->city }} 
                                            ({{ $tour->pickup_time }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tour_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Tur Tarihi Seçimi -->
                            <div id="tour-date-selection" style="display: none;">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-day text-info"></i> Tur Tarihi Seçiniz *</label>
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
                                        <div class="text-muted small mt-1 text-center" id="noDatesMessage" style="display:none;">
                                            <i class="fas fa-info-circle"></i> Bu ay için açık tarih yok
                                        </div>
                                    </div>
                                    <input type="hidden" id="tour_date" name="tour_date" value="{{ old('tour_date') }}" required onchange="window.recalcTicketPricing && window.recalcTicketPricing()">
                                </div>
                            </div>

                            <!-- Hidden Tour Information Fields -->
                            <input type="hidden" id="tour_country" name="tour_country" value="{{ old('tour_country') }}">
                            <input type="hidden" id="tour_region" name="tour_region" value="{{ old('tour_region') }}">
                            <input type="hidden" id="tour_name" name="tour_name" value="{{ old('tour_name') }}">
                            <input type="hidden" id="sales_agency" name="sales_agency" value="{{ old('sales_agency') }}">
                        </div>
                    </div>

                    <!-- Müşteri Bilgileri Card -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user"></i> Müşteri Bilgileri
                            </h3>
                        </div>
                        <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                        <label for="customer_name"><i class="fas fa-user-tie text-primary"></i> Müşteri Adı *</label>
                                <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                               id="customer_name" name="customer_name" value="{{ old('customer_name') }}" 
                                               placeholder="Ad Soyad" required>
                                @error('customer_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                                </div>
                                <div class="col-md-6">
                            <div class="form-group">
                                        <label for="customer_phone"><i class="fas fa-phone text-success"></i> Müşteri Telefonu *</label>
                                <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                                               id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" 
                                               placeholder="+90 5xx xxx xx xx" required>
                                @error('customer_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                            <div class="form-group">
                                        <label for="customer_email"><i class="fas fa-envelope text-info"></i> Müşteri E-mail</label>
                                <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                               id="customer_email" name="customer_email" value="{{ old('customer_email') }}" 
                                               placeholder="ornek@email.com">
                                @error('customer_email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                                </div>
                                <div class="col-md-6">
                            <div class="form-group">
                                        <label for="customer_nationality"><i class="fas fa-flag text-warning"></i> Müşteri Milliyeti *</label>
                                <select class="form-control @error('customer_nationality') is-invalid @enderror" 
                                        id="customer_nationality" name="customer_nationality" required>
                                    <option value="">Milliyet Seçiniz</option>
                                    @foreach(\App\Models\Ticket::getNationalityOptions() as $code => $name)
                                        <option value="{{ $code }}" {{ old('customer_nationality') == $code ? 'selected' : '' }}>
                                            {{ $name }} ({{ $code }})
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
                                        <label for="pickup_location"><i class="fas fa-map-marker-alt text-danger"></i> Alış Yeri *</label>
                                <div class="pickup-search-wrap">
                                    <input type="text" class="form-control @error('pickup_location') is-invalid @enderror"
                                                   id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}"
                                                   placeholder="Otel adı veya adres ara (servis alanı içinde)" required autocomplete="off">
                                    <div class="pickup-suggestions" id="pickup-suggestions" role="listbox"></div>
                                </div>
                                @error('pickup_location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                        <div style="position:relative;">
                                            <div id="pickup-map" class="leaflet-map mt-2"></div>
                                            <div id="map-toast" class="map-toast"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                        <label for="room_number"><i class="fas fa-door-open text-secondary"></i> Oda Numarası</label>
                                        <input type="text" class="form-control @error('room_number') is-invalid @enderror" 
                                               id="room_number" name="room_number" value="{{ old('room_number') }}" 
                                               placeholder="Örn: 205">
                                        @error('room_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            </div>
                            </div>

                            <div class="form-group">
                                <label for="passport_numbers"><i class="fas fa-passport text-primary"></i> Pasaport Numaraları</label>
                                <textarea class="form-control @error('passport_numbers') is-invalid @enderror" 
                                          id="passport_numbers" name="passport_numbers" rows="2" 
                                          placeholder="Her satıra bir pasaport numarası">{{ old('passport_numbers') }}</textarea>
                                @error('passport_numbers')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Yolcu Sayısı Card -->
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Yolcu Sayısı
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="adult_count"><i class="fas fa-user text-primary"></i> Yetişkin Sayısı *</label>
                                        <input type="number" class="form-control @error('adult_count') is-invalid @enderror" 
                                               id="adult_count" name="adult_count" min="0" value="{{ old('adult_count', 0) }}" required 
                                               oninput="window.recalcTicketPricing && window.recalcTicketPricing()">
                                        @error('adult_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="child_count"><i class="fas fa-child text-success"></i> Çocuk Sayısı *</label>
                                        <input type="number" class="form-control @error('child_count') is-invalid @enderror" 
                                               id="child_count" name="child_count" min="0" value="{{ old('child_count', 0) }}" required 
                                               oninput="window.recalcTicketPricing && window.recalcTicketPricing()">
                                        @error('child_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="infant_count"><i class="fas fa-baby text-info"></i> Bebek Sayısı *</label>
                                        <input type="number" class="form-control @error('infant_count') is-invalid @enderror" 
                                               id="infant_count" name="infant_count" min="0" value="{{ old('infant_count', 0) }}" required 
                                               oninput="window.recalcTicketPricing && window.recalcTicketPricing()">
                                        @error('infant_count')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Toplam Yolcu Sayısı:</strong> <span id="total-passengers">0</span>
                            </div>
                            </div>
                        </div>
                    </div>

                <!-- Sağ: Fiyatlandırma ve İşlemler -->
                <div class="col-lg-4">
                    <!-- Fiyatlandırma Card -->
                    <div class="card card-success card-outline sticky-top" style="top: 68px;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-money-bill-wave"></i> Fiyatlandırma
                            </h3>
                        </div>
                        <div class="card-body">
                    <div class="form-group">
                                <label for="total_price"><i class="fas fa-calculator text-primary"></i> Toplam Fiyat</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('total_price') is-invalid @enderror" 
                                           id="total_price" name="total_price" value="{{ old('total_price', 0) }}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="currency_display">TRY</span>
                    </div>
            </div>
                                @error('total_price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted" id="month-price-hint" style="display:none"></small>
        </div>

                            <div class="form-group">
                                <label for="deposit"><i class="fas fa-hand-holding-usd text-success"></i> Kapora (30%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('deposit') is-invalid @enderror" 
                                           id="deposit" name="deposit" value="{{ old('deposit', 0) }}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="currency_display2">TRY</span>
    </div>
</div>
                                @error('deposit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="rest"><i class="fas fa-coins text-warning"></i> Kalan (70%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control @error('rest') is-invalid @enderror" 
                                           id="rest" name="rest" value="{{ old('rest', 0) }}" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="currency_display3">TRY</span>
                                    </div>
                                </div>
                                @error('rest')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <input type="hidden" id="currency" name="currency" value="{{ old('currency', 'TRY') }}">

                            <hr>

                            <div class="form-group mb-0">
                                <label><i class="fas fa-toggle-on text-success"></i> Durum</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="is_active">Bileti Aktif Et</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-save"></i> Bilet Oluştur
                            </button>
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </div>

                    <!-- İpuçları Card -->
                    <div class="card card-light">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb text-warning"></i> İpuçları
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Giriş tarihi ve saati otomatik doldurulur</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Fiyatlar seçilen tura göre hesaplanır</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Kapora %30, kalan %70 otomatik hesaplanır</li>
                                <li class="mb-0"><i class="fas fa-check text-success"></i> Tur tarihini seçmeden önce tur seçin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@push('css')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
<link href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.3/mapbox-gl-geocoder.css" rel="stylesheet">
<style>
    .leaflet-map { height: 260px; width: 100%; border: 1px solid #ced4da; border-radius: 4px; position: relative; }
    .map-toast { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:10; background:rgba(30,30,40,.88); color:#fff; padding:10px 20px; border-radius:8px; font-size:13px; font-weight:500; pointer-events:none; opacity:0; transition:opacity .25s; white-space:nowrap; box-shadow:0 2px 12px rgba(0,0,0,.3); }
    .map-toast.show { opacity:1; }
    .pickup-search-wrap { position: relative; }
    .pickup-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: #fff; color: #212529; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 1050; max-height: 260px; overflow-y: auto; display: none; color-scheme: light; }
    .pickup-suggestions.show { display: block; }
    .pickup-suggestion { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f1f3f5; font-size: 13px; }
    .pickup-suggestion:last-child { border-bottom: none; }
    .pickup-suggestion:hover, .pickup-suggestion.active { background: #f0f7ff; }
    .pickup-suggestion-main { font-weight: 600; color: #212529; }
    .pickup-suggestion-sub { font-size: 11px; color: #6c757d; margin-top: 2px; }
    .pickup-suggestion-empty, .pickup-suggestion-loading { padding: 10px 12px; font-size: 12px; color: #6c757d; font-style: italic; text-align: center; }
    
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

    .sticky-top {
        position: sticky;
        z-index: 10;
    }

    /* ============================================== */
    /* DARK MODE STYLES FOR TICKET CREATE PAGE */
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
        border-color: #6366f1 !important;
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

    html.dark-mode .cal-day.today {
        background: rgba(250, 204, 21, 0.2) !important;
        border-color: #eab308 !important;
    }

    html.dark-mode #dynamic-pricing {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html.dark-mode #dynamic-pricing h5,
    html.dark-mode #dynamic-pricing label {
        color: #e2e8f0 !important;
    }

    html.dark-mode .pricing-card {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html.dark-mode .pricing-card .card-header {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #e2e8f0 !important;
    }

    /* Dark mode: pickup suggestions ve Mapbox popup */
    html.dark-mode .pickup-suggestions {
        background: #1e293b !important; color: #e2e8f0 !important;
        border-color: #334155 !important; color-scheme: dark;
    }
    html.dark-mode .pickup-suggestion {
        color: #e2e8f0 !important; border-color: #334155 !important;
    }
    html.dark-mode .pickup-suggestion:hover,
    html.dark-mode .pickup-suggestion.active {
        background: #334155 !important; color: #fff !important;
    }
    html.dark-mode .mapboxgl-popup-content {
        background: #1e293b !important; color: #e2e8f0 !important;
        box-shadow: 0 2px 12px rgba(0,0,0,0.5) !important;
    }
    html.dark-mode .mapboxgl-popup-anchor-top .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-top-left .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-top-right .mapboxgl-popup-tip { border-bottom-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-bottom-left .mapboxgl-popup-tip,
    html.dark-mode .mapboxgl-popup-anchor-bottom-right .mapboxgl-popup-tip { border-top-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-anchor-left .mapboxgl-popup-tip { border-right-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-anchor-right .mapboxgl-popup-tip { border-left-color: #1e293b !important; }
    html.dark-mode .mapboxgl-popup-close-button { color: #cbd5e1 !important; }
</style>
@endpush

@push('js')
<script>
(function(){
    if (window.__ticketCalcInitInline) return; 
    window.__ticketCalcInitInline = true;

    function getSel(){
        var tour = document.getElementById('tour_id');
        if (!tour || !tour.value) return null;
        return tour.options[tour.selectedIndex] || null;
    }

    function getDateStr(){
        var td = document.getElementById('tour_date');
        if (!td || !td.value) return null;
        return td.value;
    }

    function parseDatePrices(opt){
        if (!opt) return null; 
        var raw = opt.getAttribute('data-date-prices');
        if (!raw) return null; 
        try { return JSON.parse(raw); } catch(e){ return null; }
    }

    function updateTotal(){
        var a = parseInt((document.getElementById('adult_count')||{}).value,10)||0;
        var c = parseInt((document.getElementById('child_count')||{}).value,10)||0;
        var i = parseInt((document.getElementById('infant_count')||{}).value,10)||0;
        var t = a+c+i; 
        var el = document.getElementById('total-passengers');
        if (el) el.textContent = String(t);
        return {a:a,c:c,i:i};
    }

    function recalc(){
        var opt = getSel();
        var counts = updateTotal();
        var totalEl = document.getElementById('total_price');
        var depEl = document.getElementById('deposit');
        var restEl = document.getElementById('rest');
        var curEl = document.getElementById('currency');
        
        if (!opt){
            if (totalEl) totalEl.value = '0.00';
            if (depEl) depEl.value = '0.00';
            if (restEl) restEl.value = '0.00';
            if (curEl) curEl.value = 'TRY';
            updateCurrencyDisplay('TRY');
            return;
        }

        var ap = 0;
        var cp = 0;
        var ip = 0;
        var cur = opt.dataset.currency||'TRY';
        var dp = parseDatePrices(opt);
        var dateStr = getDateStr();
        if (dp && dateStr && dp[dateStr]) {
            var row = dp[dateStr];
            if (row && typeof row === 'object') {
                if (row.adult != null) ap = parseFloat(row.adult) || 0;
                if (row.child != null) cp = parseFloat(row.child) || 0;
                if (row.infant != null) ip = parseFloat(row.infant) || 0;
                if (row.currency) cur = row.currency;
            } else if (!isNaN(parseFloat(row))) {
                ap = parseFloat(row) || 0;
            }
        }

        var total = (counts.a*ap)+(counts.c*cp)+(counts.i*ip);
        var dep = total*0.3; 
        var rst = total*0.7;

        if (totalEl) totalEl.value = total.toFixed(2);
        if (depEl) depEl.value = dep.toFixed(2);
        if (restEl) restEl.value = rst.toFixed(2);
        if (curEl) curEl.value = cur;
        updateCurrencyDisplay(cur);
    }

    function updateCurrencyDisplay(currency) {
        ['currency_display', 'currency_display2', 'currency_display3'].forEach(function(id){
            var el = document.getElementById(id);
            if (el) el.textContent = currency;
        });
    }

    // Tur saati artık seçilen konumun bulunduğu poligonun saatlerinden dolduruluyor.
    function syncTourPickupTime() { /* no-op, retained for backward compat */ }

    window.syncTourPickupTime = syncTourPickupTime;
    window.recalcTicketPricing = recalc;

    ['adult_count','child_count','infant_count','tour_id','tour_date'].forEach(function(id){
        var el = document.getElementById(id); 
        if (!el) return;
        ['input','change','keyup','blur'].forEach(function(ev){ 
            el.addEventListener(ev, recalc, true); 
        });
    });

    var tourEl = document.getElementById('tour_id');
    if (tourEl) tourEl.addEventListener('change', syncTourPickupTime);

    syncTourPickupTime();
    recalc();
})();

</script>
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script>
// ================ Mapbox Map (Create) =================
document.addEventListener('DOMContentLoaded', function(){
    var MAPBOX_TOKEN = @json(config('services.mapbox.access_token'));
    var mapEl = document.getElementById('pickup-map');
    if (!mapEl || !MAPBOX_TOKEN) return;

    mapboxgl.accessToken = MAPBOX_TOKEN;
    var inputEl = document.getElementById('pickup_location');
    var latEl = document.getElementById('pickup_lat');
    var lngEl = document.getElementById('pickup_lng');
    var locateBtn = document.getElementById('btn-locate-me');
    var toastEl = document.getElementById('map-toast');
    var toastTimer = null;
    function showMapToast(msg){ if(!toastEl)return; toastEl.textContent=msg; toastEl.classList.add('show'); clearTimeout(toastTimer); toastTimer=setTimeout(function(){ toastEl.classList.remove('show'); },2200); }
    function isTourSelected(){ var ts=document.getElementById('tour_id'); return ts && ts.value; }

    var map = new mapboxgl.Map({
        container: 'pickup-map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [32.8541, 39.9208],
        zoom: 12,
        language: 'tr',
    });
    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

    var marker = null;
    var serviceGeoJson = null;

    function setMarker(lat, lng) {
        if (!isTourSelected()) { showMapToast('Önce bir tur seçiniz'); return; }
        if (!isInsideServiceAreas(lng, lat)) {
            showMapToast('Seçilen konum, servis alanı dışında');
            return;
        }
        if (marker) { marker.setLngLat([lng, lat]); }
        else { marker = new mapboxgl.Marker({ draggable: true }).setLngLat([lng, lat]).addTo(map); marker.on('dragend', onMarkerDrag); }
        latEl.value = lat; lngEl.value = lng;
        refreshPickupTimeOptions();
    }

    function onMarkerDrag() {
        var ll = marker.getLngLat();
        if (isInsideServiceAreas(ll.lng, ll.lat)) {
            latEl.value = ll.lat; lngEl.value = ll.lng;
            reverseGeocode(ll.lng, ll.lat);
            refreshPickupTimeOptions();
        } else {
            showMapToast('Seçilen konum, servis alanı dışında');
            var oldLat = parseFloat(latEl.value); var oldLng = parseFloat(lngEl.value);
            if (!isNaN(oldLat) && !isNaN(oldLng)) marker.setLngLat([oldLng, oldLat]);
        }
    }

    // Seçilen konumun içinde bulunduğu poligonun saatlerini pickup_time select'ine doldurur.
    function getPolygonTimesAt(lng, lat) {
        if (!serviceGeoJson) return [];
        if (serviceGeoJson.type === 'FeatureCollection') {
            var feats = serviceGeoJson.features || [];
            for (var i = 0; i < feats.length; i++) {
                if (feats[i] && isInsideGeom(lng, lat, feats[i].geometry)) {
                    var t = feats[i].properties && feats[i].properties.times;
                    return Array.isArray(t) ? t.slice().sort() : [];
                }
            }
        }
        return [];
    }
    function refreshPickupTimeOptions() {
        var sel = document.getElementById('pickup_time_input');
        if (!sel) return;
        var preEl = document.getElementById('pickup_time_preselect');
        var pre = preEl ? preEl.value : '';
        var current = sel.value || pre;
        var lat = parseFloat(latEl.value);
        var lng = parseFloat(lngEl.value);
        sel.innerHTML = '';
        if (isNaN(lat) || isNaN(lng)) {
            var o = document.createElement('option'); o.value = ''; o.textContent = 'Önce haritadan konum seçin'; sel.appendChild(o);
            sel.disabled = true; return;
        }
        var times = getPolygonTimesAt(lng, lat);
        if (!times.length) {
            var o2 = document.createElement('option'); o2.value = ''; o2.textContent = 'Bu konumun bulunduğu alanda saat tanımlanmamış'; sel.appendChild(o2);
            sel.disabled = true; return;
        }
        sel.disabled = false;
        var o3 = document.createElement('option'); o3.value = ''; o3.textContent = '-- Saat Seçiniz (opsiyonel) --'; sel.appendChild(o3);
        times.forEach(function(t){ var op = document.createElement('option'); op.value = t; op.textContent = t; sel.appendChild(op); });
        if (current && times.indexOf(current) !== -1) sel.value = current;
    }

    function reverseGeocode(lng, lat) {
        fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + lng + ',' + lat + '.json?access_token=' + MAPBOX_TOKEN + '&language=tr&limit=1')
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.features && data.features[0] && inputEl) inputEl.value = data.features[0].place_name;
            }).catch(function(){});
    }

    function forwardGeocode(query, cb) {
        fetch('https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?access_token=' + MAPBOX_TOKEN + '&language=tr&limit=1')
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.features && data.features[0]) { var c = data.features[0].center; cb(c[0], c[1], data.features[0].place_name); }
            }).catch(function(){});
    }

    function pointInPolygon(lng, lat, coords) {
        var ring = coords[0] || [];
        var inside = false;
        for (var i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            var xi = ring[i][0], yi = ring[i][1], xj = ring[j][0], yj = ring[j][1];
            if (((yi > lat) !== (yj > lat)) && (lng < (xj - xi) * (lat - yi) / (yj - yi) + xi)) inside = !inside;
        }
        return inside;
    }

    function isInsideGeom(lng, lat, g) {
        if (!g || !g.type) return false;
        if (g.type === 'Polygon') return pointInPolygon(lng, lat, g.coordinates || []);
        if (g.type === 'MultiPolygon') {
            var cs = g.coordinates || [];
            for (var i = 0; i < cs.length; i++) { if (pointInPolygon(lng, lat, cs[i])) return true; }
            return false;
        }
        return false;
    }
    function isInsideServiceAreas(lng, lat) {
        if (!serviceGeoJson) return true;
        if (serviceGeoJson.type === 'FeatureCollection') {
            var feats = serviceGeoJson.features || [];
            if (!feats.length) return true;
            for (var i = 0; i < feats.length; i++) {
                if (feats[i] && isInsideGeom(lng, lat, feats[i].geometry)) return true;
            }
            return false;
        }
        if (serviceGeoJson.type === 'Polygon' || serviceGeoJson.type === 'MultiPolygon') {
            return isInsideGeom(lng, lat, serviceGeoJson);
        }
        return true;
    }

    function clearServiceLayer() {
        serviceGeoJson = null;
        if (map.getLayer('service-area-fill')) map.removeLayer('service-area-fill');
        if (map.getLayer('service-area-line')) map.removeLayer('service-area-line');
        if (map.getSource('service-area')) map.removeSource('service-area');
    }

    function drawServiceAreas(geo) {
        clearServiceLayer();
        if (!geo) return;
        serviceGeoJson = geo;
        if (!map.isStyleLoaded()) { map.on('load', function(){ drawServiceAreas(geo); }); return; }
        var data = geo.type === 'FeatureCollection' ? geo : { type: 'Feature', properties: {}, geometry: geo };
        map.addSource('service-area', { type: 'geojson', data: data });
        map.addLayer({ id: 'service-area-fill', type: 'fill', source: 'service-area', paint: { 'fill-color': '#0d6efd', 'fill-opacity': 0.12 } });
        map.addLayer({ id: 'service-area-line', type: 'line', source: 'service-area', paint: { 'line-color': '#0d6efd', 'line-width': 2 } });
        fitToServiceBounds(geo);
        refreshPickupTimeOptions();
        loadAreaPOIs();
    }

    // ===== Mapbox Tilequery POI cache (otel, kafe, hastane vb.) =====
    var cachedPOIs = [];
    function normalizeText(s) {
        return (s == null ? '' : String(s)).toLowerCase().replace(/[\u0130\u0131]/g,'i').replace(/[\u015e\u015f]/g,'s').replace(/[\u011e\u011f]/g,'g').replace(/[\u00dc\u00fc]/g,'u').replace(/[\u00d6\u00f6]/g,'o').replace(/[\u00c7\u00e7]/g,'c').trim();
    }
    function loadAreaPOIs() {
        var b = buildServiceBounds();
        if (!b) { cachedPOIs = []; return; }
        var c = b.getCenter();
        var sw = b.getSouthWest(), ne = b.getNorthEast();
        var dx = ne.lng - sw.lng, dy = ne.lat - sw.lat;
        // Yarıçapı bbox köşegenine göre hesapla, en az 3km en fazla 50km
        var radiusMeters = Math.min(50000, Math.max(3000, Math.round(Math.sqrt(dx*dx + dy*dy) * 111000 * 0.7)));
        var url = 'https://api.mapbox.com/v4/mapbox.mapbox-streets-v8/tilequery/'
                + c.lng + ',' + c.lat + '.json'
                + '?radius=' + radiusMeters + '&limit=50&dedupe=true&geometry=point&layers=poi_label'
                + '&access_token=' + encodeURIComponent(MAPBOX_TOKEN);
        fetch(url).then(function(r){ return r.json(); }).then(function(data){
            var feats = (data && data.features) || [];
            cachedPOIs = feats.filter(function(f){
                if (!f.properties || !f.properties.name) return false;
                var c = f.geometry && f.geometry.coordinates;
                if (!Array.isArray(c)) return false;
                return isInsideServiceAreas(c[0], c[1]);
            });
        }).catch(function(){ cachedPOIs = []; });
    }
    function searchCachedPOIs(query) {
        var qNorm = normalizeText(query);
        if (!qNorm || qNorm.length < 2 || !cachedPOIs.length) return [];
        return cachedPOIs.filter(function(p){
            var name = normalizeText(p.properties.name);
            return name.indexOf(qNorm) !== -1;
        }).slice(0, 8).map(function(p){
            var cat = p.properties.category_en || p.properties.maki || p.properties.class || '';
            return {
                place_name: p.properties.name + (cat ? ' — ' + cat : ''),
                text: p.properties.name,
                center: p.geometry.coordinates,
                _isPoi: true,
                _cat: cat,
            };
        });
    }

    function fitToServiceBounds(geo) {
        try {
            var bounds = new mapboxgl.LngLatBounds();
            function addRing(ring){ (ring||[]).forEach(function(p){ bounds.extend(p); }); }
            function addGeom(g){
                if (!g) return;
                if (g.type === 'Polygon') addRing((g.coordinates||[])[0]);
                else if (g.type === 'MultiPolygon') (g.coordinates||[]).forEach(function(poly){ addRing(poly[0]); });
            }
            if (geo.type === 'FeatureCollection') {
                (geo.features||[]).forEach(function(f){ if (f) addGeom(f.geometry); });
            } else {
                addGeom(geo);
            }
            if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: 40, maxZoom: 14 });
        } catch(e){}
    }

    function loadTourAreas() {
        var tourSel = document.getElementById('tour_id');
        if (!tourSel || !tourSel.value) { clearServiceLayer(); return; }
        var url = (@json(route('admin.tours.details', ['tour'=>'__ID__']))).replace('__ID__', tourSel.value);
        fetch(url, { headers:{'Accept':'application/json'} })
            .then(function(r){ return r.json(); }).then(function(data){
                if (!data || !data.tour) return;
                if (data.tour.service_areas) drawServiceAreas(data.tour.service_areas);
                else clearServiceLayer();
                var opt = tourSel.options[tourSel.selectedIndex];
                if (opt) {
                    opt.setAttribute('data-available-dates', JSON.stringify(data.tour.available_dates || []));
                    opt.setAttribute('data-date-prices', JSON.stringify(data.tour.date_prices || {}));
                }
                if (typeof renderAvailableDates === 'function') renderAvailableDates();
                if (window.recalcTicketPricing) window.recalcTicketPricing();
            }).catch(function(){});
    }

    function centerByTour() {
        var tourSel = document.getElementById('tour_id');
        if (!tourSel || !tourSel.value) return;
        loadTourAreas();
    }

    var tourSel = document.getElementById('tour_id');
    if (tourSel) tourSel.addEventListener('change', centerByTour);
    centerByTour();

    map.on('click', function(e) {
        if (!isTourSelected()) { showMapToast('Önce bir tur seçiniz'); return; }
        var lng = e.lngLat.lng, lat = e.lngLat.lat;
        if (isInsideServiceAreas(lng, lat)) {
            setMarker(lat, lng);
            reverseGeocode(lng, lat);
        } else {
            showMapToast('Seçilen konum, servis alanı dışında');
        }
    });

    if (locateBtn && navigator.geolocation) {
        locateBtn.addEventListener('click', function(){
            navigator.geolocation.getCurrentPosition(function(pos){
                var lat = pos.coords.latitude, lng = pos.coords.longitude;
                map.flyTo({ center: [lng, lat], zoom: 15 });
                setMarker(lat, lng);
                reverseGeocode(lng, lat);
            });
        });
    }

    // Anlık arama (yazarken), sadece servis alanı içinde kalan sonuçları gösterir
    var suggestionsEl = document.getElementById('pickup-suggestions');
    var searchTimer = null;
    var searchController = null;
    function hideSuggestions(){ if (suggestionsEl) { suggestionsEl.classList.remove('show'); suggestionsEl.innerHTML = ''; } }
    function buildServiceBounds(){
        if (!serviceGeoJson) return null;
        var b = new mapboxgl.LngLatBounds();
        function addRing(r){ (r||[]).forEach(function(p){ b.extend(p); }); }
        function addGeom(g){ if(!g) return; if(g.type==='Polygon') addRing((g.coordinates||[])[0]); else if(g.type==='MultiPolygon') (g.coordinates||[]).forEach(function(p){ addRing(p[0]); }); }
        if (serviceGeoJson.type === 'FeatureCollection') (serviceGeoJson.features||[]).forEach(function(f){ if(f) addGeom(f.geometry); });
        else addGeom(serviceGeoJson);
        return b.isEmpty() ? null : b;
    }
    function renderSuggestions(feats, strict){
        if (!suggestionsEl) return;
        if (!feats.length) {
            var emptyMsg = strict
                ? 'Servis alanı içinde sonuç bulunamadı. Aramayı genişletin veya haritadan tıklayarak seçin.'
                : 'Sonuç bulunamadı.';
            suggestionsEl.innerHTML = '<div class="pickup-suggestion-empty">' + emptyMsg + '</div>';
            suggestionsEl.classList.add('show');
            return;
        }
        suggestionsEl.innerHTML = '';
        if (!strict) {
            var note = document.createElement('div');
            note.className = 'pickup-suggestion-empty';
            note.style.borderBottom = '1px solid #f1f3f5';
            note.style.fontStyle = 'normal';
            note.textContent = 'Bu tur için servis alanı tanımlı değil — sonuçlar filtrelenmedi.';
            suggestionsEl.appendChild(note);
        }
        feats.forEach(function(f){
            var item = document.createElement('div');
            item.className = 'pickup-suggestion';
            item.setAttribute('role','option');
            var primary = document.createElement('div');
            primary.className = 'pickup-suggestion-main';
            primary.textContent = f.text || (f.place_name || '').split(',')[0];
            var secondary = document.createElement('div');
            secondary.className = 'pickup-suggestion-sub';
            secondary.textContent = f.place_name || '';
            item.appendChild(primary);
            item.appendChild(secondary);
            item.addEventListener('mousedown', function(e){
                e.preventDefault();
                inputEl.value = f.place_name || '';
                map.flyTo({ center: f.center, zoom: 15 });
                setMarker(f.center[1], f.center[0]);
                hideSuggestions();
            });
            suggestionsEl.appendChild(item);
        });
        suggestionsEl.classList.add('show');
    }
    function hasServiceAreaPolygons(){
        if (!serviceGeoJson) return false;
        if (serviceGeoJson.type === 'FeatureCollection') return (serviceGeoJson.features||[]).length > 0;
        return serviceGeoJson.type === 'Polygon' || serviceGeoJson.type === 'MultiPolygon';
    }
    function instantGeocode(query){
        if (!query || query.length < 2) { hideSuggestions(); return; }
        if (!isTourSelected()) {
            if (suggestionsEl) { suggestionsEl.innerHTML = '<div class="pickup-suggestion-empty">Önce bir tur seçin</div>'; suggestionsEl.classList.add('show'); }
            return;
        }
        // Önce POI cache'den al (haritadaki tüm otel/kafe/hastane vb.) — anında, API çağrısı yok
        var poiResults = searchCachedPOIs(query);

        if (suggestionsEl) {
            if (poiResults.length) {
                renderSuggestions(poiResults, hasServiceAreaPolygons());
            } else {
                suggestionsEl.innerHTML = '<div class="pickup-suggestion-loading">Aranıyor...</div>';
                suggestionsEl.classList.add('show');
            }
        }

        if (searchController) { try { searchController.abort(); } catch(e){} }
        searchController = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        var params = 'access_token=' + MAPBOX_TOKEN + '&language=tr&limit=10&types=poi,address,neighborhood';
        var b = buildServiceBounds();
        if (b) {
            params += '&bbox=' + b.getWest() + ',' + b.getSouth() + ',' + b.getEast() + ',' + b.getNorth();
            var c = b.getCenter();
            params += '&proximity=' + c.lng + ',' + c.lat;
        }
        var url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURIComponent(query) + '.json?' + params;
        var opts = searchController ? { signal: searchController.signal } : {};
        fetch(url, opts)
            .then(function(r){ return r.json(); })
            .then(function(data){
                var feats = (data && data.features) || [];
                var strict = hasServiceAreaPolygons();
                var geocodeFiltered = strict
                    ? feats.filter(function(f){ var c = f.center; return Array.isArray(c) && isInsideServiceAreas(c[0], c[1]); })
                    : feats;
                // POI ve geocode sonuçlarını birleştir, lat/lng ile dedupe
                var seen = {};
                var combined = [];
                poiResults.concat(geocodeFiltered).forEach(function(f){
                    if (!f.center) return;
                    var key = Math.round(f.center[0] * 10000) + ',' + Math.round(f.center[1] * 10000);
                    if (seen[key]) return;
                    seen[key] = true;
                    combined.push(f);
                });
                renderSuggestions(combined, strict);
            })
            .catch(function(){ if (poiResults.length) renderSuggestions(poiResults, hasServiceAreaPolygons()); });
    }
    if (inputEl) {
        inputEl.addEventListener('input', function(){
            clearTimeout(searchTimer);
            var q = inputEl.value.trim();
            searchTimer = setTimeout(function(){ instantGeocode(q); }, 250);
        });
        inputEl.addEventListener('focus', function(){
            var q = inputEl.value.trim();
            if (q.length >= 2) instantGeocode(q);
        });
        inputEl.addEventListener('blur', function(){ setTimeout(hideSuggestions, 180); });
        inputEl.addEventListener('keydown', function(e){ if (e.key === 'Escape') hideSuggestions(); });
    }

    var oldLat = parseFloat(latEl.value || ''); var oldLng = parseFloat(lngEl.value || '');
    if (!isNaN(oldLat) && !isNaN(oldLng)) { map.flyTo({ center: [oldLng, oldLat], zoom: 15 }); setMarker(oldLat, oldLng); }
    else if (inputEl && inputEl.value) { forwardGeocode(inputEl.value, function(lng, lat){ map.flyTo({ center: [lng, lat], zoom: 15 }); setMarker(lat, lng); }); }
});

// Tour live search (by name)
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('tour_search');
    var select = document.getElementById('tour_id');
    if (!input || !select) return;
    function applyFilter(){
        var q = (input.value || '').toLowerCase().trim();
        var visible = 0;
        Array.from(select.options).forEach(function(opt, idx){
            if (idx === 0) { opt.hidden = false; return; }
            var name = (opt.getAttribute('data-name') || opt.textContent || '').toLowerCase();
            var hide = q && name.indexOf(q) === -1;
            opt.hidden = hide;
            if (!hide) visible++;
        });
        if (q) {
            var size = Math.min(8, Math.max(2, visible + 1));
            select.setAttribute('size', String(size));
            try { input.focus(); } catch(e) {}
            try { select.scrollTop = 0; } catch(e) {}
    } else {
            select.removeAttribute('size');
        }
    }
    ['input','change'].forEach(function(ev){ input.addEventListener(ev, applyFilter); });
    select.addEventListener('change', function(){ select.removeAttribute('size'); });
});

// Mini Takvim
var monthNames = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
var tourDetailsCache = {};
var tourDetailsLoading = {};
var calMonth = new Date().getMonth();
var calYear = new Date().getFullYear();

function fetchTourDetails(tourId, done) {
    if (!tourId) { if (done) done(null); return; }
    if (tourDetailsCache[tourId]) { if (done) done(tourDetailsCache[tourId]); return; }
    if (tourDetailsLoading[tourId]) { return; }
    tourDetailsLoading[tourId] = true;
    var url = (@json(route('admin.tours.details', ['tour'=>'__ID__']))).replace('__ID__', tourId);
    fetch(url, { headers:{'Accept':'application/json'} })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (data && data.tour) {
                tourDetailsCache[tourId] = data.tour;
                if (done) done(data.tour);
            } else if (done) { done(null); }
        })
        .catch(function(){ if (done) done(null); })
        .finally(function(){ tourDetailsLoading[tourId] = false; });
}

function getAvailableDates() {
    var tourSelect = document.getElementById('tour_id');
    if (!tourSelect || !tourSelect.value) return [];
    var opt = tourSelect.options[tourSelect.selectedIndex];
    var availRaw = opt.getAttribute('data-available-dates');
    var priceRaw = opt.getAttribute('data-date-prices');
    var availDates = [];
    try { if (availRaw) availDates = JSON.parse(availRaw); } catch(e) {}
    if ((!Array.isArray(availDates) || availDates.length === 0) && priceRaw) {
        try {
            var priceMap = JSON.parse(priceRaw);
            if (priceMap && typeof priceMap === 'object') availDates = Object.keys(priceMap);
        } catch(e) {}
    }
    return Array.isArray(availDates) ? availDates : [];
}

function toDS(y, m, d) {
    return y + '-' + String(m+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
}

function renderCalendar() {
    var grid = document.getElementById('calendarGrid');
    var title = document.getElementById('currentMonthYear');
    var noMsg = document.getElementById('noDatesMessage');
    var dateInput = document.getElementById('tour_date');
    var tourSelect = document.getElementById('tour_id');
    if (!grid || !title) return;
    
    title.textContent = monthNames[calMonth] + ' ' + calYear;
    var availDates = getAvailableDates();
    var availSet = new Set(availDates);
    
    if (availDates.length === 0 && tourSelect && tourSelect.value) {
        fetchTourDetails(tourSelect.value, function(tour){
            if (!tour) return;
            var opt = tourSelect.options[tourSelect.selectedIndex];
            if (opt) {
                opt.setAttribute('data-available-dates', JSON.stringify(tour.available_dates || []));
                opt.setAttribute('data-date-prices', JSON.stringify(tour.date_prices || {}));
            }
            renderCalendar();
        });
    }
    
    grid.innerHTML = '';
    var today = new Date(); today.setHours(0,0,0,0);
    var todayStr = toDS(today.getFullYear(), today.getMonth(), today.getDate());
    
    var first = new Date(calYear, calMonth, 1);
    var startDow = first.getDay();
    var offset = startDow === 0 ? 6 : startDow - 1;
    var daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    
    for (var i = 0; i < offset; i++) {
        var e = document.createElement('div');
        e.className = 'cal-day empty';
        grid.appendChild(e);
    }
    
    for (var d = 1; d <= daysInMonth; d++) {
        var ds = toDS(calYear, calMonth, d);
        var dt = new Date(calYear, calMonth, d);
        var c = document.createElement('div');
        c.className = 'cal-day';
        c.textContent = d;
        c.setAttribute('data-date', ds);
        
        var isAvail = availSet.has(ds);
        var isPast = dt < today;
        if (ds === todayStr) c.classList.add('today');
        if (dateInput && dateInput.value === ds) c.classList.add('selected');
        
        if (!isAvail || isPast) {
            c.classList.add('disabled');
        } else {
            c.onclick = (function(dateStr) {
                return function() {
                    grid.querySelectorAll('.cal-day').forEach(function(x){ x.classList.remove('selected'); });
                    this.classList.add('selected');
                    dateInput.value = dateStr;
                    if (window.recalcTicketPricing) window.recalcTicketPricing();
                };
            })(ds);
        }
        grid.appendChild(c);
    }
    
    if (noMsg) noMsg.style.display = availDates.length === 0 ? 'block' : 'none';
}

function changeMonth(delta) {
    calMonth += delta;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    else if (calMonth < 0) { calMonth = 11; calYear--; }
    renderCalendar();
}

window.refreshCalendarAvailability = function() {
    var tourSelect = document.getElementById('tour_id');
    var dateSection = document.getElementById('tour-date-selection');
    if (tourSelect && tourSelect.value && dateSection) dateSection.style.display = 'block';
    var now = new Date();
    calMonth = now.getMonth();
    calYear = now.getFullYear();
    renderCalendar();
}

document.addEventListener('DOMContentLoaded', function() {
    var prevBtn = document.getElementById('prevMonthBtn');
    var nextBtn = document.getElementById('nextMonthBtn');
    if (prevBtn) prevBtn.onclick = function() { changeMonth(-1); };
    if (nextBtn) nextBtn.onclick = function() { changeMonth(1); };
    
    var tourSelect = document.getElementById('tour_id');
    if (tourSelect) {
        tourSelect.addEventListener('change', function() {
            var dateSection = document.getElementById('tour-date-selection');
            if (this.value) {
                if (dateSection) dateSection.style.display = 'block';
                var now = new Date();
                calMonth = now.getMonth();
                calYear = now.getFullYear();
                renderCalendar();
            } else {
                if (dateSection) dateSection.style.display = 'none';
            }
        });
        if (tourSelect.value) {
            var dateSection = document.getElementById('tour-date-selection');
            if (dateSection) dateSection.style.display = 'block';
            renderCalendar();
        }
    }
});

// Voucher No anlık unique kontrolü
(function(){
    var input = document.getElementById('voucher_no');
    var msg = document.getElementById('voucher-check-msg');
    if (!input || !msg) return;
    var url = input.dataset.checkUrl;
    if (!url) return;

    var timer = null;
    var lastValue = null;

    function setState(state, text) {
        msg.style.display = text ? '' : 'none';
        msg.textContent = text || '';
        input.classList.remove('is-invalid', 'is-valid');
        msg.classList.remove('text-success', 'text-danger', 'text-muted');
        if (state === 'ok') { input.classList.add('is-valid'); msg.classList.add('text-success'); }
        else if (state === 'bad') { input.classList.add('is-invalid'); msg.classList.add('text-danger'); }
        else { msg.classList.add('text-muted'); }
    }

    function check(value) {
        if (!value) { setState('', ''); return; }
        setState('wait', 'Kontrol ediliyor...');
        fetch(url + '?voucher_no=' + encodeURIComponent(value), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (input.value.trim() !== value) return; // kullanıcı bu arada değiştirdi
            if (data.available) setState('ok', 'Voucher numarası uygun');
            else setState('bad', data.message || 'Bu voucher numarası kullanımda');
        })
        .catch(function(){ setState('', ''); });
    }

    input.addEventListener('input', function(){
        var value = input.value.trim();
        if (value === lastValue) return;
        lastValue = value;
        if (timer) clearTimeout(timer);
        if (!value) { setState('', ''); return; }
        timer = setTimeout(function(){ check(value); }, 400);
    });

    // Submit engelleme: meşgulken veya hatalıyken
    var form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function(e){
            if (input.classList.contains('is-invalid')) {
                e.preventDefault();
                input.focus();
                alert('Voucher numarası zaten kullanımda — farklı bir numara seçin.');
            }
        });
    }
})();
</script>
@endpush
