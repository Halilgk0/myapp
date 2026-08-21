<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavedMapController extends Controller
{
    public function index(Request $request)
    {
        $maps = SavedMap::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'geometry', 'created_at', 'updated_at']);

        return response()->json(['data' => $maps]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'geometry' => 'required|array',
            'geometry.type' => 'required|string|in:MultiPolygon,FeatureCollection',
        ], [
            'name.required' => 'Harita adı zorunludur.',
            'name.max' => 'Harita adı en fazla 100 karakter olabilir.',
            'geometry.required' => 'Kaydedilecek poligon verisi bulunamadı.',
            'geometry.type.in' => 'Geçersiz geometri tipi.',
        ]);

        $validator->after(function ($v) use ($request) {
            $geo = $request->input('geometry');
            $type = is_array($geo) ? ($geo['type'] ?? null) : null;
            if ($type === 'MultiPolygon') {
                if (!is_array($geo['coordinates'] ?? null) || empty($geo['coordinates'])) {
                    $v->errors()->add('geometry.coordinates', 'En az bir poligon gereklidir.');
                }
            } elseif ($type === 'FeatureCollection') {
                if (!is_array($geo['features'] ?? null) || empty($geo['features'])) {
                    $v->errors()->add('geometry.features', 'En az bir poligon gereklidir.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $map = SavedMap::create([
            'user_id' => $request->user()->id,
            'name' => trim($request->input('name')),
            'geometry' => $request->input('geometry'),
        ]);

        return response()->json([
            'data' => $map->only(['id', 'name', 'geometry', 'created_at', 'updated_at']),
        ], 201);
    }

    public function destroy(Request $request, SavedMap $savedMap)
    {
        if ($savedMap->user_id !== $request->user()->id) {
            abort(403);
        }

        $savedMap->delete();

        return response()->json(['success' => true]);
    }
}
