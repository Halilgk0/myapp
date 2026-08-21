@extends('layouts.agency')

@section('title', 'Bilet Yönetimi')

@section('content')
<div class="ag-page-header ag-flex ag-justify-between ag-items-center">
    <div>
        <h1 class="ag-page-title">Bilet Yönetimi</h1>
        <p class="ag-page-subtitle">Oluşturduğunuz biletleri görüntüleyin ve yönetin</p>
    </div>
    <a href="{{ route('agency.tickets.create') }}" class="ag-btn ag-btn-primary">
        <i data-lucide="plus"></i>
        <span>Yeni Bilet</span>
    </a>
</div>

<!-- Pending Requests -->
@if(isset($pendingRequests) && $pendingRequests->count() > 0)
<div class="ag-card ag-mb-3" style="border-left: 4px solid var(--ag-warning);">
    <div class="ag-card-header">
        <h3 class="ag-card-title">
            <i data-lucide="clock" style="color:var(--ag-warning)"></i>
            Onay Bekleyen İstekler
            <span class="ag-badge ag-badge-warning" style="margin-left:8px">{{ $pendingRequests->count() }}</span>
        </h3>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Tur</th>
                        <th>Müşteri</th>
                        <th>Tur Tarihi</th>
                        <th>Yolcu</th>
                        <th>Toplam</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingRequests as $request)
                    <tr>
                        <td>
                            @php $createdAtTr = $request->created_at->clone()->timezone('Europe/Istanbul'); @endphp
                            <div style="font-weight:500">{{ $createdAtTr->format('d.m.Y') }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $createdAtTr->format('H:i') }}</div>
                        </td>
                        <td>
                            <div style="font-weight:500">{{ $request->tour->name }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $request->tour->country }}</div>
                        </td>
                        <td>
                            <div>{{ $request->customer_name }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $request->customer_phone }}</div>
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-primary">{{ $request->tour_date->format('d.m.Y') }}</span>
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-secondary">{{ $request->total_passengers }} kişi</span>
                        </td>
                        <td>
                            <span style="font-weight:600">{{ number_format($request->total_price, 2) }} {{ strtoupper($request->currency) }}</span>
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-warning">Bekliyor</span>
                        </td>
                        <td class="ag-text-right">
                            <form action="{{ route('agency.ticket-requests.cancel', $request) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ag-btn ag-btn-danger ag-btn-sm" onclick="return confirm('Bu isteği iptal etmek istediğinize emin misiniz?')">
                                    <i data-lucide="x"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Returned Requests -->
@if(isset($returnedRequests) && $returnedRequests->count() > 0)
<div class="ag-card ag-mb-3" style="border-left: 4px solid var(--ag-accent);">
    <div class="ag-card-header">
        <h3 class="ag-card-title">
            <i data-lucide="refresh-cw" style="color:var(--ag-accent)"></i>
            Düzenleme Bekleyen İstekler
            <span class="ag-badge ag-badge-primary" style="margin-left:8px">{{ $returnedRequests->count() }}</span>
        </h3>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Tur</th>
                        <th>Müşteri</th>
                        <th>Tur Tarihi</th>
                        <th>Düzenleme Sebebi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returnedRequests as $request)
                    <tr>
                        <td>
                            @php $createdAtTr = $request->created_at->clone()->timezone('Europe/Istanbul'); @endphp
                            <div style="font-weight:500">{{ $createdAtTr->format('d.m.Y') }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $createdAtTr->format('H:i') }}</div>
                        </td>
                        <td>
                            <div style="font-weight:500">{{ $request->tour->name }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $request->tour->country }}</div>
                        </td>
                        <td>
                            <div>{{ $request->customer_name }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $request->customer_phone }}</div>
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-primary">{{ $request->tour_date->format('d.m.Y') }}</span>
                        </td>
                        <td>
                            <div class="ag-text-danger" style="max-width:200px">{{ Str::limit($request->return_reason, 50) }}</div>
                            @if($request->returned_at)
                            <div class="ag-text-muted" style="font-size:11px">{{ $request->returned_at->timezone('Europe/Istanbul')->format('d.m.Y H:i') }}</div>
                            @endif
                        </td>
                        <td class="ag-text-right">
                            <a href="{{ route('agency.ticket-requests.edit', $request) }}" class="ag-btn ag-btn-warning ag-btn-sm">
                                <i data-lucide="edit-2"></i>
                                <span>Düzenle</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Filters -->
<div class="ag-card ag-mb-3">
    <div class="ag-card-header" style="cursor:pointer" onclick="document.getElementById('filterBody').classList.toggle('d-none')">
        <h3 class="ag-card-title">
            <i data-lucide="filter"></i>
            Filtreler
        </h3>
        <i data-lucide="chevron-down" style="width:18px;height:18px;color:var(--ag-text-muted)"></i>
    </div>
    <div class="ag-card-body d-none" id="filterBody">
        <form method="GET" action="{{ route('agency.tickets.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="ag-form-group">
                        <label class="ag-form-label">Arama</label>
                        <input type="text" name="q" class="ag-form-input" placeholder="Ad, telefon, voucher..." value="{{ request('q') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ag-form-group">
                        <label class="ag-form-label">Tur</label>
                        <select name="filter_tour_id" class="ag-form-select">
                            <option value="">Tümü</option>
                            @foreach($tours as $tour)
                            <option value="{{ $tour->id }}" {{ request('filter_tour_id') == $tour->id ? 'selected' : '' }}>{{ $tour->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="ag-form-group">
                        <label class="ag-form-label">Başlangıç</label>
                        <input type="date" name="filter_from" class="ag-form-input" value="{{ request('filter_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="ag-form-group">
                        <label class="ag-form-label">Bitiş</label>
                        <input type="date" name="filter_to" class="ag-form-input" value="{{ request('filter_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="ag-form-group">
                        <label class="ag-form-label">Durum</label>
                        <select name="filter_is_active" class="ag-form-select">
                            <option value="">Tümü</option>
                            <option value="1" {{ request('filter_is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('filter_is_active') === '0' ? 'selected' : '' }}>Pasif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="ag-flex ag-gap-1 ag-mt-2">
                <button type="submit" class="ag-btn ag-btn-primary ag-btn-sm">
                    <i data-lucide="search"></i>
                    <span>Filtrele</span>
                </button>
                <a href="{{ route('agency.tickets.index') }}" class="ag-btn ag-btn-secondary ag-btn-sm">
                    <i data-lucide="x"></i>
                    <span>Temizle</span>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tickets Table -->
<div class="ag-card">
    <div class="ag-card-header">
        <h3 class="ag-card-title">
            <i data-lucide="check-circle" style="color:var(--ag-success)"></i>
            Onaylanmış Biletler
            <span class="ag-badge ag-badge-success" style="margin-left:8px">{{ $tickets->total() }}</span>
        </h3>
        <select class="ag-form-select" style="width:80px;padding:6px 10px;font-size:12px" onchange="window.location.href='{{ route('agency.tickets.index') }}?per_page='+this.value+'&{{ http_build_query(request()->except('per_page')) }}'">
            @foreach([10, 25, 50, 100] as $pp)
            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
        </select>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th>Takip No</th>
                        <th>Müşteri</th>
                        <th>Tur</th>
                        <th>Tarih</th>
                        <th>Yolcu</th>
                        <th>Toplam</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-family:monospace">{{ $ticket->tracking_no }}</div>
                            @if($ticket->voucher_no)
                            <div class="ag-text-muted" style="font-size:11px">{{ $ticket->voucher_no }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight:500">{{ $ticket->customer_name }}</div>
                            <div class="ag-text-muted" style="font-size:12px">{{ $ticket->customer_phone }}</div>
                        </td>
                        <td>
                            <div>{{ $ticket->tour_name ?? 'N/A' }}</div>
                            @if($ticket->tour_country)
                            <div class="ag-text-muted" style="font-size:12px">{{ $ticket->tour_country }}</div>
                            @endif
                        </td>
                        <td>
                            @if($ticket->tour_date)
                            <div>{{ $ticket->tour_date->format('d.m.Y') }}</div>
                            @if($ticket->pickup_time)
                            <div class="ag-text-muted" style="font-size:12px">{{ \Carbon\Carbon::parse($ticket->pickup_time)->format('H:i') }}</div>
                            @endif
                            @else
                            <span class="ag-text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-secondary">{{ $ticket->total_passengers ?? 0 }} kişi</span>
                        </td>
                        <td>
                            <span style="font-weight:600">{{ number_format($ticket->total_price, 2) }} {{ strtoupper($ticket->currency ?? 'TRY') }}</span>
                        </td>
                        <td>
                            @if($ticket->is_active)
                            <span class="ag-badge ag-badge-success">Aktif</span>
                            @else
                            <span class="ag-badge ag-badge-danger">Pasif</span>
                            @endif
                        </td>
                        <td class="ag-text-right">
                            <div class="ag-flex ag-gap-1" style="justify-content:flex-end">
                                <a href="{{ route('agency.tickets.show', $ticket) }}" class="ag-btn ag-btn-ghost ag-btn-sm" title="Görüntüle">
                                    <i data-lucide="eye"></i>
                                </a>
                                <a href="{{ route('agency.tickets.edit', $ticket) }}" class="ag-btn ag-btn-ghost ag-btn-sm" title="Düzenle">
                                    <i data-lucide="edit-2"></i>
                                </a>
                                <form action="{{ route('agency.tickets.destroy', $ticket) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ag-btn ag-btn-ghost ag-btn-sm ag-text-danger" title="Sil" onclick="return confirm('Bu bileti silmek istediğinize emin misiniz?')">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="ag-empty">
                                <i data-lucide="ticket" class="ag-empty-icon"></i>
                                <div class="ag-empty-title">Henüz onaylanmış bilet yok</div>
                                <p class="ag-empty-text">Bilet isteği oluşturun ve tur sahibinin onaylamasını bekleyin.</p>
                                <a href="{{ route('agency.tickets.create') }}" class="ag-btn ag-btn-primary">
                                    <i data-lucide="plus"></i>
                                    <span>İlk Bilet İsteğini Oluştur</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
    <div class="ag-card-footer ag-flex ag-justify-between ag-items-center">
        <span class="ag-text-muted" style="font-size:13px">
            {{ $tickets->firstItem() }}-{{ $tickets->lastItem() }} / {{ $tickets->total() }} kayıt
        </span>
        <div class="ag-pagination">
            @if($tickets->onFirstPage())
            <span class="ag-pagination-btn" style="opacity:0.5;cursor:not-allowed"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></span>
            @else
            <a href="{{ $tickets->previousPageUrl() }}" class="ag-pagination-btn"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></a>
            @endif

            @foreach($tickets->getUrlRange(max(1, $tickets->currentPage() - 2), min($tickets->lastPage(), $tickets->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}" class="ag-pagination-btn {{ $page == $tickets->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($tickets->hasMorePages())
            <a href="{{ $tickets->nextPageUrl() }}" class="ag-pagination-btn"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></a>
            @else
            <span class="ag-pagination-btn" style="opacity:0.5;cursor:not-allowed"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('js')
<script>
    lucide.createIcons();
</script>
@endpush
