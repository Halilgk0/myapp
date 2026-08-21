<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyConnectionRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'requester_id',
        'target_id',
        'status',
        'responded_at',
        'pair_key',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $request) {
            $request->pair_key = static::generatePairKey($request->requester_id, $request->target_id);
        });
    }

    public static function generatePairKey(int $firstUserId, int $secondUserId): string
    {
        $sorted = [min($firstUserId, $secondUserId), max($firstUserId, $secondUserId)];

        return implode(':', $sorted);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeBetweenUsers($query, int $firstUserId, int $secondUserId)
    {
        return $query->where('pair_key', static::generatePairKey($firstUserId, $secondUserId));
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}



