@extends('layouts.admin')

@section('title', 'Acenta Düzenle')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.agencies.update', $agency) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Basic Information Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-1"></i>
                            Temel Bilgiler
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            Bu acenta <a href="{{ route(auth()->user()?->isAdmin() ? 'admin.agencies.network' : 'agencies.network') }}" target="_blank">Acenta Ağım</a> sayfasındaki kullanıcı ID'lerine bağlıdır.
                            Gerekirse yeni kullanıcı ID girip doğrulayabilirsiniz.
                        </div>
                        <div class="form-group">
                            <label for="user_id">Kullanıcı ID <span class="text-danger">*</span></label>
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
                                        <i class="fas fa-search"></i> Doğrula
                                    </button>
                                </div>
                            </div>
                            @error('user_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Kullanıcının adı ve e-postası doğrulama sonrası gösterilir.</small>
                            <div id="lookup-user-result" class="small mt-2 text-muted">
                                @if($agency->user)
                                    <span class="text-success"><i class="fas fa-check-circle"></i> {{ $agency->user->name }} ({{ $agency->user->email }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Acenta Adı  </label>
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
                                    <label for="contact_person">İletişim Kişisi</label>
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
                                    <label for="phone">Telefon</label>
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
                            <label for="website">Website</label>
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
                            <label for="address">Adres</label>
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-percent mr-1"></i>
                            İş Bilgileri
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="commission_rate">Komisyon Oranı (%)  </label>
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
                                    <label for="is_active">Durum</label>
                                    <select class="form-control @error('is_active') is-invalid @enderror" 
                                            id="is_active" 
                                            name="is_active">
                                        <option value="1" {{ old('is_active', $agency->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ old('is_active', $agency->is_active) == '0' ? 'selected' : '' }}>Pasif</option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notlar</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="4" 
                                      placeholder="Acenta ile ilgili özel notlar...">{{ old('notes', $agency->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            Değişiklikleri Kaydet
                        </button>
                        <a href="{{ route('admin.agencies.show', $agency) }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-eye mr-1"></i>
                            Görüntüle
                        </a>
                        <a href="{{ route('admin.agencies.index') }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-list mr-1"></i>
                            Listeye Dön
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
                        Acenta Bilgileri
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
                            <td><strong>Oluşturma:</strong></td>
                            <td>{{ $agency->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Güncelleme:</strong></td>
                            <td>{{ $agency->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Bilet Sayısı:</strong></td>
                            <td>
                                @if($agency->tickets_count ?? 0 > 0)
                                    <span class="badge badge-info">{{ $agency->tickets_count ?? 0 }} bilet</span>
                                @else
                                    <span class="text-muted">Henüz bilet yok</span>
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
                        İpuçları
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Değişiklikler otomatik kaydedilmez</li>
                        <li><i class="fas fa-exclamation text-warning"></i> Pasif acentalar yeni bilet alamaz</li>
                        <li><i class="fas fa-info text-info"></i> Komisyon oranı mevcut biletleri etkilemez</li>
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
$(document).ready(function() {
    // Form validation feedback
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Güncelleniyor...');
    });

    const lookupButton = document.getElementById('lookup-user-btn');
    const userIdInput = document.getElementById('user_id');
    const resultBox = document.getElementById('lookup-user-result');

    if (lookupButton && userIdInput && resultBox) {
        lookupButton.addEventListener('click', function () {
            const userId = userIdInput.value.trim();
            if (!userId) {
                resultBox.textContent = 'Lütfen geçerli bir kullanıcı ID girin.';
                resultBox.classList.add('text-danger');
                return;
            }

            resultBox.textContent = 'Kullanıcı bilgileri alınıyor...';
            resultBox.classList.remove('text-danger');

            fetch('{{ route('agencies.users.lookup') }}?id=' + userId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Kullanıcı bulunamadı');
                    }
                    return response.json();
                })
                .then(data => {
                    resultBox.classList.remove('text-danger');
                    resultBox.innerHTML = `
                        <span class="text-success"><i class="fas fa-check-circle"></i> ${data.name} bulundu.</span><br>
                        <small>${data.email} • ${data.level_label}${data.agency ? ' • Acenta: ' + data.agency.name : ''}</small>
                    `;
                })
                .catch(() => {
                    resultBox.classList.add('text-danger');
                    resultBox.textContent = 'Kullanıcı bulunamadı veya ID hatalı.';
                });
        });
    }
});
</script>
@endpush
<!-- end of the code -->