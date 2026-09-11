@extends('layouts.agency')

@section('title', 'Muhasebe Yönetimi')

@section('content')
<div class="ag-page-header">
    <h1 class="ag-page-title">Muhasebe Yönetimi</h1>
    <p class="ag-page-subtitle">Gelir ve gider işlemlerinizi takip edin</p>
</div>

@if(($pendingSettlements ?? collect())->count() > 0)
<div class="ag-card ag-mb-3">
    <div class="ag-card-header">
        <h3 class="ag-card-title">
            <i data-lucide="bell-ring"></i>
            Onay Bekleyen Mutabakatlar
        </h3>
        <span class="ag-badge ag-badge-warning">{{ $pendingSettlements->count() }} adet</span>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th>Talep No</th>
                        <th>Tarih</th>
                        <th>İşlem Sayısı</th>
                        <th>Not</th>
                        <th class="ag-text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingSettlements as $settlement)
                    <tr>
                        <td>#{{ $settlement->id }}</td>
                        <td>{{ optional($settlement->created_at)->format('d.m.Y H:i') }}</td>
                        <td>{{ count(($settlement->ticket_ids ?? [])) ?: count(($settlement->transaction_ids ?? [])) }}</td>
                        <td>{{ $settlement->note ?: '-' }}</td>
                        <td class="ag-text-right">
                            <div class="ag-flex ag-gap-1" style="justify-content:flex-end">
                                <form method="POST" action="{{ route('agency.accounting.settlements.approve', $settlement) }}">
                                    @csrf
                                    <button type="submit" class="ag-btn ag-btn-success ag-btn-xs">Onayla</button>
                                </form>
                                <form method="POST" action="{{ route('agency.accounting.settlements.reject', $settlement) }}">
                                    @csrf
                                    <button type="submit" class="ag-btn ag-btn-danger ag-btn-xs">Reddet</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@php
    $agencyAccountingPmLabels = [
        'auto-expired-ticket' => 'Otomatik (Bilet)',
        'sale-ticket' => 'Bilet Satışı',
        'payout-owner' => 'Tur Sahibi Payı',
        'owner-share' => 'Admin Payı',
        'rest-adjustment' => 'Rest',
        'salary-auto' => 'Maaş',
    ];
    $agencyHasAccountingFilters = collect(['search', 'type', 'status', 'currency', 'payment_method', 'from', 'to'])
            ->contains(fn ($k) => request()->filled($k))
        || (int) request('per_page', 15) !== 15;
@endphp

<!-- Currency Summary Cards -->
@php
    $codes = [
        'USD' => 'ABD Doları',
        'EUR' => 'Euro',
        'GBP' => 'İngiliz Sterlini',
        'RUB' => 'Rus Rublesi',
        'TRY' => 'Türk Lirası',
    ];
@endphp
<div class="row g-3 ag-mb-3">
    @foreach($codes as $code => $name)
        @php
            $summary = $currencySummary[$code] ?? ['income' => 0, 'expense' => 0, 'net' => 0];
            $income = $summary['income'] ?? 0;
            $expense = $summary['expense'] ?? 0;
            $net = $summary['net'] ?? 0;
        @endphp
        <div class="col-6 col-md-4 col-lg">
            <div class="currency-card" data-currency="{{ $code }}" data-name="{{ $name }}">
                <div class="currency-header">
                    <span class="currency-code">{{ $code }}</span>
                    <i data-lucide="trending-up" style="width:16px;height:16px;margin-left:auto;opacity:0.6"></i>
                </div>
                <div class="currency-body">
                    <div class="currency-row">
                        <span class="currency-label">Gelir</span>
                        <span class="currency-value ag-text-success">{{ number_format($income, 2, ',', '.') }}</span>
                    </div>
                    <div class="currency-row">
                        <span class="currency-label">Gider</span>
                        <span class="currency-value ag-text-danger">{{ number_format($expense, 2, ',', '.') }}</span>
                    </div>
                    <div class="currency-row currency-net">
                        <span class="currency-label">Net</span>
                        <span class="currency-value {{ $net >= 0 ? 'ag-text-success' : 'ag-text-danger' }}">{{ number_format($net, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Transactions Table: filtreler listenin hemen üstünde, varsayılan kapalı -->
<div class="ag-card">
    <div class="ag-card-header ag-flex ag-items-center ag-justify-between" style="flex-wrap:wrap;gap:10px">
        <h3 class="ag-card-title ag-mb-0">
            <i data-lucide="list"></i>
            İşlemler
        </h3>
        <div class="ag-flex ag-items-center ag-gap-2 ag-flex-wrap" style="justify-content:flex-end">
            <button type="button" class="ag-btn ag-btn-secondary ag-btn-sm" data-bs-toggle="collapse" data-bs-target="#agencyAccountingFiltersCollapse" aria-expanded="{{ $agencyHasAccountingFilters ? 'true' : 'false' }}" aria-controls="agencyAccountingFiltersCollapse">
                <i data-lucide="filter"></i>
                Filtreler
            </button>
            @if($agencyHasAccountingFilters)
                <a href="{{ route('agency.accounting.index') }}" class="ag-btn ag-btn-ghost ag-btn-sm">
                    <i data-lucide="x-circle"></i> Tümünü temizle
                </a>
            @endif
            <button type="button" class="ag-btn ag-btn-primary ag-btn-sm d-none" id="agency-open-settlement-preview">
                <i data-lucide="calculator"></i>
                Hesap Gör (<span id="agency-selected-count">0</span>)
            </button>
            <span id="agency-settlement-status" class="ag-text-muted" style="font-size:12px"></span>
        </div>
    </div>
    <div id="agencyAccountingFiltersCollapse" class="collapse {{ $agencyHasAccountingFilters ? 'show' : '' }} border-bottom" style="border-color:var(--ag-border)">
        <div class="ag-card-body ag-accounting-filters-card">
            <form method="GET" action="{{ route('agency.accounting.index') }}" id="agency-accounting-filter-form">
                <div class="ag-accounting-filter-grid">
                    <div class="ag-accounting-filter-field ag-accounting-filter-field--wide">
                        <label class="ag-accounting-filter-label" for="agency-acct-search">Arama</label>
                        <input type="search" name="search" id="agency-acct-search" class="ag-form-input"
                               value="{{ request('search') }}"
                               placeholder="Başlık veya bilet takip numarası..."
                               autocomplete="off">
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-type">Tür</label>
                        <select name="type" id="agency-acct-type" class="ag-form-select">
                            <option value="">Tümü</option>
                            <option value="income" @selected(request('type') === 'income')>Gelir</option>
                            <option value="expense" @selected(request('type') === 'expense')>Gider</option>
                        </select>
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-status">Durum</label>
                        <select name="status" id="agency-acct-status" class="ag-form-select">
                            <option value="">Tümü</option>
                            <option value="paid" @selected(request('status') === 'paid')>Ödendi</option>
                            <option value="pending" @selected(request('status') === 'pending')>Beklemede</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>İptal</option>
                        </select>
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-currency">Para birimi</label>
                        <select name="currency" id="agency-acct-currency" class="ag-form-select">
                            <option value="">Tümü</option>
                            @foreach($filterCurrencies ?? [] as $c)
                                <option value="{{ $c }}" @selected(request('currency') === $c)>{{ strtoupper($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-pm">Ödeme türü</label>
                        <select name="payment_method" id="agency-acct-pm" class="ag-form-select">
                            <option value="">Tümü</option>
                            @foreach($filterPaymentMethods ?? [] as $pm)
                                <option value="{{ $pm }}" @selected(request('payment_method') === $pm)>
                                    {{ $agencyAccountingPmLabels[$pm] ?? $pm }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-from">Başlangıç</label>
                        <input type="date" name="from" id="agency-acct-from" class="ag-form-input" value="{{ request('from') }}">
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-to">Bitiş</label>
                        <input type="date" name="to" id="agency-acct-to" class="ag-form-input" value="{{ request('to') }}">
                    </div>
                    <div class="ag-accounting-filter-field">
                        <label class="ag-accounting-filter-label" for="agency-acct-perpage">Sayfa başı</label>
                        <select name="per_page" id="agency-acct-perpage" class="ag-form-select">
                            @foreach(($allowedPerPage ?? [15, 25, 50, 100]) as $pp)
                                <option value="{{ $pp }}" @selected((int) request('per_page', 15) === (int) $pp)>{{ $pp }} kayıt</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ag-flex ag-gap-2 ag-mt-3 ag-flex-wrap">
                    <button type="submit" class="ag-btn ag-btn-primary">
                        <i data-lucide="search"></i> Filtrele
                    </button>
                </div>
            </form>
            @if($agencyHasAccountingFilters)
                <p class="ag-text-muted ag-mt-2 ag-mb-0" style="font-size:12px">
                    Üstteki özet kartları ve liste yalnızca seçtiğiniz filtrelere göre hesaplanır (özet istatistikler hariç).
                </p>
            @endif
        </div>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th style="width:36px">
                            <input type="checkbox" id="agency-select-all-tickets" title="Tümünü seç (bilet)">
                        </th>
                        <th>Tarih</th>
                        <th>Başlık</th>
                        <th>Tür</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>Ödeme</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="agency-accounting-table-body">
                    @php $agencyRenderedTicketIds = []; @endphp
                    @forelse($transactions as $t)
                    <tr class="agency-acct-row {{ $t->is_settled ? 'settlement-done-row' : '' }}"
                        data-ticket-id="{{ $t->ticket ? $t->ticket->id : '' }}"
                        data-date="{{ $t->transaction_date->format('Y-m-d') }}"
                        data-type="{{ $t->type }}"
                        data-amount="{{ (float) $t->amount }}"
                        data-currency="{{ strtoupper($t->currency ?? 'TRY') }}"
                        data-settled="{{ $t->is_settled ? '1' : '0' }}">
                        <td>
                            @php
                                $agTicketId = $t->ticket ? (int) $t->ticket->id : 0;
                                $agFirstTicketRow = $agTicketId > 0 && !isset($agencyRenderedTicketIds[$agTicketId]);
                                if ($agFirstTicketRow) {
                                    $agencyRenderedTicketIds[$agTicketId] = true;
                                }
                            @endphp
                            @if($agFirstTicketRow && !$t->is_settled)
                                <input type="checkbox" class="agency-ticket-checkbox"
                                    data-ticket-id="{{ $agTicketId }}"
                                    data-ticket-tracking="{{ e($t->ticket->tracking_no ?? '') }}">
                            @endif
                        </td>
                        <td>{{ $t->transaction_date->format('d.m.Y') }}</td>
                        <td style="font-weight:500">
                            {{ $t->title }}
                            @if($t->ticket && $t->ticket->tracking_no)
                                <div class="ag-text-muted" style="font-size:11px;font-weight:400">Takip: {{ $t->ticket->tracking_no }}</div>
                            @endif
                        </td>
                        <td>
                            @if($t->type == 'income')
                            <span class="ag-badge ag-badge-success">Gelir</span>
                            @else
                            <span class="ag-badge ag-badge-danger">Gider</span>
                            @endif
                        </td>
                        <td style="font-weight:600">{{ number_format($t->amount, 2, ',', '.') }} {{ $t->currency }}</td>
                        <td>
                            @if($t->is_settled)
                                <span class="ag-badge ag-badge-info" title="Mutabakat: {{ optional($t->settled_at)->format('d.m.Y H:i') }}">Mutabakat Yapıldı</span>
                            @else
                                @php $statusMap = ['paid' => 'success', 'pending' => 'warning', 'cancelled' => 'secondary']; @endphp
                                <span class="ag-badge ag-badge-{{ $statusMap[$t->status] ?? 'secondary' }}">{{ ucfirst($t->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="ag-text-muted" style="font-size:12px">{{ $t->payment_method ?? '-' }}</span>
                        </td>
                        <td class="ag-text-right">
                            @if($t->ticket)
                            <a href="{{ route('agency.tickets.show', $t->ticket) }}" class="ag-btn ag-btn-ghost ag-btn-xs" title="Bileti Görüntüle">
                                <i data-lucide="eye"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="ag-empty">
                                <i data-lucide="receipt" class="ag-empty-icon"></i>
                                <div class="ag-empty-title">Kayıt bulunamadı</div>
                                <p class="ag-empty-text">Henüz işlem kaydınız bulunmuyor.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="ag-card-footer ag-flex ag-justify-between ag-items-center" style="flex-wrap:wrap;gap:12px">
        <div class="ag-flex ag-gap-2" style="font-size:13px">
            <span>Gelir: <strong class="ag-text-success">{{ number_format($totals['income'], 2, ',', '.') }} TRY</strong></span>
            <span>Gider: <strong class="ag-text-danger">{{ number_format($totals['expense'], 2, ',', '.') }} TRY</strong></span>
        </div>
        @if($transactions->hasPages())
        <div class="ag-pagination">
            @if($transactions->onFirstPage())
            <span class="ag-pagination-btn" style="opacity:0.5"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></span>
            @else
            <a href="{{ $transactions->previousPageUrl() }}" class="ag-pagination-btn"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></a>
            @endif
            @foreach($transactions->getUrlRange(max(1, $transactions->currentPage() - 2), min($transactions->lastPage(), $transactions->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}" class="ag-pagination-btn {{ $page == $transactions->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach
            @if($transactions->hasMorePages())
            <a href="{{ $transactions->nextPageUrl() }}" class="ag-pagination-btn"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></a>
            @else
            <span class="ag-pagination-btn" style="opacity:0.5"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></span>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Hesap Gör (mutabakat — kendi biletlerinde tek adımda tamamlanır) -->
<div class="modal fade" id="agencySettlementPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border:none;border-radius:var(--ag-radius);overflow:hidden">
            <div class="modal-header" style="background:var(--ag-sidebar);color:#fff;border:none">
                <h5 class="modal-title" style="font-weight:600">
                    <i data-lucide="calculator" style="width:20px;height:20px;margin-right:8px"></i>
                    Hesap Gör
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3" style="font-size:13px">
                    Kendi oluşturduğunuz biletlerde mutabakatı onayladığınızda işlem anında tamamlanır; admin tarafına ayrı talep gönderilmez.
                </div>
                <h6 class="mb-2">Para Birimi Özeti</h6>
                <div class="ag-table-wrapper mb-3">
                    <table class="ag-table">
                        <thead><tr><th>Para Birimi</th><th class="ag-text-right">Net (+/-)</th></tr></thead>
                        <tbody id="agency-settlement-currency-body">
                            <tr><td colspan="2" class="text-center ag-text-muted">Seçim bekleniyor</td></tr>
                        </tbody>
                    </table>
                </div>
                <h6 class="mb-2">Gün Bazlı TRY Karşılığı</h6>
                <div class="ag-table-wrapper mb-2">
                    <table class="ag-table">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Para Birimi</th>
                                <th class="ag-text-right">Net (+/-)</th>
                                <th class="ag-text-right">TRY</th>
                            </tr>
                        </thead>
                        <tbody id="agency-settlement-daily-body">
                            <tr><td colspan="4" class="text-center ag-text-muted">Seçim bekleniyor</td></tr>
                        </tbody>
                    </table>
                </div>
                <span class="ag-badge ag-badge-success p-2" id="agency-settlement-total-try">Toplam TRY: 0,00</span>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ag-border)">
                <button type="button" class="ag-btn ag-btn-secondary ag-btn-sm" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="ag-btn ag-btn-success ag-btn-sm" id="agency-settlement-confirm-btn" style="position:relative;z-index:1065">
                    <i data-lucide="check"></i> Onayla
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Chart Modal -->
<div class="modal fade" id="currencyChartModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:var(--ag-radius);overflow:hidden">
            <div class="modal-header" style="background:var(--ag-sidebar);color:#fff;border:none">
                <h5 class="modal-title" style="font-weight:600">
                    <i data-lucide="trending-up" style="width:20px;height:20px;margin-right:8px"></i>
                    <span id="chart-currency-name">Para Birimi</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div class="btn-group btn-group-sm" id="chart-period-buttons">
                        <button type="button" class="btn btn-outline-secondary chart-period" data-period="7days">7 Gün</button>
                        <button type="button" class="btn btn-outline-secondary chart-period" data-period="30days">30 Gün</button>
                        <button type="button" class="btn btn-outline-secondary chart-period" data-period="3months">3 Ay</button>
                        <button type="button" class="btn btn-outline-secondary chart-period" data-period="6months">6 Ay</button>
                        <button type="button" class="btn btn-secondary chart-period active" data-period="12months">12 Ay</button>
                    </div>
                    <div class="btn-group btn-group-sm" id="chart-type-buttons">
                        <button type="button" class="btn btn-primary chart-type active" data-type="line"><i data-lucide="trending-up" style="width:14px;height:14px"></i></button>
                        <button type="button" class="btn btn-outline-primary chart-type" data-type="bar"><i data-lucide="bar-chart-2" style="width:14px;height:14px"></i></button>
                    </div>
                </div>
                <div id="chart-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 ag-text-muted">Grafik yükleniyor...</p>
                </div>
                <div id="chart-container" style="display:none;height:300px">
                    <canvas id="currencyChart"></canvas>
                </div>
                <div id="chart-error" class="text-center py-5 ag-text-danger" style="display:none">
                    <i data-lucide="alert-circle" style="width:48px;height:48px;margin-bottom:8px"></i>
                    <p>Grafik yüklenirken bir hata oluştu.</p>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ag-border)">
                <small class="ag-text-muted me-auto" id="chart-period-info">Son 12 aylık veriler</small>
                <button type="button" class="ag-btn ag-btn-secondary ag-btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
.currency-card {
    background: var(--ag-card);
    border: 1px solid var(--ag-border);
    border-radius: var(--ag-radius);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}
.currency-card:hover {
    box-shadow: var(--ag-shadow-lg);
    transform: translateY(-2px);
}
.currency-header {
    background: var(--ag-sidebar);
    color: #fff;
    padding: 12px 14px;
    display: flex;
    align-items: center;
}
.currency-code {
    font-weight: 700;
    font-size: 15px;
    letter-spacing: 0.5px;
}
.currency-body {
    padding: 14px;
}
.currency-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid var(--ag-border);
}
.currency-row:last-child {
    border-bottom: none;
}
.currency-row.currency-net {
    margin-top: 4px;
    padding-top: 10px;
    border-top: 2px solid var(--ag-border);
    border-bottom: none;
}
.currency-label {
    font-size: 12px;
    color: var(--ag-text-muted);
}
.currency-value {
    font-size: 14px;
    font-weight: 600;
}
.currency-net .currency-value {
    font-size: 15px;
}
.settlement-locked-row {
    opacity: 0.72;
}
.settlement-done-row {
    opacity: 0.65;
    background-color: rgba(var(--ag-primary-rgb, 0,91,187), 0.04) !important;
}
.settlement-done-row td {
    text-decoration: line-through;
    text-decoration-color: rgba(0,0,0,0.25);
}
.settlement-done-row td:nth-child(6),
.settlement-done-row td:last-child {
    text-decoration: none;
}
.ag-accounting-filters-card {
    padding: 1rem 1.25rem 1.25rem;
    background: rgba(0, 0, 0, 0.02);
}
.ag-accounting-filter-grid {
    display: grid;
    gap: 0.85rem 1rem;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
}
.ag-accounting-filter-field--wide {
    grid-column: 1 / -1;
}
@media (min-width: 768px) {
    .ag-accounting-filter-field--wide {
        grid-column: span 2;
    }
}
@media (min-width: 1100px) {
    .ag-accounting-filter-field--wide {
        grid-column: span 3;
    }
}
.ag-accounting-filter-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--ag-text-muted);
    margin-bottom: 4px;
}
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    
    let currencyChart = null;
    let currentChartCurrency = null;
    let currentChartPeriod = '12months';
    let currentChartType = 'line';
    let currentChartData = null;
    
    const chartModal = document.getElementById('currencyChartModal');
    const bsModal = new bootstrap.Modal(chartModal);
    const chartContainer = document.getElementById('chart-container');
    const chartLoading = document.getElementById('chart-loading');
    const chartError = document.getElementById('chart-error');
    const chartCanvas = document.getElementById('currencyChart');
    const chartTitle = document.getElementById('chart-currency-name');
    const chartPeriodInfo = document.getElementById('chart-period-info');
    const chartDataUrl = '{{ route("agency.accounting.chart-data") }}';
    
    const periodLabels = {
        '7days': 'Son 7 gün',
        '30days': 'Son 30 gün',
        '3months': 'Son 3 ay',
        '6months': 'Son 6 ay',
        '12months': 'Son 12 ay'
    };
    
    document.querySelectorAll('.currency-card[data-currency]').forEach(card => {
        card.addEventListener('click', function() {
            currentChartCurrency = this.dataset.currency;
            chartTitle.textContent = this.dataset.currency + ' - ' + this.dataset.name;
            currentChartPeriod = '12months';
            currentChartType = 'line';
            updateButtons();
            bsModal.show();
            loadChartData(currentChartCurrency, currentChartPeriod);
        });
    });
    
    document.querySelectorAll('.chart-period').forEach(btn => {
        btn.addEventListener('click', function() {
            currentChartPeriod = this.dataset.period;
            updateButtons();
            if (currentChartCurrency) loadChartData(currentChartCurrency, currentChartPeriod);
        });
    });
    
    document.querySelectorAll('.chart-type').forEach(btn => {
        btn.addEventListener('click', function() {
            currentChartType = this.dataset.type;
            updateButtons();
            if (currentChartData) renderChart(currentChartData);
        });
    });
    
    function updateButtons() {
        document.querySelectorAll('.chart-period').forEach(btn => {
            btn.classList.toggle('btn-secondary', btn.dataset.period === currentChartPeriod);
            btn.classList.toggle('btn-outline-secondary', btn.dataset.period !== currentChartPeriod);
            btn.classList.toggle('active', btn.dataset.period === currentChartPeriod);
        });
        document.querySelectorAll('.chart-type').forEach(btn => {
            btn.classList.toggle('btn-primary', btn.dataset.type === currentChartType);
            btn.classList.toggle('btn-outline-primary', btn.dataset.type !== currentChartType);
            btn.classList.toggle('active', btn.dataset.type === currentChartType);
        });
        chartPeriodInfo.textContent = periodLabels[currentChartPeriod];
    }
    
    async function loadChartData(currency, period) {
        chartLoading.style.display = 'block';
        chartContainer.style.display = 'none';
        chartError.style.display = 'none';
        
        try {
            const response = await fetch(`${chartDataUrl}?currency=${currency}&period=${period}`);
            if (!response.ok) throw new Error('Error');
            const data = await response.json();
            currentChartData = data;
            renderChart(data);
            chartLoading.style.display = 'none';
            chartContainer.style.display = 'block';
        } catch (e) {
            chartLoading.style.display = 'none';
            chartError.style.display = 'block';
        }
    }
    
    function renderChart(data) {
        if (currencyChart) currencyChart.destroy();
        
        const ctx = chartCanvas.getContext('2d');
        
        currencyChart = new Chart(ctx, {
            type: currentChartType,
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Gelir',
                        data: data.income,
                        borderColor: '#10b981',
                        backgroundColor: currentChartType === 'bar' ? 'rgba(16, 185, 129, 0.8)' : 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: currentChartType !== 'bar',
                        tension: 0.4
                    },
                    {
                        label: 'Gider',
                        data: data.expense,
                        borderColor: '#ef4444',
                        backgroundColor: currentChartType === 'bar' ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: currentChartType !== 'bar',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('tr-TR', {minimumFractionDigits: 2}) + ' ' + data.currency;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(v) { return v.toLocaleString('tr-TR') + ' ' + data.currency; }
                        }
                    }
                }
            }
        });
    }
    
    chartModal.addEventListener('hidden.bs.modal', function() {
        if (currencyChart) { currencyChart.destroy(); currencyChart = null; }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('agency-accounting-table-body');
    if (!tableBody) return;

    const selectAll = document.getElementById('agency-select-all-tickets');
    const openBtn = document.getElementById('agency-open-settlement-preview');
    const selectedCountEl = document.getElementById('agency-selected-count');
    const statusEl = document.getElementById('agency-settlement-status');
    const modalEl = document.getElementById('agencySettlementPreviewModal');
    const curBody = document.getElementById('agency-settlement-currency-body');
    const dailyBody = document.getElementById('agency-settlement-daily-body');
    const totalTryEl = document.getElementById('agency-settlement-total-try');
    const confirmBtn = document.getElementById('agency-settlement-confirm-btn');
    const settlementSubmitUrl = '{{ route("agency.accounting.settlements.submit", [], false) }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const tcmbRates = (() => {
        const map = { TRY: 1 };
        const ratesPayload = {!! json_encode($exchangeRates['items'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        ratesPayload.forEach(item => {
            const code = String(item.code || '').toUpperCase();
            if (code) map[code] = Number(item.buy || 0);
        });
        return map;
    })();

    const selectedTickets = new Map();
    let currentTransactions = [];

    const modalInstance = (window.bootstrap && modalEl)
        ? window.bootstrap.Modal.getOrCreateInstance(modalEl)
        : null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getSignedAmount(type, amount) {
        const raw = Number(amount || 0);
        return type === 'expense' ? -raw : raw;
    }

    function syncCurrentTransactionsFromTable() {
        currentTransactions = Array.from(tableBody.querySelectorAll('tr.agency-acct-row[data-date]')).map(row => ({
            date_iso: row.dataset.date || '',
            type: row.dataset.type || 'income',
            amount_raw: Number(row.dataset.amount || 0),
            currency: String(row.dataset.currency || 'TRY').toUpperCase(),
            ticket_id: row.dataset.ticketId ? Number(row.dataset.ticketId) : null,
        }));
    }

    function bindCheckboxEvents() {
        tableBody.querySelectorAll('.agency-ticket-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const id = String(this.dataset.ticketId || '');
                if (!id) return;
                if (this.checked) {
                    selectedTickets.set(id, { id, tracking: this.dataset.ticketTracking || '' });
                } else {
                    selectedTickets.delete(id);
                }
                refreshSelectionUI();
            });
        });
    }

    function refreshSelectionUI() {
        const n = selectedTickets.size;
        selectedCountEl.textContent = String(n);
        openBtn.classList.toggle('d-none', n === 0);
        const boxes = tableBody.querySelectorAll('.agency-ticket-checkbox');
        const allOn = boxes.length > 0 && Array.from(boxes).every(b => b.checked);
        if (selectAll) {
            selectAll.checked = allOn;
            selectAll.indeterminate = !allOn && Array.from(boxes).some(b => b.checked);
        }
        if (window.lucide) lucide.createIcons();
    }

    function renderSettlementPreview() {
        if (selectedTickets.size === 0) {
            curBody.innerHTML = '<tr><td colspan="2" class="text-center ag-text-muted">Seçim bekleniyor</td></tr>';
            dailyBody.innerHTML = '<tr><td colspan="4" class="text-center ag-text-muted">Seçim bekleniyor</td></tr>';
            totalTryEl.textContent = 'Toplam TRY: 0,00';
            return;
        }
        const byCurrency = {};
        const byDateCurrency = {};
        let totalTry = 0;
        const sel = new Set(Array.from(selectedTickets.keys()));

        currentTransactions.forEach(tx => {
            const tid = tx.ticket_id ? String(tx.ticket_id) : '';
            if (!tid || !sel.has(tid)) return;
            const signed = getSignedAmount(tx.type, tx.amount_raw);
            const c = (tx.currency || 'TRY').toUpperCase();
            const date = tx.date_iso || '-';
            const rate = Number(tcmbRates[c] || (c === 'TRY' ? 1 : 0));
            const tryAmt = signed * rate;
            byCurrency[c] = (byCurrency[c] || 0) + signed;
            const key = `${date}|${c}`;
            if (!byDateCurrency[key]) byDateCurrency[key] = { date, currency: c, amount: 0, tryAmount: 0 };
            byDateCurrency[key].amount += signed;
            byDateCurrency[key].tryAmount += tryAmt;
            totalTry += tryAmt;
        });

        curBody.innerHTML = Object.entries(byCurrency).sort((a, b) => a[0].localeCompare(b[0]))
            .map(([cur, amt]) => `
                <tr>
                    <td>${escapeHtml(cur)}</td>
                    <td class="ag-text-right ${amt >= 0 ? 'ag-text-success' : 'ag-text-danger'}">${amt.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>`).join('');

        dailyBody.innerHTML = Object.values(byDateCurrency)
            .sort((a, b) => (a.date === b.date ? a.currency.localeCompare(b.currency) : a.date.localeCompare(b.date)))
            .map(row => `
                <tr>
                    <td>${escapeHtml(row.date)}</td>
                    <td>${escapeHtml(row.currency)}</td>
                    <td class="ag-text-right ${row.amount >= 0 ? 'ag-text-success' : 'ag-text-danger'}">${row.amount.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="ag-text-right ${row.tryAmount >= 0 ? 'ag-text-success' : 'ag-text-danger'}">${row.tryAmount.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>`).join('');

        totalTryEl.textContent = `Toplam TRY: ${totalTry.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function showSettlementStatus(msg, isError) {
        if (!statusEl) return;
        statusEl.textContent = msg;
        statusEl.classList.toggle('ag-text-danger', !!isError);
        statusEl.classList.toggle('ag-text-muted', !isError);
    }

    async function submitAgencySettlement() {
        const checked = Array.from(tableBody.querySelectorAll('.agency-ticket-checkbox:checked'));
        let rows = checked;
        if (rows.length === 0 && selectedTickets.size > 0) {
            rows = Array.from(tableBody.querySelectorAll('.agency-ticket-checkbox')).filter(cb => {
                const id = String(cb.dataset.ticketId || '');
                return id && selectedTickets.has(id);
            });
        }
        if (rows.length === 0) {
            showSettlementStatus('En az bir bilet seçin.', true);
            return;
        }
        const ticketIds = rows.map(cb => Number(cb.dataset.ticketId || 0)).filter(id => id > 0);

        try {
            confirmBtn.disabled = true;
            showSettlementStatus('Mutabakat uygulanıyor...', false);
            const response = await fetch(settlementSubmitUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ ticket_ids: ticketIds }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'İşlem başarısız.');
            }
            if (data.immediate_settlement_applied) {
                if (modalInstance) modalInstance.hide();
                showSettlementStatus(data.message || 'Tamamlandı.', false);
                window.location.reload();
                return;
            }
            showSettlementStatus(data.message || 'Tamam.', false);
        } catch (e) {
            showSettlementStatus(e.message || 'Hata oluştu.', true);
        } finally {
            confirmBtn.disabled = false;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const on = this.checked;
            tableBody.querySelectorAll('.agency-ticket-checkbox').forEach(cb => {
                cb.checked = on;
                cb.dispatchEvent(new Event('change'));
            });
        });
    }

    if (openBtn) {
        openBtn.addEventListener('click', function() {
            syncCurrentTransactionsFromTable();
            renderSettlementPreview();
            if (modalInstance) modalInstance.show();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitAgencySettlement();
        });
    }

    const agFilterCollapseEl = document.getElementById('agencyAccountingFiltersCollapse');
    if (agFilterCollapseEl && window.lucide) {
        agFilterCollapseEl.addEventListener('shown.bs.collapse', () => lucide.createIcons());
    }

    bindCheckboxEvents();
    syncCurrentTransactionsFromTable();
    refreshSelectionUI();
});
</script>
@endpush
