@extends('layouts.admin')

@section('title', __('Yeni Şoför Ekle'))
@push('css')
<style>
/* Nationality chips */
.nationality-chips { display:flex; flex-wrap:wrap; gap:8px; border:1px solid #e9ecef; border-radius:8px; padding:10px; background:#fff; }
.chip { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border:1px solid #dee2e6; border-radius:999px; cursor:pointer; user-select:none; background:#f8f9fa; color:#212529; transition:all .2s; }
.chip:hover { background:#eef2f7; }
.chip input[type="checkbox"] { display:none; }
.chip-text { color:inherit; }
.chip-selected { background:#e7f1ff; border-color:#b6d4fe; }
.chip-selected .chip-text { font-weight:600; color:#0d6efd; }
.nationality-toolbar .btn { line-height:1.1; }

/* Dark mode */
html.dark-mode .nationality-chips { background:#0f172a !important; border-color:#334155 !important; }
html.dark-mode .chip { background:#1e293b !important; border-color:#334155 !important; color:#e2e8f0 !important; }
html.dark-mode .chip-text { color:#e2e8f0 !important; }
html.dark-mode .chip:hover { background:#334155 !important; }
html.dark-mode .chip-selected { background:#1e3a8a !important; border-color:#3b82f6 !important; }
html.dark-mode .chip-selected .chip-text { color:#93c5fd !important; }
html.dark-mode .nationality-search { background:#0f172a !important; border-color:#334155 !important; color:#e2e8f0 !important; }
html.dark-mode .nationality-search::placeholder { color:#64748b !important; }

/* Salary day picker */
.salary-day-picker { display:grid; grid-template-columns: repeat(7, 1fr); gap:6px; padding:10px; background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; }
.salary-day-cell { display:flex; align-items:center; justify-content:center; background:#fff; border:1px solid #ced4da; border-radius:6px; cursor:pointer; font-weight:500; color:#495057; transition:all .15s; user-select:none; font-size:14px; height:38px; }
.salary-day-cell:hover { background:#eef2f7; border-color:#adb5bd; }
.salary-day-cell.selected { background:#0d6efd; border-color:#0a58ca; color:#fff; box-shadow:0 2px 6px rgba(13,110,253,0.35); }
.salary-day-presets { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px; }
.salary-day-preset { padding:4px 12px; background:#fff; border:1px solid #ced4da; border-radius:999px; cursor:pointer; font-size:12px; color:#495057; transition:all .15s; }
.salary-day-preset:hover { background:#eef2f7; border-color:#adb5bd; }
.salary-day-preset.active { background:#e7f1ff; border-color:#b6d4fe; color:#0d6efd; font-weight:600; }
.salary-day-summary { margin-top:8px; padding:8px 12px; background:#e7f3ff; border-left:3px solid #0d6efd; border-radius:4px; font-size:13px; color:#084298; }

html.dark-mode .salary-day-picker { background:#0f172a !important; border-color:#334155 !important; }
html.dark-mode .salary-day-cell { background:#1e293b !important; border-color:#334155 !important; color:#e2e8f0 !important; }
html.dark-mode .salary-day-cell:hover { background:#334155 !important; }
html.dark-mode .salary-day-cell.selected { background:#1e3a8a !important; border-color:#3b82f6 !important; color:#dbeafe !important; }
html.dark-mode .salary-day-preset { background:#1e293b !important; border-color:#334155 !important; color:#e2e8f0 !important; }
html.dark-mode .salary-day-preset:hover { background:#334155 !important; }
html.dark-mode .salary-day-preset.active { background:#1e3a8a !important; border-color:#3b82f6 !important; color:#93c5fd !important; }
html.dark-mode .salary-day-summary { background:#1e3a5f !important; border-left-color:#3b82f6 !important; color:#93c5fd !important; }
</style>
@endpush
<!-- şoför ekleme formu -->
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Şoför Bilgileri') }}</h3>
                    </div>
                    <form action="{{ route('admin.drivers.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">{{ __('Ad Soyad') }} *</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name') }}"
                                               placeholder="Ahmet Yılmaz" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">{{ __('E-posta') }} *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email') }}"
                                               placeholder="ahmet@example.com" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone_number">{{ __('Telefon Numarası') }} *</label>
                                        <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                               id="phone_number" name="phone_number" value="{{ old('phone_number') }}"
                                               placeholder="0555 123 45 67" required>
                                        @error('phone_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">{{ __('Şifre') }} *</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Desteklenen Milliyetler (Pill-Style) -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>{{ __('Desteklenen Milliyetler') }}</label>
                                        <div class="nationality-toolbar d-flex align-items-center mb-2" id="nationalityToolbarCreate">
                                            <input type="text" class="form-control form-control-sm nationality-search" placeholder="{{ __('Milliyet ara...') }}" style="max-width: 260px;">
                                            <button type="button" class="btn btn-sm btn-outline-primary ml-2 btn-select-all">{{ __('Tümünü Seç') }}</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ml-2 btn-clear">{{ __('Temizle') }}</button>
                                            <span class="badge badge-info ml-2 nationality-selected-count">{{ __(':count seçili', ['count' => 0]) }}</span>
                                        </div>
                                        <div class="nationality-chips" id="nationalityChipsCreate">
                                            @foreach(\App\Models\User::getNationalityOptions() as $code => $name)
                                                @php
                                                    $isChecked = in_array($code, old('supported_nationalities', []));
                                                @endphp
                                                <label class="chip {{ $isChecked ? 'chip-selected' : '' }}" data-code="{{ $code }}" data-name="{{ $name }}">
                                                    <input type="checkbox" name="supported_nationalities[]" value="{{ $code }}" {{ $isChecked ? 'checked' : '' }}>
                                                    <span class="chip-text">{{ $name }} ({{ $code }})</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <small class="form-text text-muted">
                                            {{ __('Hiçbiri seçilmezse tüm milliyetlerden yolcu alabilir') }}
                                        </small>
                                        @error('supported_nationalities')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="vehicle_id">{{ __('Araç Atama') }}</label>
                                        <select class="form-control @error('vehicle_id') is-invalid @enderror"
                                                id="vehicle_id" name="vehicle_id">
                                            <option value="">{{ __('Araç Seçiniz (Opsiyonel)') }}</option>
                                            @foreach($availableVehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->plate_number }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vehicle_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="salary_amount">{{ __('Maaş Tutarı') }}</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" class="form-control @error('salary_amount') is-invalid @enderror"
                                                   id="salary_amount" name="salary_amount" value="{{ old('salary_amount', '0') }}" placeholder="Örn: 15000">
                                            <select class="form-control col-4 @error('salary_currency') is-invalid @enderror" name="salary_currency" id="salary_currency">
                                                @php $curr = old('salary_currency','TRY'); @endphp
                                                <option value="TRY" {{ $curr==='TRY'?'selected':'' }}>TRY</option>
                                                <option value="USD" {{ $curr==='USD'?'selected':'' }}>USD</option>
                                                <option value="EUR" {{ $curr==='EUR'?'selected':'' }}>EUR</option>
                                                <option value="GBP" {{ $curr==='GBP'?'selected':'' }}>GBP</option>
                                                <option value="RUB" {{ $curr==='RUB'?'selected':'' }}>RUB</option>
                                            </select>
                                        </div>
                                        @error('salary_amount') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                        @error('salary_currency') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Maaş Günü (aylık)') }}</label>
                                        @php $salaryDay = (int) old('salary_day', 1); @endphp
                                        <div class="salary-day-presets">
                                            <span class="salary-day-preset" data-day="1">{{ __("Ayın :day'i", ['day' => 1]) }}</span>
                                            <span class="salary-day-preset" data-day="10">{{ __("Ayın :day'i", ['day' => 10]) }}</span>
                                            <span class="salary-day-preset" data-day="15">{{ __("Ayın :day'i", ['day' => 15]) }}</span>
                                            <span class="salary-day-preset" data-day="20">{{ __("Ayın :day'i", ['day' => 20]) }}</span>
                                            <span class="salary-day-preset" data-day="28">{{ __('Ay sonu (:day)', ['day' => 28]) }}</span>
                                        </div>
                                        <div class="salary-day-picker" id="salaryDayPicker">
                                            @for($d=1;$d<=28;$d++)
                                                <div class="salary-day-cell {{ $salaryDay === $d ? 'selected' : '' }}" data-day="{{ $d }}">{{ $d }}</div>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="salary_day" id="salary_day" value="{{ $salaryDay }}">
                                        <div class="salary-day-summary">
                                            <i class="fas fa-info-circle"></i> {!! __('Her ayın :day günü otomatik maaş gideri oluşur.', ['day' => '<strong id="salaryDayLabel">' . $salaryDay . '.</strong>']) !!}
                                        </div>
                                        @error('salary_day') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_active">{{ __('Aktif') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('Kaydet') }}
                            </button>
                            <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('İptal') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                        <div class="ad-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Bilgi') }}</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ __('E-posta') }}:</strong> {{ __('Şoför giriş yapmak için kullanacak') }}</p>
                        <p><strong>{{ __('Telefon') }}:</strong> {{ __('İletişim için kullanılacak') }}</p>
                        <p><strong>{{ __('Şifre') }}:</strong> {{ __('En az 6 karakter olmalı') }}</p>
                        <p><strong>{{ __('Araç Atama') }}:</strong> {{ __('Şoföre araç atayabilirsiniz (opsiyonel)') }}</p>
                        <p><strong>{{ __('Maaş Günü') }}:</strong> {{ __('Her ay seçilen günde otomatik maaş gideri yazılır') }}</p>
                        <p><strong>{{ __('Aktif') }}:</strong> {{ __('Şoförün sisteme giriş yapabilmesi için gerekli') }}</p>
                    </div>
                </div>

                @if($availableVehicles->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Atanabilir Araçlar') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Plaka') }}</th>
                                        <th>{{ __('Marka/Model') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availableVehicles as $vehicle)
                                    <tr>
                                        <td>{{ $vehicle->plate_number }}</td>
                                        <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@stop
<!-- js -->
@push('js')
    <script>
        // Form validation
        $(document).ready(function() {
            var driverFormI18n = {!! json_encode([
                'fillRequired' => __('Lütfen tüm zorunlu alanları doldurunuz.'),
                'selectedCount' => __(':count seçili'),
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
            window.__driverFormI18n = driverFormI18n;
            $('form').on('submit', function() {
                var isValid = true;

                // Required field validation
                $('input[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    alert(driverFormI18n.fillRequired);
                    return false;
                }
            });
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        function initNationalityWidget(root){
            if (!root) return;
            const search = root.querySelector('.nationality-search');
            const chips = root.parentElement.querySelector('.nationality-chips');
            const countBadge = root.querySelector('.nationality-selected-count');
            const btnSelectAll = root.querySelector('.btn-select-all');
            const btnClear = root.querySelector('.btn-clear');

            const updateCount = () => {
                const checked = chips ? chips.querySelectorAll('input[type="checkbox"]:checked').length : 0;
                if (countBadge) countBadge.textContent = (window.__driverFormI18n ? window.__driverFormI18n.selectedCount.replace(':count', checked) : checked + ' seçili');
            };

            if (chips) {
                chips.addEventListener('click', function(e){
                    const label = e.target.closest('label.chip');
                    if (!label) return;
                    const input = label.querySelector('input[type="checkbox"]');
                    if (!input) return;
                    input.checked = !input.checked;
                    label.classList.toggle('chip-selected', input.checked);
                    updateCount();
                });
            }

            if (search && chips) {
                search.addEventListener('input', function(){
                    const q = this.value.toLowerCase().trim();
                    chips.querySelectorAll('label.chip').forEach(ch => {
                        const text = (ch.getAttribute('data-name') + ' ' + ch.getAttribute('data-code')).toLowerCase();
                        ch.style.display = q === '' || text.includes(q) ? '' : 'none';
                    });
                });
            }

            if (btnSelectAll && chips) {
                btnSelectAll.addEventListener('click', function(){
                    chips.querySelectorAll('label.chip').forEach(ch => {
                        const input = ch.querySelector('input[type="checkbox"]');
                        if (input && ch.style.display !== 'none') {
                            input.checked = true;
                            ch.classList.add('chip-selected');
                        }
                    });
                    updateCount();
                });
            }

            if (btnClear && chips) {
                btnClear.addEventListener('click', function(){
                    chips.querySelectorAll('label.chip input[type="checkbox"]').forEach(input => { input.checked = false; });
                    chips.querySelectorAll('label.chip').forEach(ch => ch.classList.remove('chip-selected'));
                    updateCount();
                });
            }

            updateCount();
        }

        // Create page toolbar has create-specific IDs/classes
        const toolbarCreate = document.getElementById('nationalityToolbarCreate');
        if (toolbarCreate) initNationalityWidget(toolbarCreate);

        // Salary day picker
        (function(){
            var picker = document.getElementById('salaryDayPicker');
            var input = document.getElementById('salary_day');
            var label = document.getElementById('salaryDayLabel');
            if (!picker || !input) return;
            var presets = document.querySelectorAll('.salary-day-preset');

            function syncPresets(day){
                presets.forEach(function(p){
                    p.classList.toggle('active', parseInt(p.dataset.day, 10) === day);
                });
            }

            function select(day){
                day = parseInt(day, 10);
                if (!day || day < 1 || day > 28) return;
                input.value = day;
                if (label) label.textContent = day + '.';
                picker.querySelectorAll('.salary-day-cell').forEach(function(c){
                    c.classList.toggle('selected', parseInt(c.dataset.day, 10) === day);
                });
                syncPresets(day);
            }

            picker.addEventListener('click', function(e){
                var cell = e.target.closest('.salary-day-cell');
                if (cell) select(cell.dataset.day);
            });

            presets.forEach(function(p){
                p.addEventListener('click', function(){ select(p.dataset.day); });
            });

            syncPresets(parseInt(input.value, 10));
        })();
    });
    </script>
@endpush
<!-- end of the code -->