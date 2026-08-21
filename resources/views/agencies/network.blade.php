@extends('layouts.agency')

@section('title', 'Acenta Ağım')

@section('content')
<div class="ag-page-header">
    <h1 class="ag-page-title">
        <i data-lucide="network" style="width:28px;height:28px;color:var(--ag-accent)"></i>
        Acenta Ağım
    </h1>
    <p class="ag-page-subtitle">
        Kullanıcı ID'niz: <code style="background:var(--ag-accent);color:#fff;padding:2px 8px;border-radius:4px;font-weight:600" id="network-user-id">{{ $user->id }}</code> — bu ID ile diğer kullanıcılar size istek gönderebilir.
    </p>
</div>

<div class="ag-network-grid">
    <!-- Sol Kolon - Acenta Arama -->
    <div class="ag-card">
        <div class="ag-card-header">
            <h3 class="ag-card-title">
                <i data-lucide="search" style="width:18px;height:18px"></i>
                Acenta ID Arama
            </h3>
            <button class="ag-btn ag-btn-secondary ag-btn-sm" id="copy-network-user-id">
                <i data-lucide="copy" style="width:14px;height:14px"></i>
                <span>ID'yi Kopyala</span>
            </button>
        </div>
        <div class="ag-card-body">
            <form action="{{ route('agencies.requests.store') }}" method="POST" id="send-request-form">
                @csrf
                <div class="ag-form-group" style="position:relative">
                    <label for="target_user_id" class="ag-form-label">Acenta ID</label>
                    <input type="text"
                           name="target_user_id"
                           id="target_user_id"
                           class="ag-form-input @error('target_user_id') is-invalid @enderror"
                           autocomplete="off"
                           placeholder="ID numarası girin">
                    @error('target_user_id')
                        <span class="ag-form-error">{{ $message }}</span>
                    @enderror
                    <p class="ag-form-hint">ID yazmaya başladığınızda eşleşen kullanıcılar aşağıda listelenecek.</p>
                    <div id="user-search-suggestions" class="ag-search-suggestions"></div>
                </div>
                <div id="selected-user-info" class="ag-selected-user" style="display:none"></div>
                <button type="submit" class="ag-btn ag-btn-primary" style="width:100%" id="send-request-button" disabled>
                    <i data-lucide="send" style="width:16px;height:16px"></i>
                    <span>İstek Gönder</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Sağ Kolon - İstekler -->
    <div class="ag-requests-column">
        <!-- Gönderilen İstekler -->
        <div class="ag-card ag-mb-2">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="send" style="width:18px;height:18px"></i>
                    Gönderilen (Bekleyen) İstekler
                </h3>
                @if($outgoingRequests->count() > 0)
                    <span class="ag-badge ag-badge-secondary">{{ $outgoingRequests->count() }}</span>
                @endif
            </div>
            <div class="ag-card-body" style="padding:0">
                @forelse($outgoingRequests as $requestItem)
                    <div class="ag-request-item">
                        <div class="ag-request-info">
                            <div class="ag-request-name">{{ $requestItem->target->name }}</div>
                            <div class="ag-request-id">ID: {{ $requestItem->target->id }}</div>
                        </div>
                        <div class="ag-request-actions">
                            <span class="ag-badge ag-badge-warning">Bekleniyor</span>
                            <form action="{{ route('agencies.requests.withdraw', $requestItem) }}" method="POST" style="display:inline" onsubmit="return confirm('Bu isteği geri çekmek istediğinizden emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button class="ag-btn ag-btn-danger ag-btn-xs" type="submit" title="Geri Çek">
                                    <i data-lucide="undo-2" style="width:12px;height:12px"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="ag-empty" style="padding:32px">
                        <i data-lucide="send" class="ag-empty-icon" style="width:40px;height:40px"></i>
                        <p class="ag-empty-text ag-mb-0">Gönderdiğiniz bekleyen istek yok.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Gelen İstekler -->
        <div class="ag-card">
            <div class="ag-card-header">
                <h3 class="ag-card-title">
                    <i data-lucide="inbox" style="width:18px;height:18px"></i>
                    Gelen İstekler
                </h3>
                @if($incomingRequests->count() > 0)
                    <span class="ag-badge ag-badge-primary">{{ $incomingRequests->count() }}</span>
                @endif
            </div>
            <div class="ag-card-body" style="padding:0">
                @forelse($incomingRequests as $requestItem)
                    <div class="ag-request-item">
                        <div class="ag-request-info">
                            <div class="ag-request-name">{{ $requestItem->requester->name }}</div>
                            <div class="ag-request-id">ID: {{ $requestItem->requester->id }}</div>
                        </div>
                        <div class="ag-request-actions">
                            <form action="{{ route('agencies.requests.accept', $requestItem) }}" method="POST" style="display:inline">
                                @csrf
                                <button class="ag-btn ag-btn-success ag-btn-xs" type="submit" title="Kabul Et">
                                    <i data-lucide="check" style="width:12px;height:12px"></i>
                                </button>
                            </form>
                            <form action="{{ route('agencies.requests.reject', $requestItem) }}" method="POST" style="display:inline">
                                @csrf
                                <button class="ag-btn ag-btn-danger ag-btn-xs" type="submit" title="Reddet">
                                    <i data-lucide="x" style="width:12px;height:12px"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="ag-empty" style="padding:32px">
                        <i data-lucide="inbox" class="ag-empty-icon" style="width:40px;height:40px"></i>
                        <p class="ag-empty-text ag-mb-0">Gelen bekleyen isteğiniz yok.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .ag-page-header {
        margin-bottom: 24px;
    }
    
    .ag-page-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .ag-network-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    
    @media (max-width: 992px) {
        .ag-network-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .ag-requests-column {
        display: flex;
        flex-direction: column;
    }
    
    .ag-search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--ag-card);
        border: 1px solid var(--ag-border);
        border-radius: var(--ag-radius-sm);
        box-shadow: var(--ag-shadow-lg);
        z-index: 1000;
        max-height: 220px;
        overflow-y: auto;
        margin-top: 4px;
        display: none;
    }
    
    .ag-search-suggestions.show {
        display: block;
    }
    
    .ag-suggestion-item {
        padding: 12px 16px;
        border-bottom: 1px solid var(--ag-border);
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .ag-suggestion-item:last-child {
        border-bottom: none;
    }
    
    .ag-suggestion-item:hover {
        background: var(--ag-bg);
    }
    
    .ag-suggestion-item strong {
        color: var(--ag-text);
    }
    
    .ag-suggestion-item small {
        color: var(--ag-text-muted);
        display: block;
        margin-top: 4px;
    }
    
    .ag-selected-user {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid var(--ag-success);
        border-radius: var(--ag-radius-sm);
        padding: 12px 16px;
        margin-bottom: 16px;
        color: var(--ag-success);
    }
    
    .ag-selected-user .selected-name {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .ag-selected-user small {
        color: var(--ag-text-muted);
        display: block;
        margin-top: 4px;
    }
    
    .ag-request-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid var(--ag-border);
    }
    
    .ag-request-item:last-child {
        border-bottom: none;
    }
    
    .ag-request-info {
        flex: 1;
    }
    
    .ag-request-name {
        font-weight: 600;
        color: var(--ag-text);
        margin-bottom: 2px;
    }
    
    .ag-request-id {
        font-size: 12px;
        color: var(--ag-text-muted);
        font-family: monospace;
    }
    
    .ag-request-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .ag-form-input.is-invalid {
        border-color: var(--ag-danger);
    }
    
    .ag-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    #copy-network-user-id.copied {
        background: var(--ag-success);
        border-color: var(--ag-success);
        color: #fff;
    }
</style>
@endpush

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const copyBtn = document.getElementById('copy-network-user-id');
        const userIdElement = document.getElementById('network-user-id');
        const searchInput = document.getElementById('target_user_id');
        const suggestionBox = document.getElementById('user-search-suggestions');
        const selectedInfo = document.getElementById('selected-user-info');
        const sendButton = document.getElementById('send-request-button');

        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        if (copyBtn && userIdElement) {
            copyBtn.addEventListener('click', function () {
                navigator.clipboard.writeText(userIdElement.textContent.trim()).then(() => {
                    copyBtn.classList.add('copied');
                    copyBtn.innerHTML = '<i data-lucide="check" style="width:14px;height:14px"></i><span>Kopyalandı</span>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                    setTimeout(() => {
                        copyBtn.classList.remove('copied');
                        copyBtn.innerHTML = '<i data-lucide="copy" style="width:14px;height:14px"></i><span>ID\'yi Kopyala</span>';
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }, 2000);
                });
            });
        }

        function clearSelection() {
            selectedInfo.style.display = 'none';
            selectedInfo.innerHTML = '';
            sendButton.disabled = true;
        }

        function showSuggestions(items) {
            if (!items.length) {
                suggestionBox.classList.remove('show');
                suggestionBox.innerHTML = '';
                return;
            }
            suggestionBox.classList.add('show');
            suggestionBox.innerHTML = items.map(user => `
                <div class="ag-suggestion-item" data-user-id="${user.id}" data-user-name="${user.name}" data-user-email="${user.email}" data-user-agency="${user.agency ?? ''}">
                    <strong>ID: ${user.id}</strong> - ${user.name}
                    <small>${user.email}${user.agency ? ' • ' + user.agency : ''}</small>
                </div>
            `).join('');
        }

        function selectUser(element) {
            const id = element.getAttribute('data-user-id');
            const name = element.getAttribute('data-user-name');
            const email = element.getAttribute('data-user-email');
            const agency = element.getAttribute('data-user-agency');

            searchInput.value = id;
            suggestionBox.classList.remove('show');
            suggestionBox.innerHTML = '';
            selectedInfo.style.display = 'block';
            selectedInfo.innerHTML = `
                <div class="selected-name"><i data-lucide="check-circle" style="width:16px;height:16px"></i> ${name} seçildi.</div>
                <small>${email}${agency ? ' • ' + agency : ''}</small>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            sendButton.disabled = false;
        }

        if (suggestionBox) {
            suggestionBox.addEventListener('click', function (event) {
                const item = event.target.closest('.ag-suggestion-item');
                if (item) {
                    selectUser(item);
                }
            });
        }

        if (searchInput) {
            let typingTimer;
            searchInput.addEventListener('input', function () {
                clearSelection();
                const query = this.value.trim();
                if (!query.length) {
                    suggestionBox.classList.remove('show');
                    suggestionBox.innerHTML = '';
                    return;
                }

                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    fetch('{{ route('agencies.users.search') }}?q=' + encodeURIComponent(query), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('No results');
                            }
                            return response.json();
                        })
                        .then(showSuggestions)
                        .catch(() => {
                            suggestionBox.classList.remove('show');
                            suggestionBox.innerHTML = '';
                        });
                }, 250);
            });
        }

        // Close suggestions on outside click
        document.addEventListener('click', function(e) {
            if (!searchInput?.contains(e.target) && !suggestionBox?.contains(e.target)) {
                suggestionBox?.classList.remove('show');
            }
        });
    });
</script>
@stop
