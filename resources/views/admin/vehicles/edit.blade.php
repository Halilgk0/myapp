@extends('layouts.admin')

@section('title', 'Araç Düzenle')

@section('content')
    <div class="container-fluid">
        <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data" id="vehicle-form">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Sol: Form Bölümü -->
                <div class="col-lg-8">
                    <!-- Araç Türü Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-car-side"></i> Araç Türü
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label><i class="fas fa-list text-primary"></i> Araç Türü Seçiniz *</label>
                                <div class="row mt-2">
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="custom-control custom-radio vehicle-type-card">
                                            <input type="radio" id="type_car" name="vehicle_type" value="car" 
                                                   class="custom-control-input" {{ old('vehicle_type', $vehicle->vehicle_type) == 'car' ? 'checked' : '' }}>
                                            <label class="custom-control-label vehicle-type-label" for="type_car">
                                                <i class="fas fa-car fa-2x mb-2"></i>
                                                <span>Araba</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="custom-control custom-radio vehicle-type-card">
                                            <input type="radio" id="type_van" name="vehicle_type" value="van" 
                                                   class="custom-control-input" {{ old('vehicle_type', $vehicle->vehicle_type) == 'van' ? 'checked' : '' }}>
                                            <label class="custom-control-label vehicle-type-label" for="type_van">
                                                <i class="fas fa-shuttle-van fa-2x mb-2"></i>
                                                <span>Van</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="custom-control custom-radio vehicle-type-card">
                                            <input type="radio" id="type_minibus" name="vehicle_type" value="minibus" 
                                                   class="custom-control-input" {{ old('vehicle_type', $vehicle->vehicle_type) == 'minibus' ? 'checked' : '' }}>
                                            <label class="custom-control-label vehicle-type-label" for="type_minibus">
                                                <i class="fas fa-bus-alt fa-2x mb-2"></i>
                                                <span>Minibüs</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-2">
                                        <div class="custom-control custom-radio vehicle-type-card">
                                            <input type="radio" id="type_bus" name="vehicle_type" value="bus" 
                                                   class="custom-control-input" {{ old('vehicle_type', $vehicle->vehicle_type) == 'bus' ? 'checked' : '' }}>
                                            <label class="custom-control-label vehicle-type-label" for="type_bus">
                                                <i class="fas fa-bus fa-2x mb-2"></i>
                                                <span>Otobüs</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('vehicle_type')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Temel Bilgiler Card -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Temel Bilgiler
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="plate_number"><i class="fas fa-id-card text-primary"></i> Plaka Numarası *</label>
                                        <input type="text" class="form-control @error('plate_number') is-invalid @enderror" 
                                               id="plate_number" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number) }}" 
                                               placeholder="34 ABC 123" required>
                                        @error('plate_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="brand"><i class="fas fa-industry text-info"></i> Marka *</label>
                                        <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                               id="brand" name="brand" value="{{ old('brand', $vehicle->brand) }}" 
                                               placeholder="Mercedes, Ford, VW..." required>
                                        @error('brand')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="model"><i class="fas fa-tag text-success"></i> Model *</label>
                                        <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                               id="model" name="model" value="{{ old('model', $vehicle->model) }}" 
                                               placeholder="Sprinter, Transit..." required>
                                        @error('model')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="color"><i class="fas fa-palette text-warning"></i> Renk *</label>
                                        <select class="form-control @error('color') is-invalid @enderror" 
                                                id="color" name="color" required>
                                            <option value="">Renk Seçiniz</option>
                                            <option value="Beyaz" {{ old('color', $vehicle->color) == 'Beyaz' ? 'selected' : '' }}>Beyaz</option>
                                            <option value="Siyah" {{ old('color', $vehicle->color) == 'Siyah' ? 'selected' : '' }}>Siyah</option>
                                            <option value="Gümüş" {{ old('color', $vehicle->color) == 'Gümüş' ? 'selected' : '' }}>Gümüş</option>
                                            <option value="Gri" {{ old('color', $vehicle->color) == 'Gri' ? 'selected' : '' }}>Gri</option>
                                            <option value="Kırmızı" {{ old('color', $vehicle->color) == 'Kırmızı' ? 'selected' : '' }}>Kırmızı</option>
                                            <option value="Mavi" {{ old('color', $vehicle->color) == 'Mavi' ? 'selected' : '' }}>Mavi</option>
                                            <option value="Yeşil" {{ old('color', $vehicle->color) == 'Yeşil' ? 'selected' : '' }}>Yeşil</option>
                                            <option value="Sarı" {{ old('color', $vehicle->color) == 'Sarı' ? 'selected' : '' }}>Sarı</option>
                                            <option value="Lacivert" {{ old('color', $vehicle->color) == 'Lacivert' ? 'selected' : '' }}>Lacivert</option>
                                            <option value="Bordo" {{ old('color', $vehicle->color) == 'Bordo' ? 'selected' : '' }}>Bordo</option>
                                        </select>
                                        @error('color')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kapasite & Şoför Card -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Kapasite & Şoför
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="capacity"><i class="fas fa-users text-primary"></i> Yolcu Kapasitesi *</label>
                                        <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                               id="capacity" name="capacity" value="{{ old('capacity', $vehicle->capacity) }}" 
                                               min="1" max="100" required>
                                        @error('capacity')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Araçta kaç yolcu taşınabileceğini belirtin</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="driver_id"><i class="fas fa-user-tie text-success"></i> Şoför Ataması</label>
                                        <select class="form-control @error('driver_id') is-invalid @enderror" 
                                                id="driver_id" name="driver_id">
                                            <option value="">Şoför Seçiniz (Opsiyonel)</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ old('driver_id', $vehicle->driver_id) == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} ({{ $driver->phone_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('driver_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Araç Resmi Card -->
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-camera"></i> Araç Fotoğrafı
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($vehicle->image)
                            <div class="mb-3" id="current-image">
                                <label class="d-block"><i class="fas fa-image text-info"></i> Mevcut Resim</label>
                                <div class="position-relative d-inline-block">
                                    <img src="{{ asset('storage/' . $vehicle->image) }}" alt="{{ $vehicle->plate_number }}" 
                                         class="img-thumbnail" style="max-height: 180px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute" 
                                            style="top: 5px; right: 5px;" id="change-image">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>
                            </div>
                            @endif
                            
                            <div class="form-group mb-0" id="image-upload-section" style="{{ $vehicle->image ? 'display: none;' : '' }}">
                                <label for="image"><i class="fas fa-upload text-info"></i> {{ $vehicle->image ? 'Yeni Resim' : 'Araç Resmi' }}</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    <label class="custom-file-label" for="image" id="image-label">Dosya seçin...</label>
                                </div>
                                @error('image')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Maksimum 2MB, JPG, PNG, GIF formatları kabul edilir</small>
                                <div id="image-preview" class="mt-3" style="display: none;">
                                    <img src="" alt="Önizleme" class="img-thumbnail" style="max-height: 180px;">
                                    <button type="button" class="btn btn-sm btn-danger ml-2" id="remove-image">
                                        <i class="fas fa-times"></i> Kaldır
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notlar Card -->
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-sticky-note"></i> Notlar
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="notes"><i class="fas fa-comment text-muted"></i> Ek Bilgiler</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Araç hakkında ek bilgiler, özel durumlar...">{{ old('notes', $vehicle->notes) }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sağ: Durum ve İşlemler -->
                <div class="col-lg-4">
                    <!-- Durum Card -->
                    <div class="card card-success card-outline sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cog"></i> Durum
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label><i class="fas fa-toggle-on text-success"></i> Araç Durumu</label>
                                <div class="custom-control custom-switch mt-2">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $vehicle->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aracı Aktif Et</label>
                                </div>
                                <small class="form-text text-muted">Aktif araçlar operasyonlarda kullanılabilir</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-save"></i> Değişiklikleri Kaydet
                            </button>
                            <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> İptal
                            </a>
                        </div>
                    </div>

                    <!-- İstatistikler Card -->
                    <div class="card card-info card-outline mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar"></i> İstatistikler
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td><i class="fas fa-calendar-plus text-muted"></i> Oluşturulma</td>
                                    <td class="text-right"><strong>{{ $vehicle->created_at->format('d.m.Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-calendar-check text-muted"></i> Son Güncelleme</td>
                                    <td class="text-right"><strong>{{ $vehicle->updated_at->format('d.m.Y H:i') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-ticket-alt text-muted"></i> Toplam Bilet</td>
                                    <td class="text-right"><strong class="text-success">{{ $vehicle->tickets()->count() }}</strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-circle text-muted"></i> Durum</td>
                                    <td class="text-right">
                                        @if($vehicle->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Pasif</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($vehicle->driver)
                                <tr>
                                    <td><i class="fas fa-user-tie text-muted"></i> Mevcut Şoför</td>
                                    <td class="text-right"><strong>{{ $vehicle->driver->name }}</strong></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Bilgi Card -->
                    <div class="card card-light mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb text-warning"></i> Bilgi
                            </h3>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-unstyled mb-0" style="font-size: 13px;">
                                <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Araç türü değişikliği operasyonu etkileyebilir</li>
                                <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Kapasite değişikliği mevcut biletleri etkilemez</li>
                                <li class="mb-0"><i class="fas fa-info-circle text-info"></i> Pasif araçlar yeni atamalarda görünmez</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@push('css')
<style>
    /* Vehicle Type Selection */
    .vehicle-type-card {
        padding: 0;
    }
    
    .vehicle-type-card .custom-control-input {
        position: absolute;
        opacity: 0;
    }
    
    .vehicle-type-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px 10px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        background: #fff;
        width: 100%;
    }
    
    .vehicle-type-label::before,
    .vehicle-type-label::after {
        display: none !important;
    }
    
    .vehicle-type-label i {
        color: #6c757d;
        transition: color 0.2s ease;
    }
    
    .vehicle-type-label span {
        font-weight: 600;
        font-size: 0.9rem;
        color: #495057;
    }
    
    .vehicle-type-label:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }
    
    .vehicle-type-label:hover i {
        color: #007bff;
    }
    
    .vehicle-type-card .custom-control-input:checked + .vehicle-type-label {
        border-color: #007bff;
        background: #e7f1ff;
    }
    
    .vehicle-type-card .custom-control-input:checked + .vehicle-type-label i {
        color: #007bff;
    }
    
    .vehicle-type-card .custom-control-input:checked + .vehicle-type-label span {
        color: #007bff;
    }

    .sticky-top {
        position: sticky;
        z-index: 1020;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Change image button
    $('#change-image').on('click', function() {
        $('#current-image').hide();
        $('#image-upload-section').show();
    });
    
    // Custom file input label
    $('#image').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#image-label').text(fileName || 'Dosya seçin...');
        
        // Preview image
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview img').attr('src', e.target.result);
                $('#image-preview').show();
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Remove image
    $('#remove-image').on('click', function() {
        $('#image').val('');
        $('#image-label').text('Dosya seçin...');
        $('#image-preview').hide();
        
        @if($vehicle->image)
        $('#current-image').show();
        $('#image-upload-section').hide();
        @endif
    });
    
    // Form validation
    $('form').on('submit', function() {
        var isValid = true;
        
        // Check vehicle type
        if (!$('input[name="vehicle_type"]:checked').length) {
            alert('Lütfen araç türü seçiniz.');
            isValid = false;
        }
        
        // Required field validation
        $('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            return false;
        }
    });
});
</script>
@endpush
