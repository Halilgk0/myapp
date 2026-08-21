@extends('layouts.agency')

@section('title', 'Tur Yönetimi')

@section('content')
<div class="ag-page-header">
    <h1 class="ag-page-title">Tur Yönetimi</h1>
    <p class="ag-page-subtitle">Bağlantılı olduğunuz acentaların sizinle paylaştığı turlar</p>
</div>

<div class="ag-card">
    <div class="ag-card-header">
        <h3 class="ag-card-title">
            <i data-lucide="map"></i>
            Paylaşılan Turlar
            <span class="ag-badge ag-badge-primary" style="margin-left:8px">{{ count($sharedTours) }}</span>
        </h3>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th>Tur</th>
                        <th>Bilet</th>
                        <th>Ücret</th>
                        <th>Paylaşan</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sharedTours as $tour)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $tour->name }}</div>
                            @if($tour->description)
                            <div class="ag-text-muted" style="font-size:12px;max-width:250px;white-space:normal">{{ Str::limit($tour->description, 60) }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-secondary">{{ $tour->tickets_count ?? 0 }} bilet</span>
                        </td>
                        <td>
                            @php
                                $pricing = $tour->shared_pricing ?? [
                                    'has_custom_price' => false,
                                    'max_price' => $tour->max_display_price ?? 0,
                                    'currency' => $tour->display_currency ?? ($tour->currency ?? 'TRY'),
                                ];
                                $maxPrice = (float) ($pricing['max_price'] ?? 0);
                                $currencyLabel = strtoupper($pricing['currency'] ?? ($tour->currency ?? 'TRY'));
                            @endphp
                            <span style="font-weight:600">{{ number_format($maxPrice, 2) }} {{ $currencyLabel }}</span>
                        </td>
                        <td>
                            @php($ownerAgency = optional(optional($tour->owner)->agency)->name)
                            @php($ownerName = optional($tour->owner)->name)
                            @php($fallbackAgency = optional($tour->agency)->name)

                            @if($ownerAgency)
                                <div style="font-weight:500">{{ $ownerAgency }}</div>
                                @if($ownerName)
                                <div class="ag-text-muted" style="font-size:12px">{{ $ownerName }}</div>
                                @endif
                            @elseif($fallbackAgency)
                                <div style="font-weight:500">{{ $fallbackAgency }}</div>
                            @elseif($ownerName)
                                <div style="font-weight:500">{{ $ownerName }}</div>
                            @else
                                <span class="ag-text-muted">Belirtilmedi</span>
                            @endif
                        </td>
                        <td>
                            @if($tour->is_active)
                            <span class="ag-badge ag-badge-success">Aktif</span>
                            @else
                            <span class="ag-badge ag-badge-danger">Pasif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="ag-empty">
                                <i data-lucide="map" class="ag-empty-icon"></i>
                                <div class="ag-empty-title">Henüz paylaşılan tur yok</div>
                                <p class="ag-empty-text">Bağlantılı olduğunuz adminler tur paylaşımı yaptığında burada görünecek.</p>
                                <a href="{{ route('agency.agencies.index') }}" class="ag-btn ag-btn-secondary">
                                    <i data-lucide="users"></i>
                                    <span>Acenta Yönetimi</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    lucide.createIcons();
</script>
@endpush
