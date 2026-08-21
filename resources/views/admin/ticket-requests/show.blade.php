@extends('layouts.admin')

@section('title', 'Bilet İsteği Detayı')

@section('content')
<div class="container-fluid">
    @if ($errors->any())
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle"></i> İşlem tamamlanamadı</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Request Info -->
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> İstek Bilgileri</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">İstek Tarihi:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->created_at->format('d.m.Y H:i') }}</dd>
                        
                        <dt class="col-sm-4">İsteyen Kullanıcı:</dt>
                        <dd class="col-sm-8">
                            <strong>{{ $ticketRequest->requester->name }}</strong>
                            @if($ticketRequest->requester->agency)
                                <br><small class="text-muted">{{ $ticketRequest->requester->agency->name }}</small>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Durum:</dt>
                        <dd class="col-sm-8">
                            @if($ticketRequest->isPending())
                                <span class="badge badge-warning badge-lg"><i class="fas fa-clock"></i> Onay Bekliyor</span>
                            @elseif($ticketRequest->isApproved())
                                <span class="badge badge-success badge-lg"><i class="fas fa-check"></i> Onaylandı</span>
                            @elseif($ticketRequest->isReturned())
                                <span class="badge badge-info badge-lg"><i class="fas fa-undo"></i> Düzenleme İçin Geri Gönderildi</span>
                            @else
                                <span class="badge badge-danger badge-lg"><i class="fas fa-times"></i> Reddedildi</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Tour Info -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plane"></i> Tur Bilgileri</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Tur Adı:</dt>
                        <dd class="col-sm-8"><strong>{{ $ticketRequest->tour->name }}</strong></dd>
                        
                        <dt class="col-sm-4">Ülke:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->tour->country }}</dd>
                        
                        <dt class="col-sm-4">Şehir:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->tour->city }}</dd>
                        
                        <dt class="col-sm-4">Tur Tarihi:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-info badge-lg">{{ $ticketRequest->tour_date->format('d.m.Y') }}</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Customer & Pricing Info -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Müşteri Bilgileri</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Müşteri Adı:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->customer_name }}</dd>
                        
                        <dt class="col-sm-4">Telefon:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->customer_phone }}</dd>
                        
                        <dt class="col-sm-4">E-posta:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->customer_email ?? '-' }}</dd>
                        
                        <dt class="col-sm-4">Uyruk:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->nationality_name }}</dd>
                        
                        <dt class="col-sm-4">Voucher No:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->voucher_no }}</dd>
                        
                        <dt class="col-sm-4">Alış Noktası:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->pickup_location ?? '-' }}</dd>
                        
                        <dt class="col-sm-4">Oda No:</dt>
                        <dd class="col-sm-8">{{ $ticketRequest->room_number ?? '-' }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Pricing Info -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Fiyatlandırma</h3>
                </div>
                <div class="card-body">
                    @php
                        $baseTotal = (($ticketRequest->adult_count ?? 0) * (float) ($ticketRequest->adult_price ?? 0))
                            + (($ticketRequest->child_count ?? 0) * (float) ($ticketRequest->child_price ?? 0))
                            + (($ticketRequest->infant_count ?? 0) * (float) ($ticketRequest->infant_price ?? 0));

                        $baseCurrency = strtoupper($ticketRequest->base_currency ?: ($ticketRequest->currency ?: 'TRY'));
                        $saleCurrency = strtoupper($ticketRequest->sale_currency ?: ($ticketRequest->currency ?: $baseCurrency));

                        $restAmount = (float) ($ticketRequest->rest_adjustment_amount ?? 0);
                        $restCurrency = strtoupper($ticketRequest->rest_adjustment_currency ?: $saleCurrency);
                        $restConverted = (float) ($ticketRequest->rest_converted_amount ?? ($restCurrency === $baseCurrency ? $restAmount : 0));
                        $ownerNet = max($baseTotal - $restConverted, 0);

                        $currencyBadgeClass = static function ($curr) {
                            return match(strtoupper($curr)) {
                                'TRY' => 'badge-success',
                                'EUR' => 'badge-primary',
                                'USD' => 'badge-danger',
                                'GBP' => 'badge-gbp',
                                'RUB' => 'badge-rub',
                                default => 'badge-secondary',
                            };
                        };
                    @endphp

                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Yolcu Tipi</th>
                                <th class="text-center">Adet</th>
                                <th class="text-right">Birim Fiyat</th>
                                <th class="text-right">Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($ticketRequest->adult_count > 0)
                                <tr>
                                    <td>Yetişkin</td>
                                    <td class="text-center">{{ $ticketRequest->adult_count }}</td>
                                    <td class="text-right">{{ number_format($ticketRequest->adult_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($ticketRequest->adult_count * $ticketRequest->adult_price, 2) }}</td>
                                </tr>
                            @endif
                            @if($ticketRequest->child_count > 0)
                                <tr>
                                    <td>Çocuk</td>
                                    <td class="text-center">{{ $ticketRequest->child_count }}</td>
                                    <td class="text-right">{{ number_format($ticketRequest->child_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($ticketRequest->child_count * $ticketRequest->child_price, 2) }}</td>
                                </tr>
                            @endif
                            @if($ticketRequest->infant_count > 0)
                                <tr>
                                    <td>Bebek</td>
                                    <td class="text-center">{{ $ticketRequest->infant_count }}</td>
                                    <td class="text-right">{{ number_format($ticketRequest->infant_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($ticketRequest->infant_count * $ticketRequest->infant_price, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <th colspan="2">Toplam</th>
                                <th class="text-center">{{ $ticketRequest->total_passengers }} kişi</th>
                                <th class="text-right">
                                    <span class="badge {{ $currencyBadgeClass($baseCurrency) }} badge-lg">
                                        {{ number_format($baseTotal, 2) }} {{ $baseCurrency }}
                                    </span>
                                </th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-3 p-2 border rounded">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Toplam taban fiyat (rest hariç):</strong></span>
                            <span class="badge {{ $currencyBadgeClass($baseCurrency) }}">
                                {{ number_format($baseTotal, 2) }} {{ $baseCurrency }}
                            </span>
                        </div>

                        @if($restAmount > 0)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><strong>Rest:</strong></span>
                                <span class="badge {{ $currencyBadgeClass($restCurrency) }}">
                                    {{ number_format($restAmount, 2) }} {{ $restCurrency }}
                                </span>
                            </div>
                        @endif

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Admin net (rest düşülmüş taban):</strong></span>
                            <span class="badge {{ $currencyBadgeClass($baseCurrency) }} badge-lg">
                                {{ number_format($ownerNet, 2) }} {{ $baseCurrency }}
                            </span>
                        </div>

                        @if($restAmount > 0)
                            <div class="d-flex justify-content-between align-items-center">
                                <span><strong>Admin rest tutarı:</strong></span>
                                <span class="badge {{ $currencyBadgeClass($restCurrency) }}">
                                    {{ number_format($restAmount, 2) }} {{ $restCurrency }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    @if($ticketRequest->isPending() || $ticketRequest->isReturned())
        <div class="card">
            <div class="card-body text-center">
                <a href="{{ route('admin.ticket-requests.edit', $ticketRequest) }}" class="btn btn-warning btn-lg">
                    <i class="fas fa-edit"></i> Düzenle
                </a>
                <form action="{{ route('admin.ticket-requests.approve', $ticketRequest) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg ml-2" onclick="return confirm('Bu bilet isteğini onaylamak istediğinize emin misiniz?')">
                        <i class="fas fa-check"></i> Onayla ve Bilet Oluştur
                    </button>
                </form>
                <button type="button" id="openReturnModalBtn" class="btn btn-info btn-lg ml-2" data-toggle="modal" data-target="#returnModal" data-bs-toggle="modal" data-bs-target="#returnModal">
                    <i class="fas fa-undo"></i> Acentaya Geri Gönder
                </button>
                <button type="button" class="btn btn-danger btn-lg ml-2" data-toggle="modal" data-target="#rejectModal" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times"></i> Reddet
                </button>
            </div>
        </div>

        <!-- Return to Agency Modal -->
        <div class="modal fade" id="returnModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.ticket-requests.return', $ticketRequest) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-info">
                            <h5 class="modal-title text-white"><i class="fas fa-undo"></i> Acentaya Geri Gönder</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Bu bilet isteğini düzenleme için acentaya geri göndermek istediğinize emin misiniz?</p>
                            <p class="text-muted small">Acenta, bilet isteğini düzenleyip tekrar gönderebilecektir.</p>
                            <div class="form-group">
                                <label>Düzenleme Sebebi <span class="text-danger">*</span></label>
                                <textarea name="return_reason" class="form-control" rows="3" placeholder="Düzenlenmesi gereken kısımları açıklayın..." required></textarea>
                                <small class="form-text text-muted">Bu mesaj acentaya gösterilecektir.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-info">Geri Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.ticket-requests.reject', $ticketRequest) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Bilet İsteğini Reddet</h5>
                            <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Bu bilet isteğini reddetmek istediğinize emin misiniz?</p>
                            <div class="form-group">
                                <label>Red Sebebi (Opsiyonel)</label>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Red sebebini yazın..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" class="btn btn-danger">Reddet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($ticketRequest->isReturned() && $ticketRequest->return_reason)
        <div class="alert alert-info mt-3">
            <h5><i class="fas fa-info-circle"></i> Geri Gönderilme Sebebi</h5>
            <p class="mb-0">{{ $ticketRequest->return_reason }}</p>
            <small class="text-muted">Geri gönderilme tarihi: {{ $ticketRequest->returned_at->format('d.m.Y H:i') }}</small>
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

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openReturnModalBtn = document.getElementById('openReturnModalBtn');
    const returnModalEl = document.getElementById('returnModal');

    if (!openReturnModalBtn || !returnModalEl) {
        return;
    }

    openReturnModalBtn.addEventListener('click', function (event) {
        event.preventDefault();

        // Bootstrap 5
        if (window.bootstrap && window.bootstrap.Modal) {
            const modal = window.bootstrap.Modal.getOrCreateInstance(returnModalEl);
            modal.show();
            return;
        }

        // Bootstrap modal (jQuery)
        if (window.jQuery && typeof window.jQuery(returnModalEl).modal === 'function') {
            window.jQuery(returnModalEl).modal('show');
        }
    });
});
</script>
@endpush
