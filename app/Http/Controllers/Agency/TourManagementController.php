<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Support\Facades\Auth;

class TourManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $sharedTours = $user->sharedTours()
            ->with(['agency.user', 'owner.agency'])
            ->withCount('tickets')
            ->orderBy('name')
            ->get()
            ->map(function (Tour $tour) {
                $pivot = $tour->pivot;
                $customDatePrices = $pivot && $pivot->custom_date_prices
                    ? json_decode($pivot->custom_date_prices, true)
                    : [];
                $customMax = \App\Models\Tour::maxPriceFromPayload($customDatePrices);
                $currency = $pivot && $pivot->custom_currency
                    ? strtoupper($pivot->custom_currency)
                    : $tour->display_currency;

                $tour->shared_pricing = [
                    'has_custom_price' => $customMax > 0,
                    'max_price' => $customMax > 0 ? $customMax : $tour->max_display_price,
                    'custom_only_price' => $customMax,
                    'currency' => $currency,
                ];

                return $tour;
            });

        return view('agency.tours.index', compact('sharedTours'));
    }
}

