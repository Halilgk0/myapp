<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'vehicle_type',
        'color',
        'capacity',
        'image',
        'is_active',
        'driver_id',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer'
    ];

    /**
     * Get the driver assigned to this vehicle.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the tickets for this vehicle.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the location history for the vehicle.
     */
    public function locations()
    {
        return $this->hasMany(VehicleLocation::class);
    }

    /**
     * Get the current location of the vehicle.
     */
    public function getCurrentLocationAttribute()
    {
        return $this->locations()->latest()->first();
    }

    /**
     * Check if the vehicle is currently online.
     * made by @hllgkx.0
     */
    public function isOnline()
    {
        $lastLocation = $this->getCurrentLocationAttribute();
        return $lastLocation && $lastLocation->created_at->diffInMinutes(now()) <= 5;
    }

    /**
     * Get the status attribute.
     */
    public function getStatusAttribute()
    {
        return $this->isOnline() ? 'online' : 'offline';
    }
}
