<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesAgencyVisibility;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Ticket;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgencyController extends Controller
{
    use HandlesAgencyVisibility;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            $agencies = new LengthAwarePaginator([], 0, 15);
            return view('admin.agencies.index', compact('agencies'));
        }

        if ($user->isAdmin()) {
            $connectedUserIds = $this->getConnectedUserIds($user->id, false);

            $agencies = Agency::with(['user'])
                ->withCount('tickets')
                ->whereIn('user_id', $connectedUserIds)
                ->orderBy('name')
                ->get();

            $missingUserIds = array_diff($connectedUserIds, $agencies->pluck('user_id')->all());

            $virtualAgencies = User::whereIn('id', $missingUserIds)
                ->where('level', User::LEVEL_AGENCY)
                ->get()
                ->map(function (User $connectedUser) {
                    $agency = new Agency();
                    $agency->id = null;
                    $agency->name = $connectedUser->name ?? ('Kullanıcı #' . $connectedUser->id);
                    $agency->contact_person = $connectedUser->name;
                    $agency->email = $connectedUser->email;
                    $agency->phone = $connectedUser->phone_number;
                    $agency->commission_rate = null;
                    $agency->is_active = $connectedUser->is_active;
                    $agency->tickets_count = Ticket::where('created_by_user_id', $connectedUser->id)->count();
                    $agency->setRelation('user', $connectedUser);
                    $agency->is_virtual = true;
                    return $agency;
                });

            $merged = $agencies->concat($virtualAgencies)
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            $perPage = 15;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $results = $merged->slice(($page - 1) * $perPage, $perPage)->values();

            $agencies = new LengthAwarePaginator(
                $results,
                $merged->count(),
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        } else {
            $agencies = Agency::with(['user'])
                ->withCount('tickets')
                ->where('user_id', $user->id)
                ->orderBy('name')
                ->paginate(15);
        }

        return view('admin.agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.agencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('agencies')->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $validated = $validator->validated();

        try {
            $userId = $validated['user_id'];
            $trashed = Agency::withTrashed()->where('user_id', $userId)->first();
            if ($trashed && $trashed->trashed()) {
                Agency::detachUserRelationships($userId);
                $trashed->forceDelete();
            }

            Agency::create($validated);
            
            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Acenta başarıyla oluşturuldu.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Acenta oluşturulurken bir hata oluştu.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Agency $agency)
    {
        $agency->load([
            'user',
            'tickets' => function ($query) {
                $query->latest()->take(10);
            }
        ]);

        $authUser = auth()->user();
        $canManageTourSharing = false;
        $ownedTours = collect();
        $sharedTourIds = [];
        $sharedTourMeta = collect();

        if ($authUser && $authUser->isAdmin() && $agency->user_id) {
            $connectedUserIds = $this->getConnectedUserIds($authUser->id);
            $canManageTourSharing = in_array($agency->user_id, $connectedUserIds, true);

            if ($canManageTourSharing) {
                $pivotRows = DB::table('tour_shared_users')
                    ->where('shared_by_user_id', $authUser->id)
                    ->where('shared_with_user_id', $agency->user_id)
                    ->get();

                $sharedTourIds = $pivotRows->pluck('tour_id')->toArray();

                // Tüm turları al
                $allTours = Tour::withCount('tickets')
                    ->where('owner_id', $authUser->id)
                    ->get();

                // Seçili turlar (paylaşılan) önce, sonra bilet sayısına göre seçilmemişler
                $sharedTours = $allTours->filter(fn($t) => in_array($t->id, $sharedTourIds, true))
                    ->sortByDesc('tickets_count');
                $unsharedTours = $allTours->filter(fn($t) => !in_array($t->id, $sharedTourIds, true))
                    ->sortByDesc('tickets_count');

                // Birleştir: önce seçililer, sonra bilete göre sıralı seçilmemişler
                $ownedTours = $sharedTours->concat($unsharedTours)->values();

                $sharedTourMeta = $pivotRows->mapWithKeys(function ($row) {
                    $datePrices = $row->custom_date_prices ? json_decode($row->custom_date_prices, true) : [];
                    $customMax = \App\Models\Tour::maxPriceFromPayload($datePrices);

                    return [
                        $row->tour_id => [
                            'custom_date_prices' => $datePrices,
                            'custom_currency' => $row->custom_currency,
                            'custom_max_price' => $customMax,
                            'has_custom_price' => !empty($datePrices),
                            'raw_custom_date_prices' => $row->custom_date_prices,
                            'raw_custom_base_prices' => $row->custom_base_prices,
                            'raw_custom_monthly_prices' => $row->custom_monthly_prices,
                        ],
                    ];
                });
            }
        }
        
        return view('admin.agencies.show', compact(
            'agency',
            'ownedTours',
            'sharedTourIds',
            'canManageTourSharing',
            'sharedTourMeta'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agency $agency)
    {
        return view('admin.agencies.edit', compact('agency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agency $agency)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('agencies')->ignore($agency->id)->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            $agency->update($validator->validated());
            
            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Acenta başarıyla güncellendi.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Acenta güncellenirken bir hata oluştu.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agency $agency)
    {
        try {
            $userId = $agency->user_id;
            if (method_exists($agency, 'forceDelete')) {
                $agency->forceDelete();
            } else {
                $agency->delete();
            }
            Agency::detachUserRelationships($userId);

            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Acenta başarıyla silindi.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Acenta silinirken bir hata oluştu.');
        }
    }

    public function updateTourSharing(Request $request, Agency $agency)
    {
        $authUser = auth()->user();

        if (!$authUser || !$authUser->isAdmin() || !$agency->user_id) {
            abort(403);
        }

        $connectedUserIds = $this->getConnectedUserIds($authUser->id, false);
        if (!in_array($agency->user_id, $connectedUserIds, true)) {
            return redirect()->back()->with('error', 'Önce bu acenta ile bağlantı kurmanız gerekir.');
        }

        $data = $request->validate([
            'tour_ids' => ['nullable', 'array'],
            'tour_ids.*' => [
                'integer',
                Rule::exists('tours', 'id')->where(function ($query) use ($authUser) {
                    $query->where('owner_id', $authUser->id);
                }),
            ],
        ]);

        $tourIds = $data['tour_ids'] ?? [];

        DB::transaction(function () use ($authUser, $agency, $tourIds) {
            $existing = DB::table('tour_shared_users')
                ->where('shared_by_user_id', $authUser->id)
                ->where('shared_with_user_id', $agency->user_id)
                ->get()
                ->keyBy('tour_id');

            $incoming = collect($tourIds);
            $toDelete = $existing->keys()->diff($incoming);
            $toInsert = $incoming->diff($existing->keys());
            $toKeep = $incoming->intersect($existing->keys());

            if ($toDelete->isNotEmpty()) {
                DB::table('tour_shared_users')
                    ->where('shared_by_user_id', $authUser->id)
                    ->where('shared_with_user_id', $agency->user_id)
                    ->whereIn('tour_id', $toDelete->all())
                    ->delete();
            }

            if ($toInsert->isNotEmpty()) {
                $now = now();
                $insertRows = $toInsert->map(function ($tourId) use ($authUser, $agency, $now) {
                    return [
                        'tour_id' => $tourId,
                        'shared_by_user_id' => $authUser->id,
                        'shared_with_user_id' => $agency->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->values()->all();

                DB::table('tour_shared_users')->insert($insertRows);
            }

            if ($toKeep->isNotEmpty()) {
                DB::table('tour_shared_users')
                    ->where('shared_by_user_id', $authUser->id)
                    ->where('shared_with_user_id', $agency->user_id)
                    ->whereIn('tour_id', $toKeep->all())
                    ->update(['updated_at' => now()]);
            }
        });

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', 'Tur paylaşımı başarıyla güncellendi.');
    }

    public function showTourPricing(Agency $agency, Tour $tour)
    {
        $authUser = auth()->user();

        if (
            !$authUser ||
            !$authUser->isAdmin() ||
            !$agency->user_id ||
            (int) $tour->owner_id !== (int) $authUser->id
        ) {
            abort(403);
        }

        $connectedUserIds = $this->getConnectedUserIds($authUser->id, false);
        if (!in_array($agency->user_id, $connectedUserIds, true)) {
            abort(403, 'Önce bu acenta ile bağlantı kurmanız gerekir.');
        }

        $pivot = DB::table('tour_shared_users')
            ->where('tour_id', $tour->id)
            ->where('shared_with_user_id', $agency->user_id)
            ->where('shared_by_user_id', $authUser->id)
            ->first();

        $customDatePrices = $pivot && $pivot->custom_date_prices
            ? json_decode($pivot->custom_date_prices, true)
            : [];
        $customCurrency = $pivot && $pivot->custom_currency
            ? $pivot->custom_currency
            : $tour->currency;

        $response = [
            'tour' => [
                'id' => $tour->id,
                'name' => $tour->name,
                'currency' => $tour->currency,
                'max_price' => $tour->max_display_price,
            ],
            'agency' => [
                'id' => $agency->id,
                'name' => $agency->name,
                'user_id' => $agency->user_id,
            ],
            'custom_pricing' => [
                'date_prices' => $customDatePrices,
                'currency' => $customCurrency,
                'has_custom_price' => !empty($customDatePrices),
                'max_custom_price' => \App\Models\Tour::maxPriceFromPayload($customDatePrices),
                'is_shared' => (bool) $pivot,
            ],
        ];

        return response()->json($response);
    }

    public function updateTourPricing(Request $request, Agency $agency, Tour $tour)
    {
        $authUser = auth()->user();

        if (
            !$authUser ||
            !$authUser->isAdmin() ||
            !$agency->user_id ||
            (int) $tour->owner_id !== (int) $authUser->id
        ) {
            abort(403);
        }

        $connectedUserIds = $this->getConnectedUserIds($authUser->id, false);
        if (!in_array($agency->user_id, $connectedUserIds, true)) {
            abort(403, 'Önce bu acenta ile bağlantı kurmanız gerekir.');
        }

        $validated = $request->validate([
            'selected_prices' => 'nullable|string',
            'custom_currency' => 'nullable|string|max:3',
            'clear' => 'nullable|boolean',
        ]);

        $customPrices = null;
        if (!$request->boolean('clear') && !empty($validated['selected_prices'])) {
            $decoded = json_decode($validated['selected_prices'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $customPrices = $decoded;
            } else {
                return response()->json([
                    'message' => 'Geçersiz fiyat verisi gönderildi.',
                ], 422);
            }
        }

        $payload = [
            'shared_by_user_id' => $authUser->id,
            'custom_date_prices' => $customPrices ? json_encode($customPrices) : null,
            'custom_currency' => $validated['custom_currency'] ?? $tour->currency,
            'custom_monthly_prices' => null,
            'custom_base_prices' => null,
            'updated_at' => now(),
        ];

        $pivot = DB::table('tour_shared_users')
            ->where('tour_id', $tour->id)
            ->where('shared_with_user_id', $agency->user_id)
            ->where('shared_by_user_id', $authUser->id)
            ->first();

        if ($pivot) {
            DB::table('tour_shared_users')
                ->where('id', $pivot->id)
                ->update($payload);
        } else {
            DB::table('tour_shared_users')->insert(array_merge($payload, [
                'tour_id' => $tour->id,
                'shared_with_user_id' => $agency->user_id,
                'created_at' => now(),
            ]));
        }

        $maxCustomPrice = \App\Models\Tour::maxPriceFromPayload($customPrices);

        return response()->json([
            'success' => true,
            'custom_price' => [
                'has_custom_price' => !empty($customPrices),
                'max_custom_price' => $maxCustomPrice,
                'currency' => $payload['custom_currency'],
            ],
        ]);
    }
}
