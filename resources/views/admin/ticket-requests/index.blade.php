@extends('layouts.admin')

@section('title', __('Bilet İstekleri'))

@section('content')
<div class="container-fluid">
    <!-- Modern Kontrol Paneli -->
    <div class="guides-control-panel ad-page-header admin-list-toolbar mb-3">
        <div class="control-left">
            <h4 class="control-title"><i class="fas fa-inbox"></i> {{ __('Bilet İstekleri') }}</h4>
            <p class="control-subtitle">{{ __('Bekleyen') }}: {{ $pendingRequests->total() }}</p>
        </div>
        <div class="control-right">
            <div class="control-item">
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-ticket-alt"></i> {{ __('Biletler') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="ad-card mb-3">
        <div class="card-body table-responsive p-0">
            <table class="ad-table table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('İstek Tarihi') }}</th>
                        <th>{{ __('İsteyen') }}</th>
                        <th>{{ __('Tur') }}</th>
                        <th>{{ __('Müşteri') }}</th>
                        <th>{{ __('Tur Tarihi') }}</th>
                        <th>{{ __('Yolcu') }}</th>
                        <th>{{ __('Toplam') }}</th>
                        <th>{{ __('İşlem') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $request)
                        <tr>
                            <td>
                                {{ $request->created_at->format('d.m.Y') }}
                                <br><small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $request->requester->name }}</strong>
                                @if($request->requester->agency)
                                    <br><small class="text-muted">{{ $request->requester->agency->name }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $request->tour->name }}</strong>
                                <br><small class="text-muted">{{ $request->tour->country }}</small>
                            </td>
                            <td>
                                {{ $request->customer_name }}
                                <br><small class="text-muted">{{ $request->customer_phone }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $request->tour_date->format('d.m.Y') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ __(':count kişi', ['count' => $request->total_passengers]) }}</span>
                            </td>
                            <td>
                                @php
                                    $baseTotal = (($request->adult_count ?? 0) * (float) ($request->adult_price ?? 0))
                                        + (($request->child_count ?? 0) * (float) ($request->child_price ?? 0))
                                        + (($request->infant_count ?? 0) * (float) ($request->infant_price ?? 0));
                                    $curr = strtoupper($request->base_currency ?: ($request->currency ?: 'TRY'));
                                    $currClass = match($curr) {
                                        'TRY' => 'badge-success',
                                        'EUR' => 'badge-primary',
                                        'USD' => 'badge-danger',
                                        'GBP' => 'badge-gbp',
                                        'RUB' => 'badge-rub',
                                        default => 'badge-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $currClass }}">
                                    {{ number_format($baseTotal, 2) }} {{ $curr }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.ticket-requests.show', $request) }}" class="btn btn-sm btn-info" title="{{ __('Detay') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.ticket-requests.approve', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="{{ __('Onayla') }}" onclick="return confirm({!! json_encode(__('Bu bilet isteğini onaylamak istediğinize emin misiniz?'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger" title="{{ __('Reddet') }}" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $request->id }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.ticket-requests.reject', $request) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Bilet İsteğini Reddet') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ __('Bu bilet isteğini reddetmek istediğinize emin misiniz?') }}</p>
                                                    <div class="form-group">
                                                        <label>{{ __('Red Sebebi (Opsiyonel)') }}</label>
                                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="{{ __('Red sebebini yazın...') }}"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('İptal') }}</button>
                                                    <button type="submit" class="btn btn-danger">{{ __('Reddet') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('Bekleyen bilet isteği yok.') }}</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingRequests->hasPages())
            <div class="card-footer">
                {{ $pendingRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Returned Requests (Waiting for Agency Edit) -->
    @if(isset($returnedRequests) && $returnedRequests->count() > 0)
            <div class="ad-card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-undo"></i> {{ __('Düzenleme Bekleyen İstekler') }}
                    <span class="badge badge-info ml-2">{{ $returnedRequests->count() }}</span>
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="ad-table table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>{{ __('Geri Gönderim') }}</th>
                            <th>{{ __('İsteyen') }}</th>
                            <th>{{ __('Tur') }}</th>
                            <th>{{ __('Müşteri') }}</th>
                            <th>{{ __('Düzenleme Sebebi') }}</th>
                            <th>{{ __('Durum') }}</th>
                            <th>{{ __('İşlem') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnedRequests as $request)
                            <tr class="table-info">
                                <td>
                                    {{ $request->returned_at ? $request->returned_at->format('d.m.Y') : '-' }}
                                    @if($request->returned_at)
                                        <br><small class="text-muted">{{ $request->returned_at->format('H:i') }}</small>
                                    @endif
                                    @if($request->return_count > 1)
                                        <br><span class="badge badge-warning">{{ __(':count. kez', ['count' => $request->return_count]) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $request->requester->name }}</strong>
                                    @if($request->requester->agency)
                                        <br><small class="text-muted">{{ $request->requester->agency->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $request->tour->name }}</strong>
                                    <br><small class="text-muted">{{ $request->tour->country }}</small>
                                </td>
                                <td>
                                    {{ $request->customer_name }}
                                    <br><small class="text-muted">{{ $request->customer_phone }}</small>
                                </td>
                                <td>
                                    <span title="{{ $request->return_reason }}">
                                        {{ Str::limit($request->return_reason, 40) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><i class="fas fa-undo"></i> {{ __('Acenta Düzenleniyor') }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.ticket-requests.show', $request) }}" class="btn btn-sm btn-info" title="{{ __('Detay') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Processed Requests -->
    @if($processedRequests->count() > 0)
        <div class="ad-card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> {{ __('Son İşlenen İstekler') }}
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="ad-table table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('İşlem Tarihi') }}</th>
                            <th>{{ __('İsteyen') }}</th>
                            <th>{{ __('Tur') }}</th>
                            <th>{{ __('Müşteri') }}</th>
                            <th>{{ __('Durum') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processedRequests as $request)
                            <tr class="{{ $request->isRejected() ? 'table-danger' : 'table-success' }}">
                                <td>{{ $request->responded_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $request->requester->name }}</td>
                                <td>{{ $request->tour->name }}</td>
                                <td>{{ $request->customer_name }}</td>
                                <td>
                                    @if($request->isApproved())
                                        <span class="badge badge-success"><i class="fas fa-check"></i> {{ __('Onaylandı') }}</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times"></i> {{ __('Reddedildi') }}</span>
                                        @if($request->rejection_reason)
                                            <br><small class="text-muted">{{ Str::limit($request->rejection_reason, 50) }}</small>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@stop

@push('css')
<style>
/* GBP - Koyu Mavi */
.badge-gbp { background-color: #1a237e; color: #fff; }
/* RUB - Turuncu */
.badge-rub { background-color: #e65100; color: #fff; }
</style>
@endpush
