@extends('layouts.admin')

@section('title', __('Şoför Yönetimi'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Modern Kontrol Paneli -->
                <div class="drivers-control-panel ad-page-header admin-list-toolbar mb-3">
                    <div class="control-left">
                        <h4 class="control-title"><i class="fas fa-id-card-alt"></i> {{ __('Şoför Yönetimi') }}</h4>
                        <p class="control-subtitle">{{ __('Toplam :count şoför', ['count' => $drivers->total()]) }}</p>
                    </div>
                    <div class="control-center">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="driver-search" class="search-input" placeholder="{{ __('Şoför ara...') }}">
                            <button type="button" id="driver-search-clear" class="search-clear" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="driver-search-info" class="search-info" style="display: none;"></div>
                    </div>
                    <div class="control-right">
                        <div class="control-item">
                            <div class="size-selector" id="drivers-page-size">
                                <button type="button" class="size-btn {{ (string)request('per_page', $perPage ?? 10)==='10' ? 'active' : '' }}" data-size="10">10</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='25' ? 'active' : '' }}" data-size="25">25</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='50' ? 'active' : '' }}" data-size="50">50</button>
                            </div>
                        </div>
                        <div class="control-item">
                            <div style="display:flex;gap:3px;">
                                <a href="{{ route('admin.drivers.export.excel') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light" title="Excel">
                                    <i class="fas fa-file-excel text-success"></i>
                                </a>
                                <a href="{{ route('admin.drivers.export.pdf') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light" title="PDF">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </a>
                            </div>
                        </div>
                        <div class="control-item">
                            <a href="{{ route('admin.drivers.create') }}" class="btn btn-sm btn-light">
                                <i class="fas fa-plus"></i> {{ __('Yeni Şoför') }}
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
                                    if(request('filter_status')) $activeFilters++;
                                    if(request('filter_vehicle')) $activeFilters++;
                                    if(request('q')) $activeFilters++;
                                @endphp
                                @if($activeFilters > 0)
                                    <span class="filter-badge">{{ $activeFilters }}</span>
                                @endif
                                <i class="fas fa-chevron-down ml-auto toggle-icon"></i>
                            </button>
                            <div class="filters-content" id="filtersContent">
                                <form method="GET" action="{{ route('admin.drivers.index') }}" class="filter-form">
                                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">

                                    <div class="filters-grid">
                                        <div class="filter-card">
                                            <div class="filter-icon bg-success">
                                                <i class="fas fa-toggle-on"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Durum') }}</label>
                                                <select class="filter-select" name="filter_status">
                                                    <option value="">{{ __('Tümü') }}</option>
                                                    <option value="active" {{ request('filter_status')==='active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                                    <option value="inactive" {{ request('filter_status')==='inactive' ? 'selected' : '' }}>{{ __('Pasif') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-primary">
                                                <i class="fas fa-car"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Araç Ataması') }}</label>
                                                <select class="filter-select" name="filter_vehicle">
                                                    <option value="">{{ __('Tümü') }}</option>
                                                    <option value="with" {{ request('filter_vehicle')==='with' ? 'selected' : '' }}>{{ __('Araçlı') }}</option>
                                                    <option value="without" {{ request('filter_vehicle')==='without' ? 'selected' : '' }}>{{ __('Araçsız') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-warning">
                                                <i class="fas fa-search"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Ara') }}</label>
                                                <input type="text" class="filter-input" id="driversQuickSearch" name="q" placeholder="{{ __('Şoför Adı') }}" value="{{ request('q') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="filter-actions">
                                        <a href="{{ route('admin.drivers.index', ['per_page'=>request('per_page', $perPage ?? 10)]) }}" class="filter-btn filter-btn-clear">
                                            <i class="fas fa-times-circle mr-1"></i> {{ __('Temizle') }}
                                        </a>
                                        <button type="submit" class="filter-btn filter-btn-apply">
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('Uygula') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                    </div>

                        <div class="p-3">
                        <div class="table-scroll-top" id="drivers-scroll-top"><div></div></div>
                        <div class="table-responsive" id="drivers-table-wrapper">
                            <table class="ad-table table table-bordered table-striped" id="drivers-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ __('Ad Soyad') }}</th>
                                        <th>{{ __('E-posta') }}</th>
                                        <th>{{ __('Telefon') }}</th>
                                        <th>{{ __('Desteklenen Milliyetler') }}</th>
                                        <th>{{ __('Atanmış Araç') }}</th>
                                        <th>{{ __('Maaş') }}</th>
                                        <th>{{ __('Durum') }}</th>
                                        <th>{{ __('Son Giriş') }}</th>
                                        <th>{{ __('İşlemler') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($drivers as $driver)
                                        <tr class="driver-row" data-search-term="{{ Str::lower(trim(($driver->name ?? '') . ' ' . ($driver->email ?? '') . ' ' . ($driver->phone_number ?? '') . ' ' . ($driver->supported_nationalities_names ?? '') . ' ' . ($driver->vehicle->plate_number ?? ''))) }}">
                                            <td>{{ $driver->id }}</td>
                                            <td>
                                                <strong class="driver-name">{{ $driver->name }}</strong>
                                                @if($driver->is_active)
                                                    <span class="badge badge-success ml-1">{{ __('Aktif') }}</span>
                                                @else
                                                    <span class="badge badge-danger ml-1">{{ __('Pasif') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $driver->email }}
                                                @if($driver->plain_password)
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-key"></i> {{ __('Şifre') }}: <code>{{ $driver->plain_password }}</code>
                                                    </small>
                                                @endif
                                            </td>
                                            <td>{{ $driver->phone_number }}</td>
                                            <td>
                                                <small class="text-muted">{{ $driver->supported_nationalities_names }}</small>
                                            </td>
                                            <td>
                                                @if($driver->vehicle)
                                                    <span class="badge badge-info">{{ $driver->vehicle->plate_number }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('Araç Atanmamış') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($driver->salary_amount > 0)
                                                    <strong>{{ number_format((float) $driver->salary_amount, 2, ',', '.') }}</strong>
                                                    <small class="text-muted">{{ $driver->salary_currency ?: 'TRY' }}</small>
                                                    <br><small class="text-muted"><i class="far fa-calendar"></i> {{ __("Ayın :day'i", ['day' => $driver->salary_day ?: 1]) }}</small>
                                                    @if($driver->last_salary_paid_at)
                                                        <br><small class="text-success"><i class="fas fa-check"></i> {{ \Carbon\Carbon::parse($driver->last_salary_paid_at)->format('d.m.Y') }}</small>
                                                    @endif
                                                @else
                                                    <small class="text-muted">—</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($driver->is_active)
                                                    <span class="badge badge-success">{{ __('Aktif') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('Pasif') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($driver->last_login_at)
                                                    {{ $driver->last_login_at->format('d.m.Y H:i') }}
                                                    <br><small class="text-muted">{{ $driver->last_login_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-muted">{{ __('Hiç giriş yapmadı') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.drivers.show', $driver) }}"
                                                       class="btn btn-sm btn-info" title="{{ __('Görüntüle') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.drivers.edit', $driver) }}"
                                                       class="btn btn-sm btn-warning" title="{{ __('Düzenle') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.drivers.destroy', $driver) }}"
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('{{ __('Bu şoförü silmek istediğinize emin misiniz?') }}')"
                                                                title="{{ __('Sil') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">{{ __('Henüz şoför eklenmemiş.') }}</td>
                                        </tr>
                                    @endforelse
                                    @if($drivers->count() > 0)
                                        <tr id="no-drivers-found" style="display: none;">
                                            <td colspan="10" class="text-center">{{ __('Bu isimde şoför yok.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .btn-group .btn { margin-right: 2px; }

        /* Top control panel */
        .drivers-control-panel { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 8px; padding: 20px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 12px rgba(0,123,255,0.2); flex-wrap:wrap; gap:15px; }
        .control-left .control-title { color:#fff; margin:0; font-size:24px; font-weight:700; }
        .control-left .control-subtitle { color:#e2e6ea; margin:0; font-size:12px; }
        .control-center { display:flex; flex-direction:column; align-items:center; gap:6px; }
        .search-box { position:relative; display:flex; align-items:center; }
        .search-icon { position:absolute; left:14px; color:rgba(255,255,255,0.7); font-size:14px; pointer-events:none; }
        .search-input { width:280px; padding:10px 40px; border:2px solid rgba(255,255,255,0.3); border-radius:10px; background:rgba(255,255,255,0.15); color:#fff; font-size:14px; font-weight:500; transition:all 0.3s ease; }
        .search-input::placeholder { color:rgba(255,255,255,0.7); }
        .search-input:focus { outline:none; background:rgba(255,255,255,0.25); border-color:rgba(255,255,255,0.5); box-shadow:0 0 0 3px rgba(255,255,255,0.1); }
        .search-clear { position:absolute; right:10px; background:rgba(255,255,255,0.2); border:none; color:#fff; width:24px; height:24px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:11px; transition:all 0.2s ease; }
        .search-clear:hover { background:rgba(255,255,255,0.4); }
        .search-info { font-size:12px; color:rgba(255,255,255,0.9); background:rgba(0,0,0,0.2); padding:3px 12px; border-radius:12px; }
        .control-right { display:flex; gap:20px; align-items:center; }
        .control-item { display:flex; flex-direction:column; gap:6px; }
        .control-label { color:#fff; font-size:12px; opacity:.9; }
        .size-selector { display:flex; background: rgba(255,255,255,.15); padding:4px; border-radius:8px; }
        .size-btn { background: transparent; border: none; color:#fff; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:600; font-size:12px; }
        .size-btn.active { background:#fff; color:#0056b3; }
        .custom-pagination .pagination { margin-bottom:0; }

        /* Filters */
        .modern-filters-wrapper { border-bottom:1px solid #e9ecef; }
        .filter-toggle-btn { width:100%; display:flex; align-items:center; gap:10px; background:#f8f9fa; border:none; padding:12px 14px; cursor:pointer; border-bottom:1px solid #e9ecef; font-weight:600; }
        .filter-toggle-btn:hover { background:#e9ecef; }
        .filter-badge { background:#007bff; color:#fff; border-radius:12px; padding:2px 8px; font-size:12px; font-weight:700; }
        .filters-content { max-height:0; overflow:hidden; transition:max-height .3s ease; background:#fff; }
        .filters-content.active { max-height:800px; border-bottom:1px solid #e9ecef; }
        .filter-form { padding:14px; }
        .filters-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; }
        .filter-card { display:flex; gap:10px; padding:10px; border:1px solid #e9ecef; border-radius:8px; align-items:center; background:#fff; }
        .filter-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; }
        .filter-label { margin:0; font-size:12px; color:#6c757d; }
        .filter-select, .filter-input { width:100%; border:1px solid #e1e5e9; border-radius:6px; padding:6px 10px; font-size:13px; }
        .filter-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }
        .filter-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border:1px solid #e1e5e9; border-radius:6px; background:#fff; cursor:pointer; font-weight:600; font-size:13px; }
        .filter-btn-apply { background:#007bff; color:#fff; border-color:#007bff; }
        .filter-btn-clear { color:#dc3545; border-color:#f1aeb5; background:#fff5f5; }

        /* Table top scrollbar */
        .table-scroll-top { overflow-x:auto; overflow-y:hidden; height:12px; }
        .table-scroll-top > div { height:1px; }

        .bottom-pagination-wrapper { display:flex; justify-content:space-between; align-items:center; margin:14px 14px 18px; gap:10px; }
        .pagination-info { color:#6c757d; font-size:12px; }

        @media (max-width: 992px){ 
            .filters-grid { grid-template-columns: 1fr; } 
            .control-left .control-title{ font-size:20px; } 
            .drivers-control-panel { flex-direction: column; align-items: stretch; }
            .control-center { order: -1; width: 100%; }
            .search-input { width: 100%; }
            .control-right { flex-direction: column; align-items: stretch; }
        }
    </style>
@endpush 

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const driversI18n = {!! json_encode([
        'showing' => __(':shown / :total şoför gösteriliyor'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    // Sayfa boyutu butonları
    const group = document.getElementById('drivers-page-size');
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
        filterToggle.addEventListener('click', function(){
            this.classList.toggle('active');
            filtersContent.classList.toggle('active');
        });
        const hasActiveFilters = {{ request()->hasAny(['filter_status','filter_vehicle','q']) ? 'true' : 'false' }};
        if (hasActiveFilters) {
            filterToggle.classList.add('active');
            filtersContent.classList.add('active');
        }
    }

    // Üst-alt yatay scrollbar senkronizasyonu
    const top = document.getElementById('drivers-scroll-top');
    const bottom = null;
    const wrapper = document.getElementById('drivers-table-wrapper');
    const table = document.getElementById('drivers-table');
    function syncBars(){
        if (!table || !top) return;
        const width = table.scrollWidth;
        top.firstElementChild.style.width = width + 'px';
    }
    syncBars();
    window.addEventListener('resize', syncBars);
    if (top && wrapper) {
        top.addEventListener('scroll', ()=>{ wrapper.scrollLeft = top.scrollLeft; });
        wrapper.addEventListener('scroll', ()=>{ top.scrollLeft = wrapper.scrollLeft; });
    }

    // Şoför Arama (client-side)
    const driverSearchInput = document.getElementById('driver-search');
    const driverSearchClear = document.getElementById('driver-search-clear');
    const driverSearchInfo = document.getElementById('driver-search-info');
    const driversTableBody = document.querySelector('#drivers-table tbody');
    const driverRows = driversTableBody ? Array.from(driversTableBody.querySelectorAll('tr.driver-row')) : [];
    const noDriversFoundRow = document.getElementById('no-drivers-found');
    const totalDriverRows = driverRows.length;

    const normalizeText = (s) => {
        try {
            return (s || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/ı/g, 'i')
                .replace(/İ/g, 'i');
        } catch (e) {
            return (s || '').toLowerCase().replace(/ı/g,'i').replace(/İ/g,'i');
        }
    };

    const applyDriverFilter = () => {
        if (!driverRows.length) return;
        const query = normalizeText(driverSearchInput ? driverSearchInput.value : '');
        let matches = 0;
        driverRows.forEach(row => {
            if (row.id === 'no-drivers-found') return;
            const term = normalizeText(row.getAttribute('data-search-term') || '');
            const hit = query.length === 0 || term.includes(query);
            row.style.display = hit ? '' : 'none';
            if (hit) matches++;
        });
        
        if (query) {
            driverSearchInfo.textContent = driversI18n.showing.replace(':shown', matches).replace(':total', totalDriverRows);
            driverSearchInfo.style.display = 'block';
            driverSearchClear.style.display = 'flex';
        } else {
            driverSearchInfo.style.display = 'none';
            driverSearchClear.style.display = 'none';
        }
        
        if (noDriversFoundRow) {
            noDriversFoundRow.style.display = matches === 0 && query ? '' : 'none';
        }
    };

    if (driverSearchInput) {
        driverSearchInput.addEventListener('input', applyDriverFilter);
        driverSearchInput.addEventListener('keydown', function(e){
            if (e.key === 'Enter') { e.preventDefault(); }
        });
    }
    
    if (driverSearchClear) {
        driverSearchClear.addEventListener('click', function() {
            driverSearchInput.value = '';
            applyDriverFilter();
            driverSearchInput.focus();
        });
    }
    
    // İlk yüklemede filtreyi uygula
    applyDriverFilter();
});
</script>
@endpush 