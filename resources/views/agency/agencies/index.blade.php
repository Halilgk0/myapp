@extends('layouts.agency')

@section('title', 'Acenta Yönetimi')

@section('content')
<div class="ag-page-header ag-flex ag-justify-between ag-items-center" style="flex-wrap:wrap;gap:16px">
    <div>
        <h1 class="ag-page-title">Acenta Yönetimi</h1>
        <p class="ag-page-subtitle">Bağlantılı acentalarınızı görüntüleyin</p>
    </div>
    <a href="{{ route('agencies.network') }}" class="ag-btn ag-btn-primary">
        <i data-lucide="users"></i>
        <span>Acenta Ağım</span>
    </a>
</div>

<!-- User ID Card -->
<div class="ag-card ag-mb-3" style="background: linear-gradient(135deg, var(--ag-sidebar) 0%, #1e3a5f 100%); border:none;">
    <div class="ag-card-body ag-flex ag-justify-between ag-items-center" style="flex-wrap:wrap;gap:16px">
        <div>
            <div style="color:rgba(255,255,255,0.7);font-size:12px;margin-bottom:4px">Kullanıcı ID'niz</div>
            <div class="ag-flex ag-items-center ag-gap-2">
                <code id="current-user-id" style="background:rgba(255,255,255,0.1);padding:8px 16px;border-radius:8px;font-size:18px;font-weight:700;color:#fff">{{ $currentUser->id }}</code>
                <button type="button" class="ag-btn ag-btn-sm" id="copy-user-id" style="background:rgba(255,255,255,0.15);color:#fff;border:none">
                    <i data-lucide="copy"></i>
                    <span>Kopyala</span>
                </button>
            </div>
            <div style="color:rgba(255,255,255,0.5);font-size:12px;margin-top:8px">Bu ID ile diğer acentalar sizi ekleyebilir</div>
        </div>
    </div>
</div>

<!-- Connected Agencies -->
<div class="ag-card">
    <div class="ag-card-header">
        <h3 class="ag-card-title">
            <i data-lucide="link"></i>
            Bağlantılı Acentalar
            <span class="ag-badge ag-badge-primary" style="margin-left:8px">{{ count($agencies) }}</span>
        </h3>
    </div>
    <div class="ag-card-body" style="padding:0">
        <div class="ag-table-wrapper">
            <table class="ag-table">
                <thead>
                    <tr>
                        <th>Acenta</th>
                        <th>İletişim</th>
                        <th>Toplam Tur</th>
                        <th>Paylaşılan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agencies as $agency)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $agency->name }}</div>
                            @if($agency->user)
                            <div class="ag-text-muted" style="font-size:12px">
                                {{ $agency->user->name }}
                                <span style="opacity:0.5">•</span>
                                ID: <code style="font-size:11px">{{ $agency->user->id }}</code>
                            </div>
                            @endif
                            @if($agency->is_virtual ?? false)
                            <span class="ag-badge ag-badge-secondary" style="margin-top:4px;font-size:10px">Kullanıcı</span>
                            @endif
                        </td>
                        <td>
                            @if($agency->email)
                            <div style="font-size:13px">
                                <a href="mailto:{{ $agency->email }}" style="color:var(--ag-accent);text-decoration:none">{{ $agency->email }}</a>
                            </div>
                            @endif
                            @if($agency->phone)
                            <div class="ag-text-muted" style="font-size:12px">{{ $agency->phone }}</div>
                            @endif
                            @if(!$agency->email && !$agency->phone)
                            <span class="ag-text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="ag-badge ag-badge-secondary">{{ $agency->tours_count ?? 0 }} tur</span>
                        </td>
                        <td>
                            @if(($agency->shared_tour_count ?? 0) > 0)
                            <span class="ag-badge ag-badge-success">{{ $agency->shared_tour_count }} tur</span>
                            @else
                            <span class="ag-text-muted" style="font-size:12px">Paylaşım yok</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="ag-empty">
                                <i data-lucide="users" class="ag-empty-icon"></i>
                                <div class="ag-empty-title">Henüz bağlantılı acenta yok</div>
                                <p class="ag-empty-text">Acenta Ağım bölümünden kullanıcı ID'siyle bağlantı kurabilirsiniz.</p>
                                <a href="{{ route('agencies.network') }}" class="ag-btn ag-btn-primary">
                                    <i data-lucide="user-plus"></i>
                                    <span>Bağlantı Kur</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($agencies->hasPages())
    <div class="ag-card-footer ag-flex ag-justify-between ag-items-center">
        <span class="ag-text-muted" style="font-size:13px">
            {{ $agencies->firstItem() }}-{{ $agencies->lastItem() }} / {{ $agencies->total() }} kayıt
        </span>
        <div class="ag-pagination">
            @if($agencies->onFirstPage())
            <span class="ag-pagination-btn" style="opacity:0.5"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></span>
            @else
            <a href="{{ $agencies->previousPageUrl() }}" class="ag-pagination-btn"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></a>
            @endif

            @foreach($agencies->getUrlRange(max(1, $agencies->currentPage() - 2), min($agencies->lastPage(), $agencies->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}" class="ag-pagination-btn {{ $page == $agencies->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($agencies->hasMorePages())
            <a href="{{ $agencies->nextPageUrl() }}" class="ag-pagination-btn"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></a>
            @else
            <span class="ag-pagination-btn" style="opacity:0.5"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('js')
<script>
    lucide.createIcons();
    
    document.addEventListener('DOMContentLoaded', function () {
        var copyButton = document.getElementById('copy-user-id');
        var userIdElement = document.getElementById('current-user-id');

        if (copyButton && userIdElement) {
            copyButton.addEventListener('click', function () {
                var idValue = userIdElement.textContent.trim();
                navigator.clipboard.writeText(idValue).then(function () {
                    copyButton.innerHTML = '<i data-lucide="check"></i><span>Kopyalandı</span>';
                    lucide.createIcons();
                    setTimeout(function () {
                        copyButton.innerHTML = '<i data-lucide="copy"></i><span>Kopyala</span>';
                        lucide.createIcons();
                    }, 2000);
                }).catch(function () {
                    alert('ID kopyalanamadı, lütfen manuel kopyalayın.');
                });
            });
        }
    });
</script>
@endpush
