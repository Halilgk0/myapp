<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'website',
        'commission_rate',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $agency) {
            static::detachUserRelationships($agency->user_id);
        });
    }

    public static function detachUserRelationships(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        AgencyConnectionRequest::where(function ($query) use ($userId) {
            $query->where('requester_id', $userId)
                ->orWhere('target_id', $userId);
        })->delete();

        DB::table('tour_shared_users')
            ->where('shared_by_user_id', $userId)
            ->orWhere('shared_with_user_id', $userId)
            ->delete();
    }

    /**
     * Get the tickets for this agency.
     * Biletler created_by_user_id üzerinden agency user_id'ye bağlı
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'created_by_user_id', 'user_id');
    }

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    public function ownedTours()
    {
        return $this->hasMany(Tour::class, 'owner_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted commission rate
     */
    public function getFormattedCommissionRateAttribute()
    {
        return number_format($this->commission_rate, 2) . '%';
    }

    /**
     * Get active agencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
