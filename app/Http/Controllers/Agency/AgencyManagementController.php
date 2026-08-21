<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Concerns\HandlesAgencyVisibility;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AgencyManagementController extends Controller
{
    use HandlesAgencyVisibility;

    public function index()
    {
        $user = auth()->user();

        $connectedUserIds = $this->getConnectedUserIds($user->id, false);

        $agencies = Agency::with('user')
            ->withCount([
                'tickets',
                'ownedTours as tours_count'
            ])
            ->whereIn('user_id', $connectedUserIds)
            ->get();

        $missingUserIds = array_diff($connectedUserIds, $agencies->pluck('user_id')->all());

        $virtualAgencies = User::whereIn('id', $missingUserIds)->get()->map(function ($connectedUser) {
            $agency = new Agency();
            $agency->id = null;
            $agency->name = $connectedUser->name ?? ('Kullanıcı #' . $connectedUser->id);
            $agency->contact_person = $connectedUser->name;
            $agency->email = $connectedUser->email;
            $agency->phone = $connectedUser->phone_number;
            $agency->commission_rate = null;
            $agency->is_active = $connectedUser->is_active;
            $agency->tickets_count = 0;
            $agency->tours_count = Tour::where('owner_id', $connectedUser->id)->count();
            $agency->setRelation('user', $connectedUser);
            $agency->is_virtual = true;
            return $agency;
        });

        $merged = $agencies->concat($virtualAgencies)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $sharedCounts = DB::table('tour_shared_users')
            ->select('shared_by_user_id', DB::raw('count(*) as total'))
            ->where('shared_with_user_id', $user->id)
            ->groupBy('shared_by_user_id')
            ->pluck('total', 'shared_by_user_id');

        $merged = $merged->map(function (Agency $agency) use ($sharedCounts) {
            $connectedUserId = $agency->user?->id;
            $agency->shared_tour_count = $connectedUserId
                ? ($sharedCounts[$connectedUserId] ?? 0)
                : 0;

            return $agency;
        });

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $results = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $results,
            $merged->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('agency.agencies.index', [
            'agencies' => $paginated,
            'currentUser' => $user,
        ]);
    }
}

