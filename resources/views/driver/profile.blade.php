@extends('layouts.driver')

@section('title', 'Profil')

@section('content')
<div class="mb-4">
    <h1 style="font-size:22px;font-weight:700;color:var(--dr-text);margin:0 0 4px">
        <i data-lucide="user" style="width:22px;height:22px;display:inline-block;vertical-align:-3px;margin-right:6px;color:var(--dr-accent)"></i>
        Profil Bilgileri
    </h1>
    <p style="color:var(--dr-text-muted);font-size:13px;margin:0">Kişisel bilgilerinizi buradan güncelleyebilirsiniz.</p>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="user-pen" style="width:16px;height:16px"></i> Kişisel Bilgiler
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('driver.profile.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $driver->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" value="{{ $driver->email }}" disabled>
                        <small class="form-text text-muted">E-posta adresi değiştirilemez.</small>
                    </div>

                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Telefon Numarası</label>
                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                               id="phone_number" name="phone_number" value="{{ old('phone_number', $driver->phone_number) }}" required>
                        @error('phone_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Yeni Şifre</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" minlength="6">
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Şifrenizi değiştirmek istemiyorsanız boş bırakın.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save" style="width:15px;height:15px;display:inline-block;vertical-align:-2px;margin-right:4px"></i>
                        Güncelle
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="info" style="width:16px;height:16px"></i> Hesap Bilgileri
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="fw-semibold" style="width:40%">Kullanıcı Seviyesi</td>
                        <td><span class="badge bg-info text-dark">{{ $driver->level_label }}</span></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Hesap Durumu</td>
                        <td>
                            <span class="badge bg-{{ $driver->is_active ? 'success' : 'danger' }}">
                                {{ $driver->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Son Giriş</td>
                        <td>
                            @if($driver->last_login_at)
                                {{ $driver->last_login_at->format('d.m.Y H:i:s') }}
                            @else
                                <span class="text-muted">Hiç giriş yapılmamış</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Kayıt Tarihi</td>
                        <td>{{ $driver->created_at->format('d.m.Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($driver->vehicle)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i data-lucide="car" style="width:16px;height:16px"></i> Atanmış Araç
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box mb-0">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-car"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $driver->vehicle->brand }} {{ $driver->vehicle->model }}</span>
                        <span class="info-box-number">{{ $driver->vehicle->plate_number }}</span>
                        <span class="info-box-text">
                            <small class="text-muted">
                                {{ $driver->vehicle->vehicle_type }} &bull; {{ $driver->vehicle->color }} &bull; {{ $driver->vehicle->capacity }} kişilik
                            </small>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
