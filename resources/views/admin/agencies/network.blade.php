@extends('layouts.admin')

@section('title', 'Acenta Ağı')
@section('page_title', 'Acenta Ağı')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h5 class="mb-1">Acenta Ağ Yönetimi</h5>
                    <small class="text-muted">
                        Kullanıcı ID'niz:
                        <code id="network-user-id">{{ $user->id }}</code>
                    </small>
                </div>
                <button class="btn btn-outline-primary btn-sm" id="copy-network-user-id" type="button">ID'yi Kopyala</button>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Acenta Arama ve İstek Gönder</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('agencies.requests.store') }}" method="POST" id="send-request-form">
                    @csrf
                    <div class="form-group position-relative">
                        <label for="target_user_id">Acenta ID / Ad / E-posta</label>
                        <input type="text"
                               name="target_user_id"
                               id="target_user_id"
                               class="form-control @error('target_user_id') is-invalid @enderror"
                               autocomplete="off"
                               placeholder="Arama yapın...">
                        @error('target_user_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Eşleşen kullanıcıyı listeden seçin.</small>
                        <div id="user-search-suggestions" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050; display:none;"></div>
                    </div>

                    <div id="selected-user-info" class="alert alert-success py-2 px-3" style="display:none;"></div>

                    <button type="submit" class="btn btn-primary w-100 w-100" id="send-request-button" disabled>
                        İstek Gönder
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Gönderilen Bekleyen İstekler</h3>
                <span class="badge badge-secondary">{{ $outgoingRequests->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($outgoingRequests as $requestItem)
                    <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2">
                        <div>
                            <div><strong>{{ $requestItem->target->name }}</strong></div>
                            <small class="text-muted">ID: {{ $requestItem->target->id }}</small>
                        </div>
                        <form action="{{ route('agencies.requests.withdraw', $requestItem) }}" method="POST" onsubmit="return confirm('Bu isteği geri çekmek istiyor musunuz?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" type="submit">Geri Çek</button>
                        </form>
                    </div>
                @empty
                    <div class="p-3 text-muted">Bekleyen gönderilmiş istek yok.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Gelen İstekler</h3>
                <span class="badge badge-primary">{{ $incomingRequests->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($incomingRequests as $requestItem)
                    <div class="d-flex justify-content-between align-items-center border-bottom px-3 py-2">
                        <div>
                            <div><strong>{{ $requestItem->requester->name }}</strong></div>
                            <small class="text-muted">ID: {{ $requestItem->requester->id }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('agencies.requests.accept', $requestItem) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm" type="submit">Kabul</button>
                            </form>
                            <form action="{{ route('agencies.requests.reject', $requestItem) }}" method="POST">
                                @csrf
                                <button class="btn btn-danger btn-sm" type="submit">Reddet</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-muted">Gelen bekleyen istek yok.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyBtn = document.getElementById('copy-network-user-id');
    const userIdElement = document.getElementById('network-user-id');
    const searchInput = document.getElementById('target_user_id');
    const suggestionBox = document.getElementById('user-search-suggestions');
    const selectedInfo = document.getElementById('selected-user-info');
    const sendButton = document.getElementById('send-request-button');

    if (copyBtn && userIdElement) {
        copyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText((userIdElement.textContent || '').trim()).then(() => {
                const original = copyBtn.textContent;
                copyBtn.textContent = 'Kopyalandi';
                setTimeout(() => { copyBtn.textContent = original; }, 1200);
            });
        });
    }

    function clearSelection() {
        selectedInfo.style.display = 'none';
        selectedInfo.innerHTML = '';
        sendButton.disabled = true;
    }

    function hideSuggestions() {
        suggestionBox.style.display = 'none';
        suggestionBox.innerHTML = '';
    }

    function showSuggestions(items) {
        if (!items.length) {
            hideSuggestions();
            return;
        }
        suggestionBox.style.display = 'block';
        suggestionBox.innerHTML = items.map((user) => {
            const agency = user.agency ? ' • ' + user.agency : '';
            return `
                <button type="button" class="list-group-item list-group-item-action js-suggestion"
                        data-user-id="${user.id}" data-user-name="${user.name}" data-user-email="${user.email}" data-user-agency="${user.agency || ''}">
                    <strong>ID: ${user.id}</strong> - ${user.name}
                    <div><small class="text-muted">${user.email}${agency}</small></div>
                </button>
            `;
        }).join('');
    }

    function selectUser(element) {
        const id = element.getAttribute('data-user-id');
        const name = element.getAttribute('data-user-name');
        const email = element.getAttribute('data-user-email');
        const agency = element.getAttribute('data-user-agency');
        searchInput.value = id || '';
        hideSuggestions();
        selectedInfo.style.display = 'block';
        selectedInfo.innerHTML = `<strong>${name}</strong> secildi. <small>${email}${agency ? ' • ' + agency : ''}</small>`;
        sendButton.disabled = false;
    }

    suggestionBox.addEventListener('click', function (event) {
        const item = event.target.closest('.js-suggestion');
        if (item) {
            selectUser(item);
        }
    });

    let timer = null;
    searchInput.addEventListener('input', function () {
        clearSelection();
        const query = (this.value || '').trim();
        if (!query) {
            hideSuggestions();
            return;
        }
        clearTimeout(timer);
        timer = setTimeout(() => {
            fetch('{{ route('agencies.users.search') }}?q=' + encodeURIComponent(query), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then((response) => response.ok ? response.json() : [])
            .then(showSuggestions)
            .catch(hideSuggestions);
        }, 220);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            hideSuggestions();
        }
    });
});
</script>
@endsection










