@extends('layouts.admin')

@section('title', __('Bilet Yönetimi'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Modern Kontrol Paneli -->
                <div class="tickets-control-panel ad-page-header admin-list-toolbar mb-3">
                    <div class="control-left">
                        <h4 class="control-title"><i class="fas fa-ticket-alt"></i> {{ __('Bilet Yönetimi') }}</h4>
                        <p class="control-subtitle">{{ __('Toplam :count bilet', ['count' => $tickets->total()]) }}</p>
                    </div>
                    <div class="control-center">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="ticket-search" class="search-input" placeholder="{{ __('Bilet ara...') }}">
                            <button type="button" id="ticket-search-clear" class="search-clear" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="ticket-search-info" class="search-info" style="display: none;"></div>
                    </div>
                    <div class="control-right">
                        <div class="control-item">
                            <div class="size-selector" id="tickets-page-size">
                                <button type="button" class="size-btn {{ (string)request('per_page', $perPage ?? 10)==='10' ? 'active' : '' }}" data-size="10">10</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='25' ? 'active' : '' }}" data-size="25">25</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='50' ? 'active' : '' }}" data-size="50">50</button>
                            </div>
                        </div>
                        <div class="control-item">
                            <div style="display:flex;gap:3px;">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.tickets.export.excel', request()->query()) }}" title="Excel">
                                    <i class="fas fa-file-excel text-success"></i>
                                </a>
                                <a class="btn btn-sm btn-light" href="{{ route('admin.tickets.export.pdf', request()->query()) }}" title="PDF">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </a>
                            </div>
                        </div>
                        <div class="control-item">
                            @php $pendingCount = \App\Models\TicketRequest::forOwner(auth()->id())->pending()->count(); @endphp
                            @if($pendingCount > 0)
                            <a href="{{ route('admin.ticket-requests.index') }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-inbox"></i> {{ $pendingCount }}
                            </a>
                            @endif
                            <a href="{{ route('admin.tickets.create') }}" class="btn btn-sm btn-light">
                                <i class="fas fa-plus"></i> {{ __('Yeni Bilet') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="ad-card">
                    <div class="card-body p-0">
                        <!-- Modern Filtre Bölümü -->
                        <div class="modern-filters-wrapper">
                            <button type="button" class="filter-toggle-btn" id="filterToggle">
                                <i class="fas fa-sliders-h mr-2"></i>
                                <span>{{ __('Filtreler') }}</span>
                                @php
                                    $activeFilters = 0;
                                    if(request('filter_tour_id')) $activeFilters++;
                                    if(request('filter_from')) $activeFilters++;
                                    if(request('filter_to')) $activeFilters++;
                                    if(request('filter_has_vehicle')) $activeFilters++;
                                    if(request('filter_nationality')) $activeFilters++;
                                    if(request('filter_is_active')) $activeFilters++;
                                    $fc = request('filter_countries');
                                    if(is_array($fc) && count($fc)>0) $activeFilters++;
                                    if(request('filter_country')) $activeFilters++;
                                    $fct = request('filter_cities');
                                    if(is_array($fct) && count($fct)>0) $activeFilters++;
                                    if(request('filter_city')) $activeFilters++;
                                @endphp
                                @if($activeFilters > 0)
                                    <span class="filter-badge">{{ $activeFilters }}</span>
                                @endif
                                <i class="fas fa-chevron-down ml-auto toggle-icon"></i>
                            </button>
                            
                            <div class="filters-content" id="filtersContent">
                                <form method="GET" action="{{ route('admin.tickets.index') }}" class="filter-form">
                                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">
                                    
                                    <div class="filters-grid">
                                        <div class="filter-card">
                                            <div class="filter-icon bg-secondary">
                                                <i class="fas fa-flag"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Ülke') }}</label>
                                                <select class="filter-select select2" name="filter_country" data-placeholder="{{ __('Ülke seçin') }}" style="width:100%">
                                                    <option value="">{{ __('Tümü') }}</option>
                                                    @isset($countries)
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country }}" 
                                                                @if((is_array(request('filter_countries')) && in_array($country, request('filter_countries'))) || request('filter_country')===$country) selected @endif>
                                                                {{ $country }}
                                                            </option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                            </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-secondary">
                                                <i class="fas fa-city"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Şehir') }}</label>
                                                <select class="filter-select select2" name="filter_city" data-placeholder="{{ __('Şehir seçin') }}" style="width:100%">
                                                    <option value="">{{ __('Tümü') }}</option>
                                                    @isset($cities)
                                                        @foreach($cities as $city)
                                                            <option value="{{ $city }}" 
                                                                @if((is_array(request('filter_cities')) && in_array($city, request('filter_cities'))) || request('filter_city')===$city) selected @endif>
                                                                {{ $city }}
                                                            </option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                            </div>
                                        </div>
                                        <div class="filter-card">
                                            <div class="filter-icon bg-primary">
                                                <i class="fas fa-route"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Tur Seçimi') }}</label>
                                                <select class="filter-select" name="filter_tour_id">
                                                    <option value="">{{ __('Tüm Turlar') }}</option>
                                            @isset($tours)
                                                @foreach($tours as $tour)
                                                    <option value="{{ $tour->id }}" {{ (string)request('filter_tour_id') === (string)$tour->id ? 'selected' : '' }}>{{ $tour->name }}</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </div>
                                        </div>

                                        <div class="filter-card filter-card-wide">
                                            <div class="filter-icon bg-info">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Tarih Aralığı') }}</label>
                                                <div class="date-range-group">
                                                    <input type="date" class="filter-input" name="filter_from" value="{{ request('filter_from') }}" placeholder="{{ __('Başlangıç') }}">
                                                    <span class="date-divider"><i class="fas fa-long-arrow-alt-right"></i></span>
                                                    <input type="date" class="filter-input" name="filter_to" value="{{ request('filter_to') }}" placeholder="{{ __('Bitiş') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-success">
                                                <i class="fas fa-bus"></i>
                                    </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Araç Durumu') }}</label>
                                                <select class="filter-select" name="filter_has_vehicle">
                                            <option value="">{{ __('Tümü') }}</option>
                                            <option value="1" {{ request('filter_has_vehicle')==='1' ? 'selected' : '' }}>{{ __('Araçlı') }}</option>
                                            <option value="0" {{ request('filter_has_vehicle')==='0' ? 'selected' : '' }}>{{ __('Araçsız') }}</option>
                                        </select>
                                    </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-warning">
                                                <i class="fas fa-globe"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Milliyet') }}</label>
                                                <select class="filter-select" name="filter_nationality">
                                                    <option value="">{{ __('Tüm Milliyetler') }}</option>
                                            @isset($nationalities)
                                                @foreach($nationalities as $nat)
                                                    <option value="{{ $nat }}" {{ request('filter_nationality')===$nat ? 'selected' : '' }}>{{ $nat }}</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-danger">
                                                <i class="fas fa-power-off"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Durum') }}</label>
                                                <select class="filter-select" name="filter_is_active">
                                            <option value="">{{ __('Tümü') }}</option>
                                            <option value="1" {{ request('filter_is_active')==='1' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                            <option value="0" {{ request('filter_is_active')==='0' ? 'selected' : '' }}>{{ __('Pasif') }}</option>
                                        </select>
                                    </div>
                                </div>
                                    </div>

                                    <div class="filter-actions">
                                        <a href="{{ route('admin.tickets.index', ['per_page'=>request('per_page', $perPage ?? 10)]) }}" class="filter-btn filter-btn-clear">
                                            <i class="fas fa-times-circle mr-1"></i> {{ __('Temizle') }}
                                        </a>
                                        <button type="submit" class="filter-btn filter-btn-apply">
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('Uygula') }}
                                        </button>
                                </div>
                            </form>
                            </div>
                        </div>

                        {{-- Onay Bekleyen Bilet İstekleri --}}
                        @if(isset($pendingRequests) && $pendingRequests->count() > 0)
                        <div class="p-3">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-clock mr-2"></i>
                                <strong>{{ __(':count adet onay bekleyen bilet isteği var', ['count' => $pendingRequests->count()]) }}</strong>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-warning">
                                        <tr>
                                            <th>{{ __('Takip No') }}</th>
                                            <th>{{ __('Müşteri') }}</th>
                                            <th>{{ __('Tur Bilgileri') }}</th>
                                            <th>{{ __('Talep Eden') }}</th>
                                            <th>{{ __('Fiyat') }}</th>
                                            <th>{{ __('Durum') }}</th>
                                            <th>{{ __('İşlemler') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingRequests as $request)
                                            <tr class="table-warning">
                                                <td>
                                                    <strong>{{ $request->voucher_no ?: '-' }}</strong>
                                                    <br><small class="text-muted">{{ __('İstek') }} #{{ $request->id }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $request->customer_name }}</strong>
                                                    <br><small class="text-muted">{{ $request->customer_phone }}</small>
                                                    @if($request->customer_nationality)
                                                        <br><span class="badge badge-primary">{{ $request->nationality_name }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $request->tour->name ?? '-' }}</strong>
                                                    <br><small class="text-muted">
                                                        {{ $request->tour_date ? $request->tour_date->format('d.m.Y') : '-' }}
                                                    </small>
                                                    @if($request->tour)
                                                        <br><small class="text-muted">{{ $request->tour->country }}, {{ $request->tour->city }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $request->requester->name ?? '-' }}</strong>
                                                    @if($request->requester && $request->requester->agency)
                                                        <br><small class="text-muted">{{ $request->requester->agency->name }}</small>
                                                    @endif
                                                    <br><small class="text-muted">{{ $request->created_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $curr = strtoupper($request->currency ?? 'TRY');
                                                        $currClass = match($curr) {
                                                            'TRY' => 'badge-success',
                                                            'EUR' => 'badge-primary',
                                                            'USD' => 'badge-danger',
                                                            'GBP' => 'badge-gbp',
                                                            'RUB' => 'badge-rub',
                                                            default => 'badge-secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $currClass }}">{{ number_format($request->total_price, 2) }} {{ $curr }}</span>
                                                    <br><small class="text-muted">
                                                        {{ $request->adult_count }} {{ __('Yetişkin') }},
                                                        {{ $request->child_count }} {{ __('Çocuk') }},
                                                        {{ $request->infant_count }} {{ __('Bebek') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> {{ __('Beklemede') }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('admin.ticket-requests.show', $request) }}" 
                                                           class="btn btn-sm btn-info" title="{{ __('Görüntüle') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form action="{{ route('admin.ticket-requests.approve', $request) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" title="{{ __('Onayla') }}" onclick="return confirm('{{ __('Bu bilet isteğini onaylamak istediğinize emin misiniz?') }}')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.ticket-requests.reject', $request) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ __('Reddet') }}" onclick="return confirm('{{ __('Bu bilet isteğini reddetmek istediğinize emin misiniz?') }}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <hr class="my-0">
                        @endif

                        <div class="p-3">
                        <div class="table-responsive">
                                <table class="ad-table table table-bordered table-striped ticket-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Takip No') }}</th>
                                        <th>{{ __('Müşteri') }}</th>
                                        <th>{{ __('Tur') }}</th>
                                        <th>{{ __('Tarih') }}</th>
                                        <th>{{ __('Yolcu') }}</th>
                                        <th>{{ __('Toplam') }}</th>
                                        <th>{{ __('Durum') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tickets as $ticket)
                                        <tr>
                                            <td>
                                                <div style="font-weight:600;font-family:monospace">{{ $ticket->tracking_no }}</div>
                                                @if($ticket->voucher_no)
                                                    <div class="text-muted" style="font-size:11px">{{ $ticket->voucher_no }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="font-weight:500">{{ $ticket->customer_name }}</div>
                                                <div class="text-muted" style="font-size:12px">{{ $ticket->customer_phone }}</div>
                                            </td>
                                            <td>
                                                <div style="font-weight:500">{{ $ticket->tour_name }}</div>
                                                @if($ticket->tour_country)
                                                    <div class="text-muted" style="font-size:12px">{{ $ticket->tour_country }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($ticket->tour_date)
                                                    <div>{{ $ticket->tour_date->format('d.m.Y') }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary" style="font-size:12px;padding:5px 10px;border-radius:12px;">{{ __(':count kişi', ['count' => $ticket->total_passengers ?? 0]) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $baseTotal = ((int) ($ticket->adult_count ?? 0) * (float) ($ticket->adult_price ?? 0))
                                                        + ((int) ($ticket->child_count ?? 0) * (float) ($ticket->child_price ?? 0))
                                                        + ((int) ($ticket->infant_count ?? 0) * (float) ($ticket->infant_price ?? 0));
                                                    $baseCurrency = strtoupper($ticket->base_currency ?? ($ticket->currency ?? 'TRY'));
                                                    // Eski kayıtlarda adult/child/infant alanları boş olabildiği için fallback uygula.
                                                    if ($baseTotal <= 0) {
                                                        $passengerTotal = (float) ($ticket->passengers?->sum('total_price') ?? 0);
                                                        if ($passengerTotal > 0) {
                                                            $baseTotal = $passengerTotal;
                                                        } else {
                                                            $baseTotal = (float) ($ticket->total_price ?? 0);
                                                        }
                                                    }
                                                @endphp
                                                <span style="font-weight:600">{{ number_format($baseTotal, 2) }} {{ $baseCurrency }}</span>
                                            </td>
                                            <td>
                                                @if($ticket->is_active)
                                                    <span class="badge badge-success" style="font-size:12px;padding:5px 12px;border-radius:12px;">{{ __('Aktif') }}</span>
                                                @else
                                                    <span class="badge badge-danger" style="font-size:12px;padding:5px 12px;border-radius:12px;">{{ __('Pasif') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                                    <a href="{{ route('admin.tickets.show', $ticket) }}"
                                                       class="ticket-action-btn ticket-action-info" title="{{ __('Görüntüle') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.tickets.edit', $ticket) }}"
                                                       class="ticket-action-btn ticket-action-warning" title="{{ __('Düzenle') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.tickets.destroy', $ticket) }}"
                                                          method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="ticket-action-btn ticket-action-danger"
                                                                onclick="return confirm('{{ __('Bu bileti silmek istediğinize emin misiniz?') }}')"
                                                                title="{{ __('Sil') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <!-- made by @hllgkx.0 -->
                                        <tr>
                                            <td colspan="8" class="text-center">{{ __('Henüz bilet eklenmemiş.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
                
                
            </div>
        </div>
    </div>

    
@stop
<!-- bilet yönetimi css-->
@push('css')
    <style>
        /* Pagination görsel hizalama düzeltmeleri */
        .pagination { margin-bottom: 0; }
        .page-item .page-link { padding: .375rem .6rem; }
        /* Header sağ taraftaki grup */
        #tickets-page-size .btn.active { background-color: #17a2b8; color: #fff; }
        .card-header .pagination { margin: 0; }
        .card-header .header-pagination { display: flex; align-items: center; }
        .card-header .header-pagination nav { display: inline-flex; }
        .card-header .header-pagination .pagination { justify-content: flex-end; }

        /* Round action buttons */
        .ticket-action-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.22);
            box-shadow: 0 0 0 1px rgba(255,255,255,.08) inset;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 13px;
            text-decoration: none;
        }
        .ticket-action-btn:hover {
            transform: scale(1.1);
            text-decoration: none;
        }
        .ticket-action-info {
            background: rgba(6,182,212,.16);
            border-color: rgba(34,211,238,.75);
            color: #67e8f9;
        }
        .ticket-action-info:hover {
            background: #138496;
            color: #fff;
        }
        .ticket-action-warning {
            background: rgba(245,158,11,.16);
            border-color: rgba(251,191,36,.78);
            color: #fcd34d;
        }
        .ticket-action-warning:hover {
            background: #e0a800;
            color: #212529;
        }
        .ticket-action-danger {
            background: rgba(239,68,68,.14);
            border-color: rgba(248,113,113,.78);
            color: #fca5a5;
        }
        .ticket-action-danger:hover {
            background: #c82333;
            color: #fff;
        }

        /* Ticket table clean styling */
        .ticket-table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 700;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }
        .ticket-table td {
            vertical-align: middle;
        }

        /* Dark mode */
        html.dark-mode .ticket-table { background: #1e293b !important; color: #e2e8f0 !important; }
        html.dark-mode .ticket-table thead th { background: #0f172a !important; color: #e2e8f0 !important; border-color: #334155 !important; }
        html.dark-mode .ticket-table tbody tr { background: #1e293b !important; }
        html.dark-mode .ticket-table tbody tr:hover { background: #334155 !important; }
        html.dark-mode .ticket-table td { border-color: #334155 !important; color: #e2e8f0 !important; }
        html.dark-mode .ticket-table .text-muted { color: #94a3b8 !important; }
        html.dark-mode .ticket-table code { background: #0f172a !important; color: #fbbf24 !important; padding: 1px 5px; border-radius: 3px; }
    </style>
@endpush
<!-- bilet yönetimi js-->
@push('css')
<style>
/* Modern Kontrol Paneli */
.tickets-control-panel {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-radius: 8px;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    flex-wrap: wrap;
    gap: 15px;
}

/* Arama Kutusu */
.control-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 14px;
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    pointer-events: none;
}

.search-input {
    width: 280px;
    padding: 10px 40px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 10px;
    background: rgba(255,255,255,0.15);
    color: white;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.search-input::placeholder {
    color: rgba(255,255,255,0.7);
}

.search-input:focus {
    outline: none;
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.5);
    box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
}

.search-clear {
    position: absolute;
    right: 10px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transition: all 0.2s ease;
}

.search-clear:hover {
    background: rgba(255,255,255,0.4);
}

.search-info {
    font-size: 12px;
    color: rgba(255,255,255,0.9);
    background: rgba(0,0,0,0.2);
    padding: 3px 12px;
    border-radius: 12px;
}

.control-left .control-title {
    color: white;
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}

.control-left .control-subtitle {
    color: rgba(255,255,255,0.9);
    margin: 0;
    font-size: 14px;
}

.control-right {
    display: flex;
    gap: 25px;
    align-items: flex-end;
}

.control-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.control-label {
    color: rgba(255,255,255,0.95);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.size-selector {
    display: flex;
    gap: 5px;
    background: rgba(255,255,255,0.2);
    padding: 4px;
    border-radius: 8px;
}

.size-btn {
    background: transparent;
    border: none;
    color: white;
    padding: 6px 14px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
}

.size-btn:hover {
    background: rgba(255,255,255,0.2);
}

.size-btn.active {
    background: white;
    color: #007bff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.custom-pagination .pagination {
    margin: 0;
    background: rgba(255,255,255,0.2);
    padding: 4px;
    border-radius: 8px;
}

.custom-pagination .page-link {
    background: transparent;
    border: none;
    color: white;
    padding: 6px 12px;
    margin: 0 2px;
    border-radius: 6px;
    font-weight: 600;
}

.custom-pagination .page-link:hover {
    background: rgba(255,255,255,0.2);
    color: white;
}

.custom-pagination .page-item.active .page-link {
    background: white;
    color: #007bff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.custom-pagination .page-item.disabled .page-link {
    color: rgba(255,255,255,0.5);
}

/* Modern Filtre Wrapper */
.modern-filters-wrapper {
    border-bottom: 1px solid #e9ecef;
}

.filter-toggle-btn {
    width: 100%;
    background: #f8f9fa;
    border: none;
    padding: 18px 25px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 16px;
    font-weight: 600;
    color: #495057;
}

.filter-toggle-btn:hover {
    background: #e9ecef;
}

.filter-toggle-btn .toggle-icon {
    transition: transform 0.3s ease;
}

.filter-toggle-btn.active .toggle-icon {
    transform: rotate(180deg);
}

.filter-badge {
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    margin-left: 10px;
}

.filters-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
}

.filters-content.active {
    max-height: 800px;
}

.filter-form {
    padding: 25px;
    background: #f8f9fa;
}

/* Filtre Grid */
.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.filter-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.filter-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.filter-card-wide {
    grid-column: span 2;
}

.filter-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.filter-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-label {
    font-size: 12px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}

.filter-select,
.filter-input {
    width: 100%;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.filter-select:focus,
.filter-input:focus {
    outline: none;
    border-color: #007bff;
    background: white;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
}

.date-range-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.date-range-group .filter-input {
    flex: 1;
}

.date-divider {
    color: #6c757d;
    font-size: 18px;
}

/* Filtre Aksiyonlar */
.filter-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 15px;
    border-top: 2px solid #e9ecef;
}

.filter-btn {
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}

.filter-btn-clear {
    background: white;
    color: #6c757d;
    border: 2px solid #dee2e6;
}

.filter-btn-clear:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #6c757d;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-decoration: none;
}

.filter-btn-apply {
    background: #007bff;
    color: white;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.filter-btn-apply:hover {
    background: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* Alt Pagination */
.bottom-pagination-wrapper {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 15px 20px;
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}

.pagination-info strong {
    color: #495057;
    font-weight: 700;
}

/* Responsive */
@media (max-width: 992px) {
    .filter-card-wide {
        grid-column: span 1;
    }
    
    .tickets-control-panel {
        flex-direction: column;
        align-items: stretch;
    }
    
    .control-center {
        order: -1;
        width: 100%;
    }
    
    .search-input {
        width: 100%;
    }
    
    .control-right {
        flex-direction: column;
        align-items: stretch;
    }
    
    .size-selector,
    .custom-pagination .pagination {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-btn {
        width: 100%;
        justify-content: center;
    }
    
    .bottom-pagination-wrapper {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>
@endpush

@push('css')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sayfa boyutu butonları
    const group = document.getElementById('tickets-page-size');
    if (group) {
        group.addEventListener('click', function(e) {
            const btn = e.target.closest('button[data-size]');
            if (!btn) return;
            const size = btn.getAttribute('data-size');
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', size);
            window.location.href = url.toString();
        });
    }

    // Filtre toggle
    const filterToggle = document.getElementById('filterToggle');
    const filtersContent = document.getElementById('filtersContent');
    
    if (filterToggle && filtersContent) {
        filterToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            filtersContent.classList.toggle('active');
        });
        
        // Sayfa ilk açılışta daima kapalı kalsın (aktif filtre olsa bile)
    }
    // Akıllı arama - debounce submit
    const searchInput = document.getElementById('smartSearchInput');
    const searchForm = document.getElementById('smartSearchForm');
    if (searchInput && searchForm) {
        let t = null;
        let lastSubmitted = searchInput.value;
        const MIN_LEN = 3; // en az 3 karakter
        ['keydown','keypress'].forEach(function(ev){
            searchInput.addEventListener(ev, function(e){
                e.stopPropagation();
            });
        });
        ['input','change','keyup'].forEach(function(ev){
            searchInput.addEventListener(ev, function(){
                clearTimeout(t);
                const val = searchInput.value || '';
                const trimmed = val.trim();
                // Arama sırasında filtre panelini kapalı tut
                try {
                    const filterToggle = document.getElementById('filterToggle');
                    const filtersContent = document.getElementById('filtersContent');
                    if (filterToggle) filterToggle.classList.remove('active');
                    if (filtersContent) filtersContent.classList.remove('active');
                } catch(e) {}
                t = setTimeout(function(){
                    // Boşsa (silindi) hemen uygula; değilse en az 3 karakter şartı
                    const shouldSearch = (trimmed.length === 0) || (trimmed.length >= MIN_LEN);
                    if (!shouldSearch) return;
                    if (val === lastSubmitted) return;
                    lastSubmitted = val;
                    searchForm.submit();
                }, 600);
            });
        });
    }
});
</script>
@endpush

@push('js')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const ticketsI18n = {!! json_encode([
        'showing' => __(':shown / :total bilet gösteriliyor'),
        'noResults' => __(':term için sonuç bulunamadı'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    try {
        $('.select2').select2({ 
            theme: 'bootstrap4', 
            width: '100%', 
            placeholder: function(){ return $(this).data('placeholder') || ''; },
            allowClear: true
        });
    } catch(e) {
        // jQuery yoksa sessiz geç
    }

    // Bilet Arama Fonksiyonu
    const ticketSearchInput = document.getElementById('ticket-search');
    const ticketSearchClear = document.getElementById('ticket-search-clear');
    const ticketSearchInfo = document.getElementById('ticket-search-info');
    const ticketTableBody = document.querySelector('table tbody');
    
    if (ticketSearchInput && ticketTableBody) {
        const allRows = Array.from(ticketTableBody.querySelectorAll('tr'));
        const dataRows = allRows.filter(row => !row.querySelector('td[colspan]'));
        const totalRows = dataRows.length;
        
        function filterTicketTable(searchTerm) {
            searchTerm = searchTerm.toLowerCase().trim();
            let visibleCount = 0;
            
            allRows.forEach(row => {
                if (row.querySelector('td[colspan]')) {
                    row.style.display = searchTerm ? 'none' : '';
                    return;
                }
                
                const rowText = row.textContent.toLowerCase();
                
                if (searchTerm === '' || rowText.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (searchTerm) {
                ticketSearchInfo.textContent = ticketsI18n.showing.replace(':shown', visibleCount).replace(':total', totalRows);
                ticketSearchInfo.style.display = 'block';
                ticketSearchClear.style.display = 'flex';
            } else {
                ticketSearchInfo.style.display = 'none';
                ticketSearchClear.style.display = 'none';
            }
            
            // Sonuç yoksa mesaj göster
            const noResultRow = ticketTableBody.querySelector('.no-search-result');
            if (visibleCount === 0 && searchTerm) {
                if (!noResultRow) {
                    const tr = document.createElement('tr');
                    tr.className = 'no-search-result';
                    tr.innerHTML = '<td colspan="8" class="text-center text-muted py-4"><i class="fas fa-search mr-2"></i>' + ticketsI18n.noResults.replace(':term', '"' + searchTerm + '"') + '</td>';
                    ticketTableBody.appendChild(tr);
                }
            } else if (noResultRow) {
                noResultRow.remove();
            }
        }
        
        ticketSearchInput.addEventListener('input', function() {
            filterTicketTable(this.value);
        });
        
        ticketSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
        
        ticketSearchClear.addEventListener('click', function() {
            ticketSearchInput.value = '';
            filterTicketTable('');
            ticketSearchInput.focus();
        });
    }
});
</script>
@endpush
<!-- end of the code-->