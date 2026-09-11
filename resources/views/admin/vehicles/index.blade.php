@extends('layouts.admin')

@section('title', __('Araç Yönetimi'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Modern Kontrol Paneli -->
                <div class="vehicles-control-panel ad-page-header admin-list-toolbar mb-3">
                    <div class="control-left">
                        <h4 class="control-title"><i class="fas fa-car"></i> {{ __('Araç Yönetimi') }}</h4>
                        <p class="control-subtitle">{{ __('Toplam :count araç', ['count' => $vehicles->total()]) }}</p>
                    </div>
                    <div class="control-center">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="plate-search" class="search-input" placeholder="{{ __('Plaka ara...') }}">
                            <button type="button" id="plate-search-clear" class="search-clear" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="search-info" class="search-info" style="display: none;"></div>
                    </div>
                    <div class="control-right">
                        <div class="control-item">
                            <div class="size-selector" id="vehicles-page-size">
                                <button type="button" class="size-btn {{ (string)request('per_page', $perPage ?? 10)==='10' ? 'active' : '' }}" data-size="10">10</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='25' ? 'active' : '' }}" data-size="25">25</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='50' ? 'active' : '' }}" data-size="50">50</button>
                            </div>
                        </div>
                        <div class="control-item">
                            <div style="display:flex;gap:3px;">
                                <a href="{{ route('admin.vehicles.export.excel') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light" title="Excel">
                                    <i class="fas fa-file-excel text-success"></i>
                                </a>
                                <a href="{{ route('admin.vehicles.export.pdf') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light" title="PDF">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </a>
                            </div>
                        </div>
                        <div class="control-item">
                            <a href="{{ route('admin.vehicles.create') }}" class="btn btn-sm btn-light">
                                <i class="fas fa-plus"></i> {{ __('Yeni Araç') }}
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
                                    if(request('filter_driver')) $activeFilters++;
                                    if(request('filter_capacity')) $activeFilters++;
                                @endphp
                                @if($activeFilters > 0)
                                    <span class="filter-badge">{{ $activeFilters }}</span>
                                @endif
                                <i class="fas fa-chevron-down ml-auto toggle-icon"></i>
                            </button>
                            
                            <div class="filters-content" id="filtersContent">
                                <form method="GET" action="{{ route('admin.vehicles.index') }}" class="filter-form">
                                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">
                                    
                                    <div class="filters-grid">
                                        <div class="filter-card">
                                            <div class="filter-icon bg-success">
                                                <i class="fas fa-toggle-on"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Araç Durumu') }}</label>
                                                <select class="filter-select" name="filter_status">
                                            <option value="">{{ __('Tümü') }}</option>
                                            <option value="available" {{ request('filter_status')==='available' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                            <option value="busy" {{ request('filter_status')==='busy' ? 'selected' : '' }}>{{ __('Pasif') }}</option>
                                        </select>
                                    </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-primary">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Şoför Durumu') }}</label>
                                                <select class="filter-select" name="filter_driver">
                                                    <option value="">{{ __('Tümü') }}</option>
                                                    <option value="with" {{ request('filter_driver')==='with' ? 'selected' : '' }}>{{ __('Şoförlü') }}</option>
                                                    <option value="without" {{ request('filter_driver')==='without' ? 'selected' : '' }}>{{ __('Şoförsüz') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="filter-card">
                                            <div class="filter-icon bg-warning">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div class="filter-content">
                                                <label class="filter-label">{{ __('Kapasite') }}</label>
                                                <select class="filter-select" name="filter_capacity">
                                            <option value="">{{ __('Tümü') }}</option>
                                            <option value="small" {{ request('filter_capacity')==='small' ? 'selected' : '' }}>{{ __('Küçük (1-8)') }}</option>
                                            <option value="medium" {{ request('filter_capacity')==='medium' ? 'selected' : '' }}>{{ __('Orta (9-16)') }}</option>
                                            <option value="large" {{ request('filter_capacity')==='large' ? 'selected' : '' }}>{{ __('Büyük (17+)') }}</option>
                                        </select>
                                    </div>
                                </div>
                                    </div>

                                    <div class="filter-actions">
                                        <a href="{{ route('admin.vehicles.index', ['per_page'=>request('per_page', $perPage ?? 10)]) }}" class="filter-btn filter-btn-clear">
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
                        <div class="table-responsive">
                            <table class="ad-table table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ __('Plaka') }}</th>
                                        <th>{{ __('Marka/Model') }}</th>
                                        <th>{{ __('Tür') }}</th>
                                        <th>{{ __('Kapasite') }}</th>
                                        <th>{{ __('Şoför') }}</th>
                                        <th>{{ __('Son Konum') }}</th>
                                        <th>{{ __('Durum') }}</th>
                                        <th>{{ __('İşlemler') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $vehicle->id }}</td>
                                            <td>
                                                <strong>{{ $vehicle->plate_number }}</strong>
                                                @if($vehicle->image)
                                                    <br><small class="text-muted">{{ __('Resim mevcut') }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                            <td>{{ $vehicle->vehicle_type }}</td>
                                            <td>
                                                @php
                                                    $currentPassengers = $vehicle->tickets->sum(function($ticket) {
                                                        return $ticket->passengers->sum('quantity');
                                                    });
                                                    $availableSeats = $vehicle->capacity - $currentPassengers;
                                                @endphp
                                                <strong>{{ $currentPassengers }}/{{ $vehicle->capacity }}</strong>
                                                <br><small class="text-muted">{{ __(':count boş koltuk', ['count' => $availableSeats]) }}</small>
                                                @if($availableSeats <= 0)
                                                    <br><span class="badge badge-danger">{{ __('Dolu') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($vehicle->driver)
                                                    <span class="badge badge-info">{{ $vehicle->driver->name }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ __('Atanmamış') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php $loc = $vehicle->current_location; @endphp
                                                @if($loc)
                                                    @php $live = $loc->recorded_at && $loc->recorded_at->gt(now()->subMinutes(2)); @endphp
                                                    @if($live)
                                                        <span class="badge badge-success"><i class="fas fa-circle" style="font-size:7px;"></i> {{ __('CANLI') }}</span>
                                                    @endif
                                                    <small class="text-muted d-block">
                                                        {{ number_format((float) $loc->latitude, 5) }}, {{ number_format((float) $loc->longitude, 5) }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="far fa-clock"></i> {{ $loc->recorded_at ? $loc->recorded_at->diffForHumans() : '—' }}
                                                    </small>
                                                @else
                                                    <small class="text-muted">{{ __('Henüz konum yok') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($vehicle->is_active)
                                                    <span class="badge badge-success">{{ __('Aktif') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('Pasif') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.vehicles.show', $vehicle) }}"
                                                       class="btn btn-sm btn-info" title="{{ __('Görüntüle') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}"
                                                       class="btn btn-sm btn-warning" title="{{ __('Düzenle') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('{{ __('Bu aracı silmek istediğinize emin misiniz?') }}')"
                                                                title="{{ __('Sil') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">{{ __('Henüz araç eklenmemiş.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Alt Pagination -->
                <div class="bottom-pagination-wrapper">
                    <div class="pagination-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>{!! __('Toplam :count kayıt bulundu', ['count' => '<strong>' . $vehicles->total() . '</strong>']) !!}</span>
                    </div>
                    <div class="custom-pagination">
                        {{ $vehicles->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
/* Modern Kontrol Paneli */
.vehicles-control-panel {
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
    .vehicles-control-panel {
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

/* Dark mode */
html.dark-mode .table { background: #1e293b !important; color: #e2e8f0 !important; }
html.dark-mode .table thead th { background: #0f172a !important; color: #e2e8f0 !important; border-color: #334155 !important; }
html.dark-mode .table tbody tr { background: #1e293b !important; }
html.dark-mode .table-striped tbody tr:nth-of-type(odd) { background: #243044 !important; }
html.dark-mode .table tbody tr:hover { background: #334155 !important; }
html.dark-mode .table td { border-color: #334155 !important; color: #e2e8f0 !important; }
html.dark-mode .table .text-muted { color: #94a3b8 !important; }
    </style>
@endpush

@push('js')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    const vehiclesI18n = {!! json_encode([
        'showing' => __(':shown / :total araç gösteriliyor'),
        'noResults' => __(':term plakasına uygun araç bulunamadı'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    // Sayfa boyutu butonları
        const group = document.getElementById('vehicles-page-size');
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
        
        // Eğer filtre aktif ise başlangıçta aç
        const hasActiveFilters = {{ request()->hasAny(['filter_status', 'filter_driver', 'filter_capacity']) ? 'true' : 'false' }};
        if (hasActiveFilters) {
            filterToggle.classList.add('active');
            filtersContent.classList.add('active');
        }
    }

    // Plaka Arama Fonksiyonu
    const plateSearchInput = document.getElementById('plate-search');
    const plateSearchClear = document.getElementById('plate-search-clear');
    const searchInfo = document.getElementById('search-info');
    const tableBody = document.querySelector('table tbody');
    
    if (plateSearchInput && tableBody) {
        const allRows = Array.from(tableBody.querySelectorAll('tr'));
        const totalRows = allRows.filter(row => !row.querySelector('td[colspan]')).length;
        
        function filterTable(searchTerm) {
            searchTerm = searchTerm.toLowerCase().trim();
            let visibleCount = 0;
            
            allRows.forEach(row => {
                // Eğer "henüz araç eklenmemiş" satırıysa atla
                if (row.querySelector('td[colspan]')) {
                    row.style.display = searchTerm ? 'none' : '';
                    return;
                }
                
                // Plaka sütununu bul (2. sütun - index 1)
                const plateCell = row.querySelector('td:nth-child(2)');
                if (plateCell) {
                    const plateText = plateCell.textContent.toLowerCase();
                    
                    if (searchTerm === '' || plateText.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
            
            // Arama bilgisini güncelle
            if (searchTerm) {
                searchInfo.textContent = vehiclesI18n.showing.replace(':shown', visibleCount).replace(':total', totalRows);
                searchInfo.style.display = 'block';
                plateSearchClear.style.display = 'flex';
            } else {
                searchInfo.style.display = 'none';
                plateSearchClear.style.display = 'none';
            }
            
            // Eğer hiç sonuç yoksa "bulunamadı" mesajı göster
            const noResultRow = tableBody.querySelector('.no-search-result');
            if (visibleCount === 0 && searchTerm) {
                if (!noResultRow) {
                    const tr = document.createElement('tr');
                    tr.className = 'no-search-result';
                    tr.innerHTML = '<td colspan="8" class="text-center text-muted py-4"><i class="fas fa-search mr-2"></i>' + vehiclesI18n.noResults.replace(':term', '"' + searchTerm.toUpperCase() + '"') + '</td>';
                    tableBody.appendChild(tr);
                }
            } else if (noResultRow) {
                noResultRow.remove();
            }
        }
        
        // Input eventi
        plateSearchInput.addEventListener('input', function() {
            filterTable(this.value);
        });
        
        // Enter tuşunda form gönderimini engelle
        plateSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
        
        // Temizle butonu
        plateSearchClear.addEventListener('click', function() {
            plateSearchInput.value = '';
            filterTable('');
            plateSearchInput.focus();
        });
    }
    });
    </script>
@endpush 