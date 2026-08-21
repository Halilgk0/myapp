@extends('layouts.admin')

@section('title', 'Tur Düzenle')

@section('content')
    <div class="container-fluid">
        <form action="{{ route('admin.tours.update', $tour) }}" method="POST" enctype="multipart/form-data" id="tour-edit-form">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Sol: Form Bölümü -->
                <div class="col-lg-8">
                    <!-- Temel Bilgiler Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Temel Bilgiler
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name"><i class="fas fa-tag text-primary"></i> Tur Adı *</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $tour->name) }}" 
                                               placeholder="Örn: İstanbul - Boğaz Turu" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency"><i class="fas fa-money-bill-wave text-success"></i> Para Birimi *</label>
                                        <select class="form-control @error('currency') is-invalid @enderror" 
                                                id="currency" name="currency" required>
                                            <option value="TRY" {{ old('currency', $tour->currency) == 'TRY' ? 'selected' : '' }}>₺ Türk Lirası</option>
                                            <option value="USD" {{ old('currency', $tour->currency) == 'USD' ? 'selected' : '' }}>$ Amerikan Doları</option>
                                            <option value="EUR" {{ old('currency', $tour->currency) == 'EUR' ? 'selected' : '' }}>€ Euro</option>
                                            <option value="GBP" {{ old('currency', $tour->currency) == 'GBP' ? 'selected' : '' }}>£ İngiliz Sterlini</option>
                                            <option value="RUB" {{ old('currency', $tour->currency) == 'RUB' ? 'selected' : '' }}>₽ Rus Rublesi</option>
                                        </select>
                                        @error('currency')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country"><i class="fas fa-globe text-info"></i> Ülke *</label>
                                        <input list="country-list" type="text" class="form-control @error('country') is-invalid @enderror" 
                                               id="country" name="country" value="{{ old('country', $tour->country) }}" 
                                               placeholder="Ülke seçin veya yazın" autocomplete="off" required>
                                        <datalist id="country-list"></datalist>
                                        @error('country')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city"><i class="fas fa-city text-warning"></i> Şehir *</label>
                                        <input list="city-list" type="text" class="form-control @error('city') is-invalid @enderror" 
                                               id="city" name="city" value="{{ old('city', $tour->city) }}" 
                                               placeholder="Şehir seçin veya yazın" autocomplete="off" required>
                                        <datalist id="city-list"></datalist>
                                        @error('city')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="district"><i class="fas fa-location-arrow text-primary"></i> İlçe</label>
                                        <input list="district-list" type="text" class="form-control @error('district') is-invalid @enderror" 
                                               id="district" name="district" value="{{ old('district', $tour->district) }}" 
                                               placeholder="İlçe seçin veya yazın" autocomplete="off">
                                        <datalist id="district-list"></datalist>
                                        @error('district')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description"><i class="fas fa-align-left text-secondary"></i> Açıklama</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Tur hakkında detaylı açıklama yazın...">{{ old('description', $tour->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_capacity"><i class="fas fa-users text-primary"></i> Maksimum Kapasite</label>
                                        <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" 
                                               id="max_capacity" name="max_capacity" value="{{ old('max_capacity', $tour->max_capacity) }}" 
                                               min="1" placeholder="Boş = Sınırsız">
                                        <small class="form-text text-muted">Boş bırakılırsa sınırsız olur</small>
                                        @error('max_capacity')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-toggle-on text-success"></i> Durum</label>
                                        <div class="custom-control custom-switch" style="padding-top: 8px;">
                                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                                   {{ old('is_active', $tour->is_active) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_active">Turu Aktif Et</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-share-alt text-info"></i> Otomatik Paylaşım</label>
                                        <div class="custom-control custom-switch" style="padding-top: 8px;">
                                            <input type="checkbox" class="custom-control-input" id="auto_share_on_connect" name="auto_share_on_connect" value="1" 
                                                   {{ old('auto_share_on_connect', $tour->auto_share_on_connect) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="auto_share_on_connect">Yeni bağlantılarla otomatik paylaş</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Bu turu oluşturan kullanıcı, ağda yeni bir bağlantı kurduğunda tur otomatik olarak paylaşılır. Paylaşımı acenta sayfasından kaldırabilirsiniz.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-check-circle text-success"></i> Otomatik Bilet Kabul Etme</label>
                                        <div class="custom-control custom-switch" style="padding-top: 8px;">
                                            <input type="checkbox" class="custom-control-input" id="auto_approve_tickets" name="auto_approve_tickets" value="1"
                                                   {{ old('auto_approve_tickets', $tour->auto_approve_tickets) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="auto_approve_tickets">Bilet isteklerini otomatik onayla</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Bu tur için acentalardan gelen bilet oluşturma istekleri otomatik olarak onaylanır ve direkt bilet oluşturulur.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="street_agency_auto_approve_time">
                                            <i class="fas fa-clock text-info"></i> Sokak Acentası Otomatik Onay Saati
                                        </label>
                                        <input type="time" class="form-control @error('street_agency_auto_approve_time') is-invalid @enderror" 
                                               id="street_agency_auto_approve_time" 
                                               name="street_agency_auto_approve_time" 
                                               value="{{ old('street_agency_auto_approve_time', $tour->street_agency_auto_approve_time ? $tour->street_agency_auto_approve_time->format('H:i') : '') }}"
                                               placeholder="Örn: 14:00">
                                        <div class="custom-control custom-switch mt-2">
                                            <input type="checkbox" class="custom-control-input" id="street_agency_auto_approve_enabled" name="street_agency_auto_approve_enabled" value="1"
                                                   {{ old('street_agency_auto_approve_enabled', $tour->street_agency_auto_approve_enabled) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="street_agency_auto_approve_enabled">Bu saatten sonra otomatik kabulü aktif et</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Bu saatten sonra sokak acentasından gelen biletler otomatik olarak kabul edilir.
                                        </small>
                                        @error('street_agency_auto_approve_time')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes"><i class="fas fa-sticky-note text-warning"></i> Notlar</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="2" 
                                          placeholder="İç notlar (opsiyonel)">{{ old('notes', $tour->notes) }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="pickup_time" value="{{ old('pickup_time', $tour->pickup_time) }}">
                            <input type="hidden" name="dropoff_time" value="{{ old('dropoff_time', $tour->dropoff_time) }}">
                            <input type="hidden" name="duration_days" value="{{ old('duration_days', $tour->duration_days) }}">
                            <input type="hidden" name="duration_hours" value="{{ old('duration_hours', $tour->duration_hours) }}">
                        </div>
                    </div>

                    <!-- Servis Alanları (Poligon) -->
                    <div class="card card-info card-outline">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-draw-polygon"></i> Servis Alanları (Poligon)
                            </h3>
                            <small class="text-muted">Turun yapılabileceği bölgeleri çizin</small>
                        </div>
                        <div class="card-body">
                            <div style="position:relative;">
                                <div id="sa-toolbar" class="sa-toolbar">
                                    <button type="button" class="sa-tool-btn active" data-tool="pointer" title="Serbest Gezinme">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="M13 13l6 6"/></svg>
                                    </button>
                                    <button type="button" class="sa-tool-btn" data-tool="polygon" title="Poligon Çiz">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l9 7-3.5 11h-11L3 9z"/></svg>
                                    </button>
                                    <button type="button" class="sa-tool-btn" data-tool="circle" title="Daire Çiz">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="12" x2="12" y2="6"/></svg>
                                    </button>
                                    <div class="sa-tool-sep"></div>
                                    <button type="button" class="sa-tool-btn" data-tool="delete" title="Seçili Poligonu Sil">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                    </button>
                                    <button type="button" class="sa-tool-btn" data-tool="delete-all" title="Tümünü Sil">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><line x1="1" y1="1" x2="23" y2="23" stroke-width="2.5"/></svg>
                                    </button>
                                    <div class="sa-tool-sep"></div>
                                    <button type="button" class="sa-tool-btn sm-library-btn" data-tool="save-map" title="Haritayı Kaydet (sadece size özel)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    </button>
                                    <button type="button" class="sa-tool-btn sm-library-btn" data-tool="load-map" title="Kayıtlı Haritalardan Ekle">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                                    </button>
                                </div>
                                <div id="service-area-map" style="height: 400px; width: 100%; border:1px solid #ced4da; border-radius:4px;"></div>
                            </div>
                            <small id="service-area-map-error" class="text-danger d-block mt-2" style="display:none"></small>
                            <small class="form-text text-muted mt-2">
                                <b>Pointer:</b> Serbest gezinme &nbsp;|&nbsp;
                                <b>Poligon:</b> Sol tık ile nokta koy, ilk noktaya tıkla veya çift tıkla kapat. Sağ tık ile iptal. &nbsp;|&nbsp;
                                <b>Daire:</b> Sol tık ile merkez belirle, uzaklaştır ve tekrar tıkla. &nbsp;|&nbsp;
                                <b>Kaydet / Kütüphane:</b> Çizdiğiniz poligonları kendi kütüphanenize kaydedin veya kayıtlı haritalardan ekleyin.
                            </small>
                            <input type="hidden" id="service_areas" name="service_areas" value='@json(old("service_areas", $tour->service_areas ?? null))'>
                        </div>
                    </div>

                    {{-- Saved Maps Modals --}}
                    <div id="sm-save-modal" class="sm-modal-backdrop" style="display:none;">
                        <div class="sm-modal">
                            <div class="sm-modal-header">
                                <h5><i class="fas fa-save"></i> Haritayı Kaydet</h5>
                                <button type="button" class="sm-close" data-sm-close>&times;</button>
                            </div>
                            <div class="sm-modal-body">
                                <label for="sm-save-name" class="form-label">Harita Adı</label>
                                <input type="text" id="sm-save-name" class="form-control" maxlength="100" placeholder="Örn: Antalya Merkez Bölgesi">
                                <small class="text-muted d-block mt-2">Bu harita sadece size özel olarak kaydedilir; cihazınıza dosya indirilmez.</small>
                                <div id="sm-save-msg" class="mt-2"></div>
                            </div>
                            <div class="sm-modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-sm-close>İptal</button>
                                <button type="button" class="btn btn-primary btn-sm" id="sm-save-btn">Kaydet</button>
                            </div>
                        </div>
                    </div>

                    <div id="sm-load-modal" class="sm-modal-backdrop" style="display:none;">
                        <div class="sm-modal">
                            <div class="sm-modal-header">
                                <h5><i class="fas fa-folder-open"></i> Kayıtlı Haritalar</h5>
                                <button type="button" class="sm-close" data-sm-close>&times;</button>
                            </div>
                            <div class="sm-modal-body">
                                <div id="sm-load-list" class="sm-load-list">Yükleniyor...</div>
                            </div>
                            <div class="sm-modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-sm-close>Kapat</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tarih ve Fiyatlandırma Card -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check"></i> Tarih & Fiyatlandırma
                            </h3>
                            <div class="card-tools">
                                <button type="button" id="global-clear-btn" class="btn btn-tool btn-sm text-danger">
                                    <i class="fas fa-trash"></i> Tümünü Temizle
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-dismissible">
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                <h5><i class="icon fas fa-info"></i> Nasıl Kullanılır?</h5>
                                <ul class="mb-0 pl-3">
                                    <li><strong>Sol Tık:</strong> Tarih seç/kaldır</li>
                                    <li><strong>Sağ Tık:</strong> Seçili tarihi kaldır</li>
                                    <li><strong>Aralık Seçimi:</strong> 2 tarih seçin, ardından "Arayı Doldur" butonuna tıklayın</li>
                                    <li><strong>Fiyat Ekleme:</strong> Tarih seçtikten sonra "Fiyat Ekle" ile fiyatlandırma yapın</li>
                                </ul>
                            </div>

                            <!-- Takvim -->
                            <div class="year-planner" id="year-planner"></div>
                            
                            <!-- Context Menu -->
                            <div id="planner-menu" class="planner-menu" style="display:none">
                                <button type="button" class="btn btn-xs btn-outline-primary" id="pm-fill">
                                    <i class="fas fa-fill"></i> Arayı Doldur
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger" id="pm-clear">
                                    <i class="fas fa-eraser"></i> Temizle
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="pm-add-date">
                                    <i class="fas fa-calendar-day"></i> Özel Tarih
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-warning" id="pm-show-price">
                                    <i class="fas fa-dollar-sign"></i> Fiyat Ekle
                                </button>
                            </div>

                            <!-- Weekday Popup -->
                            <div id="weekday-popup" class="weekday-popup" style="display:none">
                                <div class="d-flex" style="gap:6px; flex-wrap:wrap;">
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="1">Pzt</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="2">Sal</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="3">Çar</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="4">Per</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="5">Cum</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="6">Cmt</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary wd-btn" data-wd="7">Paz</button>
                                </div>
                            </div>

                            <!-- Price Popup -->
                            <div id="price-popup" class="weekday-popup" style="display:none">
                                <div class="mb-2"><strong><i class="fas fa-tags"></i> Fiyat Bilgileri</strong></div>
                                <input type="number" step="0.01" class="form-control form-control-sm mb-2" id="pm-price-adult" placeholder="Yetişkin">
                                <input type="number" step="0.01" class="form-control form-control-sm mb-2" id="pm-price-child" placeholder="Çocuk">
                                <input type="number" step="0.01" class="form-control form-control-sm mb-2" id="pm-price-infant" placeholder="Bebek">
                                <div class="d-flex" style="gap:6px;">
                                    <button type="button" class="btn btn-xs btn-primary" id="price-apply"><i class="fas fa-check"></i> Uygula</button>
                                    <button type="button" class="btn btn-xs btn-secondary" id="price-close"><i class="fas fa-times"></i> Kapat</button>
                                </div>
                            </div>

                            <!-- Hidden inputs -->
                            <input type="hidden" name="selected_dates" id="selected_dates" value='{{ old("selected_dates", json_encode($tour->available_dates ?? [])) }}'>
                            <input type="hidden" name="selected_prices" id="selected_prices" value='{{ old("selected_prices", json_encode($tour->date_prices ?? new \stdClass())) }}'>

                            <!-- Selected Dates Display (Collapsible) -->
                            <div class="mt-3" id="selected-dates-wrapper" style="display:none;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-info" id="toggle-selected-dates">
                                        <i class="fas fa-chevron-right" id="toggle-icon"></i>
                                        <span id="toggle-text">Seçili Tarihleri Göster</span>
                                        <span class="badge badge-info ml-1" id="selected-dates-count">0</span>
                                    </button>
                                </div>
                                <div class="selected-dates-container" id="cdp-selected-list" style="display:none;"></div>
                            </div>

                            <!-- Summary -->
                            <div class="alert alert-light border mt-3 mb-0">
                                <strong><i class="fas fa-chart-pie"></i> Özet:</strong>
                                <div class="small" id="availability-summary">Henüz seçim yapılmadı.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Güncelle
                            </button>
                            <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sağ: Yardım Kartları -->
                <div class="col-lg-4">
                    <!-- Progress Card -->
                    <div class="card card-widget widget-user-2">
                        <div class="widget-user-header bg-gradient-primary">
                            <div class="widget-user-image">
                                <i class="fas fa-route fa-3x"></i>
                            </div>
                            <h3 class="widget-user-username">Tur Güncelleme Rehberi</h3>
                            <h5 class="widget-user-desc">Adım adım kontrol edin</h5>
                        </div>
                        <div class="card-footer p-0">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-check-circle text-success"></i> Tur bilgileri
                                        <span class="float-right badge bg-primary">1</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-calendar text-warning"></i> Tarih ve fiyatlandırma
                                        <span class="float-right badge bg-warning">2</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-toggle-on text-info"></i> Aktif durumu kontrol
                                        <span class="float-right badge bg-info">3</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- İpuçları Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb"></i> İpuçları
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-infinity"></i> Sınırsız Kapasite</h5>
                                <p class="text-sm mb-0">Kapasite alanını boş bırakırsanız tur sınırsız katılımcı alır</p>
                            </div>
                            <div class="callout callout-warning">
                                <h5><i class="fas fa-calendar-alt"></i> Toplu Fiyatlandırma</h5>
                                <p class="text-sm mb-0">Aralık seçerek birden fazla güne aynı fiyatı uygulayabilirsiniz</p>
                            </div>
                        </div>
                    </div>

                    <!-- İstatistik Card -->
                    <div class="card bg-gradient-info">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> Hızlı İstatistik
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="text-white">
                                        <h3 class="mb-0" id="stat-selected-days">0</h3>
                                        <small>Seçili Gün</small>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="text-white">
                                        <h3 class="mb-0" id="stat-priced-days">0</h3>
                                        <small>Fiyatlı Gün</small>
                                    </div>
                                </div>
                            </div>
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
    .sa-toolbar { position:absolute; top:10px; left:10px; z-index:10; display:flex; gap:2px; background:#fff; border-radius:6px; padding:3px; box-shadow:0 1px 4px rgba(0,0,0,.25); }
    .sa-tool-btn { width:32px; height:32px; border:none; background:transparent; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#555; transition:all .15s; }
    .sa-tool-btn:hover { background:#e9ecef; color:#333; }
    .sa-tool-btn.active { background:#0d6efd; color:#fff; }
    .sa-tool-sep { width:1px; background:#dee2e6; margin:4px 2px; }
    .sa-tool-btn[data-tool="delete"]:hover, .sa-tool-btn[data-tool="delete-all"]:hover { background:#fee2e2; color:#dc3545; }
    .sa-tool-btn.sm-library-btn:hover { background:#e0f2fe; color:#0369a1; }

    /* Saved Maps modal */
    .sm-modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; display:flex; align-items:center; justify-content:center; padding:16px; }
    .sm-modal { background:#fff; border-radius:8px; width:100%; max-width:480px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 10px 40px rgba(0,0,0,.3); }
    .sm-modal-header { padding:12px 16px; border-bottom:1px solid #dee2e6; display:flex; justify-content:space-between; align-items:center; }
    .sm-modal-header h5 { margin:0; font-size:15px; font-weight:600; }
    .sm-modal-body { padding:16px; overflow:auto; flex:1; }
    .sm-modal-footer { padding:10px 16px; border-top:1px solid #dee2e6; display:flex; justify-content:flex-end; gap:6px; }
    .sm-close { background:none; border:none; font-size:24px; line-height:1; cursor:pointer; color:#888; padding:0 4px; }
    .sm-close:hover { color:#333; }
    .sm-item { padding:10px 12px; border:1px solid #e9ecef; border-radius:6px; display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; gap:8px; }
    .sm-item:hover { background:#f8f9fa; border-color:#adb5bd; }
    .sm-item-info { min-width:0; flex:1; }
    .sm-item-name { font-weight:500; font-size:14px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sm-item-meta { font-size:11px; color:#888; margin-top:2px; }
    .sm-item-actions { display:flex; gap:4px; flex-shrink:0; }
    .sm-load-list { min-height:80px; }

    /* Polygon times popup */
    .mapboxgl-popup.ptp-pop { max-width: none !important; color-scheme: light; }
    .mapboxgl-popup.ptp-pop .mapboxgl-popup-content { padding:0; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.2); overflow:hidden; width:240px; background:#fff; color:#212529; }
    .mapboxgl-popup.ptp-pop .mapboxgl-popup-tip { border-top-color:#fff !important; border-bottom-color:#fff !important; }
    .poly-times-popup { font-size:13px; background:#fff; color:#212529; color-scheme: light; }
    .ptp-header { display:flex; justify-content:space-between; align-items:center; padding:8px 10px; background:#0d6efd; color:#fff; font-weight:600; font-size:12px; }
    .ptp-close { background:none; border:none; color:#fff; font-size:18px; line-height:1; cursor:pointer; padding:0 4px; }
    .ptp-close:hover { opacity:.85; }
    .ptp-list { padding:8px 10px; max-height:180px; overflow-y:auto; background:#fff; }
    .ptp-empty { color:#888; font-style:italic; font-size:12px; padding:6px 0; text-align:center; }
    .ptp-item { display:flex; justify-content:space-between; align-items:center; padding:4px 8px; background:#f8f9fa; color:#212529; border-radius:4px; margin-bottom:4px; }
    .ptp-item span { font-weight:500; font-variant-numeric:tabular-nums; color:#212529; }
    .ptp-del { background:none; border:none; color:#dc3545; cursor:pointer; font-size:12px; padding:2px 6px; border-radius:3px; }
    .ptp-del:hover { background:#fee2e2; }
    .ptp-add { display:flex; gap:4px; padding:8px 10px; border-top:1px solid #e9ecef; align-items:center; background:#fff; }
    .ptp-add input.ptp-input { flex:1; font-size:13px; padding:4px 8px; min-width:0; height:30px; color-scheme: light; background:#fff; color:#212529; border:1px solid #ced4da; border-radius:4px; }
    .ptp-add input.ptp-input:focus { outline:none; border-color:#0d6efd; box-shadow:0 0 0 2px rgba(13,110,253,.2); }
    .ptp-add .ptp-add-btn { white-space:nowrap; font-size:12px; padding:4px 10px; height:30px; }
    .ptp-hint { padding:6px 10px 10px; font-size:11px; color:#6c757d; background:#fff; line-height:1.35; border-top:1px solid #f1f3f5; }

    /* Modern Form Styles */
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    label {
        font-weight: 600;
        color: #495057;
    }

    /* Year Planner Modern */
    .year-planner { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); 
        gap: 20px; 
        margin: 20px 0;
        width: 100%;
        box-sizing: border-box;
    }
    .yp-card { 
        border: 1px solid #e3e6f0; 
        border-radius: 12px; 
        padding: 16px; 
        background: #fff; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        min-width: 0; /* prevent grid overflow */
    }
    .yp-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .yp-header { 
        text-align: center; 
        font-weight: 700; 
        font-size: 15px;
        margin-bottom: 12px; 
        color: #5a5c69;
        padding-bottom: 8px;
        border-bottom: 2px solid #4e73df;
    }
    .yp-weekdays { 
        display: grid; 
        grid-template-columns: repeat(7, 1fr); 
        gap: 3px; 
        font-size: 11px; 
        color: #858796; 
        text-align: center; 
        margin-bottom: 8px; 
        font-weight: 600;
    }
    .yp-grid { 
        display: grid; 
        grid-template-columns: repeat(7, minmax(0, 1fr)); 
        gap: 3px; 
    }
    .yp-day { 
        aspect-ratio: 1; 
        display: flex; 
        flex-direction: column;
        align-items: center; 
        justify-content: center; 
        font-size: 12px; 
        border-radius: 8px; 
        border: 1px solid #e3e6f0; 
        cursor: pointer; 
        transition: all 0.2s ease;
        min-height: 32px;
        font-weight: 500;
        position: relative;
        background: #fff;
        overflow: hidden;
        min-width: 0;
    }
    .yp-day .yp-daynum{ line-height: 1; }
    .yp-day:hover:not(.out):not(.locked) { 
        background: #f8f9fc; 
        border-color: #4e73df;
        transform: scale(1.05);
    }
    .yp-day.out { 
        color: #d1d3e2; 
        cursor: not-allowed;
        background: #f8f9fc;
    }
    .yp-day.sel { 
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        color: #fff; 
        border-color: #1cc88a; 
        font-weight: 700; 
        transform: scale(1.08);
        box-shadow: 0 2px 6px rgba(28,200,138,0.4);
    }
    .yp-day.locked {
        pointer-events: none;
        opacity: 0.6;
        border-color: #858796;
        border-style: dashed;
    }
    .yp-day .day-price {
        position: static;
        margin-top: 2px;
        font-size: 8px;
        color: #4e73df;
        background: #fff;
        padding: 1px 3px;
        border-radius: 999px;
        line-height: 1;
        pointer-events: none;
        font-weight: 600;
        border: 1px solid rgba(78,115,223,0.3);
        box-shadow: 0 1px 2px rgba(78,115,223,0.2);
        white-space: nowrap;
        max-width: 95%;
        overflow: hidden;
        text-overflow: ellipsis;
        z-index: 1;
    }
    .yp-day.sel .day-price {
        color: #fff;
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.4);
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    }
    .planner-menu { 
        position: absolute; 
        display: none; 
        gap: 6px; 
        background: #fff; 
        border: 1px solid #e3e6f0; 
        border-radius: 8px; 
        padding: 8px; 
        box-shadow: 0 4px 16px rgba(0,0,0,.15); 
        z-index: 9999; 
    }
    .btn.btn-xs { padding: 4px 10px; font-size: 12px; line-height: 1.4; border-radius: 4px; }
    .weekday-popup { 
        position: absolute; 
        display:none; 
        background:#fff; 
        border:1px solid #e3e6f0; 
        border-radius:8px; 
        padding:10px; 
        box-shadow:0 4px 16px rgba(0,0,0,.15); 
        z-index:10000; 
    }

    /* Selected dates chips */
    .selected-dates-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }
    .selected-date-item {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #36b9cc;
        box-shadow: 0 2px 4px rgba(54,185,204,0.3);
    }
    .selected-date-item .remove-date {
        margin-left: 8px;
        cursor: pointer;
        color: #fff;
        font-size: 16px;
        line-height: 1;
        opacity: 0.8;
    }
    .selected-date-item .remove-date:hover {
        opacity: 1;
    }
    
    /* Toggle button for selected dates */
    #toggle-selected-dates {
        transition: all 0.2s ease;
    }
    #toggle-selected-dates:hover {
        background-color: #17a2b8;
        color: #fff;
    }
    #toggle-icon {
        transition: transform 0.2s ease;
        margin-right: 6px;
    }
    #selected-dates-wrapper {
        background: #f8f9fa;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 12px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .year-planner { gap: 16px; }
    }
    @media (max-width: 768px) { 
        .year-planner { 
            gap: 12px;
        }
        .yp-card { 
            padding: 12px;
        }
        .yp-day { 
            min-height: 28px; 
            font-size: 11px;
        }
        .yp-day .day-price { font-size: 9px; padding: 1px 5px; }
    }

    /* ============================================== */
    /* DARK MODE STYLES FOR TOUR EDIT PAGE */
    /* ============================================== */
    html.dark-mode .year-planner {
        background: transparent !important;
    }

    html.dark-mode .yp-card {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
    }

    html.dark-mode .yp-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.4) !important;
    }

    html.dark-mode .yp-header {
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }

    html.dark-mode .yp-weekdays span {
        color: #94a3b8 !important;
    }

    html.dark-mode .yp-day {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    html.dark-mode .yp-day:hover:not(.out):not(.locked) {
        background: #334155 !important;
        border-color: #6366f1 !important;
    }

    html.dark-mode .yp-day.out {
        background: #0f172a !important;
        color: #475569 !important;
    }

    html.dark-mode .yp-day.sel {
        background: #374151 !important;
        color: #e2e8f0 !important;
        border-color: #6b7280 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important;
    }

    html.dark-mode .yp-day .day-price {
        background: #334155 !important;
        color: #818cf8 !important;
    }

    html.dark-mode .yp-day.sel .day-price {
        background: rgba(255,255,255,0.15) !important;
        color: #e2e8f0 !important;
        border-color: rgba(255,255,255,0.3) !important;
    }

    html.dark-mode .planner-menu {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.4) !important;
    }

    html.dark-mode .planner-menu button {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #e2e8f0 !important;
    }

    html.dark-mode .planner-menu button:hover {
        background: #475569 !important;
    }

    html.dark-mode #selected-dates-wrapper {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html.dark-mode #toggle-selected-dates {
        background: #334155 !important;
        color: #e2e8f0 !important;
        border-color: #475569 !important;
    }

    html.dark-mode #toggle-selected-dates:hover {
        background: #475569 !important;
    }

    html.dark-mode #weekday-popup,
    html.dark-mode #price-popup {
        background: #1e293b !important;
        border-color: #334155 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.4) !important;
    }

    html.dark-mode #weekday-popup h6,
    html.dark-mode #price-popup h6,
    html.dark-mode #weekday-popup label,
    html.dark-mode #price-popup label {
        color: #e2e8f0 !important;
    }

    /* Dark Mode - Alert Light (Özet) */
    html.dark-mode .alert-light {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    html.dark-mode .alert-light strong,
    html.dark-mode .alert-light i {
        color: #e2e8f0 !important;
    }

    html.dark-mode .alert-light .small,
    html.dark-mode .alert-light small,
    html.dark-mode #availability-summary {
        color: #94a3b8 !important;
    }

    /* Dark Mode - Callouts */
    html.dark-mode .callout {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html.dark-mode .callout-info {
        border-left-color: #3b82f6 !important;
    }

    html.dark-mode .callout-warning {
        border-left-color: #f59e0b !important;
    }

    html.dark-mode .callout h5 {
        color: #e2e8f0 !important;
    }

    html.dark-mode .callout p {
        color: #94a3b8 !important;
    }

    /* Dark Mode - Alert Info (Nasıl Kullanılır) */
    html.dark-mode .alert-info {
        background: #1e293b !important;
        border-color: #3b82f6 !important;
        color: #e2e8f0 !important;
    }

    html.dark-mode .alert-info h5 {
        color: #e2e8f0 !important;
    }

    html.dark-mode .alert-info ul li {
        color: #94a3b8 !important;
    }

    html.dark-mode .alert-info ul li strong {
        color: #e2e8f0 !important;
    }

    /* Dark Mode - Card Footer */
    html.dark-mode .card-footer {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    html.dark-mode .card-footer .nav-link {
        color: #94a3b8 !important;
    }

    html.dark-mode .card-footer .nav-link:hover {
        color: #e2e8f0 !important;
        background: #334155 !important;
    }

    /* Dark mode: Mapbox popup + sm-modal + sa-toolbar */
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
    html.dark-mode .sa-toolbar { background: #1e293b !important; border-color: #334155 !important; }
    html.dark-mode .sa-toolbar button { background: transparent !important; color: #e2e8f0 !important; }
    html.dark-mode .sa-toolbar button:hover { background: #334155 !important; }
    html.dark-mode .sm-modal { background: #1e293b !important; color: #e2e8f0 !important; }
    html.dark-mode .sm-modal-header { border-bottom-color: #334155 !important; }
    html.dark-mode .sm-modal-footer { border-top-color: #334155 !important; }
    html.dark-mode .sm-modal input, html.dark-mode .sm-modal textarea, html.dark-mode .sm-modal select {
        background: #0f172a !important; border-color: #334155 !important; color: #e2e8f0 !important;
    }
</style>
@endpush

@push('js')
<script>
$(function(){
    if (!$('#year-planner').length) return;
    const oldSelectedDates = @json(old('selected_dates', $tour->available_dates ?? []));

    function parseMaybeJson(value, fallback){
        if (value === null || value === undefined || value === '') return fallback;
        if (Array.isArray(value) || (typeof value === 'object' && value !== null)) return value;
        if (typeof value === 'string') {
            try { return JSON.parse(value); } catch(_) { return fallback; }
        }
        return fallback;
    }

    function syncHiddenInputs() {
        const sd = $('#selected_dates').val(); 
        let summary = [];
        let selectedCount = 0;
        if (sd) { 
            try { 
                const arr = JSON.parse(sd); 
                if (Array.isArray(arr)) {
                    selectedCount = arr.length;
                    if (arr.length > 0) summary.push(`${arr.length} tarih seçili`);
                }
            } catch(_){}
        }
        $('#stat-selected-days').text(selectedCount);
        
        // Count priced days
        const sp = $('#selected_prices').val();
        let pricedCount = 0;
        if (sp) {
            try {
                const obj = JSON.parse(sp);
                pricedCount = Object.keys(obj).length;
            } catch(_) {}
        }
        $('#stat-priced-days').text(pricedCount);
        if (pricedCount > 0) summary.push(`${pricedCount} gün fiyatlandırıldı`);
        
        $('#availability-summary').text(summary.length ? summary.join(' · ') : 'Henüz seçim yapılmadı');
    }

    function toLocalISO(date){
        const y = date.getFullYear();
        const m = String(date.getMonth()+1).padStart(2,'0');
        const d = String(date.getDate()).padStart(2,'0');
        return y+'-'+m+'-'+d;
    }

    function getCurrencySymbol(code){
        switch(($('#currency').val()||code||'').toUpperCase()){
            case 'TRY': return '₺';
            case 'USD': return '$';
            case 'EUR': return '€';
            case 'GBP': return '£';
            case 'RUB': return '₽';
            default: return '';
        }
    }

    function refreshPricePlaceholders(){
        const sym = getCurrencySymbol();
        $('#pm-price-adult').attr('placeholder', 'Yetişkin ('+sym+')');
        $('#pm-price-child').attr('placeholder', 'Çocuk ('+sym+')');
        $('#pm-price-infant').attr('placeholder', 'Bebek ('+sym+')');
    }

    const monthsTr=['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
    const selectedSpecialDates = new Set();
    let lastTwoClicks = [];
    let rangeLocked = false;
    let activeWeekdays = new Set();
    let inWeekdayMode = false;

    if (oldSelectedDates){ 
        const arr = parseMaybeJson(oldSelectedDates, []);
        if(Array.isArray(arr)) arr.forEach(d=>selectedSpecialDates.add(d)); 
    }

    // Normalize hidden inputs to valid JSON
    const normalizedDates = parseMaybeJson($('#selected_dates').val(), Array.from(selectedSpecialDates));
    $('#selected_dates').val(JSON.stringify(Array.isArray(normalizedDates) ? normalizedDates : []));
    const normalizedPrices = parseMaybeJson($('#selected_prices').val(), {});
    const pricesObj = (normalizedPrices && typeof normalizedPrices === 'object' && !Array.isArray(normalizedPrices)) ? normalizedPrices : {};
    $('#selected_prices').val(JSON.stringify(pricesObj));

    function renderYearPlanner(){
        const now = new Date();
        const year = now.getFullYear();
        let html = '';
        for (let month = 1; month <= 12; month++) {
            const first = new Date(year, month-1, 1);
            const start = new Date(first);
            const dow = start.getDay();
            const offset = dow === 0 ? -6 : 1 - dow;
            start.setDate(start.getDate() + offset);
            html += '<div class="yp-card">';
            html += '<div class="d-flex justify-content-between align-items-center yp-header"><span>' + monthsTr[month-1] + '</span><div><button type="button" class="btn btn-xs btn-outline-success month-select" data-month="'+month+'">Seç</button> <button type="button" class="btn btn-xs btn-outline-danger month-clear" data-month="'+month+'">Temizle</button></div></div>';
            html += '<div class="yp-weekdays"><div>Pzt</div><div>Sal</div><div>Çar</div><div>Per</div><div>Cum</div><div>Cmt</div><div>Paz</div></div>';
            html += '<div class="yp-grid">';
            for (let i=0; i<42; i++){
                const d = new Date(start); d.setDate(start.getDate()+i);
                const inMonth = d.getMonth() === month-1;
                const ds = toLocalISO(d);
                const sel = inMonth && selectedSpecialDates.has(ds);
                let priceText = '';
                try {
                    const priceMap = JSON.parse($('#selected_prices').val() || '{}');
                    if (inMonth && priceMap[ds]) {
                        const prices = priceMap[ds];
                        const maxPrice = Math.max(prices.adult || 0, prices.child || 0, prices.infant || 0);
                        if (maxPrice > 0) {
                            const sym = getCurrencySymbol();
                            priceText = '<span class="day-price">'+ sym + maxPrice + '</span>';
                        }
                    }
                } catch(e) {}
                html += '<div class="yp-day ' + (inMonth?'':'out ') + (sel?'sel':'') + '" data-date="' + ds + '"><span class="yp-daynum">' + d.getDate() + '</span>' + priceText + '</div>';
            }
            html += '</div></div>';
        }
        $('#year-planner').html(html);
    }

    // Toggle state for selected dates list (default: closed)
    let selectedDatesListOpen = false;

    function updateSelectedDatesUI(){
        const arr = Array.from(selectedSpecialDates).sort();
        const $list=$('#cdp-selected-list');
        const $wrapper=$('#selected-dates-wrapper');
        const $count=$('#selected-dates-count');
        
        if ((arr.length >= 2) || (lastTwoClicks.length >= 2)){
            const anchor = lastTwoClicks.length ? lastTwoClicks[lastTwoClicks.length-1] : arr[arr.length-1];
            setTimeout(()=>showPlannerMenu(anchor), 0);
        } else {
            hidePlannerMenu();
        }
        
        if(!arr.length){ 
            $wrapper.hide();
            $list.hide().empty(); 
            $('#selected_dates').val(''); 
            syncHiddenInputs(); 
            return; 
        }
        
        // Update count badge
        $count.text(arr.length);
        
        // Build HTML for selected dates
        let html='';
        arr.forEach(ds=>{ html += `<span class="selected-date-item"><i class="fas fa-calendar-day"></i> ${new Date(ds).toLocaleDateString('tr-TR')}<span class="remove-date" data-date="${ds}">&times;</span></span>`; });
        $list.html(html);
        
        // Show wrapper but keep list collapsed by default
        $wrapper.show();
        if(selectedDatesListOpen) {
            $list.show();
        } else {
            $list.hide();
        }
        
        $('#selected_dates').val(JSON.stringify(arr));
        syncHiddenInputs();
    }

    // Toggle selected dates list
    $(document).on('click', '#toggle-selected-dates', function(){
        selectedDatesListOpen = !selectedDatesListOpen;
        const $list = $('#cdp-selected-list');
        const $icon = $('#toggle-icon');
        const $text = $('#toggle-text');
        
        if(selectedDatesListOpen){
            $list.slideDown(200);
            $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $text.text('Seçili Tarihleri Gizle');
        } else {
            $list.slideUp(200);
            $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            $text.text('Seçili Tarihleri Göster');
        }
    });

    function showPlannerMenu(lastDs){
        const $menu = $('#planner-menu');
        if (!$menu.parent().is('body')) {
            $('body').append($menu);
        }
        $menu.data('last', lastDs);
        const $cell = $(`.yp-day[data-date="${lastDs}"]:not(.out)`).first();
        if ($cell.length){
            const rect = $cell[0].getBoundingClientRect();
            const docTop = (window.pageYOffset || document.documentElement.scrollTop);
            const docLeft = (window.pageXOffset || document.documentElement.scrollLeft);
            const top = rect.bottom + docTop + 6;
            const left = rect.left + docLeft;
            $menu.css({ top: top+'px', left: left+'px' }).show();
        } else {
            hidePlannerMenu();
        }
    }

    function hidePlannerMenu(){
        if (inWeekdayMode && lastTwoClicks.length >= 2){
            try {
                let map = {}; try { map = JSON.parse($('#selected_prices').val()||'{}'); } catch(_) { map = {}; }
                if (activeWeekdays.size > 0) {
                    [lastTwoClicks[0], lastTwoClicks[1]].forEach(function(ds){
                        const d = new Date(ds);
                        const dow = d.getDay();
                        const mondayBased = (dow === 0 ? 7 : dow);
                        if (!activeWeekdays.has(mondayBased)){
                            selectedSpecialDates.delete(ds);
                            if (map.hasOwnProperty(ds)) delete map[ds];
                        }
                    });
                } else {
                    [lastTwoClicks[0], lastTwoClicks[1]].forEach(function(ds){
                        selectedSpecialDates.delete(ds);
                        if (map.hasOwnProperty(ds)) delete map[ds];
                    });
                }
                $('#selected_prices').val(JSON.stringify(map));
            } catch(_) {}
        }
        $('#planner-menu').hide().data('last', '');
        $('#weekday-popup').hide();
        $('#price-popup').hide();
        setRangeLock(false);
        activeWeekdays.clear();
        inWeekdayMode = false;
        $('.wd-btn').removeClass('active');
    }

    function setRangeLock(lock){
        rangeLocked = !!lock;
        const pair = lastTwoClicks.slice(-2);
        if (pair.length < 2) return;
        pair.forEach(function(ds){
            const $cell = $(`.yp-day[data-date="${ds}"]:not(.out)`);
            if (lock) { $cell.addClass('locked'); } else { $cell.removeClass('locked'); }
        });
    }

    $('#pm-fill').on('click', function(){
        let pair = lastTwoClicks.slice(-2);
        if (pair.length < 2) { const arr = Array.from(selectedSpecialDates).sort(); if (arr.length<2) return; pair=[arr[0], arr[arr.length-1]]; }
        const a = new Date(pair[0]); const b = new Date(pair[1]);
        const start = a < b ? a : b; const end = a < b ? b : a; let cur = new Date(start);
        while (cur <= end){ const ds = toLocalISO(cur); selectedSpecialDates.add(ds); cur.setDate(cur.getDate()+1); }
        renderYearPlanner(); updateSelectedDatesUI();
    });

    $('#pm-clear').on('click', function(){
        let pair = lastTwoClicks.slice(-2);
        if (pair.length < 2) {
            const arr = Array.from(selectedSpecialDates).sort();
            if (arr.length < 2) return;
            pair = [arr[arr.length-2], arr[arr.length-1]];
        }
        const a = new Date(pair[0]); const b = new Date(pair[1]);
        const start = a < b ? a : b; const end = a < b ? b : a;
        let map = {}; try { map = JSON.parse($('#selected_prices').val()||'{}'); } catch(_) { map = {}; }
        let cur = new Date(start);
        while (cur <= end){
            const ds = toLocalISO(cur);
            selectedSpecialDates.delete(ds);
            if (map.hasOwnProperty(ds)) { delete map[ds]; }
            cur.setDate(cur.getDate()+1);
        }
        $('#selected_prices').val(JSON.stringify(map));
        renderYearPlanner(); updateSelectedDatesUI();
    });

    $('#global-clear-btn').on('click', function(){ 
        hidePlannerMenu(); 
        $('#weekday-popup').hide(); 
        selectedSpecialDates.clear(); 
        lastTwoClicks=[]; 
        $('#selected_prices').val('{}'); 
        renderYearPlanner(); 
        updateSelectedDatesUI(); 
    });

    $(document).on('click','.month-clear', function(e){ 
        hidePlannerMenu(); 
        $('#weekday-popup').hide(); 
        const m=parseInt($(this).data('month')); 
        const y=(new Date()).getFullYear(); 
        const days=new Date(y,m,0).getDate(); 
        let map={}; 
        try{ map=JSON.parse($('#selected_prices').val()||'{}'); }catch(_){ map={}; } 
        for(let d=1; d<=days; d++){ 
            const ds=toLocalISO(new Date(y,m-1,d)); 
            selectedSpecialDates.delete(ds); 
            if(map.hasOwnProperty(ds)) delete map[ds]; 
        } 
        $('#selected_prices').val(JSON.stringify(map)); 
        renderYearPlanner(); 
        updateSelectedDatesUI(); 
        e.stopPropagation(); 
    });

    $(document).on('click','.month-select', function(e){ 
        hidePlannerMenu(); 
        $('#weekday-popup').hide(); 
        const m=parseInt($(this).data('month')); 
        const y=(new Date()).getFullYear(); 
        const days=new Date(y,m,0).getDate(); 
        for(let d=1; d<=days; d++){ 
            const ds=toLocalISO(new Date(y,m-1,d)); 
            selectedSpecialDates.add(ds);
        } 
        renderYearPlanner(); 
        updateSelectedDatesUI(); 
        e.stopPropagation(); 
    });

    $('#pm-add-date').on('click', function(){
        if (lastTwoClicks.length < 2){
            const arr = Array.from(selectedSpecialDates).sort();
            if (arr.length >= 2){ lastTwoClicks = [arr[arr.length-2], arr[arr.length-1]]; }
        }
        activeWeekdays.clear();
        $('.wd-btn').removeClass('active');
        inWeekdayMode = true;
        setRangeLock(true);
        const $menu = $('#planner-menu');
        const $popup = $('#weekday-popup');
        if (!$popup.parent().is('body')) { $('body').append($popup); }
        const rect = $menu[0].getBoundingClientRect();
        const docTop = (window.pageYOffset || document.documentElement.scrollTop);
        const docLeft = (window.pageXOffset || document.documentElement.scrollLeft);
        $popup.css({ top: (rect.top + rect.height + 6 + docTop)+'px', left: (rect.left + docLeft)+'px' }).show();
    });

    $(document).on('click', '.wd-btn', function(){
        if (lastTwoClicks.length < 2) { alert('Lütfen aralık için iki tarih seçin.'); return; }
        const wdChip = parseInt($(this).data('wd'));
        $(this).toggleClass('active');
        const isActive = $(this).hasClass('active');
        if (isActive) { activeWeekdays.add(wdChip); } else { activeWeekdays.delete(wdChip); }
        const a = new Date(lastTwoClicks[0]); const b = new Date(lastTwoClicks[1]);
        const start = a < b ? a : b; const end = a < b ? b : a;
        let cur = new Date(start);
        while (cur <= end){
            const dow = cur.getDay();
            const mondayBased = (dow === 0 ? 7 : dow);
            const ds = toLocalISO(cur);
            if (mondayBased === wdChip){
                if (isActive){
                    selectedSpecialDates.add(ds);
                } else {
                    if (ds !== lastTwoClicks[0] && ds !== lastTwoClicks[1]){
                        selectedSpecialDates.delete(ds);
                    }
                }
            }
            cur.setDate(cur.getDate()+1);
        }
        renderYearPlanner(); updateSelectedDatesUI(); setRangeLock(true);
    });

    $(document).on('click', '#pm-show-price', function(){ 
        const $menu=$('#planner-menu'); 
        const $pp=$('#price-popup'); 
        if(!$pp.parent().is('body')){ $('body').append($pp); } 
        const rect=$menu[0].getBoundingClientRect(); 
        const docTop=(window.pageYOffset||document.documentElement.scrollTop); 
        const docLeft=(window.pageXOffset||document.documentElement.scrollLeft); 
        refreshPricePlaceholders(); 
        $pp.css({ top:(rect.top+rect.height+6+docTop)+'px', left:(rect.left+docLeft)+'px' }).show(); 
    });

    function applyPricesFromPopup(){
        let startDate = null;
        let endDate = null;
        if (lastTwoClicks && lastTwoClicks.length >= 2) {
            const a = new Date(lastTwoClicks[0]);
            const b = new Date(lastTwoClicks[1]);
            startDate = a < b ? a : b;
            endDate = a < b ? b : a;
        } else {
            const selectedArray = Array.from(selectedSpecialDates).sort();
            if (selectedArray.length < 2) {
                alert('En az 2 tarih seçmelisiniz!');
                return;
            }
            startDate = new Date(selectedArray[0]);
            endDate = new Date(selectedArray[selectedArray.length - 1]);
        }

        const adult = Number.isNaN(parseFloat($('#pm-price-adult').val())) ? 0 : parseFloat($('#pm-price-adult').val());
        const child = Number.isNaN(parseFloat($('#pm-price-child').val())) ? 0 : parseFloat($('#pm-price-child').val());
        const infant = Number.isNaN(parseFloat($('#pm-price-infant').val())) ? 0 : parseFloat($('#pm-price-infant').val());

        let priceMap = {};
        try { priceMap = JSON.parse($('#selected_prices').val() || '{}'); } catch(_) { priceMap = {}; }

        let currentDate = new Date(startDate.getTime());
        while (currentDate <= endDate) {
            const dateString = toLocalISO(currentDate);
            priceMap[dateString] = {
                adult: adult >= 0 ? adult : 0,
                child: child >= 0 ? child : 0,
                infant: infant >= 0 ? infant : 0
            };
            if (!selectedSpecialDates.has(dateString)) {
                selectedSpecialDates.add(dateString);
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }

        lastTwoClicks = [toLocalISO(startDate), toLocalISO(endDate)];
        $('#selected_prices').val(JSON.stringify(priceMap));
        renderYearPlanner();
        updateSelectedDatesUI();
        refreshPricePlaceholders();
        $('#price-popup').hide();
    }

    $(document).on('click', '#price-apply', function(){ applyPricesFromPopup(); });
    $(document).on('click', '#price-close', function(){ $('#price-popup').hide(); });

    // Para birimi değiştiğinde takvim + fiyat etiketlerini güncelle
    $('#currency').on('change', function(){ renderYearPlanner(); refreshPricePlaceholders(); });

    $(document).on('mousedown', function(e){
        if (e.button === 2) return;
        const $menu = $('#planner-menu');
        const isVisible = $menu.is(':visible');
        const clickedInside = $(e.target).closest('#planner-menu, #weekday-popup, #price-popup').length>0;
        const clickedOnDay = $(e.target).closest('.yp-day').length>0;
        if (isVisible && !clickedInside && !clickedOnDay){
            hidePlannerMenu();
            renderYearPlanner();
        }
    });

    $(document).on('keydown', function(e){ if (e.key==='Escape'){ hidePlannerMenu(); renderYearPlanner(); } });

    renderYearPlanner();
    updateSelectedDatesUI();

    $(document).on('click','.yp-day',function(e){ 
        if($(this).hasClass('out')||$(this).hasClass('locked')) return; 
        const ds=$(this).data('date'); 
        if(!selectedSpecialDates.has(ds)){ selectedSpecialDates.add(ds);} 
        lastTwoClicks = lastTwoClicks.filter(d=>d!==ds); 
        lastTwoClicks.push(ds); 
        if(lastTwoClicks.length>2) lastTwoClicks=lastTwoClicks.slice(-2); 
        renderYearPlanner();
        updateSelectedDatesUI(); 
        const anchor = lastTwoClicks[lastTwoClicks.length-1]; 
        showPlannerMenu(anchor); 
        e.stopPropagation(); 
    });

    $(document).on('contextmenu', '.yp-day', function(e){
        if($(this).hasClass('out')) return;
        e.preventDefault();
        const ds=$(this).data('date');
        if(selectedSpecialDates.has(ds)){
            selectedSpecialDates.delete(ds);
            let map={};
            try{ map=JSON.parse($('#selected_prices').val()||'{}'); }catch(_){ map={}; }
            if(map.hasOwnProperty(ds)) delete map[ds];
            $('#selected_prices').val(JSON.stringify(map));
            renderYearPlanner();
            updateSelectedDatesUI();
        }
    });

    $(document).on('click','.selected-date-item .remove-date',function(){ 
        const ds=$(this).data('date'); 
        selectedSpecialDates.delete(ds); 
        let map={}; 
        try{ map=JSON.parse($('#selected_prices').val()||'{}'); }catch(_){ map={}; } 
        if(map.hasOwnProperty(ds)) delete map[ds]; 
        $('#selected_prices').val(JSON.stringify(map)); 
        renderYearPlanner(); 
        updateSelectedDatesUI(); 
    });

    syncHiddenInputs();
});
</script>
<script>
$(function(){
    var $form = $('#tour-edit-form');
    if ($form.length) {
        $form.on('submit', function(){
            var sd = $('#selected_dates').val();
            var sp = $('#selected_prices').val();

            var sdArr = [];
            try { sdArr = JSON.parse(sd || '[]'); } catch(e){ sdArr = []; }
            if (!Array.isArray(sdArr)) { sdArr = []; }

            var spObj = {};
            try { spObj = JSON.parse(sp || '{}'); } catch(e){ spObj = {}; }
            if (spObj === null || typeof spObj !== 'object' || Array.isArray(spObj)) { spObj = {}; }

            // If dates are empty but prices exist, derive dates from price map
            if ((!sdArr || sdArr.length === 0) && Object.keys(spObj).length > 0) {
                sdArr = Object.keys(spObj).sort();
            }

            $('#selected_dates').val(JSON.stringify(sdArr || []));
            $('#selected_prices').val(JSON.stringify(spObj || {}));
        });
    }
});
</script>
<script>
$(function(){
    function fetchSuggestions(field, query, onDone){
        $.getJSON("{{ route('admin.tours.suggestions') }}", { field: field, q: query||'' })
            .done(function(resp){ if(resp && resp.success){ onDone(resp.items||[]); } })
            .fail(function(){ onDone([]); });
    }

    function bindDatalist($input, datalistId, field){
        const $list = $('#'+datalistId);
        let lastQ = '';
        function render(items){
            let html='';
            items.forEach(function(v){ if(v){ html += '<option value="'+$('<div/>').text(v).html()+'"></option>'; } });
            $list.html(html);
        }
        function refresh(){
            const q = ($input.val()||'').trim();
            if (q === lastQ) return;
            lastQ = q;
            fetchSuggestions(field, q, render);
        }
        fetchSuggestions(field, '', render);
        $input.on('input', refresh);
        $input.on('focus', function(){ fetchSuggestions(field, $input.val(), render); });
    }

    bindDatalist($('#country'), 'country-list', 'country');
    bindDatalist($('#city'), 'city-list', 'city');
    bindDatalist($('#district'), 'district-list', 'district');
});
</script>
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@7/turf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var MAPBOX_TOKEN = @json(config('services.mapbox.access_token'));
    var mapEl = document.getElementById('service-area-map');
    if (!mapEl || !MAPBOX_TOKEN) return;
    mapboxgl.accessToken = MAPBOX_TOKEN;

    var map = new mapboxgl.Map({ container:'service-area-map', style:'mapbox://styles/mapbox/streets-v12', center:[32.8541,39.9208], zoom:6, language:'tr' });
    map.addControl(new mapboxgl.NavigationControl(),'top-right');

    var polygons = [];
    var polygonTimes = [];
    var selectedIdx = -1;
    var activeTool = 'pointer';
    var drawingVerts = [];
    var circleCenter = null;
    var vertexMarkers = [];
    var previewLine = null;
    var activeTimesPopup = null;
    function isValidTime(s){ return typeof s === 'string' && /^\d{2}:\d{2}$/.test(s); }

    var SRC_POLYS = 'sa-polygons';
    var SRC_DRAW = 'sa-drawing';
    var SRC_CIRCLE_PREVIEW = 'sa-circle-preview';
    var SRC_VERTS = 'sa-vertices';

    map.on('load', function(){
        map.addSource(SRC_POLYS, { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
        map.addLayer({ id:'sa-poly-fill', type:'fill', source:SRC_POLYS, paint:{ 'fill-color':['case',['get','selected'],'#198754','#0d6efd'], 'fill-opacity':0.18 } });
        map.addLayer({ id:'sa-poly-stroke', type:'line', source:SRC_POLYS, paint:{ 'line-color':['case',['get','selected'],'#198754','#0d6efd'], 'line-width':['case',['get','selected'],3,2] } });

        map.addSource(SRC_DRAW, { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
        map.addLayer({ id:'sa-draw-line', type:'line', source:SRC_DRAW, paint:{ 'line-color':'#fd7e14', 'line-width':2, 'line-dasharray':[3,2] } });

        map.addSource(SRC_VERTS, { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
        map.addLayer({ id:'sa-verts-circle', type:'circle', source:SRC_VERTS, paint:{
            'circle-radius':['case',['get','hover'],9,['get','first'],7,5],
            'circle-color':['case',['get','hover'],'#198754',['get','first'],'#0d6efd','#fd7e14'],
            'circle-stroke-width':2,
            'circle-stroke-color':'#fff'
        } });

        map.addSource(SRC_CIRCLE_PREVIEW, { type:'geojson', data:{ type:'FeatureCollection', features:[] } });
        map.addLayer({ id:'sa-circle-fill', type:'fill', source:SRC_CIRCLE_PREVIEW, paint:{ 'fill-color':'#fd7e14', 'fill-opacity':0.15 } });
        map.addLayer({ id:'sa-circle-stroke', type:'line', source:SRC_CIRCLE_PREVIEW, paint:{ 'line-color':'#fd7e14', 'line-width':2, 'line-dasharray':[3,2] } });

        loadExisting();
    });

    function serialize(){
        var fc = { type:'FeatureCollection', features:[] };
        polygons.forEach(function(coords, i){
            if (coords.length >= 3) {
                var ring = coords.map(function(c){ return [c[0],c[1]]; });
                ring.push(ring[0]);
                var times = Array.isArray(polygonTimes[i]) ? polygonTimes[i].slice() : [];
                fc.features.push({
                    type:'Feature',
                    properties:{ times: times },
                    geometry:{ type:'Polygon', coordinates:[ring] }
                });
            }
        });
        document.getElementById('service_areas').value = JSON.stringify(fc);
    }

    function renderPolygons(){
        var features = polygons.map(function(coords, i){
            var ring = coords.map(function(c){ return [c[0],c[1]]; });
            ring.push(ring[0]);
            return { type:'Feature', properties:{ idx:i, selected: i===selectedIdx }, geometry:{ type:'Polygon', coordinates:[ring] } };
        });
        map.getSource(SRC_POLYS).setData({ type:'FeatureCollection', features:features });
    }

    var hoveringFirst = false;

    function renderDrawing(){
        var features = [];
        if (drawingVerts.length >= 1) {
            var coords = drawingVerts.map(function(c){ return [c[0],c[1]]; });
            if (previewLine) coords.push(previewLine);
            if (coords.length >= 2) features.push({ type:'Feature', geometry:{ type:'LineString', coordinates:coords } });
        }
        map.getSource(SRC_DRAW).setData({ type:'FeatureCollection', features:features });

        var vertFeats = drawingVerts.map(function(c, i){
            return { type:'Feature', properties:{ first: i===0 && drawingVerts.length >= 3, hover: i===0 && hoveringFirst && drawingVerts.length >= 3 }, geometry:{ type:'Point', coordinates:[c[0],c[1]] } };
        });
        map.getSource(SRC_VERTS).setData({ type:'FeatureCollection', features:vertFeats });
    }

    function clearDrawing(){
        drawingVerts = [];
        previewLine = null;
        circleCenter = null;
        hoveringFirst = false;
        renderDrawing();
        map.getSource(SRC_CIRCLE_PREVIEW).setData({ type:'FeatureCollection', features:[] });
        map.getCanvas().style.cursor = activeTool === 'pointer' ? '' : 'crosshair';
    }

    function setTool(tool){
        if (tool === 'delete') {
            if (selectedIdx >= 0) {
                polygons.splice(selectedIdx,1);
                polygonTimes.splice(selectedIdx,1);
                selectedIdx = -1;
                closeTimesPopup();
                renderPolygons();
                serialize();
            }
            return;
        }
        if (tool === 'delete-all') {
            polygons = [];
            polygonTimes = [];
            selectedIdx = -1;
            closeTimesPopup();
            renderPolygons();
            serialize();
            return;
        }
        if (tool === 'save-map') { smOpenSave(); return; }
        if (tool === 'load-map') { smOpenLoad(); return; }
        closeTimesPopup();
        clearDrawing();
        activeTool = tool;
        selectedIdx = -1;
        renderPolygons();
        document.querySelectorAll('.sa-tool-btn').forEach(function(b){
            b.classList.toggle('active', b.dataset.tool === tool);
        });
        if (tool === 'pointer') {
            map.getCanvas().style.cursor = '';
        } else {
            map.getCanvas().style.cursor = 'crosshair';
        }
    }

    document.querySelectorAll('.sa-tool-btn').forEach(function(btn){
        btn.addEventListener('click', function(){ setTool(this.dataset.tool); });
    });

    map.on('click', 'sa-poly-fill', function(e){
        if (activeTool !== 'pointer') return;
        var f = e.features[0];
        if (f) {
            selectedIdx = f.properties.idx;
            renderPolygons();
            openTimesPopup(selectedIdx, e.lngLat);
        }
    });

    map.on('click', function(e){
        if (activeTool === 'pointer') return;
        var lngLat = [e.lngLat.lng, e.lngLat.lat];

        if (activeTool === 'polygon') {
            if (drawingVerts.length >= 3) {
                var first = drawingVerts[0];
                var dx = e.point.x, dy = e.point.y;
                var fp = map.project(first);
                if (Math.abs(dx-fp.x) < 12 && Math.abs(dy-fp.y) < 12) {
                    polygons.push(drawingVerts.slice());
                    polygonTimes.push([]);
                    clearDrawing();
                    renderPolygons();
                    serialize();
                    return;
                }
            }
            drawingVerts.push(lngLat);
            renderDrawing();
        }

        if (activeTool === 'circle') {
            if (!circleCenter) {
                circleCenter = lngLat;
                drawingVerts = [lngLat];
                renderDrawing();
            } else {
                var from = turf.point(circleCenter);
                var to = turf.point(lngLat);
                var dist = turf.distance(from, to, { units:'kilometers' });
                if (dist > 0.01) {
                    var circle = turf.circle(circleCenter, dist, { steps:64, units:'kilometers' });
                    var ring = circle.geometry.coordinates[0];
                    polygons.push(ring.slice(0, -1));
                    polygonTimes.push([]);
                    renderPolygons();
                    serialize();
                }
                clearDrawing();
            }
        }
    });

    map.on('dblclick', function(e){
        if (activeTool === 'polygon' && drawingVerts.length >= 3) {
            e.preventDefault();
            polygons.push(drawingVerts.slice());
            polygonTimes.push([]);
            clearDrawing();
            renderPolygons();
            serialize();
        }
    });

    map.on('mousemove', function(e){
        if (activeTool === 'polygon' && drawingVerts.length >= 1) {
            previewLine = [e.lngLat.lng, e.lngLat.lat];
            var wasHovering = hoveringFirst;
            hoveringFirst = false;
            if (drawingVerts.length >= 3) {
                var fp = map.project(drawingVerts[0]);
                if (Math.abs(e.point.x - fp.x) < 12 && Math.abs(e.point.y - fp.y) < 12) {
                    hoveringFirst = true;
                }
            }
            if (hoveringFirst !== wasHovering) {
                map.getCanvas().style.cursor = hoveringFirst ? 'pointer' : 'crosshair';
            }
            renderDrawing();
        }
        if (activeTool === 'circle' && circleCenter) {
            var from = turf.point(circleCenter);
            var to = turf.point([e.lngLat.lng, e.lngLat.lat]);
            var dist = turf.distance(from, to, { units:'kilometers' });
            if (dist > 0.005) {
                var circle = turf.circle(circleCenter, dist, { steps:64, units:'kilometers' });
                map.getSource(SRC_CIRCLE_PREVIEW).setData({ type:'FeatureCollection', features:[circle] });
            }
        }
    });

    map.on('contextmenu', function(e){
        if (activeTool === 'polygon' && drawingVerts.length > 0) {
            e.preventDefault();
            clearDrawing();
        }
        if (activeTool === 'circle' && circleCenter) {
            e.preventDefault();
            clearDrawing();
        }
    });

    function loadExisting(){
        try {
            var raw = document.getElementById('service_areas').value || '';
            if (!raw) return;
            if (typeof raw === 'string' && raw.startsWith('"')) raw = JSON.parse(raw);
            var geo = typeof raw === 'string' ? JSON.parse(raw) : raw;
            if (!geo) return;
            var bounds = new mapboxgl.LngLatBounds();
            var added = 0;
            if (geo.type === 'FeatureCollection' && Array.isArray(geo.features)) {
                geo.features.forEach(function(feat){
                    if (!feat || !feat.geometry || feat.geometry.type !== 'Polygon') return;
                    var ring = (feat.geometry.coordinates && feat.geometry.coordinates[0]) || [];
                    var verts = ring.slice();
                    if (verts.length > 1 && verts[0][0]===verts[verts.length-1][0] && verts[0][1]===verts[verts.length-1][1]) verts.pop();
                    if (verts.length >= 3) {
                        polygons.push(verts);
                        var t = (feat.properties && Array.isArray(feat.properties.times)) ? feat.properties.times.filter(isValidTime) : [];
                        polygonTimes.push(t);
                        verts.forEach(function(p){ bounds.extend(p); });
                        added++;
                    }
                });
            } else if (geo.type === 'MultiPolygon' && Array.isArray(geo.coordinates)) {
                geo.coordinates.forEach(function(polyCoords){
                    var ring = polyCoords[0] || [];
                    var verts = ring.slice();
                    if (verts.length > 1 && verts[0][0]===verts[verts.length-1][0] && verts[0][1]===verts[verts.length-1][1]) verts.pop();
                    if (verts.length >= 3) {
                        polygons.push(verts);
                        polygonTimes.push([]);
                        verts.forEach(function(p){ bounds.extend(p); });
                        added++;
                    }
                });
            }
            if (added) {
                renderPolygons();
                serialize();
                if (!bounds.isEmpty()) map.fitBounds(bounds,{padding:40,maxZoom:12});
            }
        } catch(e){}
    }

    // ==================== Polygon Times Popup ====================
    function closeTimesPopup(){
        if (activeTimesPopup) { activeTimesPopup.remove(); activeTimesPopup = null; }
    }

    function openTimesPopup(idx, lngLat){
        closeTimesPopup();
        activeTimesPopup = new mapboxgl.Popup({
            closeButton: false,
            closeOnClick: false,
            offset: 12,
            className: 'ptp-pop',
            maxWidth: 'none'
        }).setLngLat(lngLat).setHTML(buildTimesPopupHtml(idx)).addTo(map);
        activeTimesPopup.on('close', function(){ activeTimesPopup = null; });
        bindTimesPopupEvents(idx);
    }

    function buildTimesPopupHtml(idx){
        var list = Array.isArray(polygonTimes[idx]) ? polygonTimes[idx].slice().sort() : [];
        var rows;
        if (!list.length) {
            rows = '<div class="ptp-empty">Henüz saat eklenmedi</div>';
        } else {
            rows = list.map(function(t){
                return '<div class="ptp-item"><span>' + smEscape(t) + '</span>' +
                       '<button type="button" class="ptp-del" data-time="' + smEscape(t) + '" title="Sil">' +
                       '<i class="fas fa-times"></i></button></div>';
            }).join('');
        }
        return '<div class="poly-times-popup">' +
                    '<div class="ptp-header"><span>Poligon #' + (idx+1) + ' — Saatler</span>' +
                        '<button type="button" class="ptp-close" title="Kapat">&times;</button></div>' +
                    '<div class="ptp-list">' + rows + '</div>' +
                    '<div class="ptp-add">' +
                        '<input type="time" class="ptp-input form-control form-control-sm" step="60">' +
                        '<button type="button" class="btn btn-sm btn-primary ptp-add-btn">Ekle</button>' +
                    '</div>' +
                    '<div class="ptp-hint">Saat girmek opsiyoneldir. En erken saat tur listesinde gösterilir.</div>' +
                '</div>';
    }

    function bindTimesPopupEvents(idx){
        if (!activeTimesPopup) return;
        var el = activeTimesPopup.getElement();
        if (!el) return;
        var close = el.querySelector('.ptp-close');
        if (close) close.addEventListener('click', function(){ closeTimesPopup(); });

        var input = el.querySelector('.ptp-input');
        var addBtn = el.querySelector('.ptp-add-btn');
        function addNow(){
            var v = (input.value || '').trim();
            if (!isValidTime(v)) return;
            if (!Array.isArray(polygonTimes[idx])) polygonTimes[idx] = [];
            if (polygonTimes[idx].indexOf(v) === -1) {
                polygonTimes[idx].push(v);
                polygonTimes[idx].sort();
            }
            input.value = '';
            serialize();
            activeTimesPopup.setHTML(buildTimesPopupHtml(idx));
            bindTimesPopupEvents(idx);
        }
        if (addBtn) addBtn.addEventListener('click', addNow);
        if (input) input.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); addNow(); } });

        el.querySelectorAll('.ptp-del').forEach(function(b){
            b.addEventListener('click', function(){
                var t = b.dataset.time;
                if (!t || !Array.isArray(polygonTimes[idx])) return;
                var i = polygonTimes[idx].indexOf(t);
                if (i >= 0) {
                    polygonTimes[idx].splice(i, 1);
                    serialize();
                    activeTimesPopup.setHTML(buildTimesPopupHtml(idx));
                    bindTimesPopupEvents(idx);
                }
            });
        });
    }

    // ==================== Saved Maps (kullanıcı bazlı poligon kütüphanesi) ====================
    var SM_URLS = {
        index:  @json(route('admin.saved-maps.index')),
        store:  @json(route('admin.saved-maps.store')),
        destroy: @json(url('admin/saved-maps'))
    };
    function smCsrf() {
        var i = document.querySelector('input[name="_token"]');
        if (i) return i.value;
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function smEscape(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }
    function smShow(id){ var el = document.getElementById(id); if (el) el.style.display = 'flex'; }
    function smHide(id){ var el = document.getElementById(id); if (el) el.style.display = 'none'; }

    function smOpenSave() {
        var msg = document.getElementById('sm-save-msg');
        if (msg) msg.innerHTML = '';
        var input = document.getElementById('sm-save-name');
        if (input) input.value = '';
        smShow('sm-save-modal');
        setTimeout(function(){ if (input) input.focus(); }, 50);
    }

    function smOpenLoad() {
        smShow('sm-load-modal');
        smLoadList();
    }

    function smSave() {
        var input = document.getElementById('sm-save-name');
        var msg = document.getElementById('sm-save-msg');
        var btn = document.getElementById('sm-save-btn');
        if (!input || !msg || !btn) return;
        msg.innerHTML = '';
        var name = (input.value || '').trim();
        if (!name) {
            msg.innerHTML = '<div class="text-danger small">Lütfen bir isim girin.</div>';
            return;
        }
        if (!polygons.length) {
            msg.innerHTML = '<div class="text-danger small">Kaydedilecek poligon yok. Önce en az bir poligon çizin.</div>';
            return;
        }
        serialize();
        var geo;
        try { geo = JSON.parse(document.getElementById('service_areas').value); }
        catch(e) { msg.innerHTML = '<div class="text-danger small">Poligon verisi okunamadı.</div>'; return; }
        var hasAny = false;
        if (geo) {
            if (geo.type === 'FeatureCollection') hasAny = Array.isArray(geo.features) && geo.features.length > 0;
            else if (geo.type === 'MultiPolygon') hasAny = Array.isArray(geo.coordinates) && geo.coordinates.length > 0;
        }
        if (!hasAny) {
            msg.innerHTML = '<div class="text-danger small">Kaydedilecek poligon yok.</div>';
            return;
        }
        btn.disabled = true;
        var origLabel = btn.innerHTML;
        btn.innerHTML = 'Kaydediliyor...';
        fetch(SM_URLS.store, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': smCsrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name: name, geometry: geo })
        })
        .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, status: r.status, body: d }; }); })
        .then(function(res){
            btn.disabled = false;
            btn.innerHTML = origLabel;
            if (res.ok) {
                msg.innerHTML = '<div class="text-success small">Harita başarıyla kaydedildi.</div>';
                setTimeout(function(){ smHide('sm-save-modal'); }, 700);
            } else {
                var err = (res.body && res.body.message) || 'Kaydedilemedi.';
                msg.innerHTML = '<div class="text-danger small">' + smEscape(err) + '</div>';
            }
        })
        .catch(function(){
            btn.disabled = false;
            btn.innerHTML = origLabel;
            msg.innerHTML = '<div class="text-danger small">Ağ hatası. Lütfen tekrar deneyin.</div>';
        });
    }

    function smLoadList() {
        var list = document.getElementById('sm-load-list');
        if (!list) return;
        list.innerHTML = '<div class="text-muted small">Yükleniyor...</div>';
        fetch(SM_URLS.index, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            var items = (res && res.data) || [];
            if (!items.length) {
                list.innerHTML = '<div class="text-muted small">Kayıtlı harita yok. Önce haritadaki bir poligonu kaydedin.</div>';
                return;
            }
            list.innerHTML = '';
            items.forEach(function(it){
                var row = document.createElement('div');
                row.className = 'sm-item';
                var when = it.updated_at ? new Date(it.updated_at).toLocaleString('tr-TR') : '';
                row.innerHTML =
                    '<div class="sm-item-info">' +
                        '<div class="sm-item-name">' + smEscape(it.name) + '</div>' +
                        '<div class="sm-item-meta">' + smEscape(when) + '</div>' +
                    '</div>' +
                    '<div class="sm-item-actions">' +
                        '<button type="button" class="btn btn-sm btn-primary" data-sm-action="add"><i class="fas fa-plus"></i> Ekle</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger" data-sm-action="delete"><i class="fas fa-trash"></i></button>' +
                    '</div>';
                row.querySelector('[data-sm-action="add"]').addEventListener('click', function(){ smAddToMap(it); });
                row.querySelector('[data-sm-action="delete"]').addEventListener('click', function(){ smDelete(it.id, row); });
                list.appendChild(row);
            });
        })
        .catch(function(){
            list.innerHTML = '<div class="text-danger small">Liste yüklenemedi.</div>';
        });
    }

    function smAddToMap(item) {
        var geo = item && item.geometry;
        if (typeof geo === 'string') {
            try { geo = JSON.parse(geo); } catch(e) { geo = null; }
        }
        if (!geo) return;
        var bounds = new mapboxgl.LngLatBounds();
        var added = 0;
        if (geo.type === 'FeatureCollection' && Array.isArray(geo.features)) {
            geo.features.forEach(function(feat){
                if (!feat || !feat.geometry || feat.geometry.type !== 'Polygon') return;
                var ring = (feat.geometry.coordinates && feat.geometry.coordinates[0]) || [];
                var verts = ring.slice();
                if (verts.length > 1 && verts[0][0]===verts[verts.length-1][0] && verts[0][1]===verts[verts.length-1][1]) verts.pop();
                if (verts.length >= 3) {
                    polygons.push(verts);
                    var t = (feat.properties && Array.isArray(feat.properties.times)) ? feat.properties.times.filter(isValidTime) : [];
                    polygonTimes.push(t);
                    verts.forEach(function(p){ bounds.extend(p); });
                    added++;
                }
            });
        } else if (geo.type === 'MultiPolygon' && Array.isArray(geo.coordinates)) {
            geo.coordinates.forEach(function(polyCoords){
                var ring = polyCoords[0] || [];
                var verts = ring.slice();
                if (verts.length > 1 && verts[0][0] === verts[verts.length-1][0] && verts[0][1] === verts[verts.length-1][1]) verts.pop();
                if (verts.length >= 3) {
                    polygons.push(verts);
                    polygonTimes.push([]);
                    verts.forEach(function(p){ bounds.extend(p); });
                    added++;
                }
            });
        }
        if (added) {
            renderPolygons();
            serialize();
            if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: 40, maxZoom: 13 });
        }
        smHide('sm-load-modal');
    }

    function smDelete(id, row) {
        if (!confirm('Bu kayıtlı harita silinsin mi?')) return;
        fetch(SM_URLS.destroy + '/' + encodeURIComponent(id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': smCsrf(),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r){ return r.json().catch(function(){ return {}; }); })
        .then(function(res){
            if (res && res.success) { row.remove(); }
            else { alert('Silinemedi.'); }
        })
        .catch(function(){ alert('Ağ hatası. Silinemedi.'); });
    }

    document.querySelectorAll('.sm-modal-backdrop [data-sm-close]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var b = btn.closest('.sm-modal-backdrop');
            if (b) b.style.display = 'none';
        });
    });
    document.querySelectorAll('.sm-modal-backdrop').forEach(function(bk){
        bk.addEventListener('click', function(e){ if (e.target === bk) bk.style.display = 'none'; });
    });
    var smSaveBtn = document.getElementById('sm-save-btn');
    if (smSaveBtn) smSaveBtn.addEventListener('click', smSave);
    var smSaveInput = document.getElementById('sm-save-name');
    if (smSaveInput) {
        smSaveInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); smSave(); } });
    }
});
</script>
@endpush
