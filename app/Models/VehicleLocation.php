<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class VehicleLocation extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'vehicle_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'altitude',
        'accuracy',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed' => 'float',
        'heading' => 'float',
        'altitude' => 'float',
        'accuracy' => 'float',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the location.
     * made by @hllgkx.0
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }
} 