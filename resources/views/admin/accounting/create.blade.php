@extends('layouts.admin')

@section('title', 'Yeni Muhasebe Kaydı')

@section('content')
@php($lockedAgencyId = request('locked_agency_id'))
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header border-0 bg-white d-flex align-items-center">
                <div>
                    <h5 class="mb-0">Yeni Muhasebe Kaydı</h5>
                    <small class="text-muted">Gelir / gider / bekleyen ödeme kayıtlarını ekleyin.</small>
                </div>
                <span class="badge badge-light ml-auto"><i class="fas fa-database mr-1"></i> Sistem Kaydı</span>
            </div>
            <div class="card-body pt-3">
                <form action="{{ route('admin.accounting.store') }}" method="POST">
                    @csrf
                    @if($lockedAgencyId)
                        <input type="hidden" name="locked_agency_id" value="{{ $lockedAgencyId }}">
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Tür</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-success active">
                                    <input type="radio" name="type" value="income" autocomplete="off" checked> Gelir
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="type" value="expense" autocomplete="off"> Gider
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Durum</label>
                            <select name="status" class="form-control">
                                <option value="paid">Ödendi</option>
                                <option value="pending">Beklemede</option>
                                <option value="cancelled">İptal</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">İşlem Tarihi</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small text-muted mb-1">Başlık</label>
                        <input type="text" name="title" class="form-control form-control-lg" placeholder="Örn: Online Satış / Ofis Kirası" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Tutar</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                                </div>
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Para Birimi</label>
                            <div class="input-group">
                                <input type="text" name="currency" id="currency-input" class="form-control" value="TRY" maxlength="3" style="text-transform: uppercase;">
                                <div class="input-group-append">
                                    <span class="input-group-text">3H</span>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge badge-light border currency-chip" data-cur="TRY">TRY</span>
                                <span class="badge badge-light border currency-chip" data-cur="USD">USD</span>
                                <span class="badge badge-light border currency-chip" data-cur="EUR">EUR</span>
                                <span class="badge badge-light border currency-chip" data-cur="GBP">GBP</span>
                                <span class="badge badge-light border currency-chip" data-cur="RUB">RUB</span>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small text-muted mb-1">Ödeme Yöntemi</label>
                            <input type="text" name="payment_method" class="form-control" placeholder="Nakit / Kredi Kartı / EFT">
                            <small class="text-muted">Otomatik kayıtlar: sale-ticket, payout-owner, owner-share…</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small text-muted mb-1">Notlar</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Açıklama, referans no, belge no…"></textarea>
                    </div>

                    <div class="d-flex align-items-center mt-3">
                        <button class="btn btn-primary mr-2" type="submit"><i class="fas fa-save mr-1"></i> Kaydet</button>
                        <a href="{{ route('admin.accounting.index', $lockedAgencyId ? ['locked_agency_id' => $lockedAgencyId] : []) }}" class="btn btn-secondary">Vazgeç</a>
                        <span class="text-muted small ml-3"><i class="fas fa-info-circle"></i> Kayıt sonrası kilitli türler: sale-ticket / payout-owner / owner-share</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 bg-white">
                <h6 class="mb-0"><i class="fas fa-lightbulb text-warning mr-1"></i> Hızlı Bilgiler</h6>
            </div>
            <div class="card-body pt-2">
                <ul class="list-unstyled mb-3">
                    <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Gelir/Gider türünü doğru seçin.</li>
                    <li class="mb-2"><i class="fas fa-clock text-info mr-2"></i> Tarihi değiştirerek geçmiş/gelecek işlemleri ekleyin.</li>
                    <li class="mb-2"><i class="fas fa-lock text-secondary mr-2"></i> Otomatik işlemler (bilet/komisyon) editlenemez.</li>
                    <li class="mb-2"><i class="fas fa-coins text-primary mr-2"></i> Para birimi 3 harf ve büyük yazılmalıdır (TRY/USD/EUR…).</li>
                </ul>
                <div class="alert alert-light border">
                    <div class="d-flex">
                        <div class="mr-2"><i class="fas fa-link text-muted"></i></div>
                        <div>
                            <strong>Entegrasyon Notu:</strong><br>
                            API veya otomatik kayıtlar için `payment_method` alanını tutarlı kullanın (örn: sale-ticket, owner-share).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
/* Gelir/Gider butonlarında radio kutucuğunu gizle */
.btn-group-toggle .btn input[type="radio"] {
    position: absolute;
    clip: rect(0, 0, 0, 0);
    pointer-events: none;
}

/* Gelir butonu stilleri */
.btn-group-toggle .btn-outline-success {
    border-color: #28a745;
    color: #28a745;
    font-weight: 600;
}
.btn-group-toggle .btn-outline-success.active,
.btn-group-toggle .btn-outline-success:focus {
    background-color: #28a745;
    color: #fff;
    box-shadow: none;
}

/* Gider butonu stilleri */
.btn-group-toggle .btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
    font-weight: 600;
}
.btn-group-toggle .btn-outline-danger.active,
.btn-group-toggle .btn-outline-danger:focus {
    background-color: #dc3545;
    color: #fff;
    box-shadow: none;
}

/* Para birimi chip stilleri */
.currency-chip {
    cursor: pointer;
    padding: 6px 12px;
    margin-right: 4px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
    color: #212529 !important;
    font-weight: 500;
    background: #f8f9fa;
}
.currency-chip:hover {
    background: #007bff;
    border-color: #007bff;
    color: #fff !important;
}
.currency-chip.active {
    background: #007bff;
    border-color: #007bff;
    color: #fff !important;
}

/* Form genel iyileştirmeler */
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}

/* Select dropdown siyah metin */
.form-control option {
    color: #212529;
}

/* Input group text iyileştirme */
.input-group-text {
    background: #f8f9fa;
    border-color: #ced4da;
    color: #495057;
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chips = document.querySelectorAll('.currency-chip');
    const input = document.getElementById('currency-input');
    
    // Para birimi chip tıklama
    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            // Tüm chip'lerden active sınıfını kaldır
            chips.forEach(c => c.classList.remove('active'));
            // Tıklanan chip'e active sınıfı ekle
            chip.classList.add('active');
            // Input değerini güncelle
            input.value = chip.dataset.cur;
        });
    });
    
    // Başlangıçta TRY seçili olarak işaretle
    const tryChip = document.querySelector('.currency-chip[data-cur="TRY"]');
    if (tryChip) tryChip.classList.add('active');
    
    // Gelir/Gider butonları için JavaScript ile toggle
    const typeButtons = document.querySelectorAll('.btn-group-toggle .btn');
    typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            typeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
        });
    });
});
</script>
@endpush
@stop
<!-- end of the code -->