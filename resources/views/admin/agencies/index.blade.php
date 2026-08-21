@extends('layouts.admin')

@section('title', 'Acenta Yönetimi')

@section('content')
@php($currentUser = auth()->user())
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Modern Kontrol Paneli -->
            <div class="guides-control-panel mb-2">
                <div class="control-left">
                    <h4 class="control-title"><i class="fas fa-building"></i> Acenta Yönetimi</h4>
                    <p class="control-subtitle">ID: <code id="current-user-id" style="background:#fff;padding:2px 6px;border-radius:4px;color:#333;">{{ $currentUser?->id }}</code></p>
                </div>
                <div class="control-right">
                    <div class="control-item">
                        <button type="button" class="btn btn-sm btn-light" id="copy-user-id">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="control-item">
                        <a href="{{ route('admin.agencies.network') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-handshake"></i> Acenta Ağım
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Acenta Adı</th>
                                <th>Kullanıcı</th>
                                <th>İletişim Kişisi</th>
                                <th>Email</th>
                                <th>Telefon</th>
                                <th>Komisyon Oranı</th>
                                <th>Bilet Sayısı</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                            <!-- made by @hllgkx.0 -->
                        </thead>
                        <tbody>
                            @forelse($agencies as $agency)
                                <tr>
                                    <td>
                                        @if($agency->id)
                                            {{ $agency->id }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $agency->name }}</strong>
                                        @if($agency->website)
                                            <br><a href="{{ $agency->website }}" target="_blank" class="text-muted">
                                                <i class="fas fa-external-link-alt"></i> Website
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($agency->user)
                                            <div><strong>{{ $agency->user->name }}</strong></div>
                                            <small class="text-muted">
                                                ID: <code>{{ $agency->user->id }}</code><br>
                                                {{ $agency->user->email }}
                                            </small>
                                        @else
                                            <span class="badge badge-light">Eşleşmemiş Kullanıcı</span>
                                        @endif
                                    </td>
                                    <td>{{ $agency->contact_person ?? '-' }}</td>
                                    <td>
                                        @if($agency->email)
                                            <a href="mailto:{{ $agency->email }}">{{ $agency->email }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($agency->phone)
                                            <a href="tel:{{ $agency->phone }}">{{ $agency->phone }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!is_null($agency->commission_rate))
                                            <span class="badge badge-info">
                                                {{ $agency->formatted_commission_rate }}
                                            </span>
                                        @else
                                            <span class="badge badge-light">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $agency->tickets_count ?? 0 }} bilet
                                        </span>
                                    </td>
                                    <td>
                                        @if($agency->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Pasif</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()?->isAdmin())
                                        <td>
                                            @if($agency->id)
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.agencies.show', $agency) }}"
                                                       class="btn btn-info btn-sm"
                                                       title="Görüntüle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.agencies.edit', $agency) }}"
                                                       class="btn btn-warning btn-sm"
                                                       title="Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($agency->user_id)
                                                        <a href="{{ route('admin.accounting.index', ['locked_agency_id' => $agency->user_id]) }}"
                                                           class="btn btn-success btn-sm"
                                                           title="Muhasebe">
                                                            <i class="fas fa-wallet"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('admin.agencies.destroy', $agency) }}"
                                                          method="POST"
                                                          style="display: inline-block;"
                                                          onsubmit="return confirm('Bu acentayı silmek istediğinizden emin misiniz?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-danger btn-sm"
                                                                title="Sil">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="badge badge-light">Bağlı kullanıcı</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Henüz bağlantılı acenta yok.</h5>
                                        <p class="text-muted mb-3">Acenta Ağım üzerinden kullanıcı ID'si ile istek göndererek listeye ekleyebilirsiniz.</p>
                                        <a href="{{ route('admin.agencies.network') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-handshake mr-1"></i> Acenta Ağım
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($agencies->hasPages())
                    <div class="card-footer">
                        {{ $agencies->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
<!-- css -->
@push('css')
<style>
    .table td {
        vertical-align: middle;
    }
    .alert-info code {
        font-size: 1rem;
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyButton = document.getElementById('copy-user-id');
        const userIdElement = document.getElementById('current-user-id');

        if (copyButton && userIdElement) {
            copyButton.addEventListener('click', function () {
                const idValue = userIdElement.textContent.trim();
                navigator.clipboard.writeText(idValue).then(function () {
                    copyButton.innerHTML = '<i class="fas fa-check mr-1"></i> Kopyalandı';
                    setTimeout(function () {
                        copyButton.innerHTML = '<i class="fas fa-copy mr-1"></i> ID\'yi Kopyala';
                    }, 2000);
                }).catch(function () {
                    alert('ID kopyalanamadı, lütfen manuel kopyalayın.');
                });
            });
        }
    });
</script>
@endpush
<!-- end of the code -->