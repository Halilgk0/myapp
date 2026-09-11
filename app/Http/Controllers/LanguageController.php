<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:tr,en',
        ]);

        $request->user()->update(['locale' => $validated['locale']]);

        return response()->json(['locale' => $validated['locale']]);
    }
}
