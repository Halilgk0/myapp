@extends('layouts.admin')

@section('title', __('Bilet İsteği Düzenle'))

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.ticket-requests.update', $ticketRequest) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Müşteri Bilgileri -->
            <div class="col-md-6">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> {{ __('Müşteri Bilgileri') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="customer_name">{{ __('Müşteri Adı') }} <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                                   value="{{ old('customer_name', $ticketRequest->customer_name) }}" required>
                            @error('customer_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_phone">{{ __('Telefon') }} <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror"
                                   value="{{ old('customer_phone', $ticketRequest->customer_phone) }}" required>
                            @error('customer_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_email">{{ __('E-posta') }}</label>
                            <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror"
                                   value="{{ old('customer_email', $ticketRequest->customer_email) }}">
                            @error('customer_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_nationality">{{ __('Uyruk') }} <span class="text-danger">*</span></label>
                            <select name="customer_nationality" id="customer_nationality" class="form-control @error('customer_nationality') is-invalid @enderror" required>
                                @foreach(\App\Models\Ticket::getNationalityOptions() as $code => $name)
                                    <option value="{{ $code }}" {{ old('customer_nationality', $ticketRequest->customer_nationality) == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_nationality')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="voucher_no">Voucher No</label>
                            <input type="text" name="voucher_no" id="voucher_no" class="form-control @error('voucher_no') is-invalid @enderror"
                                   value="{{ old('voucher_no', $ticketRequest->voucher_no) }}">
                            @error('voucher_no')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_location">{{ __('Alış Noktası') }}</label>
                                    <input type="text" name="pickup_location" id="pickup_location" class="form-control @error('pickup_location') is-invalid @enderror"
                                           value="{{ old('pickup_location', $ticketRequest->pickup_location) }}">
                                    @error('pickup_location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="room_number">{{ __('Oda No') }}</label>
                                    <input type="text" name="room_number" id="room_number" class="form-control @error('room_number') is-invalid @enderror"
                                           value="{{ old('room_number', $ticketRequest->room_number) }}">
                                    @error('room_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tur ve Fiyatlandırma -->
            <div class="col-md-6">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plane"></i> {{ __('Tur Bilgileri') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ __('Tur') }}</label>
                            <input type="text" class="form-control" value="{{ $ticketRequest->tour->name }}" disabled>
                            <small class="text-muted">{{ $ticketRequest->tour->country }}, {{ $ticketRequest->tour->city }}</small>
                        </div>

                        <div class="form-group">
                            <label for="tour_date">{{ __('Tur Tarihi') }} <span class="text-danger">*</span></label>
                            <input type="date" name="tour_date" id="tour_date" class="form-control @error('tour_date') is-invalid @enderror"
                                   value="{{ old('tour_date', $ticketRequest->tour_date->format('Y-m-d')) }}" required>
                            @error('tour_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="currency">{{ __('Para Birimi') }} <span class="text-danger">*</span></label>
                            <select name="currency" id="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                <option value="TRY" {{ old('currency', $ticketRequest->currency) == 'TRY' ? 'selected' : '' }}>₺ TRY</option>
                                <option value="EUR" {{ old('currency', $ticketRequest->currency) == 'EUR' ? 'selected' : '' }}>€ EUR</option>
                                <option value="USD" {{ old('currency', $ticketRequest->currency) == 'USD' ? 'selected' : '' }}>$ USD</option>
                                <option value="GBP" {{ old('currency', $ticketRequest->currency) == 'GBP' ? 'selected' : '' }}>£ GBP</option>
                                <option value="RUB" {{ old('currency', $ticketRequest->currency) == 'RUB' ? 'selected' : '' }}>₽ RUB</option>
                            </select>
                            @error('currency')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> {{ __('Yolcu ve Fiyatlandırma') }}</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Yolcu Tipi') }}</th>
                                    <th class="text-center" style="width: 100px;">{{ __('Adet') }}</th>
                                    <th class="text-center" style="width: 120px;">{{ __('Birim Fiyat') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ __('Yetişkin') }}</td>
                                    <td>
                                        <input type="number" name="adult_count" class="form-control text-center"
                                               value="{{ old('adult_count', $ticketRequest->adult_count) }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="adult_price" class="form-control text-center"
                                               value="{{ old('adult_price', $ticketRequest->adult_price) }}" min="0" step="0.01" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('Çocuk') }}</td>
                                    <td>
                                        <input type="number" name="child_count" class="form-control text-center"
                                               value="{{ old('child_count', $ticketRequest->child_count) }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="child_price" class="form-control text-center"
                                               value="{{ old('child_price', $ticketRequest->child_price) }}" min="0" step="0.01" required>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ __('Bebek') }}</td>
                                    <td>
                                        <input type="number" name="infant_count" class="form-control text-center"
                                               value="{{ old('infant_count', $ticketRequest->infant_count) }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="infant_price" class="form-control text-center"
                                               value="{{ old('infant_price', $ticketRequest->infant_price) }}" min="0" step="0.01" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($ticketRequest->isReturned() && $ticketRequest->return_reason)
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> {{ __('Geri Gönderilme Sebebi') }}</h5>
                <p class="mb-0">{{ $ticketRequest->return_reason }}</p>
            </div>
        @endif

        <div class="card">
            <div class="card-body text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> {{ __('Kaydet') }}
                </button>
                <a href="{{ route('admin.ticket-requests.show', $ticketRequest) }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> {{ __('İptal') }}
                </a>
            </div>
        </div>
    </form>
</div>
@stop

