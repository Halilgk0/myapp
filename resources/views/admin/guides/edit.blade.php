@extends('layouts.admin')

@section('title', __('Rehber Düzenle'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="ad-card mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Rehber Bilgileri') }}</h3>
                </div>
                <form action="{{ route('admin.guides.update', $guide) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">{{ __('Ad Soyad') }} *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $guide->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- made by @hllgkx.0 -->
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">{{ __('E-posta') }} *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $guide->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">{{ __('Telefon') }}</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $guide->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">{{ __('Durum') }} *</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">{{ __('Durum Seçiniz') }}</option>
                                        <option value="Aktif" {{ old('status', $guide->status) == 'Aktif' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                        <option value="İzinli" {{ old('status', $guide->status) == 'İzinli' ? 'selected' : '' }}>{{ __('İzinli') }}</option>
                                        <option value="Servis Dışı" {{ old('status', $guide->status) == 'Servis Dışı' ? 'selected' : '' }}>{{ __('Servis Dışı') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_number">{{ __('Rehber Belgesi No') }}</label>
                                    <input type="text" class="form-control @error('license_number') is-invalid @enderror"
                                           id="license_number" name="license_number" value="{{ old('license_number', $guide->license_number) }}">
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_expiry">{{ __('Belge Geçerlilik Tarihi') }}</label>
                                    <input type="date" class="form-control @error('license_expiry') is-invalid @enderror"
                                           id="license_expiry" name="license_expiry"
                                           value="{{ old('license_expiry', $guide->license_expiry ? $guide->license_expiry->format('Y-m-d') : '') }}">
                                    @error('license_expiry')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hire_date">{{ __('İşe Başlama Tarihi') }}</label>
                                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                           id="hire_date" name="hire_date"
                                           value="{{ old('hire_date', $guide->hire_date ? $guide->hire_date->format('Y-m-d') : '') }}">
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <!-- made by @hllgkx.0 -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salary">{{ __('Maaş') }}</label>
                                    <input type="number" step="0.01" class="form-control @error('salary') is-invalid @enderror"
                                           id="salary" name="salary" value="{{ old('salary', $guide->salary) }}">
                                    @error('salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">{{ __('Adres') }}</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3">{{ old('address', $guide->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('Desteklenen Milliyetler') }}</label>
                            <div class="row">
                                @foreach(\App\Models\Guide::getNationalityOptions() as $code => $name)
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="supported_nationalities[]"
                                                   value="{{ $code }}" id="nationality_{{ $code }}"
                                                   {{ in_array($code, old('supported_nationalities', $guide->supported_nationalities ?? [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="nationality_{{ $code }}">
                                                {{ $name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('supported_nationalities')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">{{ __('Notlar') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes', $guide->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">{{ __('Güncelle') }}</button>
                        <a href="{{ route('admin.guides.index') }}" class="btn btn-secondary">{{ __('İptal') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
<!-- rehber düzenleme css-->
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
<!-- end of the code-->