@extends('layouts.admin')

@section('title', __('Acenta Detayı'))

@section('content')
@php
    $sharedTourMeta = $sharedTourMeta ?? collect();
@endphp
<div class="container-fluid">
    <div class="row">
        <!-- Agency Information -->
        <div class="col-md-8">
            <!-- Basic Info Card -->
            <div class="ad-card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-1"></i>
                        {{ __('Acenta Bilgileri') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.agencies.edit', $agency) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit mr-1"></i>
                            {{ __('Düzenle') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="ad-table table table-bordered table-striped">
                                <tr>
                                    <th style="width: 40%">{{ __('Acenta Adı') }}:</th>
                                    <td>{{ $agency->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Kullanıcı') }}:</th>
                                    <td>
                                        @if($agency->user)
                                            <strong>{{ $agency->user->name }}</strong><br>
                                            <small class="text-muted">
                                                ID: <code>{{ $agency->user->id }}</code> • {{ $agency->user->email }}
                                            </small>
                                        @else
                                            <span class="text-muted">{{ __('Kullanıcı bağlantısı yok') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('İletişim Kişisi') }}:</th>
                                    <td>{{ $agency->contact_person ?? __('Belirtilmemiş') }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>
                                        @if($agency->email)
                                            <a href="mailto:{{ $agency->email }}">{{ $agency->email }}</a>
                                        @else
                                            {{ __('Belirtilmemiş') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Telefon') }}:</th>
                                    <td>
                                        @if($agency->phone)
                                            <a href="tel:{{ $agency->phone }}">{{ $agency->phone }}</a>
                                        @else
                                            {{ __('Belirtilmemiş') }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="ad-table table table-bordered table-striped">
                                <tr>
                                    <th style="width: 40%">{{ __('Website') }}:</th>
                                    <td>
                                        @if($agency->website)
                                            <a href="{{ $agency->website }}" target="_blank" class="btn btn-link btn-sm p-0">
                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                {{ $agency->website }}
                                            </a>
                                        @else
                                            {{ __('Belirtilmemiş') }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Komisyon Oranı') }}:</th>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            {{ $agency->formatted_commission_rate }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Durum') }}:</th>
                                    <td>
                                        @if($agency->is_active)
                                            <span class="badge badge-success badge-lg">
                                                <i class="fas fa-check mr-1"></i>{{ __('Aktif') }}
                                            </span>
                                        @else
                                            <span class="badge badge-danger badge-lg">
                                                <i class="fas fa-times mr-1"></i>{{ __('Pasif') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Kayıt Tarihi') }}:</th>
                                    <td>{{ $agency->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($agency->address)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><i class="fas fa-map-marker-alt mr-1"></i> {{ __('Adres') }}:</h6>
                                <p class="bg-light p-3 rounded">{{ $agency->address }}</p>
                            </div>
                        </div>
                    @endif

                    @if($agency->notes)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><i class="fas fa-sticky-note mr-1"></i> {{ __('Notlar') }}:</h6>
                                <p class="bg-light p-3 rounded">{{ $agency->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Tickets Card -->
            <div class="ad-card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-ticket-alt mr-1"></i>
                        {{ __('Son Biletler (:count)', ['count' => $agency->tickets->count()]) }}
                    </h3>
                    @if($agency->tickets->count() > 0)
                        <div class="card-tools">
                            <span class="badge badge-info">
                                {{ __(':count bilet', ['count' => $agency->tickets_count ?? $agency->tickets->count()]) }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="card-body table-responsive p-0">
                    @if($agency->tickets->count() > 0)
                        <table class="ad-table table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>{{ __('Takip No') }}</th>
                                    <th>{{ __('Müşteri') }}</th>
                                    <th>{{ __('Tur') }}</th>
                                    <th>{{ __('Tarih') }}</th>
                                    <th>{{ __('Toplam Fiyat') }}</th>
                                    <th>{{ __('Durum') }}</th>
                                    <th>{{ __('İşlemler') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agency->tickets as $ticket)
                                    <tr>
                                        <td>
                                            <code>{{ $ticket->tracking_no }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $ticket->customer_name }}</strong>
                                            @if($ticket->customer_phone)
                                                <br><small class="text-muted">{{ $ticket->customer_phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $ticket->tour_name ?? __('Tur belirtilmemiş') }}
                                        </td>
                                        <td>
                                            {{ $ticket->tour_date ? $ticket->tour_date->format('d.m.Y') : __('Tarih yok') }}
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                {{ $ticket->formatted_total_price }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($ticket->is_active)
                                                <span class="badge badge-success">{{ __('Aktif') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('Pasif') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.tickets.show', $ticket) }}"
                                               class="btn btn-info btn-sm"
                                               title="{{ __('Bilet Detayı') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('Henüz bilet bulunmamaktadır') }}</h5>
                            <p class="text-muted">{{ __('Bu acenta henüz hiç bilet oluşturmamış.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            @if($canManageTourSharing)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-share-alt mr-1"></i>
                            {{ __('Tur Paylaşımı') }}
                        </h3>
                        <div class="card-tools d-flex align-items-center">
                            <span class="badge badge-primary mr-2" id="selected-tour-counter">
                                {{ __(':count tur seçili', ['count' => count($sharedTourIds)]) }}
                            </span>
                            <button type="button" class="btn btn-xs btn-outline-primary" id="select-all-tours-btn">
                                <i class="fas fa-check-double mr-1"></i> {{ __('Tüm turları seç') }}
                            </button>
                        </div>
                    </div>
                    <form action="{{ route('admin.agencies.tour-sharing.update', $agency) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body p-0">
                            @if($errors->has('tour_ids') || $errors->has('tour_ids.*'))
                                <div class="alert alert-danger m-3">
                                    {{ $errors->first('tour_ids') ?? $errors->first('tour_ids.*') }}
                                </div>
                            @endif
                            @if($ownedTours->isEmpty())
                                <div class="text-center py-4">
                                    <i class="fas fa-plane text-muted fa-2x mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('Henüz paylaşılabilir turunuz yok.') }}</p>
                                </div>
                            @else
                                <!-- Arama çubuğu -->
                                <div class="p-3 border-bottom tour-search-wrapper">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                        </div>
                                        <input type="text"
                                               class="form-control"
                                               id="tour-search-input"
                                               placeholder="{{ __('Tur ismi ara...') }}"
                                               autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="tour-search-clear" style="display:none;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @php
                                        $sharedCount = count($sharedTourIds);
                                        $visibleCount = min(6, $ownedTours->count());
                                    @endphp
                                    <small class="text-muted mt-1 d-block" id="tour-count-info">
                                        @if($ownedTours->count() > 6)
                                            @if($sharedCount > 0)
                                                {{ __(':selected seçili tur + :suggested önerilen gösteriliyor (Toplam: :total)', ['selected' => min($sharedCount, 6), 'suggested' => max(0, $visibleCount - min($sharedCount, 6)), 'total' => $ownedTours->count()]) }}
                                            @else
                                                {{ __('En çok biletli :count tur gösteriliyor (Toplam: :total)', ['count' => $visibleCount, 'total' => $ownedTours->count()]) }}
                                            @endif
                                        @else
                                            {{ __(':count tur listeleniyor', ['count' => $ownedTours->count()]) }}
                                        @endif
                                    </small>
                                </div>
                                <div class="list-group list-group-flush tour-sharing-list">
                                    @foreach($ownedTours as $index => $tour)
                                        @php
                                            $maxPrice = (float) ($tour->max_display_price ?? 0);
                                            $currencyLabel = $tour->display_currency ?? ($tour->currency ?? 'TRY');
                                            $isShared = in_array($tour->id, $sharedTourIds, true);
                                            $meta = $sharedTourMeta->get($tour->id);
                                            $hasCustomPrice = $meta['has_custom_price'] ?? false;
                                            $customMaxPrice = $meta['custom_max_price'] ?? 0;
                                            $customCurrency = $meta['custom_currency'] ?? $currencyLabel;
                                            $isHidden = $index >= 6; // İlk 6 tur dışındakiler gizli
                                        @endphp
                                        <label class="list-group-item d-flex align-items-center justify-content-between tour-share-row {{ $isHidden ? 'tour-hidden-row' : '' }}" 
                                               data-tour-row="{{ $tour->id }}"
                                               data-tour-name-search="{{ mb_strtolower($tour->name) }}"
                                               style="{{ $isHidden ? 'display:none;' : '' }}">
                                            <div class="d-flex align-items-center">
                            <input type="checkbox"
                                   name="tour_ids[]"
                                   value="{{ $tour->id }}"
                                   class="mr-3 tour-share-checkbox"
                                   data-tour-name="{{ $tour->name }}"
                                   @checked($isShared)>
                                                <div>
                                                    <strong>{{ $tour->name }}</strong>
                                                    <div class="small text-muted">
                                                        {{ __(':count bilet', ['count' => $tour->tickets_count ?? 0]) }} •
                                                        {{ $tour->is_active ? __('Aktif') : __('Pasif') }}
                                                    </div>
                                                    @if($hasCustomPrice && $customMaxPrice > 0)
                                                        <span class="badge badge-warning mt-1 tour-custom-indicator">
                                                            <i class="fas fa-star mr-1"></i>
                                                            {{ __('Özel: :price :currency', ['price' => number_format($customMaxPrice, 2), 'currency' => $customCurrency]) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right ml-3 tour-price-actions">
                                                <span class="badge badge-light d-block mb-1 tour-price-badge"
                                                      data-base-price="{{ $maxPrice }}"
                                                      data-base-currency="{{ $currencyLabel }}"
                                                      data-has-custom="{{ $hasCustomPrice ? '1' : '0' }}"
                                                      data-custom-price="{{ $customMaxPrice }}"
                                                      data-custom-currency="{{ $customCurrency }}">
                                                    @if($hasCustomPrice && $customMaxPrice > 0)
                                                        {{ number_format($customMaxPrice, 2) }} {{ $customCurrency }}
                                                    @elseif($maxPrice > 0)
                                                        {{ number_format($maxPrice, 2) }} {{ $currencyLabel }}
                                                    @else
                                                        0 {{ $currencyLabel }}
                                                    @endif
                                                </span>
                                                <button type="button"
                                                        class="btn btn-link btn-sm p-0 tour-custom-price-btn"
                                                        data-tour-id="{{ $tour->id }}"
                                                        data-tour-name="{{ $tour->name }}"
                                                        data-fetch-url="{{ route('admin.agencies.tour-sharing.pricing.show', [$agency, $tour]) }}"
                                                        data-save-url="{{ route('admin.agencies.tour-sharing.pricing.update', [$agency, $tour]) }}">
                                                    <i class="fas fa-tags mr-1"></i> {{ __('Özel fiyat gir') }}
                                                </button>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @if($ownedTours->count() > 6)
                                <button type="button" class="show-all-tours-btn" id="show-all-tours-btn">
                                    <i class="fas fa-chevron-down"></i>
                                    <span>{{ __('Tüm Turları Göster (:count daha)', ['count' => $ownedTours->count() - 6]) }}</span>
                                </button>
                                @endif
                            @endif
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save mr-1"></i> {{ __('Paylaşımı Kaydet') }}
                            </button>
                        </div>
                    </form>
                </div>
            @elseif($agency->user)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('Tur paylaşımı yalnızca bağlantı kurduğunuz acentalar için kullanılabilir.') }}
                </div>
            @endif
            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        {{ __('Hızlı İşlemler') }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.agencies.edit', $agency) }}" class="btn btn-warning w-100">
                            <i class="fas fa-edit mr-1"></i>
                            {{ __('Düzenle') }}
                        </a>
                        <a href="{{ route('admin.tickets.create') }}?agency_id={{ $agency->id }}" class="btn btn-primary w-100">
                            <i class="fas fa-plus mr-1"></i>
                            {{ __('Yeni Bilet Ekle') }}
                        </a>
                        <a href="{{ route('admin.agencies.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-list mr-1"></i>
                            {{ __('Acenta Listesi') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        {{ __('İstatistikler') }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-ticket-alt"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ __('Toplam Bilet') }}</span>
                            <span class="info-box-number">{{ $agency->tickets_count ?? $agency->tickets->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ __('Komisyon Oranı') }}</span>
                            <span class="info-box-number">{{ $agency->formatted_commission_rate }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ __('Üyelik Süresi') }}</span>
                            <span class="info-box-number">{{ __(':count gün', ['count' => $agency->created_at->diffInDays(now())]) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Card -->
            @if($agency->email || $agency->phone || $agency->website)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-address-book mr-1"></i>
                            {{ __('İletişim') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($agency->email)
                            <p>
                                <i class="fas fa-envelope mr-2"></i>
                                <a href="mailto:{{ $agency->email }}">{{ $agency->email }}</a>
                            </p>
                        @endif
                        @if($agency->phone)
                            <p>
                                <i class="fas fa-phone mr-2"></i>
                                <a href="tel:{{ $agency->phone }}">{{ $agency->phone }}</a>
                            </p>
                        @endif
                        @if($agency->website)
                            <p>
                                <i class="fas fa-globe mr-2"></i>
                                <a href="{{ $agency->website }}" target="_blank">{{ __("Website'yi Ziyaret Et") }}</a>
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Özel Fiyat Modal -->
<div class="modal fade" id="tourPricingModal" tabindex="-1" role="dialog" aria-hidden="true" data-agency-name="{{ $agency->name }}">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content tour-sharing-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tags mr-1"></i>
                    {{ __('Özel Fiyat') }}: <span data-field="tour-name">-</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    {!! __('Bu takvimde belirlediğiniz fiyatlar yalnızca :agency için geçerlidir. Turun genel fiyatları değişmez.', ['agency' => '<strong data-field="agency-name">' . $agency->name . '</strong>']) !!}
                </div>
                <div id="sharing-modal-feedback" class="alert d-none"></div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center text-muted small mb-2">
                            <div class="mr-3">{{ __('Seçili gün') }}: <strong id="sharing-stat-selected-days">0</strong></div>
                            <div class="mr-3">{{ __('Fiyatlanan gün') }}: <strong id="sharing-stat-priced-days">0</strong></div>
                            <div class="mr-3">{{ __('Para birimi') }}: <strong id="sharing-current-currency">-</strong></div>
                            <div class="ml-auto">
                                <button type="button" class="btn btn-xs btn-outline-secondary mr-1" id="sharing-clear-selection" title="{{ __('Seçimi temizle') }}">
                                    <i class="fas fa-times mr-1"></i> {{ __('Seçimi Temizle') }}
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger" id="sharing-delete-selected" title="{{ __('Seçili günlerin fiyatını sil') }}">
                                    <i class="fas fa-trash mr-1"></i> {{ __('Seçilileri Sil') }}
                                </button>
                            </div>
                        </div>
                        <div class="alert alert-light border" id="sharing-availability-summary">
                            {{ __('Henüz seçim yapılmadı.') }}
                        </div>
                        <div class="year-planner mb-3" id="sharing-year-planner"></div>
                        <div class="selected-dates-container" id="sharing-selected-list" style="display:none"></div>
                    </div>
                </div>
                <input type="hidden" id="sharing-selected_dates">
                <input type="hidden" id="sharing-selected_prices">
                <input type="hidden" id="sharing-currency">
                <div id="sharing-weekday-popup" class="weekday-popup" style="display:none">
                    <div class="d-flex flex-wrap" style="gap:6px;">
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="1">{{ __('Pzt') }}</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="2">{{ __('Sal') }}</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="3">{{ __('Çar') }}</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="4">{{ __('Per') }}</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="5">{{ __('Cum') }}</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="6">{{ __('Cmt') }}</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="7">{{ __('Paz') }}</button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">{{ __('İki tarih arasında gösterilen günleri seçin.') }}</small>
                        <button type="button" class="btn btn-xs btn-link p-0" id="sharing-weekday-close">{{ __('Kapat') }}</button>
                    </div>
                </div>
                <div id="sharing-price-popup" class="weekday-popup" style="display:none; width:280px;">
                    <div class="mb-2"><strong><i class="fas fa-tags mr-1"></i> {{ __('Fiyat Bilgileri') }}</strong></div>
                    <p class="small text-muted mb-2">{{ __('Seçili günlere özel fiyat uygulayın. Boş alanlar 0 kabul edilir.') }}</p>
                    <select class="form-control form-control-sm mb-2" id="sharing-price-currency">
                        <option value="TRY">₺ {{ __('Türk Lirası') }} (TRY)</option>
                        <option value="USD">$ {{ __('Amerikan Doları') }} (USD)</option>
                        <option value="EUR">€ Euro (EUR)</option>
                        <option value="GBP">£ {{ __('İngiliz Sterlini') }} (GBP)</option>
                        <option value="RUB">₽ {{ __('Rus Rublesi') }} (RUB)</option>
                    </select>
                    <input type="number" step="0.01" class="form-control form-control-sm mb-2" id="sharing-price-adult" placeholder="{{ __('Yetişkin') }} (₺)">
                    <input type="number" step="0.01" class="form-control form-control-sm mb-2" id="sharing-price-child" placeholder="{{ __('Çocuk') }} (₺)">
                    <input type="number" step="0.01" class="form-control form-control-sm mb-3" id="sharing-price-infant" placeholder="{{ __('Bebek') }} (₺)">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-xs btn-primary" id="sharing-apply-price">
                            <i class="fas fa-check mr-1"></i> {{ __('Uygula') }}
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="sharing-price-reset">
                            <i class="fas fa-eraser mr-1"></i> {{ __('Temizle') }}
                        </button>
                        <button type="button" class="btn btn-xs btn-link" id="sharing-price-close">{{ __('Kapat') }}</button>
                    </div>
                </div>
                <div id="sharing-planner-menu" class="sharing-planner-menu" style="display:none">
                    <button type="button" class="btn btn-xs btn-outline-primary" id="sharing-menu-fill">
                        <i class="fas fa-fill-drip mr-1"></i> {{ __('Arayı Doldur') }}
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" id="sharing-menu-clear">
                        <i class="fas fa-times mr-1"></i> {{ __('Temizle') }}
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="sharing-menu-weekday">
                        <i class="fas fa-calendar-day mr-1"></i> {{ __('Özel Tarih') }}
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success" id="sharing-menu-price">
                        <i class="fas fa-tags mr-1"></i> {{ __('Fiyat Ekle') }}
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning" id="sharing-menu-clear-prices">
                        <i class="fas fa-trash mr-1"></i> {{ __('Fiyatı Kaldır') }}
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger mr-auto" id="sharing-reset-prices">
                    <i class="fas fa-undo mr-1"></i> {{ __('Özel fiyatı sıfırla') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Kapat') }}</button>
                <button type="button" class="btn btn-primary" id="sharing-save-prices">
                    <i class="fas fa-save mr-1"></i> {{ __('Kaydet') }}
                </button>
            </div>
        </div>
    </div>
</div>
@stop
<!-- css -->
@push('css')
<style>
    .info-box {
        margin-bottom: 1rem;
    }
    .badge-lg {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    .tour-sharing-list .list-group-item {
        flex-wrap: wrap;
        gap: 12px;
    }
    /* Tur arama stilleri */
    #tour-search-input {
        border-left: none;
    }
    #tour-search-input:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    #tour-search-clear {
        border-left: none;
    }
    #tour-count-info {
        font-size: 11px;
    }
    .show-all-tours-btn {
        width: 100%;
        border: none;
        background: #f8f9fa;
        padding: 10px;
        font-size: 13px;
        color: #4e73df;
        cursor: pointer;
        transition: background 0.2s;
    }
    .show-all-tours-btn:hover {
        background: #e9ecef;
    }
    .show-all-tours-btn i {
        margin-right: 5px;
    }
    /* Modal ortalama */
    #tourPricingModal .modal-dialog {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
        margin: 0.5rem auto;
    }
    @media (min-width: 576px) {
        #tourPricingModal .modal-dialog {
            min-height: calc(100% - 3.5rem);
            margin: 1.75rem auto;
        }
    }
    .tour-sharing-modal .year-planner { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); 
        gap: 16px; 
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .tour-sharing-modal .yp-card { 
        border: 1px solid #e3e6f0; 
        border-radius: 12px; 
        padding: 10px; 
        background: #fff; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        overflow: visible;
    }
    .tour-sharing-modal .yp-header { 
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-weight: 600; 
        font-size: 14px;
        margin-bottom: 10px; 
        color: #5a5c69;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom:6px;
    }
    .tour-sharing-modal .yp-weekdays { 
        display: grid; 
        grid-template-columns: repeat(7, minmax(28px, 1fr)); 
        gap: 2px; 
    }
    .tour-sharing-modal .yp-grid { 
        display: grid; 
        grid-template-columns: repeat(7, minmax(30px, 1fr)); 
        gap: 3px; 
    }
    .tour-sharing-modal .yp-weekdays div {
        font-size: 10px;
        text-align:center;
        color:#858796;
    }
    .tour-sharing-modal .yp-day { 
        aspect-ratio: 1; 
        display:flex; 
        flex-direction:column; 
        align-items:center; 
        justify-content:center; 
        border-radius: 6px; 
        border: 1px solid #e3e6f0; 
        font-size: 11px; 
        cursor: pointer; 
        position:relative;
        min-height:30px;
        background:#fff;
        transition:all .2s ease;
        padding: 2px;
    }
    .tour-sharing-modal .yp-day:hover:not(.out) {
        background:#f8f9fc;
        border-color:#4e73df;
    }
    .tour-sharing-modal .yp-day.out {
        color:#d1d3e2;
        cursor:not-allowed;
        background:#f8f9fc;
    }
    .tour-sharing-modal .yp-day.sel {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        color:#fff;
        border-color:#1cc88a;
        box-shadow:0 2px 6px rgba(28,200,138,0.3);
        font-weight:600;
    }
    /* Fiyat girilmiş ama seçili olmayan günler */
    .tour-sharing-modal .yp-day.has-price:not(.sel) {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-color:#28a745;
        color:#155724;
    }
    .tour-sharing-modal .yp-day.has-price:not(.sel) .day-price {
        color:#155724;
        border-color:rgba(21,87,36,0.3);
        background:rgba(255,255,255,0.7);
    }
    .tour-sharing-modal .yp-day .yp-daynum {
        display:block;
        line-height:1.1;
    }
    .tour-sharing-modal .day-price {
        font-size:10px;
        margin-top:2px;
        padding:2px 6px;
        border-radius:999px;
        border:1px solid rgba(78,115,223,0.3);
        color:#4e73df;
        background:#fff;
    }
    .tour-sharing-modal .yp-day.sel .day-price {
        color:#fff;
        border-color:rgba(255,255,255,0.5);
        background:rgba(255,255,255,0.2);
    }
    .tour-sharing-modal .selected-dates-container {
        display:flex;
        flex-wrap:wrap;
        gap:6px;
    }
    .tour-sharing-modal .selected-date-item {
        display:inline-flex;
        align-items:center;
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        color:#fff;
        padding:4px 10px;
        border-radius:20px;
        font-size:11px;
    }
    .tour-sharing-modal .selected-date-item .remove-date {
        margin-left:6px;
        cursor:pointer;
    }
    .sharing-planner-menu {
        position: absolute;
        display: none;
        gap: 6px;
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,.15);
        z-index: 10060;
    }
    .sharing-planner-menu .btn {
        min-width: 110px;
    }
    .weekday-popup {
        position: absolute;
        display: none;
        background:#fff;
        border:1px solid #e3e6f0;
        border-radius:8px;
        padding:10px;
        box-shadow:0 4px 16px rgba(0,0,0,.15);
        z-index: 10060;
    }
    .weekday-popup .wd-btn.active {
        background:#4e73df;
        color:#fff;
        border-color:#4e73df;
    }
    .tour-sharing-modal.sharing-loading {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }
    @media (max-width: 768px) {
        .tour-sharing-modal .year-planner { 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }
        .tour-sharing-modal .yp-grid {
            grid-template-columns: repeat(7, minmax(28px, 1fr));
            gap: 2px;
        }
        .tour-sharing-modal .yp-weekdays {
            grid-template-columns: repeat(7, minmax(24px, 1fr));
        }
    }

    /* ===== DARK MODE - Agency Pricing Modal ===== */
    html.dark-mode .tour-sharing-modal .modal-content {
        background: #1a1f2e !important;
        border-color: #334155 !important;
    }
    html.dark-mode .tour-sharing-modal .modal-header {
        border-color: #334155 !important;
    }
    html.dark-mode .tour-sharing-modal .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    html.dark-mode .tour-sharing-modal .modal-footer {
        border-color: #334155 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-card {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
    }
    html.dark-mode .tour-sharing-modal .yp-header {
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-weekdays div {
        color: #94a3b8 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day:hover:not(.out) {
        background: #334155 !important;
        border-color: #6366f1 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day.out {
        background: #0f172a !important;
        color: #475569 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day.sel {
        background: #374151 !important;
        color: #e2e8f0 !important;
        border-color: #6b7280 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day.sel .day-price {
        background: rgba(255,255,255,0.15) !important;
        color: #e2e8f0 !important;
        border-color: rgba(255,255,255,0.3) !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day.has-price:not(.sel) {
        background: #1e3a2f !important;
        border-color: #2d6a4f !important;
        color: #a7f3d0 !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day.has-price:not(.sel) .day-price {
        background: rgba(16,185,129,0.2) !important;
        color: #6ee7b7 !important;
        border-color: rgba(16,185,129,0.3) !important;
    }
    html.dark-mode .tour-sharing-modal .yp-day .day-price {
        background: #334155 !important;
        color: #818cf8 !important;
    }
    html.dark-mode .weekday-popup,
    html.dark-mode #sharing-price-popup {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.5) !important;
        color: #e2e8f0 !important;
    }
    html.dark-mode .weekday-popup .wd-btn {
        color: #e2e8f0 !important;
        border-color: #475569 !important;
    }
    html.dark-mode .weekday-popup .wd-btn.active {
        background: #6366f1 !important;
        color: #fff !important;
        border-color: #6366f1 !important;
    }
    html.dark-mode .sharing-planner-menu {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.5) !important;
    }
    html.dark-mode .sharing-planner-menu .btn {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #e2e8f0 !important;
    }
    html.dark-mode .sharing-planner-menu .btn:hover {
        background: #475569 !important;
    }
    html.dark-mode #sharing-price-popup .form-control {
        background: #0f172a !important;
        border-color: #475569 !important;
        color: #e2e8f0 !important;
    }
</style>
@endpush

@push('js')
<script>
const sharingI18n = {!! json_encode([
    'monthSelect' => __('Seç'),
    'monthClear' => __('Temizle'),
    'weekdays' => [__('Pzt'), __('Sal'), __('Çar'), __('Per'), __('Cum'), __('Cmt'), __('Paz')],
    'months' => [__('Ocak'), __('Şubat'), __('Mart'), __('Nisan'), __('Mayıs'), __('Haziran'), __('Temmuz'), __('Ağustos'), __('Eylül'), __('Ekim'), __('Kasım'), __('Aralık')],
    'noPricesUpdated' => __('Özel fiyatlar yüklenemedi.'),
    'unknownError' => __('Bilinmeyen bir hata oluştu.'),
    'needTwoDatesForWeekday' => __('Özel tarih için önce en az iki tarih seçmelisiniz.'),
    'needRangeForWeekday' => __('Özel tarih uygulamak için önce aralık seçin.'),
    'needTwoDatesForFill' => __('Arayı doldurmak için en az iki tarih seçmelisiniz.'),
    'selectDaysFirst' => __('Önce takvimden gün seçin.'),
    'enterPositivePrice' => __("En az bir fiyat alanına 0'dan büyük değer girin."),
    'priceAppliedFor' => __(':count gün için fiyat uygulandı (:currency).'),
    'noSelectedDays' => __('Seçili gün bulunmuyor.'),
    'selectionCleared' => __('Seçim temizlendi.'),
    'noPricesToDelete' => __('Silinecek fiyat bulunmuyor.'),
    'daysPriceDeleted' => __(':count günün fiyatı silindi.'),
    'saveError' => __('Kaydedilirken hata oluştu.'),
    'pricesRemovedForSelected' => __('Seçili günlerdeki fiyatlar kaldırıldı.'),
    'noCustomPriceOnSelected' => __('Seçili günlerde özel fiyat bulunamadı.'),
    'noSelectionMade' => __('Henüz seçim yapılmadı.'),
    'daysSelected' => __(':count gün seçildi'),
    'daysPriced' => __(':count gün fiyatlandırıldı'),
    'adultLabel' => __('Yetişkin'),
    'childLabel' => __('Çocuk'),
    'infantLabel' => __('Bebek'),
    'confirmResetPrices' => __('Bu acenta için girilmiş tüm özel fiyatları silmek istediğinize emin misiniz?'),
    'specialLabel' => __('Özel'),
    'toursSelected' => __(':count tur seçili'),
    'showAllTours' => __('Tüm Turları Göster (:count daha)'),
    'showLess' => __('Daha Az Göster'),
    'allToursShowing' => __('Tüm :count tur gösteriliyor'),
    'toursListed' => __(':count tur listeleniyor'),
    'selectedPlusSuggested' => __(':selected seçili tur + :suggested önerilen gösteriliyor (Toplam: :total)'),
    'selectedShowing' => __(':count seçili tur gösteriliyor (Toplam: :total)'),
    'mostBookedShowing' => __('En çok biletli :count tur gösteriliyor (Toplam: :total)'),
    'noMatchFound' => __(':query ile eşleşen tur bulunamadı'),
    'toursFound' => __(':count tur bulundu'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
(function($){
    function formatDate(dateStr) {
        const date = new Date(dateStr + 'T00:00:00');
        return date.toLocaleDateString({!! json_encode(app()->getLocale() === 'en' ? 'en-US' : 'tr-TR', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}, { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function parseLocalDate(isoString) {
        return new Date(isoString + 'T00:00:00');
    }

    const TourSharePricing = {
        modal: null,
        plannerEl: null,
        priceMap: {},
        selectedDates: new Set(),
        lastClicked: null,
        rangeAnchors: [],
        currentWeekdayPair: null,
        weekdayPopup: null,
        pricePopup: null,
        plannerMenu: null,
        menuAnchorDate: null,
        currency: 'TRY',
        fetchUrl: null,
        saveUrl: null,
        currentTourId: null,
        agencyName: '',
        _bsModal: null,
        init() {
            this.modal = $('#tourPricingModal');
            if (!this.modal.length) {
                return;
            }
            // Bootstrap 5 native modal instance
            try {
                this._bsModal = new bootstrap.Modal(this.modal[0]);
            } catch(e) {
                this._bsModal = null;
            }
            this.plannerEl = this.modal.find('#sharing-year-planner');
            this.agencyName = this.modal.data('agency-name');
            this.weekdayPopup = $('#sharing-weekday-popup');
            this.pricePopup = $('#sharing-price-popup');
            this.plannerMenu = $('#sharing-planner-menu');
            this.bindEvents();
        },
        showModal() {
            if (this._bsModal) {
                this._bsModal.show();
            } else if (this.modal && this.modal.modal) {
                this.modal.modal('show');
            }
        },
        hideModal() {
            if (this._bsModal) {
                this._bsModal.hide();
            } else if (this.modal && this.modal.modal) {
                this.modal.modal('hide');
            }
        },
        bindEvents() {
            const self = this;
            $(document).on('click', '.tour-custom-price-btn', function(){
                const $btn = $(this);
                self.open({
                    tourId: $btn.data('tour-id'),
                    tourName: $btn.data('tour-name'),
                    fetchUrl: $btn.data('fetch-url'),
                    saveUrl: $btn.data('save-url')
                });
            });

        this.modal.on('click', '.yp-day', function(event){
            const $cell = $(this);
            if ($cell.hasClass('out')) {
                return;
            }
            if (event.which && event.which !== 1) {
                return;
            }
            const date = $cell.data('date');
            if (event.shiftKey && self.lastClicked) {
                self.selectRange(self.lastClicked, date);
                self.lastClicked = date;
                self.menuAnchorDate = date;
                self.syncState();
                return;
            }
            if (!self.selectedDates.has(date)) {
                self.selectedDates.add(date);
                self.rememberAnchor(date);
                self.lastClicked = date;
                self.menuAnchorDate = date;
                self.syncState();
            } else {
                self.lastClicked = date;
                self.menuAnchorDate = date;
                self.showPlannerMenuNearElement($cell, date);
            }
        });

        this.modal.on('contextmenu', '.yp-day', function(event){
            const $cell = $(this);
            if ($cell.hasClass('out')) {
                return;
            }
            const date = $cell.data('date');
            event.preventDefault();
            // Eğer kullanıcı önceden seçmemişse ama fiyat varsa, doğrudan sil
            if (!self.selectedDates.has(date) && self.priceMap[date]) {
                delete self.priceMap[date];
                self.refreshAutoSelected();
                self.syncState();
                return;
            }
            // Seçililerden silme
            if (self.selectedDates.has(date)) {
                self.selectedDates.delete(date);
                delete self.priceMap[date];
                self.removeAnchor(date);
                self.refreshAutoSelected();
                if (self.menuAnchorDate === date) {
                    self.menuAnchorDate = Array.from(self.selectedDates).pop() || null;
                }
                self.syncState();
            }
        });

        this.modal.on('click', '.month-select', function(){
            const month = Number($(this).data('month'));
            self.selectMonth(month);
            self.menuAnchorDate = self.rangeAnchors[1] || self.rangeAnchors[0] || null;
            self.syncState();
        });

        this.modal.on('click', '.month-clear', function(){
            const month = Number($(this).data('month'));
            self.clearMonth(month);
            self.syncState();
        });

        $('#sharing-menu-fill').on('click', function(){
            self.fillRange();
        });

        $('#sharing-menu-clear').on('click', function(){
            self.clearSelection();
        });

        $('#sharing-menu-weekday').on('click', function(e){
            e.stopPropagation();
            if (self.plannerMenu && self.plannerMenu.is(':visible')) {
                self.openWeekdayPopup(self.plannerMenu);
            }
        });

        $('#sharing-menu-price').on('click', function(e){
            e.stopPropagation();
            if (self.plannerMenu && self.plannerMenu.is(':visible')) {
                self.openPricePopup(self.plannerMenu);
            }
        });

        $('#sharing-menu-clear-prices').on('click', function(){
            self.clearPricesForSelection();
        });

            this.weekdayPopup.on('click', function(e){
                e.stopPropagation();
            });

        this.pricePopup.on('click mousedown', function(e){
                e.stopPropagation();
            });

            this.weekdayPopup.on('click', '.wd-btn', function(e){
                e.preventDefault();
                const $btn = $(this);
                $btn.toggleClass('active');
                const wd = parseInt($btn.data('wd'), 10);
                self.applyWeekdayFilter(wd, $btn.hasClass('active'));
            });

            $('#sharing-weekday-close').on('click', function(){
                self.hideWeekdayPopup();
            });

            $('#sharing-price-close').on('click', function(){
                self.hidePricePopup();
            });

            $('#sharing-price-reset').on('click', function(){
                $('#sharing-price-adult,#sharing-price-child,#sharing-price-infant').val('');
            });

        $(document).on('click', function(event){
            if (!$(event.target).closest('#sharing-weekday-popup, #sharing-menu-weekday').length) {
                    self.hideWeekdayPopup();
                }
            if (!$(event.target).closest('#sharing-price-popup, #sharing-menu-price').length) {
                    self.hidePricePopup();
                }
            if (!$(event.target).closest('#sharing-planner-menu, .yp-day').length) {
                self.hidePlannerMenu();
            }
            });

            $('#sharing-apply-price').on('click', function(){
                self.applyPrice();
            });

            // Para birimi değiştiğinde placeholder'ları güncelle
            $('#sharing-price-currency').on('change', function(){
                const curr = $(this).val();
                const symbols = { 'TRY': '₺', 'USD': '$', 'EUR': '€', 'GBP': '£', 'RUB': '₽' };
                const symbol = symbols[curr] || curr;
                $('#sharing-price-adult').attr('placeholder', sharingI18n.adultLabel + ' (' + symbol + ')');
                $('#sharing-price-child').attr('placeholder', sharingI18n.childLabel + ' (' + symbol + ')');
                $('#sharing-price-infant').attr('placeholder', sharingI18n.infantLabel + ' (' + symbol + ')');
            });

            $('#sharing-save-prices').on('click', function(){
                self.save(false);
            });

            $('#sharing-reset-prices').on('click', function(){
                if (confirm(sharingI18n.confirmResetPrices)) {
                    self.save(true);
                }
            });

            // Seçimi temizle butonu
            $('#sharing-clear-selection').on('click', function(){
                if (!self.selectedDates.size) {
                    self.showFeedback(sharingI18n.noSelectedDays, false);
                    return;
                }
                self.selectedDates = new Set();
                self.rangeAnchors = [];
                self.syncState();
                self.showFeedback(sharingI18n.selectionCleared, true);
            });

            // Seçili günlerin fiyatını sil butonu
            $('#sharing-delete-selected').on('click', function(){
                // Seçim yoksa otomatik olarak fiyatlı tüm günleri seç
                if (!self.selectedDates.size) {
                    const pricedDates = Object.keys(self.priceMap);
                    if (!pricedDates.length) {
                        self.showFeedback(sharingI18n.noPricesToDelete, false);
                        return;
                    }
                    self.selectedDates = new Set(pricedDates);
                }
                const count = self.selectedDates.size;
                self.selectedDates.forEach(date => {
                    delete self.priceMap[date];
                });
                self.selectedDates = new Set();
                self.rangeAnchors = [];
                self.refreshAutoSelected();
                self.syncState();
                self.showFeedback(sharingI18n.daysPriceDeleted.replace(':count', count), true);
            });
        },
        refreshAutoSelected() {
            this.autoSelected = new Set(Object.keys(this.priceMap));
        },
        open(config) {
            this.fetchUrl = config.fetchUrl;
            this.saveUrl = config.saveUrl;
            this.currentTourId = config.tourId;
            this.selectedDates = new Set();
            this.autoSelected = new Set(); // fiyatlı günleri görselde seçili göstermek için
            this.priceMap = {};
            this.lastClicked = null;
            this.rangeAnchors = [];
            this.currentWeekdayPair = null;
            this.hideWeekdayPopup();
            this.hidePricePopup();
            this.hidePlannerMenu();
            this.menuAnchorDate = null;
            this.showFeedback('');

            this.modal.find('[data-field="tour-name"]').text(config.tourName);
            this.modal.find('[data-field="agency-name"]').text(this.agencyName);

            this.loadData();
        },
        loadData() {
            const self = this;
            self.toggleLoading(true);
            fetch(this.fetchUrl, { headers: self.headers() })
                .then(resp => {
                    if (!resp.ok) {
                        throw new Error(sharingI18n.noPricesUpdated);
                    }
                    return resp.json();
                })
                .then(data => {
                    self.currency = (data.custom_pricing.currency || data.tour.currency || 'TRY').toUpperCase();
                    self.priceMap = self.normalizePriceMap(data.custom_pricing.date_prices || {}, self.currency);
                    // Kullanıcı seçimi BOŞ, fiyatlı günler autoSelected ile görselde seçili gösterilir
                    self.selectedDates = new Set();
                    self.autoSelected = new Set(Object.keys(self.priceMap));
                    self.rangeAnchors = [];
                    self.currentWeekdayPair = null;
                    self.hideWeekdayPopup();
                    self.hidePricePopup();
                    self.hidePlannerMenu();
                    self.menuAnchorDate = null;
                    $('#sharing-currency').val(self.currency);
                    $('#sharing-price-currency').val(self.currency).trigger('change');
                    $('#sharing-price-adult,#sharing-price-child,#sharing-price-infant').val('');
                    self.syncState();
                    self.toggleLoading(false);
                    self.showModal();
                })
                .catch(err => {
                    self.toggleLoading(false);
                    self.showFeedback(err.message || sharingI18n.unknownError, false);
                });
        },
        rememberAnchor(date) {
            if (!date) {
                return;
            }
            if (this.rangeAnchors.includes(date)) {
                return;
            }
            this.rangeAnchors.push(date);
            if (this.rangeAnchors.length > 2) {
                this.rangeAnchors.shift();
            }
        },
        removeAnchor(date) {
            const idx = this.rangeAnchors.indexOf(date);
            if (idx !== -1) {
                this.rangeAnchors.splice(idx, 1);
            }
        },
        getRangePair() {
            if (this.rangeAnchors.length >= 2) {
                return [...this.rangeAnchors];
            }
            const sorted = Array.from(this.selectedDates).sort();
            if (sorted.length >= 2) {
                return [sorted[0], sorted[sorted.length - 1]];
            }
            return null;
        },
        clearSelection() {
            this.selectedDates.clear();
            this.rangeAnchors = [];
            this.lastClicked = null;
            this.hideWeekdayPopup();
            this.hidePricePopup();
            this.syncState();
        },
        openWeekdayPopup($trigger) {
            const pair = this.getRangePair();
            if (!pair) {
                this.showFeedback(sharingI18n.needTwoDatesForWeekday, false);
                return;
            }
            this.currentWeekdayPair = pair;
            if (!this.weekdayPopup.parent().is('body')) {
                $('body').append(this.weekdayPopup);
            }
            this.weekdayPopup.find('.wd-btn').removeClass('active');
            const rect = $trigger[0].getBoundingClientRect();
            const top = rect.bottom + (window.pageYOffset || document.documentElement.scrollTop) + 8;
            const left = rect.left + (window.pageXOffset || document.documentElement.scrollLeft);
            this.weekdayPopup.css({ top: `${top}px`, left: `${left}px` }).show();
        },
        hideWeekdayPopup() {
            if (this.weekdayPopup) {
                this.weekdayPopup.hide();
                this.weekdayPopup.find('.wd-btn').removeClass('active');
            }
            this.currentWeekdayPair = null;
        },
        openPricePopup($trigger) {
            if (!this.pricePopup) {
                return;
            }
            if (!this.pricePopup.parent().is('body')) {
                $('body').append(this.pricePopup);
            }
            this.hideWeekdayPopup();
            this.hidePlannerMenu();
            this.updatePricePlaceholders();
            const anchorCell = this.getAnchorCell();
            let targetRect = null;
            if (anchorCell && anchorCell.length) {
                targetRect = anchorCell[0].getBoundingClientRect();
            } else if ($trigger && $trigger.length) {
                targetRect = $trigger[0].getBoundingClientRect();
            } else {
                targetRect = this.modal[0].getBoundingClientRect();
            }
            // Ekran ortasına yerleştir
            const popupWidth = this.pricePopup.outerWidth() || 300;
            const popupHeight = this.pricePopup.outerHeight() || 260;
            const viewportW = window.innerWidth || document.documentElement.clientWidth;
            const viewportH = window.innerHeight || document.documentElement.clientHeight;
            const top = Math.max(24, (viewportH - popupHeight) / 2);
            const left = Math.max(24, (viewportW - popupWidth) / 2);
            this.pricePopup.css({
                position: 'fixed',
                top: `${top}px`,
                left: `${left}px`,
                right: 'auto',
                bottom: 'auto',
                width: `${popupWidth}px`
            }).show();
            $('#sharing-price-adult,#sharing-price-child,#sharing-price-infant').prop('disabled', false);
            setTimeout(() => {
                const $input = $('#sharing-price-adult');
                $input.trigger('focus');
                if ($input[0] && $input[0].select) {
                    $input[0].select();
                }
            }, 0);
        },
        hidePricePopup() {
            if (this.pricePopup) {
                this.pricePopup.hide();
            }
        },
        updatePricePlaceholders() {
            const symbol = this.getCurrencySymbol();
            $('#sharing-price-adult').attr('placeholder', `${sharingI18n.adultLabel} (${symbol})`);
            $('#sharing-price-child').attr('placeholder', `${sharingI18n.childLabel} (${symbol})`);
            $('#sharing-price-infant').attr('placeholder', `${sharingI18n.infantLabel} (${symbol})`);
        },
        getCurrencySymbol(currency) {
            const curr = (currency || this.currency || 'TRY').toUpperCase();
            const symbols = { 'TRY': '₺', 'USD': '$', 'EUR': '€', 'GBP': '£', 'RUB': '₽' };
            return symbols[curr] || curr;
        },
        applyWeekdayFilter(day, isActive) {
            const pair = this.currentWeekdayPair || this.getRangePair();
            if (!pair) {
                this.showFeedback(sharingI18n.needRangeForWeekday, false);
                this.hideWeekdayPopup();
                return;
            }
            let [startStr, endStr] = pair;
            let startDate = parseLocalDate(startStr);
            let endDate = parseLocalDate(endStr);
            if (startDate > endDate) {
                [startDate, endDate] = [endDate, startDate];
                [startStr, endStr] = [endStr, startStr];
            }
            const targetWeekday = (day === 7 ? 0 : day) % 7; // convert to JS weekday (0=Sunday)
            const cursor = new Date(startDate.getTime());
            while (cursor <= endDate) {
                const iso = cursor.toISOString().slice(0, 10);
                const weekday = cursor.getDay();
                if (weekday === targetWeekday) {
                    if (isActive) {
                        this.selectedDates.add(iso);
                    } else if (iso !== startStr && iso !== endStr) {
                        this.selectedDates.delete(iso);
                        delete this.priceMap[iso];
                    }
                }
                cursor.setDate(cursor.getDate() + 1);
            }
            this.pruneAnchors();
            this.refreshAutoSelected();
            this.syncState();
        },
        pruneAnchors() {
            this.rangeAnchors = this.rangeAnchors.filter(date => this.selectedDates.has(date));
        },
        selectRange(start, end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const step = startDate <= endDate ? 1 : -1;
            let current = new Date(startDate);
            while ((step === 1 && current <= endDate) || (step === -1 && current >= endDate)) {
                const ds = current.toISOString().slice(0,10);
                this.selectedDates.add(ds);
                current.setDate(current.getDate() + step);
            }
            const startIso = startDate.toISOString().slice(0,10);
            const endIso = endDate.toISOString().slice(0,10);
            if (step === 1) {
                this.rangeAnchors = [startIso, endIso];
            } else {
                this.rangeAnchors = [endIso, startIso];
            }
        },
        fillRange() {
            if (this.selectedDates.size < 2) {
                this.showFeedback(sharingI18n.needTwoDatesForFill, false);
                return;
            }
            const sorted = Array.from(this.selectedDates).sort();
            this.selectRange(sorted[0], sorted[sorted.length - 1]);
            this.menuAnchorDate = sorted[sorted.length - 1];
            this.syncState();
        },
        selectMonth(month) {
            const year = new Date().getFullYear();
            for (let day = 1; day <= 31; day++) {
                const date = new Date(year, month - 1, day);
                if (date.getMonth() !== month - 1) break;
                this.selectedDates.add(date.toISOString().slice(0,10));
            }
            const firstDay = new Date(year, month - 1, 1).toISOString().slice(0,10);
            const lastDay = new Date(year, month - 1, new Date(year, month, 0).getDate()).toISOString().slice(0,10);
            this.rangeAnchors = [firstDay, lastDay];
            this.menuAnchorDate = lastDay;
        },
        clearMonth(month) {
            const toRemove = [];
            // Seçili günlerden ilgili ayı temizle
            this.selectedDates.forEach(date => {
                const dt = new Date(date);
                if ((dt.getMonth() + 1) === month) {
                    toRemove.push(date);
                }
            });
            // Fiyatlı günlerden de ilgili ayı temizle
            Object.keys(this.priceMap).forEach(date => {
                const dt = new Date(date);
                if ((dt.getMonth() + 1) === month) {
                    toRemove.push(date);
                }
            });
            toRemove.forEach(date => {
                this.selectedDates.delete(date);
                delete this.priceMap[date];
            });
            this.pruneAnchors();
            this.refreshAutoSelected();
            if (!this.selectedDates.size) {
                this.hideWeekdayPopup();
                this.hidePlannerMenu();
                this.menuAnchorDate = null;
            } else if (this.menuAnchorDate && !this.selectedDates.has(this.menuAnchorDate)) {
                this.menuAnchorDate = Array.from(this.selectedDates).pop() || null;
            }
        },
        applyPrice() {
            if (!this.selectedDates.size) {
                this.showFeedback(sharingI18n.selectDaysFirst, false);
                return;
            }
            const adult = parseFloat($('#sharing-price-adult').val()) || 0;
            const child = parseFloat($('#sharing-price-child').val()) || 0;
            const infant = parseFloat($('#sharing-price-infant').val()) || 0;
            const currency = $('#sharing-price-currency').val() || this.currency || 'TRY';
            if (adult <= 0 && child <= 0 && infant <= 0) {
                this.showFeedback(sharingI18n.enterPositivePrice, false);
                return;
            }
            this.ensureRangeFilledForPricing();
            const appliedCount = this.selectedDates.size;
            this.selectedDates.forEach(date => {
                this.priceMap[date] = { adult, child, infant, currency };
            });
            // Fiyat uygulandıktan sonra seçimi temizle - böylece bir sonraki fiyat sadece yeni seçilen günlere uygulanır
            this.selectedDates = new Set();
            this.rangeAnchors = [];
            this.refreshAutoSelected();
            this.syncState();
            this.showFeedback(sharingI18n.priceAppliedFor.replace(':count', appliedCount).replace(':currency', currency), true);
            this.hidePricePopup();
            // Fiyat input'larını temizle
            $('#sharing-price-adult,#sharing-price-child,#sharing-price-infant').val('');
        },
        syncState() {
            const dates = Array.from(this.selectedDates).sort();
            $('#sharing-selected_dates').val(JSON.stringify(dates));
            $('#sharing-selected_prices').val(JSON.stringify(this.priceMap));
            $('#sharing-current-currency').text(this.currency);
            this.renderPlanner();
            this.renderSelected(dates);
            this.updateStats();
            if (this.menuAnchorDate && !this.selectedDates.has(this.menuAnchorDate)) {
                this.menuAnchorDate = dates.length ? dates[dates.length - 1] : null;
            }
            if (this.menuAnchorDate) {
                this.showPlannerMenuFor(this.menuAnchorDate);
            } else {
                this.hidePlannerMenu();
            }
        },
        renderPlanner() {
            const months = sharingI18n.months;
            const year = new Date().getFullYear();
            let html = '';
            for (let month=1; month<=12; month++) {
                const first = new Date(year, month-1, 1);
                const start = new Date(first);
                const dow = start.getDay();
                const offset = dow === 0 ? -6 : 1 - dow;
                start.setDate(start.getDate() + offset);
                html += '<div class="yp-card">';
                html += `<div class="yp-header"><span>${months[month-1]}</span><div><button type="button" class="btn btn-xs btn-outline-success month-select" data-month="${month}">${sharingI18n.monthSelect}</button> <button type="button" class="btn btn-xs btn-outline-danger month-clear" data-month="${month}">${sharingI18n.monthClear}</button></div></div>`;
                html += '<div class="yp-weekdays">' + sharingI18n.weekdays.map(d => `<div>${d}</div>`).join('') + '</div>';
                html += '<div class="yp-grid">';
                for (let i=0;i<42;i++){
                    const d = new Date(start); d.setDate(start.getDate()+i);
                    const ds = d.toISOString().slice(0,10);
                    const inMonth = d.getMonth() === month-1;
                    // Seçili/auto-seçili günleri sadece ilgili ayda göster
                    const sel = inMonth && this.selectedDates.has(ds);
                    const price = this.priceMap[ds];
                    const hasPrice = price && inMonth && (price.adult > 0 || price.child > 0 || price.infant > 0);
                    let priceText = '';
                    if (price && inMonth) {
                        const maxVal = Math.max(price.adult || 0, price.child || 0, price.infant || 0);
                        if (maxVal > 0) {
                            // Her günün kendi para birimini kullan
                            const daySymbol = this.symbol(price.currency);
                            priceText = `<span class="day-price">${daySymbol}${maxVal}</span>`;
                        }
                    }
                    const autoSel = inMonth && this.autoSelected && this.autoSelected.has(ds);
                    const isSelected = sel || autoSel;
                    html += `<div class="yp-day ${inMonth?'':'out'} ${isSelected?'sel':''} ${hasPrice?'has-price':''}" data-date="${ds}"><span class="yp-daynum">${d.getDate()}</span>${priceText}</div>`;
                }
                html += '</div></div>';
            }
            this.plannerEl.html(html);
        },
        renderSelected(dates) {
            const $list = $('#sharing-selected-list');
            if (!dates.length) {
                $list.hide().empty();
                return;
            }
            let html = '';
            dates.forEach(date => {
                html += `<span class="selected-date-item"><i class="fas fa-calendar-day mr-1"></i>${formatDate(date)}</span>`;
            });
            $list.html(html).show();
        },
        updateStats() {
            $('#sharing-stat-selected-days').text(this.selectedDates.size);
            $('#sharing-stat-priced-days').text(Object.keys(this.priceMap).length);
            if (!this.selectedDates.size && !Object.keys(this.priceMap).length) {
                $('#sharing-availability-summary').text(sharingI18n.noSelectionMade);
                return;
            }
            const parts = [];
            if (this.selectedDates.size) {
                parts.push(sharingI18n.daysSelected.replace(':count', this.selectedDates.size));
            }
            const priced = Object.keys(this.priceMap).length;
            if (priced) {
                parts.push(sharingI18n.daysPriced.replace(':count', priced));
            }
            $('#sharing-availability-summary').text(parts.join(' · '));
        },
        save(clearAll) {
            const self = this;
            const formData = new FormData();
            formData.append('_method', 'PUT');
            if (clearAll) {
                formData.append('clear', '1');
            } else {
                formData.append('selected_prices', JSON.stringify(this.priceMap));
                // En son seçilen para birimini varsayılan olarak kaydet
                const lastSelectedCurrency = $('#sharing-price-currency').val() || this.currency;
                formData.append('custom_currency', lastSelectedCurrency);
            }

            self.toggleLoading(true);
            fetch(this.saveUrl, {
                method: 'POST',
                headers: self.headers(),
                body: formData,
            })
            .then(resp => {
                self.toggleLoading(false);
                if (!resp.ok) {
                    return resp.json().then(err => {
                        throw new Error(err.message || sharingI18n.saveError);
                    });
                }
                return resp.json();
            })
            .then(data => {
                self.hidePlannerMenu();
                self.hidePricePopup();
                self.hideWeekdayPopup();
                self.hideModal();
                self.updateListRow(data.custom_price);
            })
            .catch(err => {
                self.showFeedback(err.message || sharingI18n.saveError, false);
            });
        },
        updateListRow(customPrice) {
            const $row = $(`.tour-share-row[data-tour-row="${this.currentTourId}"]`);
            if (!$row.length) {
                return;
            }
            const $badge = $row.find('.tour-price-badge');
            const baseCurrency = $badge.data('base-currency');
            const basePrice = Number($badge.data('base-price') || 0).toFixed(2);
            const $checkbox = $row.find('.tour-share-checkbox');

            if (customPrice && customPrice.has_custom_price) {
                const text = `${Number(customPrice.max_custom_price).toFixed(2)} ${customPrice.currency}`;
                $badge
                    .text(text)
                    .removeClass('badge-light')
                    .addClass('badge-warning')
                    .attr('data-has-custom', '1')
                    .attr('data-custom-price', customPrice.max_custom_price)
                    .attr('data-custom-currency', customPrice.currency);

                if (!$row.find('.tour-custom-indicator').length) {
                    $row.find('strong').first().after(`<span class="badge badge-warning mt-1 tour-custom-indicator"><i class="fas fa-star mr-1"></i> ${sharingI18n.specialLabel}: ${text}</span>`);
                } else {
                    $row.find('.tour-custom-indicator').html(`<i class="fas fa-star mr-1"></i> ${sharingI18n.specialLabel}: ${text}`);
                }
                $checkbox.prop('checked', true).trigger('change');
            } else {
                $badge
                    .text(`${basePrice} ${baseCurrency}`)
                    .removeClass('badge-warning')
                    .addClass('badge-light')
                    .attr('data-has-custom', '0')
                    .attr('data-custom-price', '')
                    .attr('data-custom-currency', '');
                $row.find('.tour-custom-indicator').remove();
            }
        },
        showFeedback(message, success = true) {
            const $alert = $('#sharing-modal-feedback');
            if (!message) {
                $alert.addClass('d-none').removeClass('alert-success alert-danger');
                return;
            }
            $alert
                .removeClass('d-none')
                .toggleClass('alert-success', success)
                .toggleClass('alert-danger', !success)
                .text(message);
        },
        toggleLoading(state) {
            this.modal.find('.tour-sharing-modal').toggleClass('sharing-loading', state);
        },
        clearPricesForSelection() {
            if (!this.selectedDates.size) {
                this.showFeedback(sharingI18n.selectDaysFirst, false);
                return;
            }
            let removed = false;
            this.selectedDates.forEach(date => {
                if (this.priceMap[date]) {
                    delete this.priceMap[date];
                    removed = true;
                }
            });
            if (removed) {
                this.refreshAutoSelected();
                this.syncState();
                this.showFeedback(sharingI18n.pricesRemovedForSelected, true);
                this.hidePricePopup();
            } else {
                this.showFeedback(sharingI18n.noCustomPriceOnSelected, false);
            }
        },
        getAnchorCell() {
            if (!this.menuAnchorDate) {
                return null;
            }
            return this.modal.find(`.yp-day[data-date="${this.menuAnchorDate}"]:not(.out)`).first();
        },
        showPlannerMenuNearElement($element, date) {
            if (!$element || !$element.length) {
                this.hidePlannerMenu();
                return;
            }
            if (!this.plannerMenu.parent().is('body')) {
                $('body').append(this.plannerMenu);
            }
            const rect = $element[0].getBoundingClientRect();
            const docTop = window.pageYOffset || document.documentElement.scrollTop;
            const docLeft = window.pageXOffset || document.documentElement.scrollLeft;
            this.plannerMenu
                .css({ top: rect.bottom + docTop + 6, left: rect.left + docLeft })
                .show()
                .data('anchor', date);
            this.menuAnchorDate = date;
        },
        showPlannerMenuFor(date) {
            if (!date) {
                this.hidePlannerMenu();
                return;
            }
            const $cell = this.modal.find(`.yp-day[data-date="${date}"]:not(.out)`).first();
            if ($cell.length) {
                this.showPlannerMenuNearElement($cell, date);
            } else {
                this.hidePlannerMenu();
            }
        },
        hidePlannerMenu() {
            if (this.plannerMenu) {
                this.plannerMenu.hide().data('anchor', '');
            }
            this.menuAnchorDate = null;
        },
        ensureRangeFilledForPricing() {
            const pair = this.rangeAnchors.length >= 2 ? this.rangeAnchors : this.getRangePair();
            if (!pair) {
                return;
            }
            let [startStr, endStr] = pair;
            let start = parseLocalDate(startStr);
            let end = parseLocalDate(endStr);
            if (start > end) {
                [start, end] = [end, start];
            }
            const cursor = new Date(start.getTime());
            while (cursor <= end) {
                const iso = cursor.toISOString().slice(0, 10);
                this.selectedDates.add(iso);
                cursor.setDate(cursor.getDate() + 1);
            }
        },
        symbol(currency) {
            return this.getCurrencySymbol(currency);
        },
        headers() {
            const headers = {};
            const token = document.head.querySelector('meta[name="csrf-token"]');
            if (token) {
                headers['X-CSRF-TOKEN'] = token.content;
            }
            const partition = document.body.dataset.sessionPartition;
            if (partition) {
                headers['X-Session-Partition'] = partition;
            }
            return headers;
        },
        normalizePriceMap(payload, defaultCurrency) {
            if (!payload || typeof payload !== 'object') {
                return {};
            }
            if (Array.isArray(payload)) {
                return {};
            }
            // Her gün için currency yoksa varsayılan currency ata
            const normalized = {};
            for (const [date, priceData] of Object.entries(payload)) {
                if (priceData && typeof priceData === 'object') {
                    normalized[date] = {
                        ...priceData,
                        currency: priceData.currency || defaultCurrency || 'TRY'
                    };
                }
            }
            return normalized;
        }
    };

    $(function(){
        TourSharePricing.init();

        const $counter = $('#selected-tour-counter');
        function refreshCounter() {
            const count = $('.tour-share-checkbox:checked').length;
            $counter.text(sharingI18n.toursSelected.replace(':count', count));
        }
        refreshCounter();

        $(document).on('change', '.tour-share-checkbox', refreshCounter);

        $('#select-all-tours-btn').on('click', function(){
            const $checkboxes = $('.tour-share-checkbox');
            const shouldSelect = $checkboxes.length !== $checkboxes.filter(':checked').length;
            $checkboxes.prop('checked', shouldSelect).trigger('change');
        });

        // Tur arama fonksiyonalitesi
        const $searchInput = $('#tour-search-input');
        const $searchClear = $('#tour-search-clear');
        const $countInfo = $('#tour-count-info');
        const $tourRows = $('.tour-share-row');
        const totalTours = $tourRows.length;
        const defaultVisibleCount = 6;

        // Enter tuşunu engelle (form submit olmasın)
        $searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                return false;
            }
        });

        // Sayfa yüklendiğinde seçili tur sayısını hesapla
        const initialSelectedCount = $('.tour-share-checkbox:checked').length;

        function getDefaultInfoText() {
            const selectedCount = Math.min(initialSelectedCount, defaultVisibleCount);
            const suggestedCount = Math.max(0, Math.min(defaultVisibleCount, totalTours) - selectedCount);
            
            if (totalTours <= defaultVisibleCount) {
                return sharingI18n.toursListed.replace(':count', totalTours);
            }

            if (selectedCount > 0) {
                if (suggestedCount > 0) {
                    return sharingI18n.selectedPlusSuggested.replace(':selected', selectedCount).replace(':suggested', suggestedCount).replace(':total', totalTours);
                } else {
                    return sharingI18n.selectedShowing.replace(':count', selectedCount).replace(':total', totalTours);
                }
            } else {
                return sharingI18n.mostBookedShowing.replace(':count', Math.min(defaultVisibleCount, totalTours)).replace(':total', totalTours);
            }
        }

        function updateTourVisibility() {
            const searchTerm = $searchInput.val().toLowerCase().trim();
            const isSearching = searchTerm.length > 0;
            
            $searchClear.toggle(isSearching);
            
            let matchCount = 0;
            
            $tourRows.each(function(index) {
                const $row = $(this);
                const tourName = $row.data('tour-name-search') || '';
                
                if (isSearching) {
                    // Arama modunda: eşleşenler görünsün
                    if (tourName.includes(searchTerm)) {
                        $row.show();
                        matchCount++;
                    } else {
                        $row.hide();
                    }
                } else {
                    // Normal mod: sadece ilk 6 görünsün
                    if (index < defaultVisibleCount) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                }
            });
            
            // Bilgi mesajını güncelle
            if (isSearching) {
                if (matchCount === 0) {
                    $countInfo.text(sharingI18n.noMatchFound.replace(':query', '"' + searchTerm + '"'));
                } else {
                    $countInfo.text(sharingI18n.toursFound.replace(':count', matchCount));
                }
            } else {
                $countInfo.text(getDefaultInfoText());
            }
        }

        $searchInput.on('input', function() {
            updateTourVisibility();
        });

        $searchClear.on('click', function() {
            $searchInput.val('').trigger('input');
        });

        // Tümünü Göster/Gizle butonu
        const $showAllBtn = $('#show-all-tours-btn');
        let showingAll = false;
        const hiddenCount = Math.max(0, totalTours - defaultVisibleCount);

        $showAllBtn.on('click', function() {
            showingAll = !showingAll;
            
            if (showingAll) {
                // Tüm turları göster
                $tourRows.show();
                $(this).html('<i class="fas fa-chevron-up"></i> <span>' + sharingI18n.showLess + '</span>');
                $countInfo.text(sharingI18n.allToursShowing.replace(':count', totalTours));
            } else {
                // İlk 6'ya dön
                $tourRows.each(function(index) {
                    if (index >= defaultVisibleCount) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
                $(this).html('<i class="fas fa-chevron-down"></i> <span>' + sharingI18n.showAllTours.replace(':count', hiddenCount) + '</span>');
                $countInfo.text(getDefaultInfoText());
            }

            // Aramayı temizle
            $searchInput.val('');
            $searchClear.hide();
        });

        // Arama yapıldığında "Tümünü Göster" butonunu gizle
        $searchInput.on('input', function() {
            const searchTerm = $(this).val().trim();
            if (searchTerm.length > 0) {
                $showAllBtn.hide();
            } else {
                $showAllBtn.show();
                showingAll = false;
                $showAllBtn.html('<i class="fas fa-chevron-down"></i> <span>' + sharingI18n.showAllTours.replace(':count', hiddenCount) + '</span>');
            }
        });
    });
})(jQuery);
</script>
@endpush
<!-- end of the code -->