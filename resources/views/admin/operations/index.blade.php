@extends('layouts.admin')

@section('title', 'Operasyon Yönetimi')
@push('css')
<style>
.table-title { display:flex; align-items:center; justify-content:space-between; margin-bottom:6px; }

.table-title button {
    transition: all 0.3s ease;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table-title button:hover {    
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.table-container { transition: all 0.3s ease; margin-bottom: 8px; }

/* Filters layout - horizontal display */
#tickets-filters .form-row,
#vehicles-filters .form-row {
    display: flex !important;
    flex-wrap: wrap !important;
    margin-right: -5px;
    margin-left: -5px;
}

#tickets-filters .form-group,
#vehicles-filters .form-group {
    padding-right: 5px;
    padding-left: 5px;
    margin-bottom: 8px;
    flex: 1 1 auto;
    min-width: 100px;
}

#tickets-filters .form-group label,
#vehicles-filters .form-group label {
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
    display: block;
}

#tickets-filters .form-control-sm,
#vehicles-filters .form-control-sm {
    font-size: 12px;
    padding: 4px 8px;
    height: auto;
}

#tickets-filters .text-right,
#vehicles-filters .text-right {
    display: flex;
    gap: 6px;
    justify-content: flex-start;
    margin-top: 4px;
}

#tickets-filters .text-right .btn,
#vehicles-filters .text-right .btn {
    padding: 4px 12px;
    font-size: 11px;
}

.section-collapsed {
    opacity: 0.8;
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 12px 16px;
    margin: 6px;
    flex: 0 0 auto !important;
    align-self: flex-start !important;
    min-width: 180px;
    max-width: 260px;
    height: auto !important;
    cursor: pointer;
    border: 1px dashed #ced4da;
    transition: all 0.3s ease;
}
/* made by @hllgkx.0 */
.section-collapsed:hover {
    opacity: 1;
    background-color: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.section-collapsed .table-title {
    margin-bottom: 0;
    font-size: 14px;
    white-space: nowrap;
    cursor: pointer;
}

.section-collapsed .table-title::after {
    content: ' — Genişletmek için tıklayın';
    font-size: 11px;
    color: #6c757d;
    font-weight: 400;
    font-style: italic;
    display: block;
    margin-top: 4px;
}

html.dark-mode .section-collapsed .table-title::after {
    color: #94a3b8;
}

.section-collapsed .table-container {
    min-height: auto;
}

/* Kapalı bölümlerde filtre butonlarını gizle */
.section-collapsed .btn-outline-primary[title="Filtrele"] {
    display: none !important;
}

/* Daha spesifik kural */
#collapsed-sections-container .section-collapsed .btn-outline-primary {
    display: none !important;
}
/* Kopya konteyner için de uygula */
#collapsed-sections-container-dup .section-collapsed .btn-outline-primary {
    display: none !important;
}

/* Tüm kapalı bölümlerde filtre butonlarını gizle */
.section-collapsed .table-title .btn-outline-primary {
    display: none !important;
}

/* Kapalı bölümlerde inline filtreleri gizle */
.section-collapsed #tickets-filters,
.section-collapsed #vehicles-filters,
.section-collapsed #drivers-filters,
.section-collapsed #guides-filters {
    display: none !important;
}

#collapsed-sections-container {
    gap: 60px !important;
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: flex-start !important;
    padding: 20px 0 !important;
}
/* Kopya konteyner için de aynı düzen */
#collapsed-sections-container-dup { display: none !important; }

/* İstenen: Şoförler ve Rehberler bölümleri tamamen kaldırıldı */
#drivers-section, #guides-section { display: none !important; }

/* Pagination geniş ikon/ok bozulmalarını engelle */
.pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#collapsed-sections-row {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}

/* Compact calendar header */
.calendar-header {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 6px 8px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}

.calendar-header::before { content: none; }
@keyframes pulse {}

.calendar-title-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 1;
}

.calendar-main-title { color: #343a40; margin: 0; font-size: 14px; font-weight: 700; display: flex; align-items: center; }

.calendar-main-title i { margin-right: 8px; color: #007bff; }

.calendar-month-display { color: #6c757d; font-size: 11px; display: flex; align-items: center; }

.calendar-toolbar { display: flex; gap: 4px; align-items: center; }

.calendar-nav-btn { width: 24px; height: 24px; border-radius: 6px; border: 1px solid #dee2e6; background: #ffffff; color: #495057; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .2s ease; font-size: 11px; }
.calendar-nav-btn:hover { background: #f8f9fa; border-color: #007bff; color: #007bff; }

.calendar-action-btn { padding: 4px 8px; border-radius: 6px; border: 1px solid #dee2e6; background: #ffffff; color: #495057; font-weight: 600; font-size: 11px; cursor: pointer; transition: all .2s ease; display: flex; align-items: center; }
.calendar-action-btn:hover { background: #f8f9fa; border-color: #007bff; color: #007bff; }
.calendar-today-btn:hover { color: #007bff; }
.calendar-clear-btn:hover { color: #dc3545; }

/* Compact grid */
#day-selector {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    padding: 2px 0 0 0;
    background: transparent;
    border-radius: 0;
    max-height: 48vh;
    overflow-y: auto;
}

/* Özel scroll bar */
#day-selector::-webkit-scrollbar {
    width: 8px;
}

#day-selector::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
}

#day-selector::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

#day-selector::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #f093fb 100%);
}

@media (max-width: 1400px) {
    #day-selector {
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
    }
}

@media (max-width: 1200px) {
    #day-selector {
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
    }
}

@media (max-width: 992px) {
    .calendar-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .calendar-toolbar {
        justify-content: center;
    }
    
    #day-selector { grid-template-columns: repeat(4, 1fr); gap: 4px; }
}

@media (max-width: 768px) {
    #day-selector { grid-template-columns: repeat(3, 1fr); gap: 4px; }
    .day-chip { padding: 5px 4px; }
    .day-chip .dc-day { font-size: 14px; }
}

@media (max-width: 576px) { #day-selector { grid-template-columns: repeat(2, 1fr); gap: 3px; } }
#tours-list { background:#f9f9f9; border-radius:10px; padding:8px; margin-bottom:8px; }
.tours-pagination-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; padding:8px 12px; background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.08); flex-wrap:wrap; gap:8px; }
.tours-pagination-left { display:flex; align-items:center; gap:8px; }
.tours-pagination-left .page-size-label { font-size:12px; color:#6c757d; font-weight:500; }
.tours-page-size-selector { display:flex; gap:4px; }
.tours-page-size-btn { background:#f8f9fa; border:1px solid #dee2e6; color:#495057; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all .2s ease; }
.tours-page-size-btn:hover { background:#e9ecef; border-color:#ced4da; }
.tours-page-size-btn.active { background:#007bff; border-color:#007bff; color:#fff; }
.tours-pagination-right { display:flex; align-items:center; gap:6px; }
.tours-page-nav-btn { background:#fff; border:1px solid #dee2e6; color:#495057; width:28px; height:28px; border-radius:6px; font-size:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .2s ease; }
.tours-page-nav-btn:hover:not(:disabled) { background:#007bff; border-color:#007bff; color:#fff; }
.tours-page-nav-btn:disabled { opacity:0.5; cursor:not-allowed; }
.tours-page-info { font-size:12px; color:#6c757d; padding:0 8px; }
.tours-page-info strong { color:#343a40; }
.tour-grid { display: grid; gap: 12px; align-items: stretch; }
@media (min-width: 1200px) { .tour-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 992px) and (max-width: 1199.98px) { .tour-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 576px) and (max-width: 991.98px) { .tour-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575.98px) { .tour-grid { grid-template-columns: 1fr; } }
.tour-chip-btn {
    appearance: none;
    -webkit-appearance: none;
    border: 1px solid #dee2e6;
    background: #ffffff;
    text-align: left;
    padding: 10px 12px;
    border-radius: 8px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: background-color .2s ease, border-color .2s ease, box-shadow .2s ease;
    cursor: pointer;
}
/* Eski chip stilleri kaldırıldı - yeni renkli tasarım kullanılıyor */
.tour-chip-btn:hover {
    background: #f8f9fa;
    border-color: #ced4da;
}
.tour-chip-btn:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}
.tour-chip-btn:active {
    box-shadow: 0 1px 1px rgba(0,0,0,0.08) inset;
}

/* Yumuşak ve renkli tur kartı tasarımı */
.tours-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.tour-card, .tour-chip {
    appearance:none !important; -webkit-appearance:none !important; border:none !important; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    width:100% !important; border-radius:10px !important; padding:12px 10px !important; cursor:pointer !important;
    transition:all .25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display:flex !important; flex-direction:column !important; align-items:center !important; justify-content:center !important; gap:8px !important;
    box-shadow:0 4px 14px rgba(102, 126, 234, 0.12) !important;
    min-height: 110px !important; overflow: hidden !important;
    position: relative !important;
}
.tour-card:nth-child(2n), .tour-chip:nth-child(2n) { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; box-shadow:0 4px 14px rgba(245, 87, 108, 0.12) !important; }
.tour-card:nth-child(3n), .tour-chip:nth-child(3n) { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important; box-shadow:0 4px 14px rgba(79, 172, 254, 0.12) !important; }
.tour-card:nth-child(4n), .tour-chip:nth-child(4n) { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important; box-shadow:0 4px 14px rgba(67, 233, 123, 0.12) !important; }
.tour-card:nth-child(5n), .tour-chip:nth-child(5n) { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important; box-shadow:0 4px 14px rgba(250, 112, 154, 0.12) !important; }
.tour-card:hover, .tour-chip:hover { transform: translateY(-4px) !important; box-shadow:0 10px 20px rgba(0,0,0,0.15) !important; }
.tour-card:focus, .tour-chip:focus { outline:none !important; }
.tour-card-left { display:flex; align-items:center; justify-content:center; gap:10px; min-width:0; width:100%; }
.tour-svg { display:none !important; }
.tour-title { font-weight:700 !important; color:#ffffff !important; font-size:14px !important; line-height:1.25 !important; text-align:center !important; white-space:normal !important; overflow:hidden !important; display:-webkit-box !important; -webkit-box-orient:vertical !important; -webkit-line-clamp:2 !important; word-break:break-word !important; text-shadow: 0 1px 2px rgba(0,0,0,0.1) !important; }
.tour-count-badge { border-radius:14px !important; background:rgba(255,255,255,0.25) !important; backdrop-filter:blur(8px) !important; color:#ffffff !important; padding:6px 10px !important; font-weight:800 !important; font-size:12px !important; white-space:nowrap !important; border: 1px solid rgba(255,255,255,0.3) !important; }
/* Compact day card */
.day-chip {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 12px 10px;
    border-radius: 10px;
    background: #ffffff;
    text-align: center;
    cursor: pointer;
    user-select: none;
    transition: all .2s ease;
    border: 1px solid #e9ecef;
}

.day-chip::before, .day-chip::after { content: none; }

.day-chip.btn { 
    border: 1px solid rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.85);
}

.day-chip:hover { background: #f8f9fa; border-color: #007bff; transform: translateY(-2px); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

.day-chip .dc-dow { font-size: 8.5px; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 3px; }

.day-chip .dc-day { font-size: 18px; font-weight: 800; line-height: 1; color: #343a40; margin-bottom: 3px; }

.day-chip .dc-date { font-size: 9.5px; color: #6c757d; font-weight: 600; letter-spacing: .1px; }

.day-chip.active { background: #007bff; border-color: #007bff; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,123,255,0.25); }
.day-chip.active .dc-dow, .day-chip.active .dc-date, .day-chip.active .dc-day { color: #ffffff; }

.day-chip.disabled { 
    opacity: 0.3;
    cursor: not-allowed;
    transform: none !important;
    filter: grayscale(100%);
}

.day-chip.disabled:hover {
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    transform: none !important;
}

#tickets-content, #vehicles-content, #drivers-content {
    transition: all 0.3s ease;
}

#tickets-section, #vehicles-section, #drivers-section {
    transition: all 0.3s ease;
}

/* Seçili biletler için stiller */
.table-row.ticket-item.ticket-selected {
    background-color: #f8f9fa !important;
    border-left: 4px solid #007bff !important;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1) !important;
    transform: translateY(-1px) !important;
    position: relative !important;
}

.table-row.ticket-item.ticket-selected td {
    background-color: #f8f9fa !important;
}

.table-row.ticket-item.ticket-selected:hover {
    background-color: #e9ecef !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 3px 6px rgba(0,123,255,0.15) !important;
}

.table-row.ticket-item.ticket-selected:hover td {
    background-color: #e9ecef !important;
}

.ticket-item {
    cursor: pointer;
    transition: all 0.2s ease;
}

.ticket-item:hover {
    background-color: #f5f5f5;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Seçili olmayan biletlerin normal durumu */
.ticket-item:not(.ticket-selected) {
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    box-shadow: none;
    transform: translateY(0);
}

/* Fix header controls (per-page buttons and toggle) alignment */
.table-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.table-title .float-right { float: none !important; display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.table-title .btn-group { display: inline-flex; white-space: nowrap; }
.table-title .btn-group .btn { display: inline-flex !important; align-items: center; justify-content: center; width: auto !important; }

/* Ensure extra small button size looks compact */
.btn.btn-xs { padding: .15rem .35rem; font-size: .72rem; line-height: 1.2; border-radius: .2rem; }

/* Seçim bilgisi */
#selection-info {
    margin-top: 2px;
    padding: 5px 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border-left: 4px solid #17a2b8;
}

#selection-info .badge {
    font-size: 11px;
    padding: 4px 8px;
}

/* ============================================== */
/* DARK MODE STYLES FOR OPERATIONS PAGE */
/* ============================================== */
html.dark-mode .calendar-header {
    background: #1e293b !important;
    border-color: #334155 !important;
}

html.dark-mode .calendar-main-title {
    color: #e2e8f0 !important;
}

html.dark-mode .calendar-month-display {
    color: #94a3b8 !important;
}

html.dark-mode .calendar-nav-btn,
html.dark-mode .calendar-action-btn {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}

html.dark-mode .calendar-nav-btn:hover,
html.dark-mode .calendar-action-btn:hover {
    background: #475569 !important;
    border-color: #6366f1 !important;
    color: #6366f1 !important;
}

html.dark-mode .day-chip {
    background: #1e293b !important;
    border-color: #334155 !important;
}

html.dark-mode .day-chip .dc-day-name,
html.dark-mode .day-chip .dc-month {
    color: #94a3b8 !important;
}

html.dark-mode .day-chip .dc-day {
    color: #e2e8f0 !important;
}

html.dark-mode .day-chip:hover {
    background: #334155 !important;
    border-color: #6366f1 !important;
}

html.dark-mode .day-chip.selected,
html.dark-mode .day-chip.today {
    background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
}

html.dark-mode .table-title {
    color: #e2e8f0 !important;
}

html.dark-mode .table-container {
    background: #1e293b !important;
    border-color: #334155 !important;
}

html.dark-mode .section-collapsed {
    background: #1e293b !important;
    border-color: #475569 !important;
    border-style: dashed !important;
}

html.dark-mode .section-collapsed:hover {
    background: #334155 !important;
    border-color: #6366f1 !important;
}

html.dark-mode #tickets-filters,
html.dark-mode #vehicles-filters {
    background: transparent !important;
}

html.dark-mode #tickets-filters label,
html.dark-mode #vehicles-filters label {
    color: #e2e8f0 !important;
}

html.dark-mode .table-row,
html.dark-mode .ticket-item,
html.dark-mode .vehicle-item {
    background: #1e293b !important;
}

html.dark-mode .table-row:hover,
html.dark-mode .ticket-item:hover,
html.dark-mode .vehicle-item:hover {
    background: #334155 !important;
}

html.dark-mode .tours-header {
    color: #e2e8f0 !important;
}

html.dark-mode .tours-pagination-controls {
    background: #1e293b !important;
    border-color: #334155 !important;
}

html.dark-mode .tours-pagination-btn {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}

html.dark-mode .tours-pagination-btn:hover:not(:disabled) {
    background: #475569 !important;
}

html.dark-mode .tours-page-info {
    color: #94a3b8 !important;
}

html.dark-mode .tours-per-page-btn {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}

/* ========== Nationality Warning Toast ========== */
.nationality-toast-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.3);
    animation: fadeInOverlay 0.2s ease;
}
@keyframes fadeInOverlay { from { opacity: 0; } to { opacity: 1; } }

.nationality-toast {
    background: rgba(220, 38, 38, 0.92);
    color: #fff;
    border-radius: 14px;
    padding: 22px 28px;
    max-width: 460px;
    width: 90%;
    text-align: center;
    box-shadow: 0 8px 32px rgba(220, 38, 38, 0.35);
    animation: toastPop 0.25s ease;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}
@keyframes toastPop { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.nationality-toast-icon { font-size: 32px; margin-bottom: 8px; }
.nationality-toast-title { font-weight: 700; font-size: 15px; margin-bottom: 6px; }
.nationality-toast-msg { font-size: 13px; line-height: 1.5; opacity: 0.95; margin-bottom: 16px; }

.nationality-toast-actions { display: flex; gap: 10px; justify-content: center; }
.nationality-toast-btn {
    padding: 8px 22px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.nationality-toast-btn-confirm {
    background: #fff;
    color: #dc2626;
}
.nationality-toast-btn-confirm:hover { background: #fef2f2; }
.nationality-toast-btn-cancel {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.35);
}
.nationality-toast-btn-cancel:hover { background: rgba(255,255,255,0.3); }

/* ========== Vehicle Nationality Badges ========== */
.vehicle-nationalities {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-top: 3px;
}
.vehicle-nat-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    background: #e0e7ff;
    color: #3730a3;
    line-height: 1.5;
}
.vehicle-nat-all {
    background: #d1fae5;
    color: #065f46;
}

html.dark-mode .vehicle-nat-badge {
    background: #312e81;
    color: #c7d2fe;
}
html.dark-mode .vehicle-nat-all {
    background: #064e3b;
    color: #6ee7b7;
}

html.dark-mode .tours-per-page-btn.active,
html.dark-mode .tours-per-page-btn:hover {
    background: #6366f1 !important;
    color: white !important;
}

html.dark-mode #selection-info {
    background-color: #1e293b !important;
    border-left-color: #3b82f6 !important;
    color: #e2e8f0 !important;
}

html.dark-mode .tour-chip-btn {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}

html.dark-mode .tour-chip-btn:hover {
    background: #334155 !important;
}

/* Tours List Container - Dark Mode */
html.dark-mode #tours-list {
    background: #1e293b !important;
    border-color: #334155 !important;
}

/* Tours Pagination Bar - Dark Mode */
html.dark-mode .tours-pagination-bar {
    background: #0f172a !important;
    border: 1px solid #334155 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}

html.dark-mode .tours-pagination-left .page-size-label {
    color: #94a3b8 !important;
}

html.dark-mode .tours-page-size-btn {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}

html.dark-mode .tours-page-size-btn:hover {
    background: #475569 !important;
    border-color: #64748b !important;
}

html.dark-mode .tours-page-size-btn.active {
    background: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: #fff !important;
}

html.dark-mode .tours-page-nav-btn {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}

html.dark-mode .tours-page-nav-btn:hover:not(:disabled) {
    background: #3b82f6 !important;
    border-color: #3b82f6 !important;
    color: #fff !important;
}

html.dark-mode .tours-page-nav-btn:disabled {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #475569 !important;
}

html.dark-mode .tours-page-info {
    color: #94a3b8 !important;
}

html.dark-mode .tours-page-info strong {
    color: #e2e8f0 !important;
}

/* Tour Cards - Dark Mode (matching theme) */
html.dark-mode .tour-card,
html.dark-mode .tour-chip,
html.dark-mode .tour-card:nth-child(2n),
html.dark-mode .tour-chip:nth-child(2n),
html.dark-mode .tour-card:nth-child(3n),
html.dark-mode .tour-chip:nth-child(3n),
html.dark-mode .tour-card:nth-child(4n),
html.dark-mode .tour-chip:nth-child(4n),
html.dark-mode .tour-card:nth-child(5n),
html.dark-mode .tour-chip:nth-child(5n) {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    border: 1px solid #475569 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
}

html.dark-mode .tour-card:hover,
html.dark-mode .tour-chip:hover {
    background: linear-gradient(135deg, #334155 0%, #475569 100%) !important;
    border-color: #6366f1 !important;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3) !important;
}

html.dark-mode .tour-title {
    color: #e2e8f0 !important;
    text-shadow: none !important;
}

html.dark-mode .tour-count-badge {
    background: #6366f1 !important;
    border-color: #818cf8 !important;
    color: #ffffff !important;
}

/* Calendar Selected Day - Much More Visible */
.day-chip.selected,
.day-chip.today.selected {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    border: 4px solid #60a5fa !important;
    box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.35), 0 6px 20px rgba(37, 99, 235, 0.5) !important;
    transform: scale(1.08);
    z-index: 10;
    position: relative;
}

.day-chip.selected .dc-day-name,
.day-chip.selected .dc-month,
.day-chip.selected .dc-day {
    color: #ffffff !important;
    font-weight: 700 !important;
}

.day-chip.selected .dc-day {
    font-size: 22px !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
}

html.dark-mode .day-chip.selected,
html.dark-mode .day-chip.today.selected {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
    border: 4px solid #93c5fd !important;
    box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.5), 0 8px 24px rgba(59, 130, 246, 0.6) !important;
    transform: scale(1.08);
    z-index: 10;
}

html.dark-mode .day-chip.selected .dc-day-name,
html.dark-mode .day-chip.selected .dc-month,
html.dark-mode .day-chip.selected .dc-day {
    color: #ffffff !important;
    font-weight: 700 !important;
}

html.dark-mode .day-chip.selected .dc-day {
    font-size: 22px !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
}

/* Animation for selected day */
@keyframes selectedDayPulse {
    0%, 100% { box-shadow: 0 0 0 6px rgba(59, 130, 246, 0.35), 0 6px 20px rgba(37, 99, 235, 0.5); }
    50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.5), 0 8px 24px rgba(37, 99, 235, 0.6); }
}

.day-chip.selected {
    animation: selectedDayPulse 2s ease-in-out infinite;
}
</style>
@endpush
<!-- end of the css -->
<!-- operasyon yönetimi başlık-->
@section('content_header')
    <h1>Operasyon Yönetimi</h1>
@stop
<!-- operasyon yönetimi formu-->
@section('content')
<!-- Modern Kontrol Paneli -->
<div class="guides-control-panel mb-2">
    <div class="control-left">
        <h4 class="control-title"><i class="fas fa-calendar-check"></i> Operasyon Yönetimi</h4>
        <p class="control-subtitle">Günlük atamalar</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body" style="overflow: hidden;">
                <!-- Modern Takvim Header -->
                <div class="mb-3">
                    <div class="calendar-header">
                        <div class="calendar-title-section">
                            <h4 class="calendar-main-title">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                <span id="modeTitle">Gün Seçimi</span>
                            </h4>
                            <div class="calendar-month-display">
                                <i class="far fa-clock mr-2"></i>
                                <span id="monthTitle"></span>
                            </div>
                        </div>
                        <div class="calendar-toolbar" id="toolbarButtons">
                            <button type="button" id="dayPrev" class="calendar-nav-btn" title="Önceki Ay">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" id="dayToday" class="calendar-action-btn calendar-today-btn">
                                <i class="fas fa-calendar-day mr-1"></i>Bugün
                            </button>
                            <button type="button" id="dayClear" class="calendar-action-btn calendar-clear-btn">
                                <i class="fas fa-eraser mr-1"></i>Tümü
                            </button>
                            <button type="button" id="dayNext" class="calendar-nav-btn" title="Sonraki Ay">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div id="day-selector"></div>
                    <div id="tours-list" class="pt-2" style="display:none;"></div>
                </div>
                <!-- Collapsed Sections Row (removed on initial page; moved to modal only) -->
                <!-- operasyon yönetimi formu: Tahta (varsayılan gizli, modalda açılır) -->
                <div id="operations-board-home" style="display:none"></div>
                <div id="operations-board-inline-slot" class="mt-2" style="display:none;"></div>
                <div id="operations-board" style="display:none;">
                <div class="row" id="main-sections-row">
                    <!-- Biletler -->
                    <div class="col-md-6" id="tickets-section">
                        <div class="table-container">
                            <h4 class="table-title">
                                Biletler
                                <div class="float-right">
                                    <div class="btn-group mr-2" id="tickets-pagination" style="display: inline-block;">
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('tickets', 5)">5</button>
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('tickets', 10)">10</button>
                                        <button type="button" class="btn btn-xs btn-outline-info active" onclick="setPagination('tickets', 15)">15</button>
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('tickets', 20)">20</button>
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('tickets', -1)">Tümü</button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSection('tickets')" id="tickets-toggle">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </h4>
                            <div class="table-responsive" id="tickets-content">
                                <div id="tickets-filters" class="mb-2">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label class="mb-1">Tura Göre</label>
                                            <select class="form-control form-control-sm" id="ticketsTourFilter">
                                                <option value="">Tümü</option>
                                                @if(isset($tours))
                                                    @foreach($tours as $tour)
                                                        <option value="{{ $tour->id }}">{{ $tour->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="mb-1">Tarih</label>
                                            <input type="date" class="form-control form-control-sm" id="ticketsDateFilter">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="mb-1">Araç Durumu</label>
                                            <select class="form-control form-control-sm" id="ticketsStatusFilter">
                                                <option value="">Tümü</option>
                                                <option value="with-vehicle">Araçlı</option>
                                                <option value="without-vehicle">Araçsız</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label class="mb-1">Milliyet</label>
                                            <select class="form-control form-control-sm" id="ticketsNationalityFilter">
                                                <option value="">Tümü</option>
                                                <option value="DE">Almanca</option>
                                                <option value="RU">Rusça</option>
                                                <option value="EN">İngilizce</option>
                                                <option value="TR">Türkçe</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-1">
                                            <label class="mb-1">Aktif</label>
                                            <select class="form-control form-control-sm" id="ticketsActiveStatusFilter">
                                                <option value="">Tümü</option>
                                                <option value="active">Aktif</option>
                                                <option value="inactive">Pasif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-sm btn-primary mr-2" onclick="applyTicketsFilter()">Uygula</button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearTicketsFilter()">Temizle</button>
                                    </div>
                                </div>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Takip No</th>
                                            <th>Müşteri</th>
                                            <th>Oda</th>
                                            <th>Tur</th>
                                            <th>Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tickets-area">
                                        @foreach($tickets as $ticket)
                                        <tr class="table-row ticket-item" 
                                            data-ticket-id="{{ $ticket->id }}" 
                                            data-tour-id="{{ $ticket->tour_id ?? '' }}"
                                            data-tour-date="{{ $ticket->tour_date ? $ticket->tour_date->format('Y-m-d') : '' }}"
                                            data-is-active="{{ $ticket->is_active ? '1' : '0' }}"
                                            data-nationality="{{ $ticket->customer_nationality ?? '' }}"
                                            data-has-vehicle="{{ $ticket->vehicle_id ? '1' : '0' }}"
                                            data-vehicle-id="{{ $ticket->vehicle_id ?? '' }}"
                                            data-passenger-count="{{ $ticket->passengers->sum('quantity') }}"
                                            draggable="true">
                                            <td>{{ $ticket->id }}</td>
                                            <td>
                                                <strong>{{ $ticket->voucher_no }}</strong>
                                                @if($ticket->vehicle)
                                                    <br><small class="text-success">Araç: {{ $ticket->vehicle->plate_number }}</small>
                                                @else
                                                    <br><small class="text-muted">Araçsız</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $ticket->customer_name }}
                                                @if($ticket->customer_nationality)
                                                    <br><span class="badge badge-primary badge-sm">{{ $ticket->nationality_name }}</span>
                                                @endif
                                                <br><small class="text-info">{{ $ticket->passenger_breakdown }}</small>
                                            </td>
                                            <td>{{ $ticket->room_number ?: '-' }}</td>
                                            <td>
                                                @if($ticket->tour_name)
                                                    <strong>{{ $ticket->tour_name }}</strong>
                                                    @if($ticket->tour_date)
                                                        <br><small class="text-muted">{{ $ticket->tour_date->format('d.m.Y') }}</small>
                                                        @if($ticket->tour_date->isPast())
                                                            <br><small class="text-danger font-weight-bold">Süresi geçmiş</small>
                                                        @endif
                                                    @endif
                                                @else
                                                    <span class="text-muted">Tur Atanmamış</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="mb-1">
                                                        @if($ticket->vehicle)
                                                            <span class="badge badge-success">Araçlı</span>
                                                        @else
                                                            <span class="badge badge-secondary">Araçsız</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        @if($ticket->is_active)
                                                            <span class="badge badge-success">Aktif</span>
                                                        @else
                                                            <span class="badge badge-danger">Pasif</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- operasyon yönetimi formu-->
                    <!-- Araçlar -->
                    <div class="col-md-6" id="vehicles-section">
                        <div class="table-container">
                            <h4 class="table-title">
                                Araçlar
                                <div class="float-right">
                                    <div class="btn-group mr-2" id="vehicles-pagination" style="display: inline-block;">
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('vehicles', 5)">5</button>
                                        <button type="button" class="btn btn-xs btn-outline-info active" onclick="setPagination('vehicles', 10)">10</button>
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('vehicles', 15)">15</button>
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('vehicles', 20)">20</button>
                                        <button type="button" class="btn btn-xs btn-outline-info" onclick="setPagination('vehicles', -1)">Tümü</button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSection('vehicles')" id="vehicles-toggle">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </h4>
                            <div class="table-responsive" id="vehicles-content">
                                <div id="vehicles-filters" class="mb-2">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="mb-1">Durum</label>
                                            <select class="form-control form-control-sm" id="vehiclesStatusFilter">
                                                <option value="">Tümü</option>
                                                <option value="available">Şoförlü</option>
                                                <option value="busy">Şoförsüz</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="mb-1">Kapasite</label>
                                            <select class="form-control form-control-sm" id="vehiclesCapacityFilter">
                                                <option value="">Tümü</option>
                                                <option value="small">Küçük (1-8)</option>
                                                <option value="medium">Orta (9-16)</option>
                                                <option value="large">Büyük (17+)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="btn btn-sm btn-primary mr-2" onclick="applyVehiclesFilter()">Uygula</button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearVehiclesFilter()">Temizle</button>
                                    </div>
                                </div>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Plaka</th>
                                            <th>Model</th>
                                            <th>Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vehicles-area">
                                        @foreach($vehicles as $vehicle)
                                        <tr class="table-row vehicle-item" 
                                            data-vehicle-id="{{ $vehicle->id }}"
                                            data-has-driver="{{ $vehicle->driver ? '1' : '0' }}"
                                            data-capacity="{{ $vehicle->capacity ?? '0' }}"
                                            ondrop="dropOnVehicle(event)" 
                                            ondragover="allowDrop(event)">
                                            <td>{{ $vehicle->id }}</td>
                                            <td>
                                                <strong>{{ $vehicle->plate_number }}</strong>
                                                @if($vehicle->driver)
                                                    <br><small class="text-success">Şoför: {{ $vehicle->driver->name }}</small>
                                                @else
                                                    <br><small class="text-muted">Şoförsüz</small>
                                                @endif
                                                @php
                                                    $currentPassengers = $vehicle->tickets->sum(function($ticket) {
                                                        return $ticket->passengers->sum('quantity');
                                                    });
                                                    $availableSeats = $vehicle->capacity - $currentPassengers;

                                                    // Desteklenen milliyetleri hesapla (rehber > şoför)
                                                    $supportedNats = null;
                                                    if ($vehicle->driver) {
                                                        if ($vehicle->driver->guide && !empty($vehicle->driver->guide->supported_nationalities)) {
                                                            $supportedNats = $vehicle->driver->guide->supported_nationalities;
                                                        } elseif (!empty($vehicle->driver->supported_nationalities)) {
                                                            $supportedNats = $vehicle->driver->supported_nationalities;
                                                        }
                                                    }
                                                    $natOptions = \App\Models\User::getNationalityOptions();
                                                @endphp
                                                <br><small class="text-info vehicle-capacity" data-vehicle-id="{{ $vehicle->id }}">Kapasite: {{ $currentPassengers }}/{{ $vehicle->capacity }} ({{ $availableSeats }} boş)</small>
                                                <div class="vehicle-nationalities">
                                                    @if($supportedNats === null || empty($supportedNats))
                                                        <span class="vehicle-nat-badge vehicle-nat-all">Tüm Milliyetler</span>
                                                    @else
                                                        @foreach($supportedNats as $natCode)
                                                            <span class="vehicle-nat-badge">{{ $natOptions[$natCode] ?? $natCode }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $vehicle->model }}</td>
                                            <td>
                                                @if($vehicle->driver)
                                                    <span class="badge badge-success">Şoförlü</span>
                                                @else
                                                    <span class="badge badge-secondary">Şoförsüz</span>
                                                @endif
                                                @php $isFull = $availableSeats <= 0; @endphp
                                                <br><span class="badge badge-danger vehicle-full-badge" data-vehicle-id="{{ $vehicle->id }}" style="display: {{ $isFull ? '' : 'none' }};">Dolu</span>
                                            </td>
                                            <!-- made by @hllgkx.0 -->
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- operasyon yönetimi formu-->
                    <!-- Şoförler ve Rehberler bölümleri kaldırıldı -->
                </div>
                <!-- operasyon yönetimi formu-->
                <!-- Kapatılan Bölümler (İPTAL EDİLDİ) -->
                <div id="collapsed-sections-row-dup" style="display:none"></div>
            </div>
        </div>
    </div>
</div>
@stop
<!-- operasyon yönetimi js-->
@section('js')
<script>
// Tours verilerini JavaScript'te kullanmak için
window.toursData = @json($tours ?? []);

let draggedElement = null;

// Sürükle başlatma
function dragStart(event) {
    draggedElement = event.target;
    event.target.classList.add('dragging');
}

// Sürükle bitirme
function dragEnd(event) {
    event.target.classList.remove('dragging');
    draggedElement = null;
}

// Bırakma izni
function allowDrop(event) {
    event.preventDefault();
    event.currentTarget.classList.add('drag-over');
}

// Araç üzerine bırakma işlemi - Şoför veya bilet atama
function dropOnVehicle(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    /* made by @hllgkx.0 */
    const vehicleId = event.currentTarget.dataset.vehicleId;
    const hasDriver = event.currentTarget.dataset.hasDriver === '1';
    const dragType = event.dataTransfer.getData('type');
    const dragData = event.dataTransfer.getData('text/plain');

    if (!hasDriver && (dragType === 'single-ticket' || dragType === 'multiple-tickets' || draggedElement?.classList?.contains('ticket-item'))) {
        showAlert('error', 'Şoförsüz araca bilet atanamaz.');
        return;
    }
    
    if (dragType === 'multiple-tickets') {
        // Çoklu bilet atama
        const ticketIds = dragData.split(',');
        assignMultipleTicketsToVehicle(ticketIds, vehicleId);
    } else if (draggedElement) {
        // Eski yöntem ile uyumluluk
        if (draggedElement.classList.contains('driver-item')) {
            // Şoför atama
            const driverId = draggedElement.dataset.driverId;
            assignDriverToVehicle(driverId, vehicleId);
        } else if (draggedElement.classList.contains('ticket-item')) {
            // Tek bilet atama
            const ticketId = draggedElement.dataset.ticketId;
            assignTicketToVehicle(ticketId, vehicleId);
        }
    } else if (dragType === 'single-ticket') {
        // Tek bilet atama (yeni yöntem)
        assignTicketToVehicle(dragData, vehicleId);
    }
}

// Eski dropDriver ve dropTicket fonksiyonları - artık kullanılmıyor
function dropDriver(event) {
    // Bu fonksiyon artık dropOnVehicle ile değiştirildi
    dropOnVehicle(event);
}

// Rehber üzerine bırakma işlemi - Şoför atama
function dropOnGuide(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    /* made by @hllgkx.0 */
    const guideId = event.currentTarget.dataset.guideId;
    
    if (draggedElement && draggedElement.classList.contains('driver-item')) {
        // Şoför atama
        const driverId = draggedElement.dataset.driverId;
        assignDriverToGuide(driverId, guideId);
    }
}

function dropTicket(event) {
    // Bu fonksiyon artık dropOnVehicle ile değiştirildi
    dropOnVehicle(event);
}

// Bileti araca ata
function assignTicketToVehicle(ticketId, vehicleId, forceAssign = false) {
    // Client-side guard: prevent assigning expired tickets
    try {
        const row = document.querySelector(`.ticket-item[data-ticket-id="${ticketId}"]`);
        if (row) {
            const ds = row.getAttribute('data-tour-date');
            if (ds) {
                const today = new Date(); today.setHours(0,0,0,0);
                const d = new Date(ds); d.setHours(0,0,0,0);
                if (d < today) {
                    showAlert('error', 'Bu biletin tarihi geçmiş. Araca atanamaz.');
                    return;
                }
            }
        }
    } catch (_) {}
    fetch('{{ route("admin.operations.assign-ticket-to-vehicle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            ticket_id: ticketId,
            vehicle_id: vehicleId,
            force_assign: forceAssign
        })
    })
    .then(async response => {
        const data = await response.json();
        return { ok: response.ok, data };
    })
    .then(data => {
        const payload = data.data || {};
        if (payload.success) {
            showAlert('success', payload.message);
            const vehicle = payload.vehicle || {};
            updateTicketRowVehicle(ticketId, vehicleId, vehicle.plate_number);
            refreshData();
        } else {
            if (payload.require_confirmation && !forceAssign) {
                showNationalityWarning(payload.message, function() {
                    assignTicketToVehicle(ticketId, vehicleId, true);
                });
                return;
            }
            showAlert('error', payload.message || 'Atama sırasında bir hata oluştu.');
        }
    })
    .catch(error => {
        showAlert('error', 'Bir hata oluştu: ' + error.message);
    });
}

// Çoklu bileti araca ata
function assignMultipleTicketsToVehicle(ticketIds, vehicleId) {
    // Loading mesajı göster
    showAlert('info', `${ticketIds.length} bilet atanıyor...`);
    // Client-side filter: remove expired before sending
    try {
        const today = new Date(); today.setHours(0,0,0,0);
        ticketIds = ticketIds.filter(function(id){
            const row = document.querySelector(`.ticket-item[data-ticket-id="${id}"]`);
            if (!row) return true;
            const ds = row.getAttribute('data-tour-date');
            if (!ds) return true;
            const d = new Date(ds); d.setHours(0,0,0,0);
            if (d < today) {
                showAlert('error', `#${id} tarihi geçmiş: atlanıyor`);
                return false;
            }
            return true;
        });
        if (ticketIds.length === 0) return;
    } catch(_) {}
    
    fetch('{{ route("admin.operations.assign-multiple-tickets-to-vehicle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            ticket_ids: ticketIds,
            vehicle_id: vehicleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Seçimi temizle
            clearSelection();
            const info = getVehicleInfoFromDom(vehicleId) || { plate_number: '' };
            ticketIds.forEach(function(tid){
                updateTicketRowVehicle(tid, vehicleId, info.plate_number);
            });
            refreshData();
        } else {
            /* made by @hllgkx.0 */
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'Bir hata oluştu: ' + error.message);
    });
}

// Şoförü araca ata
function assignDriverToVehicle(driverId, vehicleId) {
    fetch('{{ route("admin.operations.assign-driver-to-vehicle") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            driver_id: driverId,
            vehicle_id: vehicleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            refreshData();
        } else {
            /* made by @hllgkx.0 */
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'Bir hata oluştu: ' + error.message);
    });
}

// Şoförü rehbere ata
function assignDriverToGuide(driverId, guideId) {
    fetch('{{ route("admin.operations.assign-driver-to-guide") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            driver_id: driverId,
            guide_id: guideId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            refreshData();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'Bir hata oluştu: ' + error.message);
    });
}

// UI'yi yenile (sayfa yenilemeden)
function refreshData() {
    try {
        if (typeof applyTicketsFilter === 'function') applyTicketsFilter();
        if (typeof applyVehiclesFilter === 'function') applyVehiclesFilter();
        if (typeof applyDriversFilter === 'function') applyDriversFilter();

        const dateFilterEl = document.getElementById('ticketsDateFilter');
        const dateISO = (dateFilterEl && dateFilterEl.value) ? dateFilterEl.value : (typeof selectedDateISO !== 'undefined' ? selectedDateISO : '');
        updateVehicleCapacitiesForDate(dateISO || '');

        if (typeof filterGuides === 'function' && typeof savedFilters !== 'undefined') {
            const nat = (savedFilters.guides && (savedFilters.guides.nationality || '')) || '';
            const drivers = (savedFilters.guides && (savedFilters.guides.driverCount || savedFilters.guides.drivers || '')) || '';
            filterGuides(nat, drivers);
        }
    } catch (e) {
        console.warn('refreshData error', e);
    }
}

function getVehicleInfoFromDom(vehicleId) {
    const row = document.querySelector(`.vehicle-item[data-vehicle-id="${vehicleId}"]`);
    if (!row) return null;
    const plateEl = row.querySelector('td strong');
    const plate = plateEl ? plateEl.textContent.trim() : '';
    return { id: vehicleId, plate_number: plate };
}

function updateTicketRowVehicle(ticketId, vehicleId, plateNumber) {
    const row = document.querySelector(`.ticket-item[data-ticket-id="${ticketId}"]`);
    if (!row) return;

    const oldVehicleId = row.getAttribute('data-vehicle-id') || '';
    const plate = plateNumber || '';

    row.setAttribute('data-vehicle-id', vehicleId);
    row.setAttribute('data-has-vehicle', '1');

    const vehicleCell = row.querySelector('td:nth-child(2)');
    if (vehicleCell) {
        const voucherEl = vehicleCell.querySelector('strong');
        const voucher = voucherEl ? voucherEl.textContent.trim() : '';
        const vehicleLabel = plate ? `Araç: ${plate}` : `Araç: #${vehicleId}`;
        vehicleCell.innerHTML = `<strong>${voucher}</strong><br><small class="text-success">${vehicleLabel}</small>`;
    }

    const statusCell = row.querySelector('td:nth-child(6)');
    if (statusCell) {
        const isActive = row.getAttribute('data-is-active') === '1';
        statusCell.innerHTML = `
            <div class="d-flex flex-column align-items-start">
                <div class="mb-1"><span class="badge badge-success">Araçlı</span></div>
                <div>${isActive ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Pasif</span>'}</div>
            </div>
        `;
    }

    if (oldVehicleId && oldVehicleId !== String(vehicleId)) {
        // Kapasite hesaplaması için sadece data güncellemesi yeterli
        row.setAttribute('data-vehicle-id', vehicleId);
    }
}
// Seçilen tarihe göre araç kapasitelerini yeniden hesapla
function updateVehicleCapacitiesForDate(dateISO){
    try {
        const capEls = document.querySelectorAll('.vehicle-capacity');
        if (!capEls.length) return;

        const vehicles = Array.from(document.querySelectorAll('.vehicle-item'));
        const ticketRows = Array.from(document.querySelectorAll('.ticket-item'));

        // Map vehicleId -> passenger count for date
        const counts = {};
        vehicles.forEach(v => { counts[v.getAttribute('data-vehicle-id')] = 0; });

        ticketRows.forEach(tr => {
            const d = tr.getAttribute('data-tour-date');
            const vid = tr.getAttribute('data-vehicle-id');
            const qty = parseInt(tr.getAttribute('data-passenger-count') || '0', 10);
            if (!vid || !d || !qty) return;
            if (dateISO && d !== dateISO) return;
            counts[vid] = (counts[vid] || 0) + qty;
        });

        capEls.forEach(el => {
            const vid = el.getAttribute('data-vehicle-id');
            const row = document.querySelector(`.vehicle-item[data-vehicle-id="${vid}"]`);
            const capacity = parseInt(row ? row.getAttribute('data-capacity') : '0', 10);
            const used = counts[vid] || 0;
            const empty = Math.max(capacity - used, 0);
            el.textContent = `Kapasite: ${used}/${capacity} (${empty} boş)`;
            const badge = document.querySelector(`.vehicle-full-badge[data-vehicle-id="${vid}"]`);
            if (badge) badge.style.display = used >= capacity ? '' : 'none';
        });
    } catch (e) { console.warn('updateVehicleCapacitiesForDate error', e); }
}

// Alert göster
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="margin-bottom:12px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    // Hedef konteyner: modal açıksa modal-body, aksi halde sayfa başlığı altı
    const modal = document.getElementById('operationsModal');
    const modalShown = modal && modal.classList.contains('show');
    const container = modalShown ? modal.querySelector('.modal-body') : document.querySelector('.content-header');
    if (!container) return;
    // Sadece bu konteyner içindeki önceki alert'leri kaldır
    Array.from(container.querySelectorAll('.alert')).forEach(a => a.remove());
    // Modal içindeyse en başa ekle; değilse başlığın altına ekle
    if (modalShown) {
        container.insertAdjacentHTML('afterbegin', alertHtml);
    } else {
    container.insertAdjacentHTML('afterend', alertHtml);
    }
    // 5 sn sonra kaldır
    setTimeout(() => {
        const toRemove = modalShown ? container.querySelector('.alert') : document.querySelector('.alert');
        if (toRemove) toRemove.remove();
    }, 5000);
}

// Milliyet uyumsuzluğu uyarısı - şeffaf kırmızı toast
function showNationalityWarning(message, onConfirm) {
    // Eski toast varsa kaldır
    const existing = document.querySelector('.nationality-toast-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.className = 'nationality-toast-overlay';
    overlay.innerHTML = `
        <div class="nationality-toast">
            <div class="nationality-toast-icon">⚠️</div>
            <div class="nationality-toast-title">Milliyet Uyarısı</div>
            <div class="nationality-toast-msg">${message}</div>
            <div class="nationality-toast-actions">
                <button class="nationality-toast-btn nationality-toast-btn-confirm" id="natToastConfirm">Devam Et</button>
                <button class="nationality-toast-btn nationality-toast-btn-cancel" id="natToastCancel">İptal</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    // Overlay dışına tıklama = iptal
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) { overlay.remove(); }
    });

    document.getElementById('natToastConfirm').addEventListener('click', function() {
        overlay.remove();
        if (typeof onConfirm === 'function') onConfirm();
    });

    document.getElementById('natToastCancel').addEventListener('click', function() {
        overlay.remove();
    });

    // Escape tuşu = iptal
    function escHandler(e) {
        if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', escHandler); }
    }
    document.addEventListener('keydown', escHandler);
}

// Bölüm gizleme/gösterme fonksiyonu
function toggleSection(sectionName) {
    const section = document.getElementById(sectionName + '-section');
    const content = document.getElementById(sectionName + '-content');
    const toggle = document.getElementById(sectionName + '-toggle');
    const pagination = document.getElementById(sectionName + '-pagination');
    const ticketsFiltersBar = document.getElementById('tickets-filters');
    const vehiclesFiltersBar = document.getElementById('vehicles-filters');
    const driversFiltersBar = document.getElementById('drivers-filters');
    const guidesFiltersBar = document.getElementById('guides-filters');
    const icon = toggle ? toggle.querySelector('i') : null;
    
    // Filtre butonunu bul
    const filterButton = section.querySelector('.btn-outline-primary[title="Filtrele"]');
    
    if (section.classList.contains('section-collapsed')) {
        // Göster - Ana satıra geri taşı
        // moveToMainRow kapatıldı; sadece görünür yap
        section.style.display = '';
        content.style.display = 'block';
        content.style.opacity = '0';
        if (icon) icon.className = 'fas fa-minus';
        section.classList.remove('section-collapsed');
        
        // Pagination butonlarını göster
        if (pagination) {
            pagination.style.display = 'inline-block';
        }
        
        // Filtre barlarını göster
        if (sectionName === 'tickets' && ticketsFiltersBar) {
            ticketsFiltersBar.style.display = 'block';
        }
        if (sectionName === 'vehicles' && vehiclesFiltersBar) {
            vehiclesFiltersBar.style.display = 'block';
        }
        if (sectionName === 'drivers' && driversFiltersBar) {
            driversFiltersBar.style.display = 'block';
        }
        if (sectionName === 'guides' && guidesFiltersBar) {
            guidesFiltersBar.style.display = 'block';
        }
        
        // Fade in animasyonu
        setTimeout(() => {
            content.style.opacity = '1';
        }, 10);
        
        // Grid'i yeniden düzenle
        setTimeout(() => {
            adjustGridLayout();
        }, 150);
    } else {
        // Gizle - Alt satıra taşı
        content.style.opacity = '0';
        if (icon) icon.className = 'fas fa-plus';
        section.classList.add('section-collapsed');
        
        // Pagination butonlarını gizle
        if (pagination) {
            pagination.style.display = 'none';
        }
        
        // Filtre barlarını gizle
        if (sectionName === 'tickets' && ticketsFiltersBar) {
            ticketsFiltersBar.style.display = 'none';
        }
        if (sectionName === 'vehicles' && vehiclesFiltersBar) {
            vehiclesFiltersBar.style.display = 'none';
        }
        if (sectionName === 'drivers' && driversFiltersBar) {
            driversFiltersBar.style.display = 'none';
        }
        if (sectionName === 'guides' && guidesFiltersBar) {
            guidesFiltersBar.style.display = 'none';
        }
        
        // Fade out animasyonu
        setTimeout(() => {
            content.style.display = 'none';
            adjustGridLayout();
        }, 300);
    }
}

// Kapalı bölüme tıklayınca genişlet
document.addEventListener('click', function(e) {
    const collapsed = e.target.closest('.section-collapsed');
    if (!collapsed) return;
    // Toggle butonuna tıklandıysa zaten toggleSection çalışır, mükerrer engelle
    if (e.target.closest('button')) return;
    const sectionId = collapsed.id;
    if (sectionId) {
        const sectionName = sectionId.replace('-section', '');
        toggleSection(sectionName);
    }
});

// Pagination ayarlama fonksiyonu
function setPagination(sectionName, count) {
    const tbody = document.querySelector(`#${sectionName}-area`);
    const paginationButtons = document.querySelectorAll(`#${sectionName}-pagination .btn`);
    
    if (!tbody) return;
    
    // Aktif buton stilini güncelle
    paginationButtons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Tüm satırları al
    const allRows = Array.from(tbody.querySelectorAll('tr'));
    
    if (count === -1) {
        // Tümünü göster
        allRows.forEach(row => row.style.display = 'table-row');
    } else {
        // Belirtilen sayı kadar göster
        allRows.forEach((row, index) => {
            if (index < count) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

// Kaldırıldı: kapalı bölümler alanına taşıma davranışı

// Grid layout'u ayarla
function adjustGridLayout() {
    const mainRow = document.getElementById('main-sections-row');
    const visibleSections = Array.from(mainRow.children).filter(function(el){ return el.style.display !== 'none'; });
    
    // Ana satırdaki tüm bölümlerin col sınıflarını temizle
    visibleSections.forEach(section => {
        section.className = section.className.replace(/col-md-\d+/g, '');
    });
    
    // Ana satırdaki bölümlere yeni col sınıfları ekle
    if (visibleSections.length > 0) {
        const colSize = Math.floor(12 / visibleSections.length);
        visibleSections.forEach(section => {
            section.classList.add('col-md-' + colSize);
        });
    }
    
    // Kapatılan bölümlerin stilini ayarla
    // kapalı bölümler alanı kaldırıldı
}

// Çoklu bilet seçimi için global değişkenler
let selectedTickets = new Set();

// Filtre değerlerini saklamak için global değişkenler
let savedFilters = {
    tickets: {
        tour: '',
        date: '',
        status: '',
        nationality: '',
        activeStatus: ''
    },
    vehicles: {
        status: '',
        capacity: ''
    },
    drivers: {
        status: '',
        nationality: ''
    },
    guides: {
        nationality: '',
        drivers: ''
    }
};

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== OPERASYON SAYFASI YÜKLENDİ ===');
    // Sürükle-bırak event listener'ları
    const driverItems = document.querySelectorAll('.driver-item');
    driverItems.forEach(item => {
        item.addEventListener('dragstart', dragStart);
        item.addEventListener('dragend', dragEnd);
    });
    /* made by @hllgkx.0 */
    const ticketItems = document.querySelectorAll('.ticket-item');
    ticketItems.forEach(item => {
        item.addEventListener('dragstart', dragStart);
        item.addEventListener('dragend', dragEnd);
        item.addEventListener('click', toggleTicketSelection);
    });
    // Kapalı bölümler satırını turların hemen altına taşı
    // Kapalı bölümler özelliği iptal edildi; boşluk bırakma
    
    // Boş alana tıklandığında seçimi temizle
    document.addEventListener('click', function(event) {
        // Eğer tıklanan element bilet değilse ve seçim butonları değilse
        if (!event.target.closest('.ticket-item') && 
            !event.target.closest('#selection-info') &&
            !event.target.closest('.table-title')) {
            clearSelection();
        }
    });

    // Escape tuşuna basıldığında seçimi temizle
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && selectedTickets.size > 0) {
            clearSelection();
        }
    });

    // Şoförler ve rehberler bölümünü default kapalı yap
    setTimeout(() => {
        toggleSection('drivers');
        toggleSection('guides');

        // Filtre butonlarının doğru durumda olduğundan emin ol
        updateFilterButtonsVisibility();
    }, 100);

    // Tarihe göre araç kapasitesini güncelle
    try {
        const dateSel = document.getElementById('ticketsDateFilter');
        if (dateSel) {
            dateSel.addEventListener('change', function(){
                updateVehicleCapacitiesForDate(this.value || '');
            });
            if (dateSel.value) updateVehicleCapacitiesForDate(dateSel.value);
        } else if (typeof selectedDateISO !== 'undefined' && selectedDateISO) {
            updateVehicleCapacitiesForDate(selectedDateISO);
        }
    } catch (e) { console.warn('vehicle capacity date hook error', e); }

    // Başlangıçta pagination'ları ayarla
    setPagination('tickets', 15);
    setPagination('vehicles', 10);
    setPagination('drivers', 10);
    setPagination('guides', 10);

    // Biletler inline filtrelerini bağla
    const tourFilterEl = document.getElementById('ticketsTourFilter');
    const dateFilterEl = document.getElementById('ticketsDateFilter');
    const statusFilterEl = document.getElementById('ticketsStatusFilter');
    const nationalityFilterEl = document.getElementById('ticketsNationalityFilter');
    const activeStatusFilterEl = document.getElementById('ticketsActiveStatusFilter');
    const applyBtnEl = document.getElementById('ticketsFiltersApply');
    const clearBtnEl = document.getElementById('ticketsFiltersClear');

    function applyTicketsInlineFilters() {
        try {
            console.log('=== BİLET FİLTRESİ UYGULANIYORR ===');
            const tourVal = tourFilterEl ? tourFilterEl.value : '';
            const dateVal = dateFilterEl ? dateFilterEl.value : '';
            const statusVal = statusFilterEl ? statusFilterEl.value : '';
            const nationalityVal = nationalityFilterEl ? nationalityFilterEl.value : '';
            const activeVal = activeStatusFilterEl ? activeStatusFilterEl.value : '';

            console.log('Filtre değerleri:', {
                tour: tourVal,
                date: dateVal,
                status: statusVal,
                nationality: nationalityVal,
                active: activeVal
            });

            // Kaydet
            savedFilters.tickets.tour = tourVal;
            savedFilters.tickets.date = dateVal;
            savedFilters.tickets.status = statusVal;
            savedFilters.tickets.nationality = nationalityVal;
            savedFilters.tickets.activeStatus = activeVal;

            // Uygula
            filterTickets(tourVal, dateVal, statusVal, nationalityVal, activeVal);
        } catch (error) {
            console.error('applyTicketsInlineFilters HATASI:', error);
        }
    }

    // Kaydedilmiş değerleri geri yükle
    if (tourFilterEl) tourFilterEl.value = savedFilters.tickets.tour || '';
    if (dateFilterEl) dateFilterEl.value = savedFilters.tickets.date || '';
    if (statusFilterEl) statusFilterEl.value = savedFilters.tickets.status || '';
    if (nationalityFilterEl) nationalityFilterEl.value = savedFilters.tickets.nationality || '';
    if (activeStatusFilterEl) activeStatusFilterEl.value = savedFilters.tickets.activeStatus || '';

    // Uygula butonuna event listener ekle
    if (applyBtnEl) {
        applyBtnEl.addEventListener('click', function() {
            console.log('Bilet filtreleri uygula butonu tıklandı');
            applyTicketsInlineFilters();
        });
    }

    // Değişiklik olunca anında uygula
    if (tourFilterEl) tourFilterEl.addEventListener('change', applyTicketsInlineFilters);
    if (dateFilterEl) dateFilterEl.addEventListener('change', applyTicketsInlineFilters);
    if (statusFilterEl) statusFilterEl.addEventListener('change', applyTicketsInlineFilters);
    if (nationalityFilterEl) nationalityFilterEl.addEventListener('change', applyTicketsInlineFilters);
    if (activeStatusFilterEl) activeStatusFilterEl.addEventListener('change', applyTicketsInlineFilters);

    if (clearBtnEl) {
        clearBtnEl.addEventListener('click', function() {
            console.log('Temizle butonu tıklandı');
            if (tourFilterEl) tourFilterEl.value = '';
            if (dateFilterEl) dateFilterEl.value = '';
            if (statusFilterEl) statusFilterEl.value = '';
            if (nationalityFilterEl) nationalityFilterEl.value = '';
            if (activeStatusFilterEl) activeStatusFilterEl.value = '';

            // Kaydı temizle ve tüm satırları göster
            savedFilters.tickets.tour = '';
            savedFilters.tickets.date = '';
            savedFilters.tickets.status = '';
            savedFilters.tickets.nationality = '';
            savedFilters.tickets.activeStatus = '';

            const rows = document.querySelectorAll('#tickets-area tr');
            rows.forEach(r => r.style.display = '');
            console.log('Tüm biletler gösterildi');
        });
    }

    // Araçlar inline filtreleri
    const vStatusEl = document.getElementById('vehiclesStatusFilter');
    const vCapacityEl = document.getElementById('vehiclesCapacityFilter');
    const vApplyEl = document.getElementById('vehiclesFiltersApply');
    const vClearEl = document.getElementById('vehiclesFiltersClear');

    function applyVehiclesInlineFilters() {
        console.log('=== ARAÇ FİLTRESİ UYGULANIYORR ===');
        const statusVal = vStatusEl ? vStatusEl.value : '';
        const capacityVal = vCapacityEl ? vCapacityEl.value : '';

        console.log('Araç filtre değerleri:', {
            status: statusVal,
            capacity: capacityVal
        });

        savedFilters.vehicles.status = statusVal;
        savedFilters.vehicles.capacity = capacityVal;

        filterVehicles(statusVal, capacityVal);
    }

    if (vStatusEl) vStatusEl.value = savedFilters.vehicles.status || '';
    if (vCapacityEl) vCapacityEl.value = savedFilters.vehicles.capacity || '';

    // Araç uygula butonuna event listener ekle
    if (vApplyEl) {
        vApplyEl.addEventListener('click', function() {
            console.log('Araç filtreleri uygula butonu tıklandı');
            applyVehiclesInlineFilters();
        });
    }

    // Değişiklik olunca anında uygula (Araçlar)
    if (vStatusEl) vStatusEl.addEventListener('change', applyVehiclesInlineFilters);
    if (vCapacityEl) vCapacityEl.addEventListener('change', applyVehiclesInlineFilters);

    if (vClearEl) {
        vClearEl.addEventListener('click', function() {
            if (vStatusEl) vStatusEl.value = '';
            if (vCapacityEl) vCapacityEl.value = '';
            savedFilters.vehicles.status = '';
            savedFilters.vehicles.capacity = '';
            const rows = document.querySelectorAll('#vehicles-area tr');
            rows.forEach(r => r.style.display = '');
        });
    }

    // Şoförler inline filtreleri
    const dStatusEl = document.getElementById('driversStatusFilter');
    const dNationalityEl = document.getElementById('driversNationalityFilter');
    const dApplyEl = document.getElementById('driversFiltersApply');
    const dClearEl = document.getElementById('driversFiltersClear');

    function applyDriversInlineFilters() {
        const statusVal = dStatusEl ? dStatusEl.value : '';
        const nationalityVal = dNationalityEl ? dNationalityEl.value : '';

        savedFilters.drivers.status = statusVal;
        savedFilters.drivers.nationality = nationalityVal;

        filterDrivers(statusVal, nationalityVal);
    }

    if (dStatusEl) dStatusEl.value = savedFilters.drivers.status || '';
    if (dNationalityEl) dNationalityEl.value = savedFilters.drivers.nationality || '';

    // Şoför uygula butonuna event listener ekle
    if (dApplyEl) {
        dApplyEl.addEventListener('click', function() {
            console.log('Şoför filtreleri uygula butonu tıklandı');
            applyDriversInlineFilters();
        });
    }

    // Değişiklik olunca anında uygula (Şoförler)
    if (dStatusEl) dStatusEl.addEventListener('change', applyDriversInlineFilters);
    if (dNationalityEl) dNationalityEl.addEventListener('change', applyDriversInlineFilters);

    if (dClearEl) {
        dClearEl.addEventListener('click', function() {
            if (dStatusEl) dStatusEl.value = '';
            if (dNationalityEl) dNationalityEl.value = '';
            savedFilters.drivers.status = '';
            savedFilters.drivers.nationality = '';
            const rows = document.querySelectorAll('#drivers-area tr');
            rows.forEach(r => r.style.display = '');
        });
    }

    // Rehberler inline filtreleri
    const gNationalityEl = document.getElementById('guidesNationalityFilter');
    const gDriverCountEl = document.getElementById('guidesDriverCountFilter');
    const gApplyEl = document.getElementById('guidesFiltersApply');
    const gClearEl = document.getElementById('guidesFiltersClear');

    function applyGuidesInlineFilters() {
        const nationalityVal = gNationalityEl ? gNationalityEl.value : '';
        const driverCountVal = gDriverCountEl ? gDriverCountEl.value : '';

        savedFilters.guides.nationality = nationalityVal;
        savedFilters.guides.driverCount = driverCountVal;

        filterGuides(nationalityVal, driverCountVal);
    }

    if (gNationalityEl) gNationalityEl.value = savedFilters.guides.nationality || '';
    if (gDriverCountEl) gDriverCountEl.value = savedFilters.guides.driverCount || '';

    // Rehber uygula butonuna event listener ekle
    if (gApplyEl) {
        gApplyEl.addEventListener('click', function() {
            console.log('Rehber filtreleri uygula butonu tıklandı');
            applyGuidesInlineFilters();
        });
    }

    // Değişiklik olunca anında uygula (Rehberler)
    if (gNationalityEl) gNationalityEl.addEventListener('change', applyGuidesInlineFilters);
    if (gDriverCountEl) gDriverCountEl.addEventListener('change', applyGuidesInlineFilters);
    if (gClearEl) {
        gClearEl.addEventListener('click', function() {
            if (gNationalityEl) gNationalityEl.value = '';
            if (gDriverCountEl) gDriverCountEl.value = '';
            savedFilters.guides.nationality = '';
            savedFilters.guides.driverCount = '';
            const rows = document.querySelectorAll('#guides-area tr');
            rows.forEach(r => r.style.display = '');
        });
    }
});

// Gün seçici şerit yönetimi
(function(){
    const daySelector = document.getElementById('day-selector');
    const dateFilterEl = document.getElementById('ticketsDateFilter');
    const btnPrev = document.getElementById('dayPrev');
    const btnNext = document.getElementById('dayNext');
    const btnToday = document.getElementById('dayToday');
    const btnClear = document.getElementById('dayClear');
    const backToDaysBtn = document.getElementById('backToDays');
    const toursListEl = document.getElementById('tours-list');
    const inlineSlot = document.getElementById('operations-board-inline-slot');
    const boardEl = document.getElementById('operations-board');

    let selectedDateISO = '';
    let selectedTourId = '';

    if (!daySelector) return;

    let currentMonth = new Date();
    currentMonth.setDate(1);
    currentMonth.setHours(0,0,0,0);

    function toISO(d){
        const t = new Date(d);
        t.setHours(0,0,0,0);
        const y = t.getFullYear();
        const m = (t.getMonth()+1).toString().padStart(2,'0');
        const dd = t.getDate().toString().padStart(2,'0');
        return y + '-' + m + '-' + dd; // Local YYYY-MM-DD (timezone-shift safe)
    }
    function trDow(d){ return ['Paz','Pzt','Sal','Çar','Per','Cum','Cmt'][d.getDay()]; }
    function pad(n){ return (n<10?'0':'') + n; }

    function renderDays(){
        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        // Başlık
        const titleEl = document.getElementById('monthTitle');
        if (titleEl) {
            const trMonths = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
            titleEl.textContent = trMonths[month] + ' ' + year;
        }

        let dayCards = [];
        for (let day = 1; day <= lastDay.getDate(); day++){
            const d = new Date(year, month, day);
            const isToday = toISO(d) === toISO(new Date());
            const isSelected = dateFilterEl && dateFilterEl.value && dateFilterEl.value === toISO(d);
            const cls = ['day-chip'];
            if (isSelected) cls.push('active');
            dayCards.push(`<div class=\"${cls.join(' ')}\" data-date=\"${toISO(d)}\">`+
                             `<div class=\"dc-dow\">${trDow(d)}</div>`+
                             `<div class=\"dc-day\">${d.getDate()}</div>`+
                             `<div class=\"dc-date\">${pad(d.getMonth()+1)}.${d.getFullYear()}</div>`+
                             `</div>`);
        }
        daySelector.innerHTML = dayCards.join('');

        // Click binding
        daySelector.querySelectorAll('.day-chip').forEach(el=>{
            el.addEventListener('click', function(){
                const ds = this.getAttribute('data-date');
                if (dateFilterEl) {
                    dateFilterEl.value = ds;
                    // Kaydet ve uygula (mevcut filterTickets fonksiyonunu kullan)
                    const tourVal = (document.getElementById('ticketsTourFilter')||{}).value || '';
                    const statusVal = (document.getElementById('ticketsStatusFilter')||{}).value || '';
                    const natVal = (document.getElementById('ticketsNationalityFilter')||{}).value || '';
                    const activeVal = (document.getElementById('ticketsActiveStatusFilter')||{}).value || '';
                    savedFilters.tickets.date = ds;
                    filterTickets(tourVal, ds, statusVal, natVal, activeVal);
                }
                // UI güncelle ve tur listesine geç
                daySelector.querySelectorAll('.day-chip').forEach(c=>c.classList.remove('active'));
                this.classList.add('active');
                showToursForDate(ds);
            });
        });
    }

    function shiftMonth(delta){
        currentMonth.setMonth(currentMonth.getMonth()+delta);
        currentMonth.setDate(1);
        renderDays();
    }

    renderDays();

    if (btnPrev) btnPrev.addEventListener('click', ()=>{ if (toursListEl && toursListEl.style.display !== 'none') { hideToursAndBoard(); } shiftMonth(-1); });
    if (btnNext) btnNext.addEventListener('click', ()=>{ if (toursListEl && toursListEl.style.display !== 'none') { hideToursAndBoard(); } shiftMonth(1); });
    if (btnToday) btnToday.addEventListener('click', ()=>{ if (toursListEl && toursListEl.style.display !== 'none') { hideToursAndBoard(); } const today = new Date(); currentMonth = new Date(); currentMonth.setDate(1); currentMonth.setHours(0,0,0,0); const todayISO = toISO(today); if(dateFilterEl){ dateFilterEl.value=todayISO; savedFilters.tickets.date=todayISO; const tourVal=(document.getElementById('ticketsTourFilter')||{}).value||''; const statusVal=(document.getElementById('ticketsStatusFilter')||{}).value||''; const natVal=(document.getElementById('ticketsNationalityFilter')||{}).value||''; const activeVal=(document.getElementById('ticketsActiveStatusFilter')||{}).value||''; filterTickets(tourVal, todayISO, statusVal, natVal, activeVal);} renderDays(); });
    if (btnClear) btnClear.addEventListener('click', ()=>{ if(dateFilterEl){ dateFilterEl.value=''; savedFilters.tickets.date=''; const tourVal=(document.getElementById('ticketsTourFilter')||{}).value||''; const statusVal=(document.getElementById('ticketsStatusFilter')||{}).value||''; const natVal=(document.getElementById('ticketsNationalityFilter')||{}).value||''; const activeVal=(document.getElementById('ticketsActiveStatusFilter')||{}).value||''; filterTickets(tourVal, '', statusVal, natVal, activeVal);} daySelector.querySelectorAll('.day-chip').forEach(c=>c.classList.remove('active')); hideToursAndBoard(); });
    if (backToDaysBtn) backToDaysBtn.addEventListener('click', ()=>{ hideToursAndBoard(true); });

    // İlk girişte: alt kısımdaki TUR LİSTESİ görünsün ve tarih otomatik yarın olsun
    (function initBoardWithTomorrow(){
        try {
            const tourVal = (document.getElementById('ticketsTourFilter')||{}).value || '';
            const statusVal = (document.getElementById('ticketsStatusFilter')||{}).value || '';
            const natVal = (document.getElementById('ticketsNationalityFilter')||{}).value || '';
            const activeVal = (document.getElementById('ticketsActiveStatusFilter')||{}).value || '';
            const todayISO = toISO(new Date());
            let dateISO = dateFilterEl ? (dateFilterEl.value || '') : '';
            if (!dateISO || dateISO <= todayISO) {
                const t = new Date(); t.setDate(t.getDate()+1);
                dateISO = toISO(t);
                if (dateFilterEl) dateFilterEl.value = dateISO;
                savedFilters.tickets.date = dateISO;
            }
            // Önce biletleri tarihe göre filtrele, ardından tur listesine geç
            filterTickets(tourVal, dateISO, statusVal, natVal, activeVal);
            // Gün listesindeki ilgili günü aktif işaretle
            try {
                daySelector.querySelectorAll('.day-chip').forEach(c=>{
                    c.classList.toggle('active', c.getAttribute('data-date')===dateISO);
                });
            } catch(e) {}
            showToursForDate(dateISO);
        } catch(e) { console.warn('initBoardWithTomorrow error', e); }
    })();

    function hideToursAndBoard(resetActive){
        if (toursListEl) toursListEl.style.display = 'none';
        if (backToDaysBtn) backToDaysBtn.style.display = 'none';
        // Gün şeridi görünür kalır
        if (inlineSlot && boardEl) {
            inlineSlot.style.display = 'none';
            const home = document.getElementById('operations-board-home');
            if (home) home.appendChild(boardEl);
            boardEl.style.display = 'none';
        }
        if (resetActive) {
            daySelector.querySelectorAll('.day-chip').forEach(c=>c.classList.remove('active'));
        }
        selectedDateISO = '';
        selectedTourId = '';
        // Başlık ve toolbar aynı kalabilir
    } 

    // Tur listesi pagination değişkenleri
    let toursCurrentPage = 1;
    let toursPerPage = 10;
    let allToursData = [];

    function showToursForDate(dateISO){
        selectedDateISO = dateISO;
        if (!toursListEl) return;
        // Günler görünür kalsın, turlar altta gösterilsin
        if (backToDaysBtn) backToDaysBtn.style.display = 'none';
        toursListEl.style.display = '';
        // Tur seçimi açıldığında kapalı bölümler satırını görünür yap
        // Kapalı bölümler özelliği kaldırıldığı için bir şey yapma
        try { (document.getElementById('modeTitle')||{}).textContent = 'Tur Seçimi'; } catch(e) {}
        const monthTitle = document.getElementById('monthTitle');
        if (monthTitle) {
            try {
                const d = new Date(dateISO);
                const trMonths = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
                const text = `${d.getDate()} ${trMonths[d.getMonth()]} ${d.getFullYear()}`;
                monthTitle.textContent = text;
            } catch(e) {}
        }
        // Toolbar görünür kalır

        // Build tour chips with counts for the date
        try { updateVehicleCapacitiesForDate(dateISO); } catch(e) {}
        const tours = (window.toursData || []);
        const ticketRows = Array.from(document.querySelectorAll('#tickets-area tr'));
        
        // Turları bilet sayısıyla birlikte sakla
        allToursData = tours.map(function(t){
            const tourId = (t.id || t.ID || t.Id);
            const name = t.name || t.title || ('Tur #' + tourId);
            const count = ticketRows.filter(r => r.dataset.tourId == String(tourId) && r.dataset.tourDate === dateISO).length;
            return { id: tourId, name: name, count: count };
        });
        
        toursCurrentPage = 1; // Yeni tarih seçildiğinde ilk sayfaya dön
        renderToursPaginated();
    }
    
    function renderToursPaginated() {
        const totalTours = allToursData.length;
        const totalPages = Math.ceil(totalTours / toursPerPage);
        const startIndex = (toursCurrentPage - 1) * toursPerPage;
        const endIndex = Math.min(startIndex + toursPerPage, totalTours);
        const currentTours = allToursData.slice(startIndex, endIndex);
        
        let html = '';
        
        if (totalTours === 0) {
            html = '<div class="text-center text-muted py-3">Bu tarihte tur bulunamadı</div>';
        } else {
            // Pagination bar
            html += '<div class="tours-pagination-bar">';
            html += '<div class="tours-pagination-left">';
            html += '<span class="page-size-label">Sayfa başına:</span>';
            html += '<div class="tours-page-size-selector">';
            [5, 10, 15, 20].forEach(function(size) {
                html += `<button type="button" class="tours-page-size-btn ${toursPerPage === size ? 'active' : ''}" data-size="${size}">${size}</button>`;
            });
            html += '</div></div>';
            
            html += '<div class="tours-pagination-right">';
            html += `<button type="button" class="tours-page-nav-btn" id="toursPrevPage" ${toursCurrentPage <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
            html += `<span class="tours-page-info"><strong>${toursCurrentPage}</strong> / ${totalPages} <span class="d-none d-sm-inline">(${totalTours} tur)</span></span>`;
            html += `<button type="button" class="tours-page-nav-btn" id="toursNextPage" ${toursCurrentPage >= totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
            html += '</div></div>';
            
            // Tour grid
            html += '<div class="tour-grid">';
            currentTours.forEach(function(t, idx){
                const globalIndex = startIndex + idx;
                html += `<button type="button" class="tour-card tour-chip" data-tour-id="${t.id}" data-index="${globalIndex}">`+
                        `<div class="tour-card-left">`+
                        `<span class="tour-title">${t.name}</span>`+
                        `</div>`+
                        `<span class="tour-count-badge">${t.count}</span>`+
                        `</button>`;
            });
            html += '</div>';
        }
        
        toursListEl.innerHTML = html;
        
        // Apply styles dynamically to tour cards
        toursListEl.querySelectorAll('.tour-chip').forEach(function(el){
            const index = parseInt(el.dataset.index) || 0;
            // Base styles - daha küçük ve sade
            el.style.cssText = `
                appearance: none !important;
                -webkit-appearance: none !important;
                border: 1px solid #e1e5e9 !important;
                width: 100% !important;
                border-radius: 8px !important;
                padding: 12px 10px !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 8px !important;
                min-height: 50px !important;
                overflow: hidden !important;
                background: #ffffff !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
            `;
            
            // Sade renkler - sadece kenarlık rengi değişiyor
            const borderColors = [
                '#6366f1', // indigo
                '#ef4444', // red
                '#06b6d4', // cyan
                '#10b981', // emerald
                '#f59e0b'  // amber
            ];
            el.style.borderLeftColor = borderColors[index % borderColors.length] + ' !important';
            el.style.borderLeftWidth = '3px !important';
            
            // Style title
            const title = el.querySelector('.tour-title');
            if (title) {
                title.style.cssText = `
                    font-weight: 600 !important;
                    color: #374151 !important;
                    font-size: 14px !important;
                    line-height: 1.4 !important;
                    text-align: left !important;
                    white-space: nowrap !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    flex: 1 !important;
                `;
            }
            
            // Style badge
            const badge = el.querySelector('.tour-count-badge');
            if (badge) {
                const badgeColor = borderColors[index % borderColors.length];
                badge.style.cssText = `
                    border-radius: 12px !important;
                    background: ${badgeColor} !important;
                    color: #ffffff !important;
                    padding: 4px 8px !important;
                    font-weight: 600 !important;
                    font-size: 12px !important;
                    white-space: nowrap !important;
                    min-width: 20px !important;
                    text-align: center !important;
                `;
            }
            
            // Hover effects - daha sade
            el.addEventListener('mouseenter', function(){
                this.style.transform = 'translateY(-1px) !important';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15) !important';
                this.style.backgroundColor = '#f8fafc !important';
            });
            el.addEventListener('mouseleave', function(){
                this.style.transform = 'none !important';
                this.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1) !important';
                this.style.backgroundColor = '#ffffff !important';
            });
        });

        // Bind clicks for tour cards
        const backBtn = document.getElementById('localBackToDays');
        if (backBtn) backBtn.addEventListener('click', function(){ hideToursAndBoard(false); });
        toursListEl.querySelectorAll('.tour-chip').forEach(function(el){
            el.addEventListener('click', function(){
                const tourId = this.getAttribute('data-tour-id');
                chooseTour(tourId);
            });
        });
        
        // Bind pagination controls
        const prevBtn = document.getElementById('toursPrevPage');
        const nextBtn = document.getElementById('toursNextPage');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function(){
                if (toursCurrentPage > 1) {
                    toursCurrentPage--;
                    renderToursPaginated();
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(){
                const totalPages = Math.ceil(allToursData.length / toursPerPage);
                if (toursCurrentPage < totalPages) {
                    toursCurrentPage++;
                    renderToursPaginated();
                }
            });
        }
        
        // Bind page size buttons
        toursListEl.querySelectorAll('.tours-page-size-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
                const newSize = parseInt(this.dataset.size);
                if (newSize !== toursPerPage) {
                    toursPerPage = newSize;
                    toursCurrentPage = 1; // Sayfa boyutu değişince ilk sayfaya dön
                    renderToursPaginated();
                }
            });
        });
    }

    function chooseTour(tourId){
        selectedTourId = tourId;
        // Ensure inline board visible
        if (inlineSlot && boardEl) {
            inlineSlot.style.display = '';
            inlineSlot.appendChild(boardEl);
            boardEl.style.display = 'block';
        }
        // Sync filters and apply
        const tourSel = document.getElementById('ticketsTourFilter');
        const dateSel = document.getElementById('ticketsDateFilter');
        if (tourSel) tourSel.value = String(tourId);
        if (dateSel) dateSel.value = selectedDateISO;
        savedFilters.tickets.tour = String(tourId);
        savedFilters.tickets.date = selectedDateISO;
        filterTickets(String(tourId), selectedDateISO, '', '', '');
        // Scroll into view
        try { inlineSlot.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch(e) {}
    }
})();

// Modal yönetimi: Operasyon tahtasını ayrı bir sayfa gibi göster
function openOperationsModal(dateISO){
    try {
        const modal = document.getElementById('operationsModal');
        const slot = document.getElementById('operations-board-slot');
        const home = document.getElementById('operations-board-home');
        const board = document.getElementById('operations-board');
        const title = document.getElementById('operationsModalTitle');
        if (!modal || !slot || !board) return;
        title.textContent = 'Operasyon - ' + (new Date(dateISO)).toLocaleDateString('tr-TR');
        // Tahtayı modal slotuna taşı
        slot.appendChild(board);
        board.style.display = 'block';
        $(modal).modal('show');
    } catch(e) { console.error('openOperationsModal error', e); }
}

function closeOperationsModal(){
    try {
        const modal = document.getElementById('operationsModal');
        const slot = document.getElementById('operations-board-slot');
        const home = document.getElementById('operations-board-home');
        const board = document.getElementById('operations-board');
        if (!modal || !home || !board) return;
        // Tahtayı ana sayfadaki gizli bölgeye geri taşı ve gizli tut
        home.appendChild(board);
        board.style.display = 'none';
        $(modal).modal('hide');
    } catch(e) { console.error('closeOperationsModal error', e); }
}

// Wrapper functions for inline filter buttons (onclick attributes)
function applyTicketsFilter() {
    try {
        const tour = document.getElementById('ticketsTourFilter');
        const date = document.getElementById('ticketsDateFilter');
        const status = document.getElementById('ticketsStatusFilter');
        const nat = document.getElementById('ticketsNationalityFilter');
        const active = document.getElementById('ticketsActiveStatusFilter');
        const tourVal = tour ? tour.value : '';
        const dateVal = date ? date.value : '';
        const statusVal = status ? status.value : '';
        const natVal = nat ? nat.value : '';
        const activeVal = active ? active.value : '';
        savedFilters.tickets.tour = tourVal;
        savedFilters.tickets.date = dateVal;
        savedFilters.tickets.status = statusVal;
        savedFilters.tickets.nationality = natVal;
        savedFilters.tickets.activeStatus = activeVal;
        filterTickets(tourVal, dateVal, statusVal, natVal, activeVal);
    } catch (e) { console.error('applyTicketsFilter error', e); }
}

function clearTicketsFilter() {
    const tour = document.getElementById('ticketsTourFilter');
    const date = document.getElementById('ticketsDateFilter');
    const status = document.getElementById('ticketsStatusFilter');
    const nat = document.getElementById('ticketsNationalityFilter');
    const active = document.getElementById('ticketsActiveStatusFilter');
    if (tour) tour.value = '';
    if (date) date.value = '';
    if (status) status.value = '';
    if (nat) nat.value = '';
    if (active) active.value = '';
    savedFilters.tickets.tour = '';
    savedFilters.tickets.date = '';
    savedFilters.tickets.status = '';
    savedFilters.tickets.nationality = '';
    savedFilters.tickets.activeStatus = '';
    const rows = document.querySelectorAll('#tickets-area tr');
    rows.forEach(r => r.style.display = '');
}

function applyVehiclesFilter() {
    try {
        const status = document.getElementById('vehiclesStatusFilter');
        const capacity = document.getElementById('vehiclesCapacityFilter');
        const statusVal = status ? status.value : '';
        const capacityVal = capacity ? capacity.value : '';
        savedFilters.vehicles.status = statusVal;
        savedFilters.vehicles.capacity = capacityVal;
        filterVehicles(statusVal, capacityVal);
    } catch (e) { console.error('applyVehiclesFilter error', e); }
}

function clearVehiclesFilter() {
    const status = document.getElementById('vehiclesStatusFilter');
    const capacity = document.getElementById('vehiclesCapacityFilter');
    if (status) status.value = '';
    if (capacity) capacity.value = '';
    savedFilters.vehicles.status = '';
    savedFilters.vehicles.capacity = '';
    const rows = document.querySelectorAll('#vehicles-area tr');
    rows.forEach(r => r.style.display = '');
}

function applyDriversFilter() {
    try {
        const status = document.getElementById('driversStatusFilter');
        const nat = document.getElementById('driversNationalityFilter');
        const statusVal = status ? status.value : '';
        const natVal = nat ? nat.value : '';
        savedFilters.drivers.status = statusVal;
        savedFilters.drivers.nationality = natVal;
        filterDrivers(statusVal, natVal);
    } catch (e) { console.error('applyDriversFilter error', e); }
}

function clearDriversFilter() {
    const status = document.getElementById('driversStatusFilter');
    const nat = document.getElementById('driversNationalityFilter');
    if (status) status.value = '';
    if (nat) nat.value = '';
    savedFilters.drivers.status = '';
    savedFilters.drivers.nationality = '';
    const rows = document.querySelectorAll('#drivers-area tr');
    rows.forEach(r => r.style.display = '');
}

// Filtre butonlarının görünürlüğünü güncelle
function updateFilterButtonsVisibility() {
    const sections = ['tickets', 'vehicles', 'drivers', 'guides'];

    sections.forEach(sectionName => {
        const section = document.getElementById(sectionName + '-section');
        const filterButton = section ? section.querySelector('.btn-outline-primary[title="Filtrele"]') : null;

        if (section && filterButton) {
            if (section.classList.contains('section-collapsed')) {
                filterButton.style.display = 'none';
                console.log(`Filtre butonu gizlendi: ${sectionName}`);
            } else {
                filterButton.style.display = 'inline-block';
                console.log(`Filtre butonu gösterildi: ${sectionName}`);
            }
        }
    });
}

// Bilet seçimi toggle fonksiyonu
function toggleTicketSelection(event) {
    event.preventDefault();
    event.stopPropagation();

    const ticketRow = event.currentTarget;
    const ticketId = ticketRow.getAttribute('data-ticket-id');

    if (selectedTickets.has(ticketId)) {
        // Seçimi kaldır
        selectedTickets.delete(ticketId);
        ticketRow.classList.remove('ticket-selected');

        // Inline stilleri temizle
        ticketRow.style.cssText = '';
    } else {
        // Seç
        selectedTickets.add(ticketId);
        ticketRow.classList.add('ticket-selected');

        // İnce ve zarif seçim efekti
        ticketRow.style.backgroundColor = '#f8f9fa';
        ticketRow.style.borderLeft = '4px solid #007bff';
        ticketRow.style.boxShadow = '0 2px 4px rgba(0,123,255,0.1)';
        ticketRow.style.transform = 'translateY(-1px)';
    }

    updateSelectionInfo();
}

// Seçim bilgisini güncelle
function updateSelectionInfo() {
    const count = selectedTickets.size;
    let infoElement = document.getElementById('selection-info');

    if (!infoElement) {
        // İlk kez oluştur
        const ticketsHeader = document.querySelector('#tickets-section .table-title');
        if (ticketsHeader) {
            infoElement = document.createElement('div');
            infoElement.id = 'selection-info';
            infoElement.style.cssText = 'margin-top: 5px; font-size: 12px;';
            ticketsHeader.appendChild(infoElement);
        } else {
            console.error('tickets-section .table-title bulunamadı');
            return;
        }
    }

    if (count > 0) {
        infoElement.innerHTML = `<span class="badge badge-info">${count} bilet seçili</span> 
                                <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="clearSelection()">
                                    <i class="fas fa-times"></i> Seçimi Temizle
                                </button>`;
        infoElement.style.display = 'block';
    } else {
        infoElement.style.display = 'none';
    }
}

// Seçimi temizle
function clearSelection() {
    selectedTickets.clear();
    document.querySelectorAll('.ticket-selected').forEach(element => {
        element.classList.remove('ticket-selected');
        // Normal stile dönmesini sağla - tüm stil özelliklerini sıfırla
        element.style.cssText = '';
    });
    updateSelectionInfo();
}

// Çoklu bilet drag start
function multiTicketDragStart(event) {
    const ticketId = event.target.getAttribute('data-ticket-id');

    // Eğer drag edilen bilet seçili değilse, sadece onu seç
    if (!selectedTickets.has(ticketId)) {
        clearSelection();
        selectedTickets.add(ticketId);
        event.target.classList.add('ticket-selected');
        updateSelectionInfo();
    }

    // Seçili biletlerin ID'lerini transfer et
    const selectedIds = Array.from(selectedTickets);
    event.dataTransfer.setData('text/plain', selectedIds.join(','));
    event.dataTransfer.setData('type', 'multiple-tickets');

    // Görsel geri bildirim
    event.target.style.opacity = '0.5';
    selectedTickets.forEach(id => {
        const element = document.querySelector(`[data-ticket-id="${id}"]`);
        if (element && element !== event.target) {
            element.style.opacity = '0.5';
        }
    });
}

// Orjinal dragStart fonksiyonunu güncelle
function dragStart(event) {
    const ticketId = event.target.getAttribute('data-ticket-id');

    // Eğer seçili biletler varsa, çoklu drag başlat
    if (selectedTickets.size > 0 && selectedTickets.has(ticketId)) {
        multiTicketDragStart(event);
        return;
    }

    // Tek bilet drag (orijinal davranış)
    event.dataTransfer.setData('text/plain', ticketId);
    event.dataTransfer.setData('type', 'single-ticket');
    event.target.style.opacity = '0.5';
}

// dragEnd fonksiyonunu güncelle
function dragEnd(event) {
    event.target.style.opacity = '1';

    // Tüm seçili biletlerin opacity'sini sıfırla
    selectedTickets.forEach(id => {
        const element = document.querySelector(`[data-ticket-id="${id}"]`);
        if (element) {
            element.style.opacity = '1';
        }
    });
}

// Legacy: Filtre modalını göster (disabled - using inline filters now)
function showFilterModal_DISABLED(section) {
    const modal = document.getElementById('filterModal');
    const modalTitle = document.getElementById('filterModalTitle');
    const filterContent = document.getElementById('filterModalContent');

    // Modal başlığını ayarla
    const sectionTitles = {
        'tickets': 'Biletler',
        'vehicles': 'Araçlar',
        'drivers': 'Şoförler',
        'guides': 'Rehberler'
    };
    modalTitle.textContent = sectionTitles[section] + ' Filtresi';

    // Filtre içeriğini oluştur
    let filterHTML = '';

    if (section === 'tickets') {
        filterHTML = `
            <div class="form-group">
                <label>Tura Göre Filtrele:</label>
                <select class="form-control" id="tourFilter">
                    <option value="">Tümü</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tarihe Göre Filtrele:</label>
                <input type="date" class="form-control" id="dateFilter">
            </div>
            <div class="form-group">
                <label>Araç Durumuna Göre Filtrele:</label>
                <select class="form-control" id="statusFilter">
                    <option value="">Tümü</option>
                    <option value="with-vehicle">Araçlı</option>
                    <option value="without-vehicle">Araçsız</option>
                </select>
            </div>
            <div class="form-group">
                <label>Milliyete Göre Filtrele:</label>
                <select class="form-control" id="nationalityFilter">
                    <option value="">Tümü</option>
                    <option value="DE">Almanca</option>
                    <option value="RU">Rusça</option>
                    <option value="EN">İngilizce</option>
                    <option value="TR">Türkçe</option>
                </select>
            </div>
            <div class="form-group">
                <label>Aktiflik Durumuna Göre Filtrele:</label>
                <select class="form-control" id="activeStatusFilter">
                    <option value="">Tümü</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Pasif</option>
                </select>
            </div>
        `;
    } else if (section === 'vehicles') {
        filterHTML = `
            <div class="form-group">
                <label>Duruma Göre Filtrele:</label>
                <select class="form-control" id="vehicleStatusFilter">
                    <option value="">Tümü</option>
                    <option value="available">Müsait</option>
                    <option value="busy">Meşgul</option>
                </select>
            </div>
            <div class="form-group">
                <label>Kapasiteye Göre Filtrele:</label>
                <select class="form-control" id="capacityFilter">
                    <option value="">Tümü</option>
                    <option value="small">Küçük (1-8 kişi)</option>
                    <option value="medium">Orta (9-16 kişi)</option>
                    <option value="large">Büyük (17+ kişi)</option>
                </select>
            </div>
        `;
    } else if (section === 'drivers') {
        filterHTML = `
            <div class="form-group">
                <label>Duruma Göre Filtrele:</label>
                <select class="form-control" id="driverStatusFilter">
                    <option value="">Tümü</option>
                    <option value="available">Müsait</option>
                    <option value="busy">Meşgul</option>
                </select>
            </div>
            <div class="form-group">
                <label>Desteklenen Milliyete Göre Filtrele:</label>
                <select class="form-control" id="driverNationalityFilter">
                    <option value="">Tümü</option>
                    <option value="DE">Almanca</option>
                    <option value="RU">Rusça</option>
                    <option value="EN">İngilizce</option>
                    <option value="TR">Türkçe</option>
                </select>
            </div>
        `;
    } else if (section === 'guides') {
        filterHTML = `
            <div class="form-group">
                <label>Desteklenen Milliyete Göre Filtrele:</label>
                <select class="form-control" id="guideNationalityFilter">
                    <option value="">Tümü</option>
                    <option value="DE">Almanca</option>
                    <option value="RU">Rusça</option>
                    <option value="EN">İngilizce</option>
                    <option value="TR">Türkçe</option>
                </select>
            </div>
            <div class="form-group">
                <label>Atanmış Şoförlere Göre Filtrele:</label>
                <select class="form-control" id="assignedDriversFilter">
                    <option value="">Tümü</option>
                    <option value="with_drivers">Şoförü Olan</option>
                    <option value="without_drivers">Şoförü Olmayan</option>
                </select>
            </div>
        `;
    }
    
    filterContent.innerHTML = filterHTML;
    
    // Tur filtresi için seçenekleri doldur
    if (section === 'tickets') {
        const tourSelect = document.getElementById('tourFilter');
        if (window.toursData && tourSelect) {
            window.toursData.forEach(tour => {
                const option = document.createElement('option');
                /* made by @hllgkx.0 */
                option.value = tour.id;
                option.textContent = tour.name;
                tourSelect.appendChild(option);
            });
        }
    }

    // Kaydedilen filtre değerlerini geri yükle
    restoreFilterValues(section);

    // Modalı göster
    $(modal).modal('show');

    // Mevcut filtreyi kaydet
    window.currentFilterSection = section;
}

// Filtreyi uygula
function applyFilter_DISABLED() {
    const section = window.currentFilterSection;

    if (section === 'tickets') {
        const tourFilter = document.getElementById('tourFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const nationalityFilter = document.getElementById('nationalityFilter').value;
        const activeStatusFilter = document.getElementById('activeStatusFilter').value;

        // Değerleri kaydet
        savedFilters.tickets.tour = tourFilter;
        savedFilters.tickets.date = dateFilter;
        savedFilters.tickets.status = statusFilter;
        /* made by @hllgkx.0 */
        savedFilters.tickets.nationality = nationalityFilter;
        savedFilters.tickets.activeStatus = activeStatusFilter;

        filterTickets(tourFilter, dateFilter, statusFilter, nationalityFilter, activeStatusFilter);
    } else if (section === 'vehicles') {
        const statusFilter = document.getElementById('vehicleStatusFilter').value;
        const capacityFilter = document.getElementById('capacityFilter').value;

        // Değerleri kaydet
        savedFilters.vehicles.status = statusFilter;
        savedFilters.vehicles.capacity = capacityFilter;

        filterVehicles(statusFilter, capacityFilter);
    } else if (section === 'drivers') {
        const statusFilter = document.getElementById('driverStatusFilter').value;
        const nationalityFilter = document.getElementById('driverNationalityFilter').value;

        // Değerleri kaydet
        savedFilters.drivers.status = statusFilter;
        savedFilters.drivers.nationality = nationalityFilter;

        filterDrivers(statusFilter, nationalityFilter);
    } else if (section === 'guides') {
        const nationalityFilter = document.getElementById('guideNationalityFilter').value;
        const driversFilter = document.getElementById('assignedDriversFilter').value;

        // Değerleri kaydet
        savedFilters.guides.nationality = nationalityFilter;
        savedFilters.guides.drivers = driversFilter;
        
        filterGuides(nationalityFilter, driversFilter);
    }

    // Modalı kapat
    $('#filterModal').modal('hide');
}

// Kaydedilen filtre değerlerini geri yükle
function restoreFilterValues(section) {
    if (section === 'tickets') {
        const tourFilter = document.getElementById('tourFilter');
        const dateFilter = document.getElementById('dateFilter');
        const statusFilter = document.getElementById('statusFilter');
        /* made by @hllgkx.0 */
        const nationalityFilter = document.getElementById('nationalityFilter');
        const activeStatusFilter = document.getElementById('activeStatusFilter');
        
        if (tourFilter) tourFilter.value = savedFilters.tickets.tour;
        if (dateFilter) dateFilter.value = savedFilters.tickets.date;
        if (statusFilter) statusFilter.value = savedFilters.tickets.status;
        if (nationalityFilter) nationalityFilter.value = savedFilters.tickets.nationality;
        if (activeStatusFilter) activeStatusFilter.value = savedFilters.tickets.activeStatus;
    } else if (section === 'vehicles') {
        const statusFilter = document.getElementById('vehicleStatusFilter');
        const capacityFilter = document.getElementById('capacityFilter');
        
        if (statusFilter) statusFilter.value = savedFilters.vehicles.status;
        if (capacityFilter) capacityFilter.value = savedFilters.vehicles.capacity;
    } else if (section === 'drivers') {
        const statusFilter = document.getElementById('driverStatusFilter');
        const nationalityFilter = document.getElementById('driverNationalityFilter');
        
        if (statusFilter) statusFilter.value = savedFilters.drivers.status;
        if (nationalityFilter) nationalityFilter.value = savedFilters.drivers.nationality;
    } else if (section === 'guides') {
        const nationalityFilter = document.getElementById('guideNationalityFilter');
        const driversFilter = document.getElementById('assignedDriversFilter');
        
        if (nationalityFilter) nationalityFilter.value = savedFilters.guides.nationality;
        if (driversFilter) driversFilter.value = savedFilters.guides.drivers;
    }
}

// Filtreyi temizle
function clearFilter() {
    const section = window.currentFilterSection;

    // Kaydedilen filtreleri temizle
    if (section === 'tickets') {
        savedFilters.tickets.tour = '';
        savedFilters.tickets.date = '';
        savedFilters.tickets.status = '';
        savedFilters.tickets.nationality = '';
        savedFilters.tickets.activeStatus = '';
    } else if (section === 'vehicles') {
        savedFilters.vehicles.status = '';
        savedFilters.vehicles.capacity = '';
    } else if (section === 'drivers') {
        savedFilters.drivers.status = '';
        savedFilters.drivers.nationality = '';
    } else if (section === 'guides') {
        savedFilters.guides.nationality = '';
        savedFilters.guides.drivers = '';
    }

    // Tüm öğeleri göster
    const sectionArea = document.getElementById(section + '-area');
    const rows = sectionArea.querySelectorAll('tr');
    rows.forEach(row => {
        row.style.display = '';
    });

    // Modalı kapat
    $('#filterModal').modal('hide');
}

// Biletleri filtrele
function filterTickets(tourFilter, dateFilter, statusFilter, nationalityFilter, activeStatusFilter) {
    console.log('=== filterTickets çağrıldı ===');
    console.log('Parametreler:', { tourFilter, dateFilter, statusFilter, nationalityFilter, activeStatusFilter });
    
    const ticketsArea = document.getElementById('tickets-area');
    if (!ticketsArea) {
        console.error('tickets-area bulunamadı!');
        return;
    }
    
    const rows = ticketsArea.querySelectorAll('tr');
    console.log('Toplam satır sayısı:', rows.length);

    let visibleCount = 0;
    let hiddenCount = 0;

    rows.forEach((row, index) => {
        let show = true;

        // Tur filtresini kontrol et
        if (tourFilter && row.dataset.tourId !== tourFilter) {
            console.log(`Satır ${index}: Tur filtresinde elendi (${row.dataset.tourId} !== ${tourFilter})`);
            show = false;
        }

        // Tarih filtresini kontrol et
        if (dateFilter && row.dataset.tourDate !== dateFilter) {
            console.log(`Satır ${index}: Tarih filtresinde elendi (${row.dataset.tourDate} !== ${dateFilter})`);
            show = false;
        }

        // Araç durumu filtresini kontrol et
        if (statusFilter) {
            const hasVehicle = row.dataset.hasVehicle === '1';
            if ((statusFilter === 'with-vehicle' && !hasVehicle) || (statusFilter === 'without-vehicle' && hasVehicle)) {
                console.log(`Satır ${index}: Araç durumu filtresinde elendi (${hasVehicle ? 'araçlı' : 'araçsız'} !== ${statusFilter})`);
                show = false;
            }
        }

        // Milliyet filtresini kontrol et
        if (nationalityFilter && row.dataset.nationality !== nationalityFilter) {
            console.log(`Satır ${index}: Milliyet filtresinde elendi (${row.dataset.nationality} !== ${nationalityFilter})`);
            show = false;
        }

        // Aktiflik durumu filtresini kontrol et
        if (activeStatusFilter) {
            const isActive = row.dataset.isActive === '1';
            if ((activeStatusFilter === 'active' && !isActive) || (activeStatusFilter === 'inactive' && isActive)) {
                console.log(`Satır ${index}: Aktiflik filtresinde elendi (${isActive ? 'aktif' : 'pasif'} !== ${activeStatusFilter})`);
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
        
        if (show) {
            visibleCount++;
        } else {
            hiddenCount++;
        }
    });
    
    console.log(`Filtre sonucu: ${visibleCount} görünür, ${hiddenCount} gizli`);
}

// Araçları filtrele
function filterVehicles(statusFilter, capacityFilter) {
    const rows = document.getElementById('vehicles-area').querySelectorAll('tr');

    rows.forEach(row => {
        let show = true;

        // Durum filtresini kontrol et
        if (statusFilter) {
            const hasDriver = row.dataset.hasDriver === '1';
            if ((statusFilter === 'available' && hasDriver) || (statusFilter === 'busy' && !hasDriver)) {
                show = false;
            }
        }

        // Kapasite filtresini kontrol et
        if (capacityFilter) {
            const capacity = parseInt(row.dataset.capacity) || 0;
            if ((capacityFilter === 'small' && capacity > 8) || 
                (capacityFilter === 'medium' && (capacity < 9 || capacity > 16)) || 
                (capacityFilter === 'large' && capacity < 17)) {
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
    });
}

// Şoförleri filtrele
function filterDrivers(statusFilter, nationalityFilter) {
    const rows = document.getElementById('drivers-area').querySelectorAll('tr');

    rows.forEach(row => {
        let show = true;

        // Durum filtresini kontrol et
        if (statusFilter) {
            const hasVehicle = row.dataset.hasVehicle === '1';
            if ((statusFilter === 'available' && hasVehicle) || (statusFilter === 'busy' && !hasVehicle)) {
                show = false;
            }
        }

        // Milliyet filtresini kontrol et
        if (nationalityFilter) {
            const supportedNationalities = JSON.parse(row.dataset.supportedNationalities || '[]');
            if (!supportedNationalities.includes(nationalityFilter)) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// Rehberleri filtrele
function filterGuides(nationalityFilter, driversFilter) {
    const rows = document.getElementById('guides-area').querySelectorAll('tr');
    
    rows.forEach(row => {
        let show = true;
        
        // Milliyet filtresini kontrol et
        if (nationalityFilter) {
            const supportedNationalities = JSON.parse(row.dataset.supportedNationalities || '[]');
            if (!supportedNationalities.includes(nationalityFilter)) {
                show = false;
            }
        }
        
        // Atanmış şoförler filtresini kontrol et
        if (driversFilter) {
            const driversCount = parseInt(row.dataset.driversCount) || 0;
            if ((driversFilter === 'with_drivers' && driversCount === 0) || 
                (driversFilter === 'without_drivers' && driversCount > 0)) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });
}
</script>
<!-- Operasyon Modal -->
<div class="modal fade" id="operationsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document" style="max-width: 96%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="operationsModalTitle">Operasyon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeOperationsModal()"></button>
      </div>
      <div class="modal-body" id="operations-board-slot">
        <!-- Tahta buraya taşınacak -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="closeOperationsModal()">Kapat</button>
      </div>
    </div>
  </div>
</div>
<!-- end of js -->
@stop 
<!-- end of code -->