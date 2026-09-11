@extends('layouts.admin')
<!-- acenta ekleme sayfası-->
@section('title', __('Yeni Acenta Ekle'))
<!-- Acenta ekleme sayfası-->
@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('Yeni Acenta Ekle') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Ana Sayfa') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.agencies.index') }}">{{ __('Acenta Yönetimi') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Yeni Acenta') }}</li>
                </ol>
            </div>
        </div>
    </div>
@stop
<!-- acenta ekleme formu-->
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.agencies.store') }}" method="POST">
                @csrf
                
                <!-- Basic Information Card -->
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-1"></i>
                            {{ __('Temel Bilgiler') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            {{ __('Bu acenta mutlaka sistemde kayıtlı bir kullanıcıya bağlı olmalıdır.') }}
                            {!! __("Kullanıcılar kendi ID'lerini :link sayfasından görebilir.", ['link' => '<a href="' . route(auth()->user()?->isAdmin() ? 'admin.agencies.network' : 'agencies.network') . '" target="_blank">' . __('Acenta Ağım') . '</a>']) !!}
                        </div>
                        <div class="form-group">
                            <label for="user_id">{{ __('Kullanıcı ID') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control @error('user_id') is-invalid @enderror"
                                       id="user_id"
                                       name="user_id"
                                       value="{{ old('user_id') }}"
                                       required
                                       min="1"
                                       placeholder="{{ __('Örn: 42') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-info" type="button" id="lookup-user-btn">
                                        <i class="fas fa-search"></i> {{ __('Doğrula') }}
                                    </button>
                                </div>
                            </div>
                            @error('user_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">{{ __("ID'yi girdikten sonra doğrula butonuyla kullanıcı bilgilerini kontrol edin.") }}</small>
                            <div id="lookup-user-result" class="small mt-2 text-muted"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('Acenta Adı') }}</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">{{ __('İletişim Kişisi') }}</label>
                                    <input type="text"
                                           class="form-control @error('contact_person') is-invalid @enderror"
                                           id="contact_person"
                                           name="contact_person"
                                           value="{{ old('contact_person') }}">
                                    @error('contact_person')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}">
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">{{ __('Telefon') }}</label>
                                    <input type="text"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="website">{{ __('Website') }}</label>
                            <input type="url"
                                   class="form-control @error('website') is-invalid @enderror"
                                   id="website"
                                   name="website"
                                   value="{{ old('website') }}"
                                   placeholder="https://www.example.com">
                            @error('website')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="address">{{ __('Adres') }}</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address"
                                      name="address"
                                      rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- made by @hllgkx.0 -->
                <!-- Business Information Card -->
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-percent mr-1"></i>
                            {{ __('İş Bilgileri') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="commission_rate">{{ __('Komisyon Oranı (%)') }}</label>
                                    <div class="input-group">
                                        <input type="number"
                                               class="form-control @error('commission_rate') is-invalid @enderror"
                                               id="commission_rate"
                                               name="commission_rate"
                                               value="{{ old('commission_rate', '0.00') }}"
                                               step="0.01"
                                               min="0"
                                               max="100"
                                               required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    @error('commission_rate')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">{{ __('Durum') }}</label>
                                    <select class="form-control @error('is_active') is-invalid @enderror"
                                            id="is_active"
                                            name="is_active">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ __('Pasif') }}</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">{{ __('Notlar') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="4"
                                      placeholder="{{ __('Acenta ile ilgili özel notlar...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="ad-card mb-3">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            {{ __('Acentayı Kaydet') }}
                        </button>
                        <a href="{{ route('admin.agencies.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-1"></i>
                            {{ __('İptal') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Help Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('Bilgi') }}
                    </h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-star text-warning"></i> {{ __('Gerekli Alanlar') }}</h6>
                    <ul class="list-unstyled">
                        <li><strong>{{ __('Acenta Adı') }}:</strong> {{ __('Zorunlu alan') }}</li>
                        <li><strong>{{ __('Komisyon Oranı') }}:</strong> {{ __('0-100 arası değer') }}</li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-lightbulb text-info"></i> {{ __('İpuçları') }}</h6>
                    <ul class="list-unstyled">
                        <li>• {{ __('Website URL\'si "https://" ile başlamalı') }}</li>
                        <li>• {{ __('Komisyon oranı ondalık olarak girilebilir (örn: 15.50)') }}</li>
                        <li>• {{ __('Pasif acentalar sistemde görünmez') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
<!-- acenta ekleme css-->
@push('css')
<style>
    .required {
        color: #dc3545;
    }
</style>
@endpush
<!-- acenta ekleme js-->
@push('js')
<script>
const agencyCreateI18n = {!! json_encode([
    'saving' => __('Kaydediliyor...'),
    'enterValidId' => __('Lütfen geçerli bir kullanıcı ID girin.'),
    'fetching' => __('Kullanıcı bilgileri alınıyor...'),
    'notFound' => __('Kullanıcı bulunamadı'),
    'found' => __(':name bulundu.'),
    'agencyLabel' => __('Acenta'),
    'notFoundOrInvalid' => __('Kullanıcı bulunamadı veya ID hatalı.'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
$(document).ready(function() {
    // Form validation feedback
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + agencyCreateI18n.saving);
    });

    const lookupButton = document.getElementById('lookup-user-btn');
    const userIdInput = document.getElementById('user_id');
    const resultBox = document.getElementById('lookup-user-result');

    if (lookupButton && userIdInput && resultBox) {
        lookupButton.addEventListener('click', function () {
            const userId = userIdInput.value.trim();
            if (!userId) {
                resultBox.textContent = agencyCreateI18n.enterValidId;
                resultBox.classList.add('text-danger');
                return;
            }

            resultBox.textContent = agencyCreateI18n.fetching;
            resultBox.classList.remove('text-danger');

            fetch('{{ route('agencies.users.lookup') }}?id=' + userId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(agencyCreateI18n.notFound);
                    }
                    return response.json();
                })
                .then(data => {
                    resultBox.classList.remove('text-danger');
                    resultBox.innerHTML = `
                        <span class="text-success"><i class="fas fa-check-circle"></i> ${agencyCreateI18n.found.replace(':name', data.name)}</span><br>
                        <small>${data.email} • ${data.level_label}${data.agency ? ' • ' + agencyCreateI18n.agencyLabel + ': ' + data.agency.name : ''}</small>
                    `;
                })
                .catch(() => {
                    resultBox.classList.add('text-danger');
                    resultBox.textContent = agencyCreateI18n.notFoundOrInvalid;
                });
        });
    }
});
</script>
@endpush
<!-- end of the code -->