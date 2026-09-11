@extends('layouts.admin')

@section('title', __('Muhasebe Yönetimi'))

@section('content')
<!-- Modern Kontrol Paneli -->
<div class="guides-control-panel ad-page-header admin-list-toolbar mb-3">
    <div class="control-left">
        <h4 class="control-title"><i class="fas fa-wallet"></i> {{ __('Muhasebe Yönetimi') }}</h4>
        <p class="control-subtitle">
            @if(!empty($selectedAgency))
                {{ __(':agency acentasına ait finansal kayıtlar', ['agency' => $selectedAgency->name]) }}
            @else
                {{ __('Finansal kayıtlar') }}
            @endif
        </p>
    </div>
    <div class="control-right">
        @if(!empty($selectedAgency))
        <div class="control-item">
            <a href="{{ route('admin.accounting.index') }}" class="btn btn-sm btn-outline-light">
                <i class="fas fa-list"></i> {{ __('Genel Muhasebeye Dön') }}
            </a>
        </div>
        @endif
        <div class="control-item">
            <a href="{{ route('admin.accounting.create', request('locked_agency_id') ? ['locked_agency_id' => request('locked_agency_id')] : []) }}" class="btn btn-sm btn-light">
                <i class="fas fa-plus"></i> {{ __('Yeni Kayıt') }}
            </a>
        </div>
    </div>
</div>
<div class="row">
    @php
        $codes = [
            'USD' => __('ABD Doları'),
            'EUR' => __('Euro'),
            'GBP' => __('İngiliz Sterlini'),
            'RUB' => __('Rus Rublesi'),
            'TRY' => __('Türk Lirası'),
        ];
    @endphp
    <div class="col-12 mb-3">
        <div class="d-flex flex-wrap align-items-stretch gap-3">
            @foreach($codes as $code => $name)
                @php
                    $summary = $currencySummary[$code] ?? ['income' => 0, 'expense' => 0, 'net' => 0];
                    $income = $summary['income'] ?? 0;
                    $expense = $summary['expense'] ?? 0;
                    $net = $summary['net'] ?? 0;
                @endphp
                <div class="currency-card mr-3 mb-3" data-currency="{{ $code }}" data-name="{{ $name }}" style="cursor: pointer;" title="{{ __('Grafik için tıklayın') }}">
                    <div class="currency-header">
                        <span class="currency-code">{{ $code }}</span>
                        <small class="currency-name">{{ $name }}</small>
                        <i class="fas fa-chart-line ml-auto" style="opacity: 0.7;"></i>
                    </div>
                    <div class="currency-body">
                        <div class="currency-row">
                            <span class="currency-label">{{ __('Gelir') }}</span>
                            <span class="currency-value text-success" data-role="income-value">{{ number_format($income, 2, ',', '.') }}</span>
                        </div>
                        <div class="currency-row">
                            <span class="currency-label">{{ __('Gider') }}</span>
                            <span class="currency-value text-danger" data-role="expense-value">{{ number_format($expense, 2, ',', '.') }}</span>
                        </div>
                        <div class="currency-row currency-net">
                            <span class="currency-label">Net</span>
                            <span class="currency-value {{ $net >= 0 ? 'text-success' : 'text-danger' }}" data-role="net-value">{{ number_format($net, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @php
        $adminFilterPanelOpen = collect(['search', 'type', 'status', 'currency', 'payment_method', 'agency_id', 'from', 'to'])
            ->contains(fn ($k) => request()->filled($k));
    @endphp
    <div class="col-12">
        <!-- Liste: filtreler listenin hemen üstünde, varsayılan kapalı -->
        <div class="ad-card mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0"><i class="fas fa-list"></i> {{ __('İşlem Listesi') }}</h3>
                <div class="d-flex align-items-center flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-1" data-bs-toggle="collapse" data-bs-target="#adminAccountingFilters" aria-expanded="{{ $adminFilterPanelOpen ? 'true' : 'false' }}" aria-controls="adminAccountingFilters">
                        <i class="fas fa-filter"></i> {{ __('Filtreler') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-primary mr-2 mb-1 d-none" id="open-settlement-preview">
                        <i class="fas fa-calculator"></i> {{ __('Hesap Gör') }} (<span id="selected-count">0</span>)
                    </button>
                    <span class="badge badge-secondary mb-1" id="total-count-badge">{{ __(':count kayıt', ['count' => $transactions->total()]) }}</span>
                </div>
            </div>
            <div id="adminAccountingFilters" class="collapse {{ $adminFilterPanelOpen ? 'show' : '' }} border-bottom">
                <div class="card-body py-3 bg-light">
                <div class="row">
                    <!-- Arama -->
                    <div class="col-md-4 col-sm-12 mb-3">
                        <label class="small text-muted mb-1"><i class="fas fa-search"></i> {{ __('Arama (başlık / takip no)') }}</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="live-search"
                                   value="{{ request('search') }}" placeholder="{{ __('Başlık veya bilet takip numarası...') }}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="clear-search" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-muted"><i class="fas fa-info-circle"></i> {{ __('Sayfa yenilenmeden arar') }}</small>
                    </div>

                    <!-- Tür -->
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1">{{ __('Tür') }}</label>
                        <select class="form-control form-control-sm filter-select" id="filter-type">
                            <option value="">{{ __('Tümü') }}</option>
                            <option value="income" {{ request('type')=='income'?'selected':'' }}>{{ __('Gelir') }}</option>
                            <option value="expense" {{ request('type')=='expense'?'selected':'' }}>{{ __('Gider') }}</option>
                        </select>
                    </div>

                    <!-- Durum -->
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1">{{ __('Durum') }}</label>
                        <select class="form-control form-control-sm filter-select" id="filter-status">
                            <option value="">{{ __('Tümü') }}</option>
                            <option value="paid" {{ request('status')=='paid'?'selected':'' }}>{{ __('Ödendi') }}</option>
                            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>{{ __('Beklemede') }}</option>
                            <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>{{ __('İptal') }}</option>
                            <option value="settled" {{ request('status')=='settled'?'selected':'' }}>{{ __('Mutabakat Yapıldı') }}</option>
                        </select>
                    </div>

                    <!-- Para Birimi -->
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1">{{ __('Para Birimi') }}</label>
                        <select class="form-control form-control-sm filter-select" id="filter-currency">
                            <option value="">{{ __('Tümü') }}</option>
                            @foreach($currencies ?? [] as $cur)
                                <option value="{{ $cur }}" {{ request('currency')==$cur?'selected':'' }}>{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ödeme Yöntemi -->
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1">{{ __('Ödeme Yöntemi') }}</label>
                        <select class="form-control form-control-sm filter-select" id="filter-payment">
                            <option value="">{{ __('Tümü') }}</option>
                            @php
                                $methodLabels = [
                                    'auto-expired-ticket' => __('Otomatik (Bilet)'),
                                    'sale-ticket' => __('Bilet Satışı'),
                                    'payout-owner' => __('Tur Sahibi Payı'),
                                    'owner-share' => __('Acenta Satışı'),
                                    'salary-auto' => __('Maaş Ödemesi'),
                                    'rest-adjustment' => __('Rest Geliri'),
                                    'cash' => __('Nakit'),
                                    'bank' => __('Banka'),
                                    'credit_card' => __('Kredi Kartı'),
                                ];
                            @endphp
                            @foreach($paymentMethods ?? [] as $method)
                                <option value="{{ $method }}" {{ request('payment_method')==$method?'selected':'' }}>
                                    {{ $methodLabels[$method] ?? $method }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Acenta Filtresi -->
                    @if(count($agencies ?? []) > 0)
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1"><i class="fas fa-store"></i> {{ __('Acenta') }}</label>
                        <select class="form-control form-control-sm filter-select" id="filter-agency" {{ !empty(request('locked_agency_id')) ? 'disabled' : '' }}>
                            <option value="">{{ __('Tüm Acentalar') }}</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ ((string) request('agency_id') === (string) $agency->id || (string) request('locked_agency_id') === (string) $agency->id) ? 'selected' : '' }}>
                                    {{ $agency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div class="row">
                    <!-- Başlangıç Tarihi -->
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1">{{ __('Başlangıç') }}</label>
                        <input type="date" class="form-control form-control-sm filter-input" id="filter-from" value="{{ request('from') }}">
                    </div>

                    <!-- Bitiş Tarihi -->
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="small text-muted mb-1">{{ __('Bitiş') }}</label>
                        <input type="date" class="form-control form-control-sm filter-input" id="filter-to" value="{{ request('to') }}">
                    </div>

                    <!-- Hızlı Tarih Butonları -->
                    <div class="col-md-4 col-sm-12 mb-2">
                        <label class="small text-muted mb-1">{{ __('Hızlı Seçim') }}</label>
                        <div class="btn-group btn-group-sm d-flex" role="group">
                            <button type="button" class="btn btn-outline-secondary quick-date" data-range="today">{{ __('Bugün') }}</button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-range="week">{{ __('Bu Hafta') }}</button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-range="month">{{ __('Bu Ay') }}</button>
                            <button type="button" class="btn btn-outline-secondary quick-date" data-range="year">{{ __('Bu Yıl') }}</button>
                        </div>
                    </div>

                    <!-- Temizle Butonu -->
                    <div class="col-md-4 col-sm-12 mb-2">
                        <label class="small text-muted mb-1">&nbsp;</label>
                        <div class="d-flex">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="clear-all-filters">
                                <i class="fas fa-times"></i> {{ __('Tüm Filtreleri Temizle') }}
                            </button>
                            <span class="ml-2 align-self-center text-muted small" id="filter-status-text"></span>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="min-height: 200px;">
                <!-- Loading overlay -->
                <div id="loading-overlay" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; justify-content: center; align-items: center;">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 mb-0">{{ __('Aranıyor...') }}</p>
                    </div>
                </div>
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th style="width:36px;">
                                <input type="checkbox" id="select-all-transactions" title="{{ __('Tümünü seç') }}">
                            </th>
                            <th>{{ __('Tarih') }}</th>
                            <th>{{ __('Başlık') }}</th>
                            <th>{{ __('Tür') }}</th>
                            <th>{{ __('Tutar') }}</th>
                            <th>{{ __('Para Birimi') }}</th>
                            <th>{{ __('Durum') }}</th>
                            <th>{{ __('Ödeme Yöntemi') }}</th>
                            <th>{{ __('İşlemler') }}</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body">
                        @php
                            $renderedTicketIds = [];
                        @endphp
                        @forelse($transactions as $t)
                        <tr data-ticket-id="{{ $t->ticket ? $t->ticket->id : '' }}"
                            data-date="{{ $t->transaction_date->format('Y-m-d') }}"
                            data-type="{{ $t->type }}"
                            data-amount="{{ (float) $t->amount }}"
                            data-currency="{{ strtoupper($t->currency ?? 'TRY') }}"
                            data-settled="{{ $t->is_settled ? '1' : '0' }}"
                            @if($t->is_settled) class="settlement-done-row" @endif>
                            <td>
                                @php
                                    $ticketId = $t->ticket ? (int) $t->ticket->id : 0;
                                    $isFirstTicketRow = $ticketId > 0 && !isset($renderedTicketIds[$ticketId]);
                                    if ($isFirstTicketRow) {
                                        $renderedTicketIds[$ticketId] = true;
                                    }
                                @endphp
                                @if($isFirstTicketRow && !$t->is_settled)
                                    <input type="checkbox"
                                           class="ticket-checkbox"
                                           data-ticket-id="{{ $ticketId }}"
                                           data-ticket-tracking="{{ e($t->ticket->tracking_no ?? '') }}">
                                @endif
                            </td>
                            <td>{{ $t->transaction_date->format('d.m.Y') }}</td>
                            <td>
                                {{ $t->title }}
                                @if($t->ticket && $t->ticket->tracking_no)
                                    <br><small class="text-muted">{{ __('Takip') }}: {{ $t->ticket->tracking_no }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $t->type=='income'?'success':'danger' }}">
                                    {{ $t->type=='income'? __('Gelir') : __('Gider') }}
                                </span>
                            </td>
                            <td>{{ number_format($t->amount,2,',','.') }}</td>
                            <td>{{ $t->currency }}</td>
                            <td>
                                @if($t->is_settled)
                                    <span class="badge badge-info" title="{{ __('Mutabakat') }}: {{ optional($t->settled_at)->format('d.m.Y H:i') }}">{{ __('Mutabakat Yapıldı') }}</span>
                                @else
                                    @php $map=['paid'=>'success','pending'=>'warning','cancelled'=>'secondary']; @endphp
                                    <span class="badge badge-{{ $map[$t->status] ?? 'secondary' }}">{{ ucfirst($t->status) }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $t->payment_method ?? '-' }}
                                @if($t->payment_method === 'salary-auto')
                                    <br><small class="text-muted">{{ __('Maaş ödemesi') }}</small>
                                @endif
                            </td>
                            <td>
                                @php $isAuto = in_array($t->payment_method, ['auto-expired-ticket','salary-auto','sale-ticket','payout-owner','owner-share','rest-adjustment']); @endphp
                                <div class="btn-group">
                                    @if($isAuto && $t->ticket)
                                        <a href="{{ route('admin.tickets.show', $t->ticket) }}" class="btn btn-sm btn-info" title="{{ __('Bileti Görüntüle') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @elseif($t->payment_method === 'salary-auto')
                                        <button class="btn btn-sm btn-secondary" disabled title="{{ __('Maaş ödemesi') }}">
                                            <i class="fas fa-money-check-alt"></i>
                                        </button>
                                    @elseif(!$isAuto)
                                        <a href="{{ route('admin.accounting.edit', ['transaction' => $t, 'locked_agency_id' => request('locked_agency_id')]) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.accounting.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm({!! json_encode(__('Silinsin mi?'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!})">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4">{{ __('Kayıt yok') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div id="totals-display">
                    <span class="mr-3">{{ __('Görünen') }}: <strong id="visible-count">{{ $transactions->total() }}</strong> {{ __('kayıt') }}</span>
                    <span class="mr-3">{{ __('Gelir') }}: <strong class="text-success" id="visible-income">{{ number_format($totals['income'],2,',','.') }}</strong></span>
                    <span>{{ __('Gider') }}: <strong class="text-danger" id="visible-expense">{{ number_format($totals['expense'],2,',','.') }}</strong></span>
                </div>
                <div id="pagination-container">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Hesap Gör Modal -->
<div class="modal fade" id="settlementPreviewModal" tabindex="-1" aria-labelledby="settlementPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="settlementPreviewModalLabel">
                    <i class="fas fa-calculator"></i> {{ __('Hesap Gör') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Kapat') }}"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    {{ __('Seçili kayıtların para birimi bazlı net toplamı ve TRY karşılığı ön izleme olarak gösterilir.') }}
                </div>

                <h6 class="mb-2">{{ __('Para Birimi Özeti') }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-3">
                        <thead>
                            <tr>
                                <th>{{ __('Para Birimi') }}</th>
                                <th class="text-right">Net (+/-)</th>
                            </tr>
                        </thead>
                        <tbody id="settlement-currency-summary-body">
                            <tr><td colspan="2" class="text-center text-muted">{{ __('Seçim bekleniyor') }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="mb-2">{{ __('Gün Bazlı TRY Karşılığı') }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-2">
                        <thead>
                            <tr>
                                <th>{{ __('Tarih') }}</th>
                                <th>{{ __('Para Birimi') }}</th>
                                <th class="text-right">Net (+/-)</th>
                                <th class="text-right">{{ __('TRY Karşılığı') }}</th>
                            </tr>
                        </thead>
                        <tbody id="settlement-daily-summary-body">
                            <tr><td colspan="4" class="text-center text-muted">{{ __('Seçim bekleniyor') }}</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    <span class="badge badge-success p-2" id="settlement-total-try">{{ __('Toplam TRY') }}: 0,00</span>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto">{{ __('Onay sonrası karşı taraf onayı beklenir.') }}</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Kapat') }}</button>
                <button type="button" class="btn btn-success" id="settlement-confirm-btn" style="position:relative; z-index:1065; pointer-events:auto;">
                    <i class="fas fa-check"></i> {{ __('Onayla') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Modal -->
<div class="modal fade" id="currencyChartModal" tabindex="-1" role="dialog" aria-labelledby="currencyChartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="currencyChartModalLabel">
                    <i class="fas fa-chart-line"></i> <span id="chart-currency-name">{{ __('Para Birimi') }}</span> - {{ __('Gelir/Gider Grafiği') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('Kapat') }}"></button>
            </div>
            <div class="modal-body">
                <!-- Zaman Dilimleri ve Grafik Türü -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <!-- Zaman Dilimleri -->
                    <div class="btn-group btn-group-sm" role="group" id="chart-period-buttons">
                        <button type="button" class="btn btn-outline-primary chart-period" data-period="7days">{{ __(':count Gün', ['count' => 7]) }}</button>
                        <button type="button" class="btn btn-outline-primary chart-period" data-period="30days">{{ __(':count Gün', ['count' => 30]) }}</button>
                        <button type="button" class="btn btn-outline-primary chart-period" data-period="3months">{{ __(':count Ay', ['count' => 3]) }}</button>
                        <button type="button" class="btn btn-outline-primary chart-period" data-period="6months">{{ __(':count Ay', ['count' => 6]) }}</button>
                        <button type="button" class="btn btn-primary chart-period active" data-period="12months">{{ __(':count Ay', ['count' => 12]) }}</button>
                    </div>

                    <!-- Grafik Türü -->
                    <div class="btn-group btn-group-sm" role="group" id="chart-type-buttons">
                        <button type="button" class="btn btn-success chart-type active" data-type="line" title="{{ __('Çizgi Grafik') }}">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success chart-type" data-type="bar" title="{{ __('Çubuk Grafik') }}">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success chart-type" data-type="area" title="{{ __('Alan Grafik') }}">
                            <i class="fas fa-chart-area"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success chart-type" data-type="radar" title="{{ __('Radar Grafik') }}">
                            <i class="fas fa-bullseye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success chart-type" data-type="polarArea" title="{{ __('Polar Alan') }}">
                            <i class="fas fa-circle-notch"></i>
                        </button>
                    </div>
                </div>

                <div id="chart-loading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">{{ __('Grafik yükleniyor...') }}</p>
                </div>
                <div id="chart-container" style="display: none; position: relative; height: 350px;">
                    <canvas id="currencyChart"></canvas>
                </div>
                <div id="chart-error" class="text-center py-5 text-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <p class="mt-2">{{ __('Grafik yüklenirken bir hata oluştu.') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted mr-auto" id="chart-period-info"><i class="fas fa-info-circle"></i> {{ __('Son 12 aylık veriler') }}</small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Kapat') }}</button>
            </div>
        </div>
    </div>
</div>
@stop
<!-- end of the code -->

@push('css')
<style>
.currency-card { 
    min-width: 200px; 
    flex: 1;
    max-width: 220px;
    background:#fff; 
    border:1px solid #e9ecef; 
    border-radius:12px; 
    box-shadow:0 2px 8px rgba(0,0,0,0.05); 
    overflow:hidden; 
    transition: all 0.3s ease;
}
.currency-card:hover {
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.currency-header { 
    background: linear-gradient(135deg, #002b5c 0%, #004080 100%);
    color:#fff; 
    padding:10px 14px; 
    display:flex; 
    align-items:center; 
    gap:8px; 
}
.currency-code { 
    font-weight:700; 
    font-size:18px; 
    letter-spacing:1px; 
}
.currency-name { 
    opacity:.85; 
    font-size:11px;
}
.currency-body { 
    padding:14px; 
}
.currency-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #f1f1f1;
}
.currency-row:last-child {
    border-bottom: none;
}
.currency-row.currency-net {
    margin-top: 4px;
    padding-top: 10px;
    border-top: 2px solid #e9ecef;
    border-bottom: none;
}
.currency-label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
}
.currency-value { 
    font-size: 15px; 
    font-weight: 700; 
}
.currency-net .currency-label {
    font-weight: 600;
    color: #495057;
}
.currency-net .currency-value {
    font-size: 17px;
}
.gap-3 { 
    gap:12px; 
}
/* Grafik Modal */
#currencyChartModal .modal-header {
    background: linear-gradient(135deg, #002b5c 0%, #004080 100%);
}
#currencyChartModal .modal-title {
    font-weight: 600;
}
#chart-container {
    min-height: 350px;
}
#chart-period-buttons .btn,
#chart-type-buttons .btn {
    font-size: 13px;
    padding: 6px 12px;
    border-radius: 0;
}
#chart-period-buttons .btn:first-child,
#chart-type-buttons .btn:first-child {
    border-radius: 4px 0 0 4px;
}
#chart-period-buttons .btn:last-child,
#chart-type-buttons .btn:last-child {
    border-radius: 0 4px 4px 0;
}
#chart-period-buttons .btn.active,
#chart-type-buttons .btn.active {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}
#chart-type-buttons .btn i {
    font-size: 14px;
}
.currency-card[data-currency] {
    cursor: pointer;
    position: relative;
}
.currency-card[data-currency]:hover .currency-header {
    background: linear-gradient(135deg, #003d7a 0%, #0059b3 100%);
}
.currency-card[data-currency]:active {
    transform: translateY(0);
}

/* ============================================== */
/* DARK MODE STYLES FOR CURRENCY CARDS */
/* ============================================== */
html.dark-mode .currency-card {
    background: #1e293b !important;
    border-color: #334155 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}

html.dark-mode .currency-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.4) !important;
    border-color: #475569 !important;
}

html.dark-mode .currency-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    border-bottom: 1px solid #334155 !important;
}

html.dark-mode .currency-card[data-currency]:hover .currency-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
}

html.dark-mode .currency-body {
    background: #1e293b !important;
}

html.dark-mode .currency-row {
    border-bottom-color: #334155 !important;
}

html.dark-mode .currency-row.currency-net {
    border-top-color: #475569 !important;
}

html.dark-mode .currency-label {
    color: #94a3b8 !important;
}

html.dark-mode .currency-net .currency-label {
    color: #e2e8f0 !important;
}

.settlement-locked-row {
    opacity: 0.72;
}
.settlement-done-row {
    opacity: 0.65;
    background-color: #f0f9ff !important;
}
.settlement-done-row td {
    text-decoration: line-through;
    text-decoration-color: rgba(0,0,0,0.25);
}
.settlement-done-row td:nth-child(7),
.settlement-done-row td:last-child {
    text-decoration: none;
}
html.dark-mode .settlement-done-row {
    background-color: rgba(30,58,95,0.25) !important;
}
</style>
@endpush

@push('js')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const accountingI18n = {!! json_encode([
    'filterActive' => __('Filtre aktif'),
    'record' => __(':count kayıt'),
    'errorOccurred' => __('Bir hata oluştu'),
    'noMatchingRecords' => __('Filtreye uygun kayıt bulunamadı'),
    'settlement' => __('Mutabakat'),
    'settlementDone' => __('Mutabakat Yapıldı'),
    'tracking' => __('Takip'),
    'salaryPayment' => __('Maaş ödemesi'),
    'viewTicket' => __('Bileti Görüntüle'),
    'confirmDelete' => __('Silinsin mi?'),
    'selectAtLeastOneTicket' => __('Lütfen en az bir bilet seçin.'),
    'settlementSubmitting' => __('Mutabakat onaya gönderiliyor...'),
    'settlementSubmitFailed' => __('Mutabakat gönderimi başarısız.'),
    'settlementCompleted' => __('Mutabakat tamamlandı.'),
    'awaitingApproval' => __('Onay Bekliyor'),
    'settlementSentAwaitingApproval' => __('Mutabakat onaya gönderildi. Karşı taraf onayı bekleniyor.'),
    'settlementSendError' => __('Mutabakat gönderilirken hata oluştu.'),
    'selectionPending' => __('Seçim bekleniyor'),
    'totalTry' => __('Toplam TRY'),
    'income' => __('Gelir'),
    'expense' => __('Gider'),
    'periodLabels' => [
        '7days' => __('Son :count günlük veriler (günlük)', ['count' => 7]),
        '30days' => __('Son :count günlük veriler (günlük)', ['count' => 30]),
        '3months' => __('Son :count aylık veriler (haftalık)', ['count' => 3]),
        '6months' => __('Son :count aylık veriler (aylık)', ['count' => 6]),
        '12months' => __('Son :count aylık veriler (aylık)', ['count' => 12]),
    ],
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
document.addEventListener('DOMContentLoaded', function() {
    // Filtre elemanları
    const searchInput = document.getElementById('live-search');
    const clearSearchBtn = document.getElementById('clear-search');
    const typeSelect = document.getElementById('filter-type');
    const statusSelect = document.getElementById('filter-status');
    const currencySelect = document.getElementById('filter-currency');
    const paymentSelect = document.getElementById('filter-payment');
    const agencySelect = document.getElementById('filter-agency');
    const fromInput = document.getElementById('filter-from');
    const toInput = document.getElementById('filter-to');
    const clearAllBtn = document.getElementById('clear-all-filters');
    const statusText = document.getElementById('filter-status-text');
    
    // Tablo ve yükleme
    const tableBody = document.getElementById('transactions-table-body');
    const loadingOverlay = document.getElementById('loading-overlay');
    const paginationContainer = document.getElementById('pagination-container');
    const totalCountBadge = document.getElementById('total-count-badge');
    
    // Toplamlar
    const visibleCountEl = document.getElementById('visible-count');
    const visibleIncomeEl = document.getElementById('visible-income');
    const visibleExpenseEl = document.getElementById('visible-expense');
    const currencyCards = document.querySelectorAll('.currency-card[data-currency]');
    const selectAllTransactions = document.getElementById('select-all-transactions');
    const openSettlementPreviewBtn = document.getElementById('open-settlement-preview');
    const selectedCountEl = document.getElementById('selected-count');
    const settlementPreviewModal = document.getElementById('settlementPreviewModal');
    const settlementCurrencySummaryBody = document.getElementById('settlement-currency-summary-body');
    const settlementDailySummaryBody = document.getElementById('settlement-daily-summary-body');
    const settlementTotalTry = document.getElementById('settlement-total-try');
    const settlementConfirmBtn = document.getElementById('settlement-confirm-btn');
    
    let filterTimeout = null;
    let isFiltering = false;
    const filterUrl = '{{ route("admin.accounting.filter", [], false) }}';
    const settlementSubmitUrl = '{{ route("admin.accounting.settlements.submit", [], false) }}';
    const csrfToken = '{{ csrf_token() }}';
    const lockedAgencyId = '{{ request("locked_agency_id") }}';
    const indexUrl = '{{ route("admin.accounting.index", [], false) }}';
    const tcmbRates = (() => {
        const map = { TRY: 1 };
        const ratesPayload = {!! json_encode($exchangeRates['items'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
        ratesPayload.forEach(item => {
            const code = String(item.code || '').toUpperCase();
            if (code) {
                map[code] = Number(item.buy || 0);
            }
        });
        return map;
    })();
    const selectedTickets = new Map();
    let currentTransactions = [];
    const settlementPreviewModalInstance = (window.bootstrap && settlementPreviewModal)
        ? window.bootstrap.Modal.getOrCreateInstance(settlementPreviewModal)
        : null;
    
    // AJAX ile filtreleme
    async function applyFilters() {
        const searchTerm = searchInput.value.trim();
        const typeFilter = typeSelect.value;
        const statusFilter = statusSelect.value;
        const currencyFilter = currencySelect.value;
        const paymentFilter = paymentSelect.value;
        const agencyFilter = agencySelect ? agencySelect.value : '';
        const fromDate = fromInput.value;
        const toDate = toInput.value;
        
        // Filtre var mı kontrol et
        const hasFilter = searchTerm || typeFilter || statusFilter || currencyFilter || paymentFilter || agencyFilter || fromDate || toDate;
        
        // Temizle butonunu göster/gizle
        clearSearchBtn.style.display = searchTerm ? '' : 'none';
        
        // Filtre durumu
        if (hasFilter) {
            statusText.innerHTML = '<i class="fas fa-filter text-primary"></i> ' + accountingI18n.filterActive;
        } else {
            statusText.textContent = '';
        }
        
        // Filtre yoksa sayfa yenileme ile varsayılan listeye dön
        if (!hasFilter) {
            const defaultUrl = lockedAgencyId
                ? indexUrl + '?locked_agency_id=' + encodeURIComponent(lockedAgencyId)
                : indexUrl;
            window.location.href = defaultUrl;
            return;
        }
        
        // Loading göster
        loadingOverlay.style.display = 'flex';
        paginationContainer.style.display = 'none';
        
        try {
            const params = new URLSearchParams();
            if (searchTerm) params.append('search', searchTerm);
            if (typeFilter) params.append('type', typeFilter);
            if (statusFilter) params.append('status', statusFilter);
            if (currencyFilter) params.append('currency', currencyFilter);
            if (paymentFilter) params.append('payment_method', paymentFilter);
            if (agencyFilter) params.append('agency_id', agencyFilter);
            if (lockedAgencyId) params.append('locked_agency_id', lockedAgencyId);
            if (fromDate) params.append('from', fromDate);
            if (toDate) params.append('to', toDate);
            
            const response = await fetch(`${filterUrl}?${params.toString()}`, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const contentType = response.headers.get('content-type') || '';
            if (!response.ok || !contentType.includes('application/json')) {
                throw new Error('Unexpected response');
            }
            
            const data = await response.json();
            
            // Tabloyu güncelle
            renderTable(data.transactions);
            
            // Toplamları güncelle
            visibleCountEl.textContent = data.total_count;
            visibleIncomeEl.textContent = data.total_income;
            visibleExpenseEl.textContent = data.total_expense;
            totalCountBadge.textContent = accountingI18n.record.replace(':count', data.total_count);
            updateCurrencyCards(data.currency_summary || {});
            
        } catch (error) {
            console.error('Filtre hatası:', error);
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle"></i> ' + accountingI18n.errorOccurred + '</td></tr>';
            selectedTickets.clear();
            currentTransactions = [];
            refreshSelectionUI();
        } finally {
            loadingOverlay.style.display = 'none';
        }
    }
    
    // Tablo satırlarını render et
    function renderTable(transactions) {
        selectedTickets.clear();
        currentTransactions = Array.isArray(transactions) ? transactions : [];
        refreshSelectionUI();

        if (!transactions || transactions.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-search"></i> ' + accountingI18n.noMatchingRecords + '</td></tr>';
            bindTransactionSelectionEvents();
            return;
        }
        
        let html = '';
        const renderedTickets = new Set();
        transactions.forEach(t => {
            const ticketId = t.ticket_id ? String(t.ticket_id) : '';
            const isSettled = !!t.is_settled;
            const shouldRenderTicketCheckbox = ticketId && !renderedTickets.has(ticketId) && !isSettled;
            if (ticketId && !renderedTickets.has(ticketId)) {
                renderedTickets.add(ticketId);
            }

            const statusCell = isSettled
                ? `<span class="badge badge-info" title="${accountingI18n.settlement}: ${escapeHtml(t.settled_at || '')}">${accountingI18n.settlementDone}</span>`
                : `<span class="badge badge-${t.status_badge}">${t.status_label}</span>`;

            html += `<tr data-ticket-id="${ticketId}"
                data-date="${t.date_iso || ''}"
                data-type="${t.type}"
                data-amount="${t.amount_raw || 0}"
                data-currency="${(t.currency || 'TRY').toUpperCase()}"
                data-settled="${isSettled ? '1' : '0'}"
                ${isSettled ? 'class="settlement-done-row"' : ''}>
                <td>
                    ${shouldRenderTicketCheckbox
                        ? `<input type="checkbox" class="ticket-checkbox" data-ticket-id="${ticketId}" data-ticket-tracking="${escapeHtml(t.ticket_tracking || '')}">`
                        : ''}
                </td>
                <td>${t.date}</td>
                <td>${escapeHtml(t.title)}${t.ticket_tracking ? '<br><small class="text-muted">' + accountingI18n.tracking + ': ' + escapeHtml(t.ticket_tracking) + '</small>' : ''}</td>
                <td><span class="badge badge-${t.type_badge}">${t.type_label}</span></td>
                <td>${t.amount}</td>
                <td>${t.currency}</td>
                <td>${statusCell}</td>
                <td>
                    ${t.payment_label}
                    ${t.is_salary ? '<br><small class="text-muted">' + accountingI18n.salaryPayment + '</small>' : ''}
                </td>
                <td>
                    <div class="btn-group">
                        ${t.ticket_url ? '<a href="' + t.ticket_url + '" class="btn btn-sm btn-info" title="' + accountingI18n.viewTicket + '"><i class="fas fa-eye"></i></a>' : ''}
                        ${t.is_salary ? '<button class="btn btn-sm btn-secondary" disabled title="' + accountingI18n.salaryPayment + '"><i class="fas fa-money-check-alt"></i></button>' : ''}
                        ${t.edit_url ? '<a href="' + t.edit_url + '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>' : ''}
                        ${t.delete_url ? '<form action="' + t.delete_url + '" method="POST" class="d-inline" onsubmit="return confirm(\'' + accountingI18n.confirmDelete + '\')"><input type="hidden" name="_token" value="' + csrfToken + '"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>' : ''}
                    </div>
                </td>
            </tr>`;
        });
        
        tableBody.innerHTML = html;
        bindTransactionSelectionEvents();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function syncCurrentTransactionsFromTable() {
        currentTransactions = Array.from(tableBody.querySelectorAll('tr[data-date]')).map(row => ({
            date_iso: row.dataset.date || '',
            type: row.dataset.type || 'income',
            amount_raw: Number(row.dataset.amount || 0),
            currency: String(row.dataset.currency || 'TRY').toUpperCase(),
            ticket_id: row.dataset.ticketId ? Number(row.dataset.ticketId) : null,
        }));
    }

    function updateCurrencyCards(summaryMap) {
        currencyCards.forEach(card => {
            const code = String(card.dataset.currency || '').toUpperCase();
            const summary = summaryMap[code] || {};

            const incomeValue = summary.income_formatted ?? '0,00';
            const expenseValue = summary.expense_formatted ?? '0,00';
            const netValue = summary.net_formatted ?? '0,00';
            const netRaw = Number(summary.net ?? 0);

            const incomeEl = card.querySelector('[data-role="income-value"]');
            const expenseEl = card.querySelector('[data-role="expense-value"]');
            const netEl = card.querySelector('[data-role="net-value"]');

            if (incomeEl) incomeEl.textContent = incomeValue;
            if (expenseEl) expenseEl.textContent = expenseValue;
            if (netEl) {
                netEl.textContent = netValue;
                netEl.classList.remove('text-success', 'text-danger');
                netEl.classList.add(netRaw >= 0 ? 'text-success' : 'text-danger');
            }
        });
    }

    function getSignedAmount(type, amount) {
        const raw = Number(amount || 0);
        return type === 'expense' ? -raw : raw;
    }

    function bindTransactionSelectionEvents() {
        const checkboxes = tableBody.querySelectorAll('.ticket-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const id = String(this.dataset.ticketId || '');
                if (!id) return;

                if (this.checked) {
                    selectedTickets.set(id, {
                        id: id,
                        tracking: this.dataset.ticketTracking || '',
                    });
                } else {
                    selectedTickets.delete(id);
                }
                refreshSelectionUI();
            });
        });
    }

    function refreshSelectionUI() {
        const count = selectedTickets.size;
        selectedCountEl.textContent = String(count);
        openSettlementPreviewBtn.classList.toggle('d-none', count === 0);

        const checkboxes = tableBody.querySelectorAll('.ticket-checkbox');
        const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
        if (selectAllTransactions) {
            selectAllTransactions.checked = allChecked;
            selectAllTransactions.indeterminate = !allChecked && Array.from(checkboxes).some(cb => cb.checked);
        }
    }

    function renderSettlementPreview() {
        if (selectedTickets.size === 0) {
            settlementCurrencySummaryBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">' + accountingI18n.selectionPending + '</td></tr>';
            settlementDailySummaryBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">' + accountingI18n.selectionPending + '</td></tr>';
            settlementTotalTry.textContent = accountingI18n.totalTry + ': 0,00';
            return;
        }

        const byCurrency = {};
        const byDateCurrency = {};
        let totalTry = 0;

        const selectedTicketIds = new Set(Array.from(selectedTickets.keys()));
        currentTransactions.forEach(tx => {
            const ticketId = tx.ticket_id ? String(tx.ticket_id) : '';
            if (!ticketId || !selectedTicketIds.has(ticketId)) {
                return;
            }
            const signed = getSignedAmount(tx.type, tx.amount_raw ?? tx.amount ?? 0);
            const currency = (tx.currency || 'TRY').toUpperCase();
            const date = tx.date_iso || tx.date || '-';
            const rate = Number(tcmbRates[currency] || (currency === 'TRY' ? 1 : 0));
            const convertedTry = signed * rate;

            byCurrency[currency] = (byCurrency[currency] || 0) + signed;

            const dailyKey = `${date}|${currency}`;
            if (!byDateCurrency[dailyKey]) {
                byDateCurrency[dailyKey] = { date, currency, amount: 0, tryAmount: 0 };
            }
            byDateCurrency[dailyKey].amount += signed;
            byDateCurrency[dailyKey].tryAmount += convertedTry;

            totalTry += convertedTry;
        });

        settlementCurrencySummaryBody.innerHTML = Object.entries(byCurrency)
            .sort((a, b) => a[0].localeCompare(b[0]))
            .map(([currency, amount]) => `
                <tr>
                    <td>${currency}</td>
                    <td class="text-right ${amount >= 0 ? 'text-success' : 'text-danger'}">${amount.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>
            `).join('');

        settlementDailySummaryBody.innerHTML = Object.values(byDateCurrency)
            .sort((a, b) => {
                if (a.date === b.date) return a.currency.localeCompare(b.currency);
                return a.date.localeCompare(b.date);
            })
            .map(row => `
                <tr>
                    <td>${row.date}</td>
                    <td>${row.currency}</td>
                    <td class="text-right ${row.amount >= 0 ? 'text-success' : 'text-danger'}">${row.amount.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="text-right ${row.tryAmount >= 0 ? 'text-success' : 'text-danger'}">${row.tryAmount.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>
            `).join('');

        settlementTotalTry.textContent = `${accountingI18n.totalTry}: ${totalTry.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function showSettlementNotice(message) {
        if (!statusText) return;
        statusText.innerHTML = `<i class="fas fa-check-circle text-success"></i> ${message}`;
    }

    async function submitSettlementSelection() {
        const checkedRows = Array.from(tableBody.querySelectorAll('.ticket-checkbox:checked'));
        let effectiveRows = checkedRows;
        if (effectiveRows.length === 0 && selectedTickets.size > 0) {
            effectiveRows = Array.from(tableBody.querySelectorAll('.ticket-checkbox')).filter(cb => {
                const ticketId = String(cb.dataset.ticketId || '');
                return ticketId && selectedTickets.has(ticketId);
            });
        }
        if (effectiveRows.length === 0) {
            showSettlementNotice(accountingI18n.selectAtLeastOneTicket);
            return;
        }

        const ticketIds = effectiveRows
            .map(cb => Number(cb.dataset.ticketId || 0))
            .filter(id => id > 0);

        try {
            settlementConfirmBtn.disabled = true;
            showSettlementNotice(accountingI18n.settlementSubmitting);
            const response = await fetch(settlementSubmitUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    ticket_ids: ticketIds,
                }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || accountingI18n.settlementSubmitFailed);
            }

            if (data.immediate_settlement_applied) {
                refreshSelectionUI();
                if (settlementPreviewModalInstance) {
                    settlementPreviewModalInstance.hide();
                }
                showSettlementNotice(data.message || accountingI18n.settlementCompleted);
                window.location.reload();
                return;
            }

            effectiveRows.forEach(cb => {
                const row = cb.closest('tr');
                cb.checked = false;
                cb.disabled = true;

                if (row) {
                    row.classList.add('settlement-locked-row');
                    const titleCell = row.children[2];
                    if (titleCell && !titleCell.querySelector('.settlement-waiting-badge')) {
                        titleCell.insertAdjacentHTML('beforeend', ' <span class="badge badge-warning settlement-waiting-badge">' + accountingI18n.awaitingApproval + '</span>');
                    }
                }

                const ticketId = String(cb.dataset.ticketId || '');
                if (ticketId) {
                    selectedTickets.delete(ticketId);
                }
            });

            refreshSelectionUI();
            if (settlementPreviewModalInstance) {
                settlementPreviewModalInstance.hide();
            }
            showSettlementNotice(data.message || accountingI18n.settlementSentAwaitingApproval);
        } catch (error) {
            showSettlementNotice(error.message || accountingI18n.settlementSendError);
        } finally {
            settlementConfirmBtn.disabled = false;
        }
    }
    
    // Event listeners - Arama (debounce ile)
    searchInput.addEventListener('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(applyFilters, 400);
    });

    if (selectAllTransactions) {
        selectAllTransactions.addEventListener('change', function () {
            const checked = this.checked;
            tableBody.querySelectorAll('.ticket-checkbox').forEach(cb => {
                cb.checked = checked;
                cb.dispatchEvent(new Event('change'));
            });
        });
    }

    if (openSettlementPreviewBtn) {
        openSettlementPreviewBtn.addEventListener('click', function () {
            renderSettlementPreview();
            if (settlementPreviewModalInstance) {
                settlementPreviewModalInstance.show();
            } else if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(settlementPreviewModal).modal('show');
            }
        });
    }

    if (settlementConfirmBtn) {
        settlementConfirmBtn.addEventListener('click', function (e) {
            e.preventDefault();
            submitSettlementSelection();
        });
    }
    
    // Temizle butonu
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        // Filtre yoksa sayfa yenile
        const defaultUrl = lockedAgencyId
            ? indexUrl + '?locked_agency_id=' + encodeURIComponent(lockedAgencyId)
            : indexUrl;
        window.location.href = defaultUrl;
    });
    
    // Select filtreler (anında)
    [typeSelect, statusSelect, currencySelect, paymentSelect, agencySelect].filter(Boolean).forEach(select => {
        select.addEventListener('change', applyFilters);
    });
    
    // Tarih filtreler
    [fromInput, toInput].forEach(input => {
        input.addEventListener('change', applyFilters);
    });
    
    // Hızlı tarih butonları
    document.querySelectorAll('.quick-date').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const range = this.dataset.range;
            const today = new Date();
            let from, to;
            
            switch(range) {
                case 'today':
                    from = to = formatDate(today);
                    break;
                case 'week':
                    const dayOfWeek = today.getDay();
                    const monday = new Date(today);
                    monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
                    from = formatDate(monday);
                    to = formatDate(today);
                    break;
                case 'month':
                    from = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                    to = formatDate(today);
                    break;
                case 'year':
                    from = formatDate(new Date(today.getFullYear(), 0, 1));
                    to = formatDate(today);
                    break;
            }
            
            fromInput.value = from;
            toInput.value = to;
            
            // Aktif butonu işaretle
            document.querySelectorAll('.quick-date').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('active', 'btn-primary');
            
            applyFilters();
        });
    });
    
    // Tüm filtreleri temizle
    clearAllBtn.addEventListener('click', function() {
        // Sayfa yenileme ile tüm filtreleri temizle
        const defaultUrl = lockedAgencyId
            ? indexUrl + '?locked_agency_id=' + encodeURIComponent(lockedAgencyId)
            : indexUrl;
        window.location.href = defaultUrl;
    });

    // İlk yüklemede seçim checkbox eventlerini bağla
    syncCurrentTransactionsFromTable();
    bindTransactionSelectionEvents();
    refreshSelectionUI();
    
    // ==========================================
    // Para Birimi Grafik Modalı
    // ==========================================
    let currencyChart = null;
    let currentChartCurrency = null;
    let currentChartPeriod = '12months';
    let currentChartType = 'line';
    let currentChartData = null;
    
    const chartModal = document.getElementById('currencyChartModal');
    const chartModalInstance = (window.bootstrap && chartModal)
        ? window.bootstrap.Modal.getOrCreateInstance(chartModal)
        : null;
    const chartContainer = document.getElementById('chart-container');
    const chartLoading = document.getElementById('chart-loading');
    const chartError = document.getElementById('chart-error');
    const chartCanvas = document.getElementById('currencyChart');
    const chartTitle = document.getElementById('chart-currency-name');
    const chartPeriodInfo = document.getElementById('chart-period-info');
    const chartDataUrl = '{{ route("admin.accounting.chart-data", [], false) }}';
    
    const periodLabels = accountingI18n.periodLabels;
    
    const chartTypeLabels = {
        'line': 'Çizgi',
        'bar': 'Çubuk',
        'area': 'Alan',
        'radar': 'Radar',
        'polarArea': 'Polar'
    };
    
    // Para birimi kartlarına tıklama
    document.querySelectorAll('.currency-card[data-currency]').forEach(card => {
        card.addEventListener('click', function() {
            const currency = this.dataset.currency;
            const name = this.dataset.name;
            
            currentChartCurrency = currency;
            
            // Modal başlığını güncelle
            chartTitle.textContent = currency + ' - ' + name;
            
            // Varsayılan değerlere reset
            currentChartPeriod = '12months';
            currentChartType = 'line';
            updatePeriodButtons();
            updateTypeButtons();
            
            // Modal'ı aç
            if (chartModalInstance) {
                chartModalInstance.show();
            } else if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery(chartModal).modal('show');
            }
            
            // Grafik verilerini yükle
            loadChartData(currency, currentChartPeriod);
        });
    });
    
    // Zaman dilimi butonları
    document.querySelectorAll('.chart-period').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            currentChartPeriod = period;
            
            // Butonları güncelle
            updatePeriodButtons();
            
            // Grafiği yeniden yükle
            if (currentChartCurrency) {
                loadChartData(currentChartCurrency, period);
            }
        });
    });
    
    // Grafik türü butonları
    document.querySelectorAll('.chart-type').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            currentChartType = type;
            
            // Butonları güncelle
            updateTypeButtons();
            
            // Grafiği mevcut veriyle yeniden çiz
            if (currentChartData) {
                renderChart(currentChartData);
            }
        });
    });
    
    function updatePeriodButtons() {
        document.querySelectorAll('.chart-period').forEach(btn => {
            if (btn.dataset.period === currentChartPeriod) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary', 'active');
            } else {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-outline-primary');
            }
        });
        
        // Info text güncelle
        chartPeriodInfo.innerHTML = '<i class="fas fa-info-circle"></i> ' + periodLabels[currentChartPeriod];
    }
    
    function updateTypeButtons() {
        document.querySelectorAll('.chart-type').forEach(btn => {
            if (btn.dataset.type === currentChartType) {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success', 'active');
            } else {
                btn.classList.remove('btn-success', 'active');
                btn.classList.add('btn-outline-success');
            }
        });
    }
    
    async function loadChartData(currency, period) {
        // Loading göster
        chartLoading.style.display = 'block';
        chartContainer.style.display = 'none';
        chartError.style.display = 'none';
        
        try {
            const chartParams = new URLSearchParams({
                currency: currency,
                period: period,
            });
            if (lockedAgencyId) {
                chartParams.append('locked_agency_id', lockedAgencyId);
            }

            const response = await fetch(`${chartDataUrl}?${chartParams.toString()}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            
            if (!response.ok) throw new Error('Network error');
            
            const data = await response.json();
            
            // Veriyi sakla (grafik türü değiştiğinde kullanılacak)
            currentChartData = data;
            
            // Grafiği oluştur
            renderChart(data);
            
            chartLoading.style.display = 'none';
            chartContainer.style.display = 'block';
            
        } catch (error) {
            console.error('Grafik hatası:', error);
            chartLoading.style.display = 'none';
            chartError.style.display = 'block';
        }
    }
    
    function renderChart(data) {
        // Önceki grafiği yok et
        if (currencyChart) {
            currencyChart.destroy();
        }
        
        const ctx = chartCanvas.getContext('2d');
        
        // Grafik türüne göre ayarlar
        let chartType = currentChartType;
        let datasets = [];
        let options = {};
        
        // Area türü için line kullan, fill: true
        if (chartType === 'area') {
            chartType = 'line';
        }
        
        // Temel dataset ayarları
        const incomeDataset = {
            label: accountingI18n.income,
            data: data.income,
            borderColor: '#28a745',
            backgroundColor: currentChartType === 'area' ? 'rgba(40, 167, 69, 0.3)' : 
                            (currentChartType === 'polarArea' || currentChartType === 'radar') ? 'rgba(40, 167, 69, 0.5)' : 
                            'rgba(40, 167, 69, 0.8)',
        };
        
        const expenseDataset = {
            label: accountingI18n.expense,
            data: data.expense,
            borderColor: '#dc3545',
            backgroundColor: currentChartType === 'area' ? 'rgba(220, 53, 69, 0.3)' : 
                            (currentChartType === 'polarArea' || currentChartType === 'radar') ? 'rgba(220, 53, 69, 0.5)' : 
                            'rgba(220, 53, 69, 0.8)',
        };
        
        // Grafik türüne göre özelleştirme
        switch (currentChartType) {
            case 'line':
                Object.assign(incomeDataset, {
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#28a745',
                });
                Object.assign(expenseDataset, {
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#dc3545',
                });
                break;
                
            case 'area':
                Object.assign(incomeDataset, {
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#28a745',
                });
                Object.assign(expenseDataset, {
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#dc3545',
                });
                break;
                
            case 'bar':
                Object.assign(incomeDataset, {
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.8,
                    categoryPercentage: 0.9,
                });
                Object.assign(expenseDataset, {
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.8,
                    categoryPercentage: 0.9,
                });
                break;
                
            case 'radar':
                Object.assign(incomeDataset, {
                    borderWidth: 2,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                });
                Object.assign(expenseDataset, {
                    borderWidth: 2,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                });
                break;
                
            case 'polarArea':
                // Polar Area için veriyi birleştir
                const combinedLabels = [accountingI18n.income, accountingI18n.expense];
                const combinedData = [
                    data.income.reduce((a, b) => a + b, 0),
                    data.expense.reduce((a, b) => a + b, 0)
                ];
                const combinedColors = ['rgba(40, 167, 69, 0.7)', 'rgba(220, 53, 69, 0.7)'];
                const combinedBorders = ['#28a745', '#dc3545'];
                
                currencyChart = new Chart(ctx, {
                    type: 'polarArea',
                    data: {
                        labels: combinedLabels,
                        datasets: [{
                            data: combinedData,
                            backgroundColor: combinedColors,
                            borderColor: combinedBorders,
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { usePointStyle: true, padding: 20, font: { size: 13 } }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed.r.toLocaleString('tr-TR', {
                                            minimumFractionDigits: 2, maximumFractionDigits: 2
                                        }) + ' ' + data.currency;
                                    }
                                }
                            }
                        }
                    }
                });
                return;
        }
        
        datasets = [incomeDataset, expenseDataset];
        
        // Temel seçenekler
        options = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 13 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14 },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            let value = currentChartType === 'radar' ? context.parsed.r : context.parsed.y;
                            return context.dataset.label + ': ' + value.toLocaleString('tr-TR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' ' + data.currency;
                        }
                    }
                }
            }
        };
        
        // Radar için özel scale
        if (currentChartType === 'radar') {
            options.scales = {
                r: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.1)' },
                    angleLines: { color: 'rgba(0, 0, 0, 0.1)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('tr-TR');
                        }
                    }
                }
            };
        } else {
            options.scales = {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('tr-TR') + ' ' + data.currency;
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            };
        }
        
        currencyChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: data.labels,
                datasets: datasets
            },
            options: options
        });
    }
    
    // Modal kapandığında grafiği temizle
    if (chartModal) {
        chartModal.addEventListener('hidden.bs.modal', function() {
            if (currencyChart) {
                currencyChart.destroy();
                currencyChart = null;
            }
        });
    }
});
</script>
@endpush

