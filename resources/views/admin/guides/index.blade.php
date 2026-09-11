@extends('layouts.admin')

@section('title', __('Rehber Yönetimi'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Modern Kontrol Paneli -->
            <div class="guides-control-panel ad-page-header admin-list-toolbar mb-3">
                <div class="control-left">
                    <h4 class="control-title"><i class="fas fa-user-tie"></i> {{ __('Rehber Yönetimi') }}</h4>
                    <p class="control-subtitle">{{ __('Toplam :count rehber', ['count' => $guides->count()]) }}</p>
                </div>
                <div class="control-center">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="guide-search" class="search-input" placeholder="{{ __('Rehber ara...') }}">
                        <button type="button" id="guide-search-clear" class="search-clear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="guide-search-info" class="search-info" style="display: none;"></div>
                </div>
                <div class="control-right">
                    <div class="control-item">
                        <a href="{{ route('admin.guides.create') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-plus"></i> {{ __('Yeni Rehber') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="ad-card">
                <div class="card-body table-responsive p-0">
                    <table class="ad-table table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ __('Ad Soyad') }}</th>
                                <th>{{ __('E-posta') }}</th>
                                <th>{{ __('Telefon') }}</th>
                                <th>{{ __('Desteklenen Milliyetler') }}</th>
                                <th>{{ __('Atanmış Şoförler') }}</th>
                                <th>{{ __('Durum') }}</th>
                                <th>{{ __('İşlemler') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guides as $guide)
                                <tr>
                                    <td>{{ $guide->id }}</td>
                                    <td>{{ $guide->name }}</td>
                                    <td>{{ $guide->email }}</td>
                                    <td>{{ $guide->phone ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $guide->supported_nationalities_names }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ __(':count Şoför', ['count' => $guide->drivers->count()]) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($guide->status == 'Aktif')
                                            <span class="badge badge-success">{{ __('Aktif') }}</span>
                                        @elseif($guide->status == 'İzinli')
                                            <span class="badge badge-warning">{{ __('İzinli') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __($guide->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.guides.show', $guide) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.guides.edit', $guide) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.guides.destroy', $guide) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm({!! json_encode(__('Bu rehberi silmek istediğinizden emin misiniz?'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('Henüz rehber bulunmamaktadır.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($guides->hasPages())
                    <div class="card-footer clearfix">
                        {{ $guides->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
<!-- rehber yönetimi css -->
@push('css')
<style>
    /* Top control panel */
    .guides-control-panel { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 8px; padding: 20px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 12px rgba(0,123,255,0.2); flex-wrap:wrap; gap:15px; }
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
    
    @media (max-width: 992px){ 
        .guides-control-panel { flex-direction: column; align-items: stretch; }
        .control-center { order: -1; width: 100%; }
        .search-input { width: 100%; }
        .control-right { flex-direction: column; align-items: stretch; }
        .control-left .control-title{ font-size:20px; }
    }
</style>
@endpush
<!-- rehber yönetimi js-->
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const guidesI18n = {!! json_encode([
        'showing' => __(':shown / :total rehber gösteriliyor'),
        'notFound' => __(':query için rehber bulunamadı'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

    // Rehber Arama Fonksiyonu
    const guideSearchInput = document.getElementById('guide-search');
    const guideSearchClear = document.getElementById('guide-search-clear');
    const guideSearchInfo = document.getElementById('guide-search-info');
    const guideTableBody = document.querySelector('table tbody');
    
    if (guideSearchInput && guideTableBody) {
        const allRows = Array.from(guideTableBody.querySelectorAll('tr'));
        const dataRows = allRows.filter(row => !row.querySelector('td[colspan]'));
        const totalRows = dataRows.length;
        
        function filterGuideTable(searchTerm) {
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
                guideSearchInfo.textContent = guidesI18n.showing.replace(':shown', visibleCount).replace(':total', totalRows);
                guideSearchInfo.style.display = 'block';
                guideSearchClear.style.display = 'flex';
            } else {
                guideSearchInfo.style.display = 'none';
                guideSearchClear.style.display = 'none';
            }
            
            // Sonuç yoksa mesaj göster
            const noResultRow = guideTableBody.querySelector('.no-search-result');
            if (visibleCount === 0 && searchTerm) {
                if (!noResultRow) {
                    const tr = document.createElement('tr');
                    tr.className = 'no-search-result';
                    tr.innerHTML = '<td colspan="8" class="text-center text-muted py-4"><i class="fas fa-search mr-2"></i>' + guidesI18n.notFound.replace(':query', '"' + searchTerm + '"') + '</td>';
                    guideTableBody.appendChild(tr);
                }
            } else if (noResultRow) {
                noResultRow.remove();
            }
        }
        
        guideSearchInput.addEventListener('input', function() {
            filterGuideTable(this.value);
        });
        
        guideSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
        
        guideSearchClear.addEventListener('click', function() {
            guideSearchInput.value = '';
            filterGuideTable('');
            guideSearchInput.focus();
        });
    }
});
</script>
@endpush
<!-- end of the code-->