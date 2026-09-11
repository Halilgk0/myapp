@extends('layouts.admin')

@section('title', __('Tur Yönetimi'))

@section('content')
    <div class="container-fluid tour-page">
        <div class="row">
            <div class="col-12">
                <!-- Modern Kontrol Paneli -->
                <div class="tours-control-panel ad-page-header admin-list-toolbar mb-3">
                    <div class="control-left">
                        <h4 class="control-title"><i class="fas fa-route"></i> {{ __('Tur Yönetimi') }}</h4>
                        <p class="control-subtitle">{{ __('Toplam :count tur', ['count' => $tours->total()]) }}</p>
                    </div>
                    <div class="control-right">
                        <div class="control-item">
                            <form method="GET" action="{{ route('admin.tours.index') }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">
                                <input type="text" class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="{{ __('Ara...') }}" style="width:140px;">
                            </form>
                        </div>
                        <div class="control-item">
                            <div class="size-selector" id="tours-page-size">
                                <button type="button" class="size-btn {{ (string)request('per_page', $perPage ?? 10)==='10' ? 'active' : '' }}" data-size="10">10</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='25' ? 'active' : '' }}" data-size="25">25</button>
                                <button type="button" class="size-btn {{ (string)request('per_page')==='50' ? 'active' : '' }}" data-size="50">50</button>
                            </div>
                        </div>
                        <div class="control-item">
                            <div style="display:flex;gap:3px;">
                                <a href="{{ route('admin.tours.export.excel') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light" title="Excel">
                                    <i class="fas fa-file-excel text-success"></i>
                                </a>
                                <a href="{{ route('admin.tours.export.pdf') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-light" title="PDF">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </a>
                            </div>
                        </div>
                        <div class="control-item">
                                    <a href="{{ route('admin.tours.create') }}" class="btn btn-sm btn-light">
                                <i class="fas fa-plus"></i> {{ __('Yeni Tur') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="ad-card">
                    <div class="card-body">
                        @if($tours->count() > 0)
                            <div class="table-responsive">
                                <table class="ad-table table table-bordered table-striped" id="tours-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>{{ __('Tur Adı') }}</th>
                                            <th>{{ __('Ülke/Şehir') }}</th>
                                            <th>{{ __('Saat') }}</th>
                                            <th>{{ __('Fiyat') }}</th>
                                            <th>{{ __('Kapasite') }}</th>
                                            <th>{{ __('Bilet Sayısı') }}</th>
                                            <th>{{ __('Durum') }}</th>
                                            <th>{{ __('İşlemler') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tours as $tour)
                                            <tr class="tour-row" data-tour-name="{{ Str::lower($tour->name) }}">
                                                <td>{{ $tour->id }}</td>
                                                <td>
                                                    <strong class="tour-name">{{ $tour->name }}</strong>
                                                    @if($tour->description)
                                                        <br><small class="text-muted">{{ Str::limit($tour->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $tour->country }} / {{ $tour->city }}
                                                </td>
                                                <td>
                                                    @if($tour->earliest_service_area_time)
                                                        <span class="badge badge-info"><i class="far fa-clock"></i> {{ $tour->earliest_service_area_time }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $maxPrice = (float) ($tour->max_display_price ?? 0);
                                                        $currencyLabel = $tour->display_currency ?? ($tour->currency ?? 'TRY');
                                                    @endphp
                                                    @if($maxPrice > 0)
                                                        <strong>{{ number_format($maxPrice, 2) }} {{ $currencyLabel }}</strong>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($tour->max_capacity)
                                                        <span class="badge badge-secondary">{{ __(':count kişi', ['count' => $tour->max_capacity]) }}</span>
                                                    @else
                                                        <span class="text-muted">{{ __('Sınırsız') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $tour->total_tickets }}</span>
                                                    @if($tour->active_tickets > 0)
                                                        <span class="badge badge-success">{{ __(':count aktif', ['count' => $tour->active_tickets]) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $tour->status_badge }}">{{ __($tour->status) }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group justify-content-end" role="group" style="width:100%;">
                                                        <a href="{{ route('admin.tours.show', $tour) }}"
                                                           class="ad-btn ad-btn-info ad-btn-sm" title="{{ __('Görüntüle') }}">
                                                            <i data-lucide="eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.tours.edit', $tour) }}"
                                                           class="ad-btn ad-btn-warning ad-btn-sm" title="{{ __('Düzenle') }}">
                                                            <i data-lucide="edit-2"></i>
                                                        </a>
                                                        <form action="{{ route('admin.tours.destroy', $tour) }}"
                                                              method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="ad-btn ad-btn-danger ad-btn-sm"
                                                                    title="{{ __('Sil') }}"
                                                                    onclick="return confirm('{{ __('Bu turu silmek istediğinizden emin misiniz?') }}')">
                                                                <i data-lucide="trash-2"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr id="no-results-row" style="display:none;">
                                            <td colspan="9" class="text-center">{{ __('Bu isimde tur yok') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="bottom-pagination-wrapper">
                                <div class="pagination-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span>{!! __('Toplam :count kayıt bulundu', ['count' => '<strong>' . $tours->total() . '</strong>']) !!}</span>
                                </div>
                                <div class="custom-pagination">
                                    @if ($tours->hasPages())
                                        <nav>
                                            <ul class="pagination mb-0">
                                                {{-- Previous --}}
                                                @if ($tours->onFirstPage())
                                                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>
                                                @else
                                                    <li class="page-item"><a class="page-link" href="{{ $tours->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a></li>
                                                @endif

                                                {{-- Page Numbers --}}
                                                @foreach ($tours->getUrlRange(1, $tours->lastPage()) as $page => $url)
                                                    @if ($page == $tours->currentPage())
                                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                                    @else
                                                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                                    @endif
                                                @endforeach

                                                {{-- Next --}}
                                                @if ($tours->hasMorePages())
                                                    <li class="page-item"><a class="page-link" href="{{ $tours->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a></li>
                                                @else
                                                    <li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>
                                                @endif
                                            </ul>
                                        </nav>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('Henüz tur bulunmuyor') }}</h5>
                                <p class="text-muted">{{ __('İlk turu oluşturmak için yukarıdaki "Yeni Tur" butonuna tıklayın.') }}</p>
                                <a href="{{ route('admin.tours.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('İlk Turu Oluştur') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop 
<!-- end of the code-->
@push('css')
<style>
.tours-control-panel { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 8px; padding: 20px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 12px rgba(0,123,255,0.2); flex-wrap:wrap; gap:15px; }
.control-left .control-title { color:#fff; margin:0; font-size:24px; font-weight:700; }
.control-left .control-subtitle { color:#e2e6ea; margin:0; font-size:12px; }
.control-right { display:flex; gap:20px; align-items:center; }
.control-item { display:flex; flex-direction:column; gap:6px; }
.control-label { color:#fff; font-size:12px; opacity:.9; }
.size-selector { display:flex; background: rgba(255,255,255,.15); padding:4px; border-radius:8px; }
.size-btn { background: transparent; border: none; color:#fff; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:600; font-size:12px; }
.size-btn.active { background:#fff; color:#0056b3; }
.custom-pagination .pagination { margin-bottom:0; }
.quick-search { display:flex; align-items:center; background:#fff; border-radius:6px; overflow:hidden; border:1px solid rgba(255,255,255,0.4); }
.quick-search input { border:none; padding:6px 10px; font-size:13px; min-width:220px; }
.quick-search button { background:#0056b3; color:#fff; border:none; padding:6px 10px; display:flex; align-items:center; justify-content:center; cursor:pointer; }
.bottom-pagination-wrapper { display:flex; justify-content:space-between; align-items:center; margin:14px 0 0; gap:10px; background:#fff; padding:12px 16px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.pagination-info { color:#6c757d; font-size:12px; }
.custom-pagination nav { display:block !important; }
.custom-pagination .pagination { margin:0 !important; display:flex !important; gap:4px; list-style:none !important; padding:0 !important; }
.custom-pagination .pagination .page-item { display:inline-block !important; }
.custom-pagination .pagination .page-item .page-link { border:1px solid #dee2e6 !important; background:#fff !important; color:#495057 !important; padding:6px 12px !important; border-radius:6px !important; font-size:13px !important; font-weight:500 !important; text-decoration:none !important; display:flex !important; align-items:center !important; justify-content:center !important; min-width:36px !important; transition:all .2s ease !important; }
.custom-pagination .pagination .page-item .page-link:hover { background:#007bff !important; border-color:#007bff !important; color:#fff !important; }
.custom-pagination .pagination .page-item.active .page-link { background:#007bff !important; border-color:#007bff !important; color:#fff !important; }
.custom-pagination .pagination .page-item.disabled .page-link { background:#f8f9fa !important; color:#adb5bd !important; cursor:not-allowed !important; }
@media (max-width: 992px){ .control-left .control-title{ font-size:20px; } .quick-search input{ min-width:140px; } .bottom-pagination-wrapper { flex-direction:column; text-align:center; } }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Sayfa boyutu butonları
    const group = document.getElementById('tours-page-size');
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

    // Hızlı arama: sayfa yenilemeden istemci tarafında filtrele
    const qsForm = document.querySelector('.quick-search-form');
    const qsInput = qsForm ? qsForm.querySelector('input[name="q"]') : null;
    const table = document.getElementById('tours-table');
    if (qsForm && qsInput && table) {
        qsForm.addEventListener('submit', function(e){ e.preventDefault(); });
        const rows = Array.from(table.querySelectorAll('tbody tr.tour-row'));
        const noRow = document.getElementById('no-results-row');
        const normalizeText = (s) => {
            try {
                return (s || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/ı/g, 'i');
            } catch (e) {
                return (s || '').toLowerCase().replace(/ı/g,'i');
            }
        };
        const applyFilter = () => {
            const val = normalizeText(qsInput.value || '');
            let shown = 0;
            rows.forEach(r => {
                const rawName = (r.querySelector('.tour-name') ? r.querySelector('.tour-name').textContent : '') || '';
                const name = normalizeText(rawName);
                const match = val.length === 0 ? true : name.includes(val);
                r.style.display = match ? '' : 'none';
                if (match) shown++;
            });
            if (noRow) noRow.style.display = shown === 0 ? '' : 'none';
        };
        qsInput.addEventListener('input', applyFilter);
        // İlk yüklemede URL'deki q varsa uygula
        applyFilter();
    }
});
</script>
@endpush