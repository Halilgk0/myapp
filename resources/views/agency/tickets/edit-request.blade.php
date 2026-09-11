@extends('layouts.agency')

@section('title', 'Bilet İsteği Düzenle')

@section('content')
<div class="ag-page-header ag-flex ag-justify-between ag-items-center ag-mb-3" style="flex-wrap:wrap;gap:16px">
    <div>
        <h1 class="ag-page-title">Bilet İsteği Düzenle</h1>
        <p class="ag-page-subtitle">İstek #{{ $ticketRequest->id }} - Düzenleme istendi</p>
    </div>
    <a href="{{ route('agency.tickets.index') }}" class="ag-btn ag-btn-secondary ag-btn-sm">
        <i data-lucide="arrow-left"></i>
        <span>Geri</span>
    </a>
</div>
<div class="container-fluid">
    @if($ticketRequest->return_reason)
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Düzenleme Sebebi</h5>
            <p class="mb-0">{{ $ticketRequest->return_reason }}</p>
            @if($ticketRequest->returned_at)
                <small class="text-muted">Geri gönderilme: {{ $ticketRequest->returned_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</small>
            @endif
        </div>
    @endif

    <form action="{{ route('agency.ticket-requests.update', $ticketRequest) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Müşteri Bilgileri -->
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Müşteri Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="customer_name">Müşteri Adı <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                   value="{{ old('customer_name', $ticketRequest->customer_name) }}" required>
                            @error('customer_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_phone">Telefon <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" 
                                   value="{{ old('customer_phone', $ticketRequest->customer_phone) }}" required>
                            @error('customer_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_email">E-posta</label>
                            <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" 
                                   value="{{ old('customer_email', $ticketRequest->customer_email) }}">
                            @error('customer_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_nationality">Uyruk <span class="text-danger">*</span></label>
                            <select name="customer_nationality" id="customer_nationality" class="form-control @error('customer_nationality') is-invalid @enderror" required>
                                @foreach(\App\Models\Ticket::getNationalityOptions() as $code => $name)
                                    <option value="{{ $code }}" {{ old('customer_nationality', $ticketRequest->customer_nationality) == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_nationality')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="voucher_no">Voucher No</label>
                            <input type="text" name="voucher_no" id="voucher_no" class="form-control @error('voucher_no') is-invalid @enderror" 
                                   value="{{ old('voucher_no', $ticketRequest->voucher_no) }}">
                            @error('voucher_no')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_location">Alış Noktası</label>
                                    <input type="text" name="pickup_location" id="pickup_location" class="form-control @error('pickup_location') is-invalid @enderror" 
                                           value="{{ old('pickup_location', $ticketRequest->pickup_location) }}">
                                    @error('pickup_location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_number">Oda No</label>
                                    <input type="text" name="room_number" id="room_number" class="form-control @error('room_number') is-invalid @enderror" 
                                           value="{{ old('room_number', $ticketRequest->room_number) }}">
                                    @error('room_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tur ve Fiyatlandırma -->
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plane"></i> Tur Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tur</label>
                            <input type="text" class="form-control" value="{{ $ticketRequest->tour->name }}" disabled>
                            <small class="text-muted">{{ $ticketRequest->tour->country }}, {{ $ticketRequest->tour->city }}</small>
                        </div>

                        <div class="form-group">
                            <label for="tour_date">Tur Tarihi <span class="text-danger">*</span></label>
                            <input type="date" name="tour_date" id="tour_date" class="form-control @error('tour_date') is-invalid @enderror" 
                                   value="{{ old('tour_date', $ticketRequest->tour_date->format('Y-m-d')) }}" required>
                            @error('tour_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="currency">Para Birimi <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                <option value="TRY" {{ old('currency', $ticketRequest->currency) == 'TRY' ? 'selected' : '' }}>₺ TRY</option>
                                <option value="EUR" {{ old('currency', $ticketRequest->currency) == 'EUR' ? 'selected' : '' }}>€ EUR</option>
                                <option value="USD" {{ old('currency', $ticketRequest->currency) == 'USD' ? 'selected' : '' }}>$ USD</option>
                                <option value="GBP" {{ old('currency', $ticketRequest->currency) == 'GBP' ? 'selected' : '' }}>£ GBP</option>
                                <option value="RUB" {{ old('currency', $ticketRequest->currency) == 'RUB' ? 'selected' : '' }}>₽ RUB</option>
                            </select>
                            @error('currency')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> Yolcu ve Fiyatlandırma</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Yolcu Tipi</th>
                                    <th class="text-center" style="width: 100px;">Adet</th>
                                    <th class="text-center" style="width: 120px;">Birim Fiyat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Yetişkin</td>
                                    <td>
                                        <input type="number" name="adult_count" id="adult_count" class="form-control text-center" 
                                               value="{{ old('adult_count', $ticketRequest->adult_count) }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="adult_price" id="adult_price" class="form-control text-center" 
                                               value="{{ old('adult_price', $ticketRequest->adult_price) }}" min="0" step="0.01" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Çocuk</td>
                                    <td>
                                        <input type="number" name="child_count" id="child_count" class="form-control text-center" 
                                               value="{{ old('child_count', $ticketRequest->child_count) }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="child_price" id="child_price" class="form-control text-center" 
                                               value="{{ old('child_price', $ticketRequest->child_price) }}" min="0" step="0.01" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Bebek</td>
                                    <td>
                                        <input type="number" name="infant_count" id="infant_count" class="form-control text-center" 
                                               value="{{ old('infant_count', $ticketRequest->infant_count) }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="infant_price" id="infant_price" class="form-control text-center" 
                                               value="{{ old('infant_price', $ticketRequest->infant_price) }}" min="0" step="0.01" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <small class="text-muted d-block mb-2">
                            Birim fiyatlar turun seçili gün fiyatından otomatik gelir, manuel değiştirilemez.
                        </small>
                        <div class="alert alert-info py-2 mb-0">
                            <strong>Toplam:</strong>
                            <span id="calculated_total_text">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-paper-plane"></i> Düzenle ve Tekrar Gönder
                </button>
                <a href="{{ route('agency.tickets.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>
        </div>
    </form>
</div>
@stop

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tourDateEl = document.getElementById('tour_date');
    const currencyEl = document.getElementById('currency');
    const adultCountEl = document.getElementById('adult_count');
    const childCountEl = document.getElementById('child_count');
    const infantCountEl = document.getElementById('infant_count');
    const adultPriceEl = document.getElementById('adult_price');
    const childPriceEl = document.getElementById('child_price');
    const infantPriceEl = document.getElementById('infant_price');
    const totalTextEl = document.getElementById('calculated_total_text');

    const detailsUrl = {!! json_encode(route('agency.tours.details', $ticketRequest->tour_id), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

    function toNumber(val) {
        const n = parseFloat(val);
        return Number.isFinite(n) ? n : 0;
    }

    function formatMoney(val) {
        return toNumber(val).toFixed(2);
    }

    function lockUnitPriceInputs() {
        [adultPriceEl, childPriceEl, infantPriceEl].forEach(function (el) {
            if (!el) return;
            el.readOnly = true;
            el.style.backgroundColor = '#f8f9fa';
            el.style.cursor = 'not-allowed';
        });
    }

    function recalcTotal() {
        const total =
            (toNumber(adultCountEl.value) * toNumber(adultPriceEl.value)) +
            (toNumber(childCountEl.value) * toNumber(childPriceEl.value)) +
            (toNumber(infantCountEl.value) * toNumber(infantPriceEl.value));
        const curr = (currencyEl.value || 'TRY').toUpperCase();
        totalTextEl.textContent = formatMoney(total) + ' ' + curr;
    }

    function applyPriceForDate(payload, dateStr) {
        const datePrices = payload?.tour?.date_prices || {};
        const row = datePrices[dateStr] || {};

        let adult = 0, child = 0, infant = 0;
        if (typeof row === 'number' || typeof row === 'string') {
            adult = toNumber(row);
        } else {
            adult = toNumber(row.adult);
            child = toNumber(row.child);
            infant = toNumber(row.infant);
        }

        adultPriceEl.value = formatMoney(adult);
        childPriceEl.value = formatMoney(child);
        infantPriceEl.value = formatMoney(infant);

        if (row.currency) {
            currencyEl.value = String(row.currency).toUpperCase();
        } else if (payload?.tour?.currency) {
            currencyEl.value = String(payload.tour.currency).toUpperCase();
        }
    }

    async function refreshPricesByDate() {
        const dateStr = tourDateEl.value;
        if (!dateStr) {
            recalcTotal();
            return;
        }

        try {
            const res = await fetch(detailsUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('Tur detayı alınamadı');
            const data = await res.json();
            applyPriceForDate(data, dateStr);
            recalcTotal();
        } catch (err) {
            console.error(err);
            recalcTotal();
        }
    }

    lockUnitPriceInputs();
    [adultCountEl, childCountEl, infantCountEl].forEach(function (el) {
        el.addEventListener('input', recalcTotal);
    });
    tourDateEl.addEventListener('change', refreshPricesByDate);

    refreshPricesByDate();
});
</script>
@endpush

