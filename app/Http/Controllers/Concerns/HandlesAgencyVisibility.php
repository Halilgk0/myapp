<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AgencyConnectionRequest;

trait HandlesAgencyVisibility
{
    protected function getConnectedUserIds(int $userId, bool $includeSelf = true): array
    {
        $connections = AgencyConnectionRequest::accepted()
            ->where(function ($query) use ($userId) {
                $query->where('requester_id', $userId)
                    ->orWhere('target_id', $userId);
            })
            ->get()
            ->map(function ($request) use ($userId) {
                return $request->requester_id === $userId
                    ? $request->target_id
                    : $request->requester_id;
            })
            ->all();

        if ($includeSelf) {
            $connections[] = $userId;
        }

        return array_values(array_unique($connections));
    }
}

