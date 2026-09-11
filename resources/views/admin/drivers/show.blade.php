@extends('layouts.admin')

@section('title', __('Şoför Detayları'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Şoför Bilgileri') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <dt>{{ __('Ad Soyad') }}</dt>
                                    <dd><strong>{{ $driver->name }}</strong></dd>

                                    <dt>{{ __('E-posta') }}</dt>
                                    <dd>{{ $driver->email }}</dd>

                                    <dt>{{ __('Telefon') }}</dt>
                                    <dd>{{ $driver->phone_number }}</dd>

                                    <dt>{{ __('Kullanıcı Seviyesi') }}</dt>
                                    <dd>{{ $driver->level_label }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt>{{ __('Durum') }}</dt>
                                    <dd>
                                        @if($driver->is_active)
                                            <span class="badge badge-success">{{ __('Aktif') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('Pasif') }}</span>
                                        @endif
                                    </dd>

                                    <dt>{{ __('Atanmış Araç') }}</dt>
                                    <dd>
                                        @if($driver->vehicle)
                                            <span class="badge badge-info">{{ $driver->vehicle->plate_number }}</span>
                                            <br><small class="text-muted">{{ $driver->vehicle->brand }} {{ $driver->vehicle->model }}</small>
                                        @else
                                            <span class="badge badge-secondary">{{ __('Araç Atanmamış') }}</span>
                                        @endif
                                    </dd>

                                    <dt>{{ __('Son Giriş') }}</dt>
                                    <dd>
                                        @if($driver->last_login_at)
                                            {{ $driver->last_login_at->format('d.m.Y H:i') }}
                                            <br><small class="text-muted">{{ $driver->last_login_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">{{ __('Hiç giriş yapmadı') }}</span>
                                        @endif
                                    </dd>

                                    <dt>{{ __('Kayıt Tarihi') }}</dt>
                                    <dd>{{ $driver->created_at->format('d.m.Y H:i') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- şoför giriş bilgileri kartı-->
                <!-- Giriş Bilgileri -->
                <div class="ad-card mb-3">
                    <div class="card-header bg-info">
                        <h3 class="card-title">
                            <i class="fas fa-key"></i> {{ __('Şoför Paneli Giriş Bilgileri') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>{{ __('Bu bilgileri şoföre verin') }}</strong> - {{ __('Şoför paneline giriş için kullanacak.') }}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <dl>
                                    <dt><i class="fas fa-globe"></i> {{ __('Giriş Adresi') }}</dt>
                                    <dd>
                                        <code>{{ url('/login') }}</code>
                                        <button class="btn btn-xs btn-outline-primary ml-2" onclick="copyToClipboard('{{ url('/login') }}')">
                                            <i class="fas fa-copy"></i> {{ __('Kopyala') }}
                                        </button>
                                    </dd>

                                    <dt><i class="fas fa-envelope"></i> {{ __('E-posta') }}</dt>
                                    <dd>
                                        <code>{{ $driver->email }}</code>
                                        <button class="btn btn-xs btn-outline-primary ml-2" onclick="copyToClipboard('{{ $driver->email }}')">
                                            <i class="fas fa-copy"></i> {{ __('Kopyala') }}
                                        </button>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl>
                                    <dt><i class="fas fa-lock"></i> {{ __('Şifre') }}</dt>
                                    <dd>
                                        @php
                                            $shownPwd = session('reset_password') && session('reset_driver_id') == $driver->id
                                                ? session('reset_password')
                                                : $driver->plain_password;
                                        @endphp
                                        @if($shownPwd)
                                            <span id="pwd-value" class="bg-warning px-2 py-1" style="font-family:monospace;border-radius:3px;letter-spacing:0.5px;">
                                                <span id="pwd-text" data-shown="0" data-real="{{ $shownPwd }}">{{ str_repeat('•', max(strlen($shownPwd), 6)) }}</span>
                                            </span>
                                            <button type="button" class="btn btn-xs btn-outline-primary ml-2" id="pwd-toggle" title="{{ __('Göster/Gizle') }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-primary ml-1" onclick="copyToClipboard('{{ $shownPwd }}')" title="{{ __('Kopyala') }}">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-warning ml-1" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                                <i class="fas fa-key"></i> {{ __('Yeni Şifre') }}
                                            </button>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                {{ __('Şifre yalnızca admin panelinden görülebilir; veritabanında :status.', ['status' => session('reset_password') ? __('az önce sıfırlandı') : __('şifrelenmiş olarak saklanır')]) }}
                                            </small>
                                        @else
                                            <span class="text-muted"><i class="fas fa-question-circle"></i> {{ __('Şifre kayıtlı değil (eski hesap)') }}</span>
                                            <br>
                                            <button type="button" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                                <i class="fas fa-key"></i> {{ __('Yeni Şifre Belirle') }}
                                            </button>
                                        @endif
                                    </dd>

                                    <dt><i class="fas fa-mobile-alt"></i> {{ __('Panel Türü') }}</dt>
                                    <dd>
                                        <span class="badge badge-success">{{ __('Şoför Paneli') }}</span>
                                        <br><small class="text-muted">Level 2 - Driver Dashboard</small>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <div class="mt-3 p-3 bg-light rounded">
                            <h6><i class="fas fa-info-circle"></i> {{ __('Giriş Talimatları:') }}</h6>
                            <ol class="mb-0">
                                <li>{{ __('Yukarıdaki giriş adresine gidin') }}</li>
                                <li>{{ __('E-posta') }}: <code>{{ $driver->email }}</code></li>
                                <li>{!! __('Şifre: yukarıdaki :label ile bir şifre oluşturup şoföre iletin', ['label' => '"' . __('Yeni Şifre Belirle') . '"']) !!}</li>
                                <li>{!! __(':label butonuna tıklayın', ['label' => '"' . __('Giriş Yap') . '"']) !!}</li>
                            </ol>
                        </div>

                        {{-- Şifre sıfırlama modal --}}
                        <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.drivers.reset-password', $driver) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="fas fa-key"></i> {{ $driver->name }} — {{ __('Yeni Şifre') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Kapat') }}"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="new_password">{{ __('Yeni Şifre') }} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="new_password" name="new_password" minlength="4" maxlength="50" required autocomplete="off" placeholder="{{ __('En az 4 karakter') }}">
                                                <small class="form-text text-muted">{{ __('Şoföre vereceğiniz net şifreyi yazın. Kaydedildikten sonra ekrana yansır.') }}</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('İptal') }}</button>
                                            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> {{ __('Şifreyi Kaydet') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i> {{ __('Aktivite Geçmişi') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($driver->activities->count() > 0)
                            <div class="timeline">
                                @foreach($driver->activities as $activity)
                                    <div class="time-label">
                                        <span class="bg-blue">{{ $activity->recorded_at->format('d.m.Y') }}</span>
                                    </div>
                                    <div>
                                        <i class="{{ $activity->icon }}"></i>
                                        <div class="timeline-item">
                                            <span class="time">
                                                <i class="fas fa-clock"></i> {{ $activity->recorded_at->format('H:i') }}
                                            </span>
                                            <h3 class="timeline-header">
                                                {{ $activity->activity_type_label }}
                                            </h3>
                                            <div class="timeline-body">
                                                {{ $activity->description }}
                                                @if($activity->formatted_metadata)
                                                    <br><small class="text-muted">{{ $activity->formatted_metadata }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div>
                                    <i class="fas fa-clock bg-gray"></i>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <p class="text-muted">{{ __('Henüz aktivite kaydı bulunmuyor.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- İstatistikler -->
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('İstatistikler') }}</h3>
                    </div>
                    <div class="card-body">
                        @if($driver->vehicle)
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-car"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Atanmış Araç') }}</span>
                                <span class="info-box-number">{{ $driver->vehicle->plate_number }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-map-marker-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Konum Kayıtları') }}</span>
                                <span class="info-box-number">{{ $driver->vehicle->locations()->count() }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-ticket-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Toplam Bilet') }}</span>
                                <span class="info-box-number">{{ $driver->vehicle->tickets()->count() }}</span>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ __('Bu şoföre henüz araç atanmamış.') }}
                        </div>
                        @endif

                        <div class="info-box">
                            <span class="info-box-icon bg-primary"><i class="fas fa-history"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ __('Toplam Aktivite') }}</span>
                                <span class="info-box-number">{{ $driver->activities()->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gün Bazında Atamalar + Rota Planı -->
                <div class="ad-card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                        <h3 class="card-title mb-0"><i class="fas fa-route text-info"></i> {{ __('Gün Bazında Atanan Biletler & Rota') }}</h3>
                        <small class="text-muted">{{ __('Bir bileti başlangıç olarak işaretle → sıra trafiğe göre otomatik hesaplanır') }}</small>
                    </div>
                    <div class="card-body p-0">
                        @if(!empty($ticketsByDay))
                            {{-- Gün sekmeleri --}}
                            <div id="driver-days-nav" class="driver-days-nav px-3 pt-3 pb-2 border-bottom">
                                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                                    <small class="driver-days-label mr-1"><i class="far fa-calendar-alt"></i> {{ __('Günler:') }}</small>
                                    @foreach($ticketsByDay as $day)
                                        <button type="button" class="day-tab @if($day['has_route']) has-route @endif" data-date="{{ $day['date'] }}">
                                            {{ \Carbon\Carbon::parse($day['date'])->format('d.m.Y') }}
                                            @if($day['has_route']) <i class="fas fa-route ml-1" style="font-size:10px;"></i> @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @foreach($ticketsByDay as $day)
                                @php $hasStart = collect($day['tickets'])->contains('is_route_start', true); @endphp
                                <div class="day-block" data-date="{{ $day['date'] }}">
                                    <div class="day-header d-flex justify-content-between align-items-center flex-wrap p-3 border-bottom" style="gap:8px;">
                                        <div>
                                            <strong>{{ \Carbon\Carbon::parse($day['date'])->locale(app()->getLocale())->translatedFormat('d F Y, l') }}</strong>
                                            <span class="badge badge-secondary ml-2">{{ __(':count bilet', ['count' => count($day['tickets'])]) }}</span>
                                            @if($driver->vehicle)
                                                <span class="badge badge-{{ $day['passenger_count'] >= $driver->vehicle->capacity ? 'danger' : 'success' }} ml-1">
                                                    {{ __(':current/:total yolcu', ['current' => $day['passenger_count'], 'total' => $driver->vehicle->capacity]) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap" style="gap:6px;">
                                            <form action="{{ route('admin.routes.recalculate', $driver) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $day['date'] }}">
                                                <button type="submit" class="btn btn-sm btn-primary" @disabled(!$hasStart)>
                                                    <i class="fas fa-sync-alt"></i> {{ __('Trafiğe Göre Yenile') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.routes.clear', $driver) }}" method="POST" class="d-inline" onsubmit="return confirm({!! json_encode(__('Bu günün rota planı silinsin mi?'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!});">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $day['date'] }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" @disabled(!$day['has_route'])>
                                                    <i class="fas fa-eraser"></i> {{ __('Rotayı Temizle') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Bilet listesi --}}
                                    <div class="rt-list" data-day="{{ $day['date'] }}">
                                        @foreach($day['tickets'] as $t)
                                            @php
                                                $loc = $t->location;
                                                $hasLoc = $loc && $loc->latitude && $loc->longitude;
                                                $isStart = (bool) $t->is_route_start;
                                                $orderNum = $t->route_order;
                                            @endphp
                                            <div class="rt-item @if($isStart) is-start @endif"
                                                data-ticket-id="{{ $t->id }}"
                                                @if($hasLoc)
                                                    data-lat="{{ $loc->latitude }}"
                                                    data-lng="{{ $loc->longitude }}"
                                                @endif>
                                                <div class="d-flex" style="gap:10px;align-items:flex-start;">
                                                    <div class="rt-num @if($isStart) is-start @elseif(!$orderNum) is-unset @endif">
                                                        @if($isStart) S @elseif($orderNum) {{ $orderNum }} @else ? @endif
                                                    </div>
                                                    <div style="flex:1; min-width:0;">
                                                        <div class="d-flex justify-content-between align-items-start" style="gap:6px;">
                                                            <div style="min-width:0;">
                                                                <strong class="rt-name">{{ $t->customer_name ?: __('Müşteri') }}</strong>
                                                                <div class="rt-meta">
                                                                    <span class="text-muted">#{{ $t->voucher_no ?: $t->tracking_no }}</span>
                                                                    @if($t->pickup_time)
                                                                        <span class="badge badge-info ml-1"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($t->pickup_time)->format('H:i') }}</span>
                                                                    @endif
                                                                    <span class="badge badge-light ml-1">{{ __(':count kişi', ['count' => $t->passengers->sum('quantity')]) }}</span>
                                                                </div>
                                                                <div class="rt-meta mt-1">
                                                                    <i class="fas fa-route text-muted"></i> {{ $t->tour->name ?? $t->tour_name ?? '—' }}
                                                                </div>
                                                                <div class="rt-meta">
                                                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                                                    {{ $t->pickup_location ?: __('Konum yazılmamış') }}
                                                                </div>
                                                                <div class="rt-meta">
                                                                    <i class="fas fa-money-bill-wave text-success"></i>
                                                                    <strong>{{ number_format((float) $t->total_price, 2, ',', '.') }} {{ $t->currency ?: 'TRY' }}</strong>
                                                                    @if((float) $t->rest > 0)
                                                                        <span class="text-muted">· Rest:</span>
                                                                        <span class="text-warning"><strong>{{ number_format((float) $t->rest, 2, ',', '.') }}</strong></span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="text-right" style="flex-shrink:0;">
                                                                @if($t->customer_phone)
                                                                    <a href="tel:{{ $t->customer_phone }}" class="btn btn-xs btn-outline-success" title="{{ __('Telefonla Ara') }}"><i class="fas fa-phone"></i></a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="mt-2 d-flex flex-wrap" style="gap:4px;">
                                                            @if($isStart)
                                                                <span class="badge badge-success"><i class="fas fa-flag"></i> {{ __('Başlangıç noktası') }}</span>
                                                            @elseif($hasLoc)
                                                                <form action="{{ route('admin.routes.set-start', [$driver, $t]) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="date" value="{{ $day['date'] }}">
                                                                    <button type="submit" class="btn btn-xs btn-outline-success">
                                                                        <i class="fas fa-flag"></i> {{ __('Başlangıç Yap') }}
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> {{ __('Konum yok — başlangıç yapılamaz') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Harita kartı (listenin altında ayrı kutu) --}}
                                @if($mapboxToken)
                                    <div class="card rt-map-card day-block" data-date="{{ $day['date'] }}">
                                        <div class="card-header py-2">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-map-marked-alt text-info"></i>
                                                {{ __(':date — Harita ve Rota', ['date' => \Carbon\Carbon::parse($day['date'])->format('d.m.Y')]) }}
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="rt-map" data-day="{{ $day['date'] }}"></div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="p-3 text-muted">{{ __('Gün bazında atanmış bilet bulunmuyor.') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Hızlı İşlemler -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Hızlı İşlemler') }}</h3>
                    </div>
                    <div class="card-body">
                        @if($driver->vehicle)
                        <a href="{{ route('admin.vehicles.show', $driver->vehicle) }}" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-car"></i> {{ __('Araç Detayları') }}
                        </a>
                        @else
                        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-plus"></i> {{ __('Araç Ata') }}
                        </a>
                        @endif

                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> {{ __('Düzenle') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.css" rel="stylesheet">
<style>
/* ===== Gün Bazında Biletler - Tema Uyumlu Stiller ===== */
.driver-days-nav { background: #f8f9fa; }
.driver-days-label { color: #6c757d; }
.day-tab { border: 1px solid #28a745; background: #fff; color: #333; padding: 4px 12px; border-radius: 4px; font-size: 13px; cursor: pointer; transition: all 0.2s; font-weight: 400; }
.day-tab:hover { background: #e8f5e9; }
.day-tab.active { background: #d4edda; color: #155724; border-color: #28a745; font-weight: 600; }
.day-tab.has-route { border-color: #0d6efd; color: #0d6efd; }
.day-tab.has-route.active { background: #cfe2ff; color: #084298; border-color: #0d6efd; }

/* ===== Rota planlayıcı (tek kolon: liste üstte, harita altta ayrı kart) ===== */
.day-block { display: none; }
.day-block.is-active { display: block; }
.rt-list { padding: 12px; max-height: 520px; overflow-y: auto; }
.rt-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; background: #fff; transition: border-color 0.15s, box-shadow 0.15s; }
.rt-item:hover { border-color: #0d6efd; }
.rt-item.is-start { border-color: #198754; background: #f0fdf4; }
.rt-num { width: 32px; height: 32px; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
.rt-num.is-start { background: #198754; }
.rt-num.is-unset { background: #adb5bd; }
.rt-name { font-size: 14px; }
.rt-meta { font-size: 12px; color: #6c757d; line-height: 1.5; word-break: break-word; }

/* Ayrı harita kartı */
.rt-map-card { margin-top: 10px; }
.rt-map { width: 100%; height: 420px; min-height: 380px; }
@media (max-width: 575.98px) { .rt-map { height: 320px; } }

.rt-pin { width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,.3); border: 2px solid #fff; cursor: pointer; }
.rt-pin span { transform: rotate(45deg); color: #fff; font-weight: 700; font-size: 12px; }
.rt-pin.start { background: #198754; }
.rt-pin.stop { background: #0d6efd; }
.rt-pin.unset { background: #6c757d; }
.btn-xs { padding: 0.15rem 0.4rem; font-size: 0.72rem; line-height: 1.4; border-radius: 3px; }

html.dark-mode .rt-item { background: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
html.dark-mode .rt-item.is-start { background: #064e3b !important; border-color: #10b981 !important; }
html.dark-mode .rt-meta { color: #94a3b8 !important; }
html.dark-mode .rt-map-card { background: #1e293b !important; border-color: #334155 !important; }
html.dark-mode .rt-map-card .card-header { background: #0f172a !important; border-color: #334155 !important; color: #e2e8f0 !important; }
html.dark-mode .day-header { background: #0f172a !important; border-color: #334155 !important; }

/* Mapbox popup karanlık modda da okunsun */
html.dark-mode .mapboxgl-popup-content {
    background: #1e293b !important;
    color: #e2e8f0 !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5) !important;
}
html.dark-mode .mapboxgl-popup-content strong { color: #f1f5f9 !important; }
html.dark-mode .mapboxgl-popup-anchor-top .mapboxgl-popup-tip,
html.dark-mode .mapboxgl-popup-anchor-top-left .mapboxgl-popup-tip,
html.dark-mode .mapboxgl-popup-anchor-top-right .mapboxgl-popup-tip { border-bottom-color: #1e293b !important; }
html.dark-mode .mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip,
html.dark-mode .mapboxgl-popup-anchor-bottom-left .mapboxgl-popup-tip,
html.dark-mode .mapboxgl-popup-anchor-bottom-right .mapboxgl-popup-tip { border-top-color: #1e293b !important; }
html.dark-mode .mapboxgl-popup-anchor-left .mapboxgl-popup-tip { border-right-color: #1e293b !important; }
html.dark-mode .mapboxgl-popup-anchor-right .mapboxgl-popup-tip { border-left-color: #1e293b !important; }
html.dark-mode .mapboxgl-popup-close-button { color: #cbd5e1 !important; font-size: 18px; }
html.dark-mode .mapboxgl-popup-close-button:hover { color: #f1f5f9 !important; background: transparent !important; }

/* Dark Mode */
html.dark-mode .driver-days-nav {
    background: #1e293b !important;
    border-color: #334155 !important;
}
html.dark-mode .driver-days-label {
    color: #94a3b8 !important;
}
html.dark-mode .day-tab {
    background: #334155 !important;
    color: #e2e8f0 !important;
    border-color: #10b981 !important;
}
html.dark-mode .day-tab:hover {
    background: #475569 !important;
}
html.dark-mode .day-tab.active {
    background: #064e3b !important;
    color: #6ee7b7 !important;
    border-color: #10b981 !important;
}
html.dark-mode .day-block,
html.dark-mode .day-block.list-group-item {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
html.dark-mode .day-block strong {
    color: #e2e8f0 !important;
}
html.dark-mode .day-block .badge-light {
    background: #334155 !important;
    color: #94a3b8 !important;
}
html.dark-mode .driver-ticket-list {
    color: #94a3b8 !important;
}
html.dark-mode .driver-ticket-list span {
    color: #cbd5e1 !important;
}
/* Info boxes dark mode */
html.dark-mode .info-box {
    background: #1e293b !important;
    color: #e2e8f0 !important;
}
html.dark-mode .info-box-content .info-box-text {
    color: #94a3b8 !important;
}
html.dark-mode .info-box-content .info-box-number {
    color: #e2e8f0 !important;
}
/* Login info section dark mode */
html.dark-mode .bg-light {
    background: #334155 !important;
    color: #e2e8f0 !important;
}
html.dark-mode .bg-light h6,
html.dark-mode .bg-light ol {
    color: #e2e8f0 !important;
}
</style>
@endpush

<!-- js -->
@section('js')
@if($mapboxToken)
<script src="https://api.mapbox.com/mapbox-gl-js/v3.4.0/mapbox-gl.js"></script>
@endif
<script>
const driverShowI18n = {!! json_encode([
    'copied' => __('Kopyalandı!'),
    'copiedText' => __('Metin panoya kopyalandı.'),
    'error' => __('Hata!'),
    'copyFailed' => __('Kopyalama başarısız oldu.'),
    'copyUnsupported' => __('Kopyalama desteklenmiyor.'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
document.addEventListener('DOMContentLoaded', function() {
    @if($mapboxToken)
    try { mapboxgl.accessToken = {!! json_encode($mapboxToken, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}; } catch(e){}
    var dayMaps = {}; // date → mapboxgl.Map
    @endif

    try {
        const tabs = document.querySelectorAll('#driver-days-nav .day-tab');
        const blocks = document.querySelectorAll('.day-block');
        if (tabs.length === 0) return;

        function selectDate(dateStr) {
            tabs.forEach(btn => btn.classList.toggle('active', btn.getAttribute('data-date') === dateStr));
            blocks.forEach(li => li.classList.toggle('is-active', li.getAttribute('data-date') === dateStr));
            @if($mapboxToken)
            initDayMap(dateStr);
            @endif
        }

        tabs.forEach(btn => {
            btn.addEventListener('click', function() { selectDate(this.getAttribute('data-date')); });
        });

        const today = new Date();
        const todayKey = today.getFullYear() + '-'
            + String(today.getMonth() + 1).padStart(2, '0') + '-'
            + String(today.getDate()).padStart(2, '0');
        let defaultDate = null;
        tabs.forEach(btn => { if (btn.getAttribute('data-date') === todayKey) defaultDate = todayKey; });
        if (!defaultDate) defaultDate = tabs[0].getAttribute('data-date');
        selectDate(defaultDate);
    } catch (e) { console.warn('driver days nav init error', e); }

    @if($mapboxToken)
    function initDayMap(dateStr) {
        if (dayMaps[dateStr]) {
            // Resize sonra fitBounds tekrar
            setTimeout(function(){ try { dayMaps[dateStr].resize(); } catch(e){} }, 100);
            return;
        }
        var mapEl = document.querySelector('.rt-map[data-day="' + dateStr + '"]');
        if (!mapEl) return;
        var listEl = document.querySelector('.rt-list[data-day="' + dateStr + '"]');
        if (!listEl) return;

        var stops = [];
        listEl.querySelectorAll('.rt-item[data-lat]').forEach(function (el) {
            var lat = parseFloat(el.dataset.lat);
            var lng = parseFloat(el.dataset.lng);
            if (isNaN(lat) || isNaN(lng)) return;
            var numEl = el.querySelector('.rt-num');
            var label = numEl ? numEl.textContent.trim() : '';
            var isStart = el.classList.contains('is-start');
            var nameEl = el.querySelector('.rt-name');
            var name = nameEl ? nameEl.textContent.trim() : '';
            var orderNum = parseInt(label, 10);
            stops.push({ lat: lat, lng: lng, label: label || '?', name: name, isStart: isStart, order: isNaN(orderNum) ? null : orderNum });
        });

        var map = new mapboxgl.Map({
            container: mapEl,
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [28.27, 36.85],
            zoom: 11,
            language: {!! json_encode(app()->getLocale(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
        });
        map.addControl(new mapboxgl.NavigationControl(), 'top-right');
        dayMaps[dateStr] = map;

        if (!stops.length) return;

        var bounds = new mapboxgl.LngLatBounds();
        stops.forEach(function (s) {
            var pin = document.createElement('div');
            pin.className = 'rt-pin ' + (s.isStart ? 'start' : (s.order === null ? 'unset' : 'stop'));
            pin.innerHTML = '<span>' + s.label + '</span>';
            var popup = new mapboxgl.Popup({ offset: 25 }).setHTML('<strong>' + s.name + '</strong>');
            new mapboxgl.Marker(pin).setLngLat([s.lng, s.lat]).setPopup(popup).addTo(map);
            bounds.extend([s.lng, s.lat]);
        });

        map.on('load', function () {
            try { map.fitBounds(bounds, { padding: 50, maxZoom: 14 }); } catch(e){}
            drawRoute(map, stops);
        });
    }

    function drawRoute(map, stops) {
        var ordered = stops.filter(function (s) { return s.isStart || s.order !== null; });
        ordered.sort(function (a, b) {
            return (a.isStart ? 0 : a.order) - (b.isStart ? 0 : b.order);
        });
        if (ordered.length < 2) return;
        var coords = ordered.map(function (s) { return s.lng + ',' + s.lat; }).join(';');
        var url = 'https://api.mapbox.com/directions/v5/mapbox/driving-traffic/' + coords
                + '?geometries=geojson&overview=full&access_token=' + encodeURIComponent(mapboxgl.accessToken);
        fetch(url).then(function (r) { return r.json(); }).then(function (data) {
            if (!data.routes || !data.routes[0]) return;
            if (map.getLayer('rt-line')) map.removeLayer('rt-line');
            if (map.getSource('rt-route')) map.removeSource('rt-route');
            map.addSource('rt-route', { type: 'geojson', data: { type: 'Feature', geometry: data.routes[0].geometry } });
            map.addLayer({
                id: 'rt-line', type: 'line', source: 'rt-route',
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#0d6efd', 'line-width': 5, 'line-opacity': 0.85 },
            });
        }).catch(function () {});
    }
    @endif

    // Şifre göster/gizle
    var pwdToggle = document.getElementById('pwd-toggle');
    var pwdText = document.getElementById('pwd-text');
    if (pwdToggle && pwdText) {
        pwdToggle.addEventListener('click', function () {
            var shown = pwdText.dataset.shown === '1';
            if (shown) {
                pwdText.textContent = '•'.repeat(Math.max(pwdText.dataset.real.length, 6));
                pwdText.dataset.shown = '0';
                pwdToggle.innerHTML = '<i class="fas fa-eye"></i>';
            } else {
                pwdText.textContent = pwdText.dataset.real;
                pwdText.dataset.shown = '1';
                pwdToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
            }
        });
    }
});
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // Modern async clipboard API
        navigator.clipboard.writeText(text).then(function() {
            // Success feedback
            Swal.fire({
                icon: 'success',
                title: driverShowI18n.copied,
                text: driverShowI18n.copiedText,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }).catch(function(err) {
            console.error('Kopyalama hatası: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        var successful = document.execCommand('copy');
        if (successful) {
            Swal.fire({
                icon: 'success',
                title: driverShowI18n.copied,
                text: driverShowI18n.copiedText,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: driverShowI18n.error,
                text: driverShowI18n.copyFailed,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    } catch (err) {
        console.error('Fallback kopyalama hatası: ', err);
        Swal.fire({
            icon: 'error',
            title: driverShowI18n.error,
            text: driverShowI18n.copyUnsupported,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
    
    document.body.removeChild(textArea);
}
</script>
@stop 
<!-- end of the code -->