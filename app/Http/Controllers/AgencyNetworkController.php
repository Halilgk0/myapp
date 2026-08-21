<?php

namespace App\Http\Controllers;

use App\Models\AgencyConnectionRequest;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgencyNetworkController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $incomingRequests = $user->receivedAgencyRequests()
            ->pending()
            ->with('requester.agency')
            ->orderByDesc('created_at')
            ->get();

        $outgoingRequests = $user->sentAgencyRequests()
            ->pending()
            ->with('target.agency')
            ->orderByDesc('created_at')
            ->get();

        $ownedAgency = $user->agency()
            ->withCount('tickets')
            ->first();

        $view = request()->routeIs('admin.*')
            ? 'admin.agencies.network'
            : 'agencies.network';

        return view($view, [
            'user' => $user,
            'incomingRequests' => $incomingRequests,
            'outgoingRequests' => $outgoingRequests,
            'ownedAgency' => $ownedAgency,
        ]);
    }

    public function storeRequest(Request $request)
    {
        $data = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'target_user_id.required' => 'Lütfen bağlantı kurmak istediğiniz kullanıcı ID\'sini girin.',
        ]);

        $targetId = (int) $data['target_user_id'];
        $currentUser = Auth::user();

        if ($targetId === $currentUser->id) {
            return back()->with('error', 'Kendinize istek gönderemezsiniz.');
        }

        $existing = AgencyConnectionRequest::betweenUsers($currentUser->id, $targetId)->first();

        if ($existing) {
            if ($existing->status === AgencyConnectionRequest::STATUS_PENDING) {
                return back()->with('error', 'Bu kullanıcıyla zaten bekleyen bir isteğiniz var.');
            }

            if ($existing->status === AgencyConnectionRequest::STATUS_ACCEPTED) {
                return back()->with('error', 'Bu kullanıcıyla zaten bağlantınız bulunuyor.');
            }

            // Reddedilmiş/geçmiş bir kayıt varsa aynı pair_key üzerinde tekrar beklemeye al
            $existing->update([
                'requester_id' => $currentUser->id,
                'target_id' => $targetId,
                'status' => AgencyConnectionRequest::STATUS_PENDING,
                'responded_at' => null,
                'pair_key' => AgencyConnectionRequest::generatePairKey($currentUser->id, $targetId),
            ]);

            return back()->with('success', 'İstek yeniden gönderildi. Karşı taraf onayladığında bağlantı listesine ekleneceksiniz.');
        }

        AgencyConnectionRequest::create([
            'requester_id' => $currentUser->id,
            'target_id' => $targetId,
            'status' => AgencyConnectionRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'İstek başarıyla gönderildi. Karşı taraf onayladığında bağlantı listesine ekleneceksiniz.');
    }

    public function accept(AgencyConnectionRequest $agencyConnectionRequest)
    {
        $this->authorizeRequestResponse($agencyConnectionRequest);

        if ($agencyConnectionRequest->status !== AgencyConnectionRequest::STATUS_PENDING) {
            return back()->with('error', 'Bu isteğin durumu zaten güncellenmiş.');
        }

        $agencyConnectionRequest->update([
            'status' => AgencyConnectionRequest::STATUS_ACCEPTED,
            'responded_at' => now(),
        ]);

        $this->ensureAgencyRecord($agencyConnectionRequest->requester_id);
        $this->ensureAgencyRecord($agencyConnectionRequest->target_id);

        $this->autoShareToursBetweenUsers($agencyConnectionRequest->requester_id, $agencyConnectionRequest->target_id);

        return back()->with('success', 'İstek kabul edildi. Artık birbirinizi liste üzerinde görebilirsiniz.');
    }

    public function reject(AgencyConnectionRequest $agencyConnectionRequest)
    {
        $this->authorizeRequestResponse($agencyConnectionRequest);

        if ($agencyConnectionRequest->status !== AgencyConnectionRequest::STATUS_PENDING) {
            return back()->with('error', 'Bu isteğin durumu zaten güncellenmiş.');
        }

        $agencyConnectionRequest->update([
            'status' => AgencyConnectionRequest::STATUS_REJECTED,
            'responded_at' => now(),
        ]);

        return back()->with('success', 'İstek reddedildi.');
    }

    public function lookupUser(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::with('agency')
            ->select('id', 'name', 'email', 'level', 'is_active')
            ->find($request->integer('id'));

        if (!$user) {
            return response()->json(['message' => 'Kullanıcı bulunamadı.'], 404);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'level_label' => $user->level_label,
            'is_active' => (bool) $user->is_active,
            'agency' => $user->agency ? [
                'name' => $user->agency->name,
                'id' => $user->agency->id,
            ] : null,
        ];
    }

    public function searchUsers(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);

        $term = trim((string) $request->input('q'));

        $users = User::with('agency')
            ->where('id', '!=', Auth::id())
            ->where(function ($query) use ($term) {
                $query->whereRaw('CAST(id AS CHAR) LIKE ?', ['%' . $term . '%'])
                    ->orWhere('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhereHas('agency', function ($agencyQuery) use ($term) {
                        $agencyQuery->where('name', 'like', '%' . $term . '%');
                    });
            })
            ->orderBy('id')
            ->limit(10)
            ->get();

        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'agency' => optional($user->agency)->name,
            ];
        });
    }

    public function withdraw(AgencyConnectionRequest $agencyConnectionRequest)
    {
        if ($agencyConnectionRequest->requester_id !== Auth::id()) {
            abort(403, 'Bu isteği geri çekme yetkiniz yok.');
        }

        if ($agencyConnectionRequest->status !== AgencyConnectionRequest::STATUS_PENDING) {
            return back()->with('error', 'Yalnızca bekleyen istekler geri çekilebilir.');
        }

        $agencyConnectionRequest->delete();

        return back()->with('success', 'İstek başarıyla geri çekildi.');
    }

    protected function authorizeRequestResponse(AgencyConnectionRequest $request): void
    {
        if ($request->target_id !== Auth::id()) {
            abort(403, 'Bu isteği yönetme yetkiniz yok.');
        }
    }

    /**
     * If any tour owners have auto-share enabled, share those tours with the newly connected user.
     */
    protected function autoShareToursBetweenUsers(int $userA, int $userB): void
    {
        $now = now();

        $pairs = [
            ['owner_id' => $userA, 'partner_id' => $userB],
            ['owner_id' => $userB, 'partner_id' => $userA],
        ];

        foreach ($pairs as $pair) {
            if ($pair['owner_id'] === $pair['partner_id']) {
                continue;
            }

            $tours = Tour::where('owner_id', $pair['owner_id'])
                ->where('auto_share_on_connect', true)
                ->pluck('id');

            if ($tours->isEmpty()) {
                continue;
            }

            $rows = $tours->map(function ($tourId) use ($pair, $now) {
                return [
                    'tour_id' => $tourId,
                    'shared_by_user_id' => $pair['owner_id'],
                    'shared_with_user_id' => $pair['partner_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            DB::table('tour_shared_users')->upsert(
                $rows,
                ['tour_id', 'shared_with_user_id'],
                ['updated_at']
            );
        }
    }

    protected function ensureAgencyRecord(int $userId): void
    {
        $user = User::withTrashed()->find($userId);
        if (!$user || $user->level !== User::LEVEL_AGENCY) {
            return;
        }

        $agency = \App\Models\Agency::withTrashed()->where('user_id', $userId)->first();
        if ($agency) {
            if ($agency->trashed()) {
                $agency->restore();
            }
            return;
        }

        \App\Models\Agency::create([
            'user_id' => $userId,
            'name' => $user->name ?? ('Sokak Acentası #' . $userId),
            'email' => $user->email,
            'phone' => $user->phone_number,
            'contact_person' => $user->name,
            'commission_rate' => 0,
            'is_active' => $user->is_active ?? true,
        ]);
    }
}


