@extends('layouts.admin')

@section('title', __('Acenta Düzenle'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.agencies.update', $agency) }}" method="POST">
                @csrf
                @method('PUT')
                
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
                            {!! __("Bu acenta :link sayfasındaki kullanıcı ID'lerine bağlıdır.", ['link' => '<a href="' . route(auth()->user()?->isAdmin() ? 'admin.agencies.network' : 'agencies.network') . '" target="_blank">' . __('Acenta Ağım') . '</a>']) !!}
                            {{ __('Gerekirse yeni kullanıcı ID girip doğrulayabilirsiniz.') }}
                        </div>
                        <div class="form-group">
                            <label for="user_id">{{ __('Kullanıcı ID') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control @error('user_id') is-invalid @enderror"
                                       id="user_id"
                                       name="user_id"
                                       value="{{ old('user_id', $agency->user_id) }}"
                                       required
                                       min="1">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-info" type="button" id="lookup-user-btn">
                                        <i class="fas fa-search"></i> {{ __('Doğrula') }}
                                    </button>
                                </div>
                            </div>
                            @error('user_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">{{ __("Kullanıcının adı ve e-postası doğrulama sonrası gösterilir.") }}</small>
                            <div id="lookup-user-result" class="small mt-2 text-muted">
                                @if($agency->user)
                                    <span class="text-success"><i class="fas fa-check-circle"></i> {{ $agency->user->name }} ({{ $agency->user->email }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('Acenta Adı') }}</label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $agency->name) }}"
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
                                           value="{{ old('contact_person', $agency->contact_person) }}">
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
                                           value="{{ old('email', $agency->email) }}">
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
                                           value="{{ old('phone', $agency->phone) }}">
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
                                   value="{{ old('website', $agency->website) }}"
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
                                      rows="3">{{ old('address', $agency->address) }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

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
                                               value="{{ old('commission_rate', $agency->commission_rate) }}"
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
                                        <option value="1" {{ old('is_active', $agency->is_active) == '1' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                        <option value="0" {{ old('is_active', $agency->is_active) == '0' ? 'selected' : '' }}>{{ __('Pasif') }}</option>
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
                                      placeholder="{{ __('Acenta ile ilgili özel notlar...') }}">{{ old('notes', $agency->notes) }}</textarea>
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
                            {{ __('Değişiklikleri Kaydet') }}
                        </button>
                        <a href="{{ route('admin.agencies.show', $agency) }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-eye mr-1"></i>
                            {{ __('Görüntüle') }}
                        </a>
                        <a href="{{ route('admin.agencies.index') }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-list mr-1"></i>
                            {{ __('Listeye Dön') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Agency Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ __('Acenta Bilgileri') }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-building fa-3x text-muted"></i>
                        <h4 class="mt-2">{{ $agency->name }}</h4>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $agency->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Oluşturma') }}:</strong></td>
                            <td>{{ $agency->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Güncelleme') }}:</strong></td>
                            <td>{{ $agency->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Bilet Sayısı') }}:</strong></td>
                            <td>
                                @if($agency->tickets_count ?? 0 > 0)
                                    <span class="badge badge-info">{{ __(':count bilet', ['count' => $agency->tickets_count ?? 0]) }}</span>
                                @else
                                    <span class="text-muted">{{ __('Henüz bilet yok') }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb mr-1"></i>
                        {{ __('İpuçları') }}
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> {{ __('Değişiklikler otomatik kaydedilmez') }}</li>
                        <li><i class="fas fa-exclamation text-warning"></i> {{ __('Pasif acentalar yeni bilet alamaz') }}</li>
                        <li><i class="fas fa-info text-info"></i> {{ __('Komisyon oranı mevcut biletleri etkilemez') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
<!-- acenta düzenleme css-->
@push('css')
<style>
    .required {
        color: #dc3545;
    }
</style>
@endpush
<!-- acenta düzenleme js-->
@push('js')
<script>
const agencyEditI18n = {!! json_encode([
    'updating' => __('Güncelleniyor...'),
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
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + agencyEditI18n.updating);
    });

    const lookupButton = document.getElementById('lookup-user-btn');
    const userIdInput = document.getElementById('user_id');
    const resultBox = document.getElementById('lookup-user-result');

    if (lookupButton && userIdInput && resultBox) {
        lookupButton.addEventListener('click', function () {
            const userId = userIdInput.value.trim();
            if (!userId) {
                resultBox.textContent = agencyEditI18n.enterValidId;
                resultBox.classList.add('text-danger');
                return;
            }

            resultBox.textContent = agencyEditI18n.fetching;
            resultBox.classList.remove('text-danger');

            fetch('{{ route('agencies.users.lookup') }}?id=' + userId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(agencyEditI18n.notFound);
                    }
                    return response.json();
                })
                .then(data => {
                    resultBox.classList.remove('text-danger');
                    resultBox.innerHTML = `
                        <span class="text-success"><i class="fas fa-check-circle"></i> ${agencyEditI18n.found.replace(':name', data.name)}</span><br>
                        <small>${data.email} • ${data.level_label}${data.agency ? ' • ' + agencyEditI18n.agencyLabel + ': ' + data.agency.name : ''}</small>
                    `;
                })
                .catch(() => {
                    resultBox.classList.add('text-danger');
                    resultBox.textContent = agencyEditI18n.notFoundOrInvalid;
                });
        });
    }
});
</script>
@endpush
<!-- end of the code -->