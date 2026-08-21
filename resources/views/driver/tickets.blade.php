@extends('layouts.driver')

@section('title', 'Biletler')

@push('css')
<style>
.dt-day-header {
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
    gap: 8px; padding: 10px 14px; background: var(--dr-card, #fff);
    border-top: 1px solid var(--dr-border, #e5e7eb);
    border-bottom: 1px solid var(--dr-border, #e5e7eb);
    font-weight: 600; font-size: 14px; color: var(--dr-text);
}
.dt-day-header .dt-day-label-today { color: #16a34a; }
.dt-day-header.is-past { opacity: 0.65; background: var(--dr-bg, #f3f4f6); }
.dt-day-meta { font-size: 12px; color: var(--dr-text-muted); font-weight: 400; }
.dt-day-pill { font-size: 11px; padding: 2px 8px; border-radius: 999px; background: rgba(13,110,253,0.12); color: #0d6efd; font-weight: 600; }
.dt-day-pill.today { background: rgba(22,163,74,0.15); color: #16a34a; }
.dt-day-pill.past { background: rgba(107,114,128,0.15); color: #6b7280; }

.dt-card-list { padding: 8px 12px; display: grid; grid-template-columns: 1fr; gap: 8px; }
.dt-card {
    border: 1px solid var(--dr-border, #e5e7eb); border-radius: 8px;
    padding: 10px 12px; background: var(--dr-bg, #fff); font-size: 13px;
}
.dt-card.is-past { opacity: 0.7; }
.dt-card-row { display: flex; gap: 10px; align-items: flex-start; }
.dt-card-num {
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 12px; color: #fff; background: #0d6efd;
}
.dt-card-num.is-start { background: #198754; }
.dt-card-num.is-unset { background: #adb5bd; }
.dt-card-body { flex: 1; min-width: 0; }
.dt-card-customer { font-weight: 600; font-size: 14px; color: var(--dr-text); }
.dt-card-meta { font-size: 12px; color: var(--dr-text-muted); line-height: 1.5; word-break: break-word; }
.dt-card-meta .badge { font-size: 10px; }
.dt-card-row-bottom {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 6px; padding-top: 6px; border-top: 1px solid var(--dr-border, #e5e7eb);
    font-size: 12px;
}
.dt-rest { font-weight: 700; color: var(--dr-success, #16a34a); }

html.dark-mode .dt-day-header { background: #1e293b; border-color: #334155; color: #e2e8f0; }
html.dark-mode .dt-day-header.is-past { background: #0f172a; }
html.dark-mode .dt-card { background: #1e293b; border-color: #334155; color: #e2e8f0; }
html.dark-mode .dt-card-customer { color: #f1f5f9; }
html.dark-mode .dt-card-meta { color: #94a3b8; }
html.dark-mode .dt-card-row-bottom { border-color: #334155; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h1 style="font-size:22px;font-weight:700;color:var(--dr-text);margin:0 0 4px">
        <i data-lucide="ticket" style="width:22px;height:22px;display:inline-block;vertical-align:-3px;margin-right:6px;color:var(--dr-accent)"></i>
        Atanmış Biletler
    </h1>
    <p style="color:var(--dr-text-muted);font-size:13px;margin:0">
        {{ $vehicle->brand }} {{ $vehicle->model }} — <span class="badge bg-primary" style="font-size:11px">{{ $vehicle->plate_number }}</span>
        @php $totalTickets = collect($ticketsByDay)->sum(fn($d) => count($d['tickets'])); @endphp
        <span class="ml-2" style="opacity:.85">• {{ $totalTickets }} bilet, {{ count($ticketsByDay) }} gün</span>
    </p>
</div>

<div class="card">
    <div class="card-body p-0">
        @if(empty($ticketsByDay))
            <div class="text-center py-5">
                <i data-lucide="ticket" style="width:48px;height:48px;color:var(--dr-border);margin-bottom:12px"></i>
                <h5 class="text-muted" style="font-size:16px">Henüz bilet atanmamış</h5>
                <p class="text-muted mb-0">Size atanmış aktif bilet bulunmamaktadır.</p>
            </div>
        @else
            @foreach($ticketsByDay as $day)
                @php
                    $dayCarbon = \Carbon\Carbon::parse($day['date']);
                    $isToday = $day['is_today'];
                    $isPast = $day['is_past'];
                    $label = $isToday ? 'Bugün' : ($dayCarbon->isTomorrow() ? 'Yarın' : '');
                @endphp
                <div class="dt-day-header @if($isPast) is-past @endif">
                    <div>
                        <span @if($isToday) class="dt-day-label-today" @endif>
                            {{ $dayCarbon->locale('tr')->translatedFormat('d F Y, l') }}
                        </span>
                        @if($label)
                            <span class="dt-day-pill @if($isToday) today @elseif($isPast) past @endif ml-2">{{ $label }}</span>
                        @endif
                    </div>
                    <div class="dt-day-meta">
                        {{ count($day['tickets']) }} bilet • {{ $day['passenger_count'] }} yolcu
                    </div>
                </div>

                <div class="dt-card-list">
                    @foreach($day['tickets'] as $t)
                        @php
                            $isStart = (bool) $t->is_route_start;
                            $orderNum = $t->route_order;
                            $passengerCount = $t->passengers->sum('quantity');
                        @endphp
                        <div class="dt-card @if($isPast) is-past @endif">
                            <div class="dt-card-row">
                                <div class="dt-card-num @if($isStart) is-start @elseif(!$orderNum) is-unset @endif">
                                    @if($isStart) S @elseif($orderNum) {{ $orderNum }} @else ? @endif
                                </div>
                                <div class="dt-card-body">
                                    <div class="d-flex justify-content-between align-items-start" style="gap:6px;">
                                        <div style="min-width:0;">
                                            <div class="dt-card-customer">{{ $t->customer_name ?: 'Müşteri' }}</div>
                                            <div class="dt-card-meta">
                                                <span class="badge bg-info text-dark">{{ $t->tracking_no }}</span>
                                                @if($t->pickup_time)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-clock" style="font-size:9px"></i> {{ $t->pickup_time->format('H:i') }}
                                                    </span>
                                                @endif
                                                <span class="badge bg-light text-dark">{{ $passengerCount }} kişi</span>
                                            </div>
                                            <div class="dt-card-meta mt-1">
                                                <i class="fas fa-route text-muted" style="font-size:10px"></i>
                                                {{ $t->tour_name ?: '—' }}
                                            </div>
                                            <div class="dt-card-meta">
                                                <i class="fas fa-map-marker-alt text-danger" style="font-size:10px"></i>
                                                {{ $t->pickup_location ?: 'Konum belirtilmemiş' }}
                                                @if($t->room_number)
                                                    <span class="ml-1"><i class="fas fa-door-open" style="font-size:10px"></i> Oda: {{ $t->room_number }}</span>
                                                @endif
                                            </div>
                                            @if($t->passport_numbers)
                                                <div class="dt-card-meta mt-1" style="max-height:3em;overflow:hidden;">
                                                    <i class="fas fa-id-card text-muted" style="font-size:10px"></i>
                                                    {{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $t->passport_numbers)), 100) }}
                                                </div>
                                            @endif
                                        </div>
                                        @if($t->customer_phone)
                                            <a href="tel:{{ $t->customer_phone }}" class="btn btn-sm btn-outline-success" title="Ara" style="flex-shrink:0;">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        @endif
                                    </div>
                                    <div class="dt-card-row-bottom">
                                        <span class="text-muted">Alınacak rest</span>
                                        <span class="dt-rest">
                                            {{ number_format((float) $t->rest, 2, ',', '.') }} {{ $t->currency }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@push('js')
<script>
// Sayfayı her dakikada bir tazele (yeni biletler/atamalar otomatik gelsin)
setTimeout(function () { location.reload(); }, 60000);
</script>
@endpush
