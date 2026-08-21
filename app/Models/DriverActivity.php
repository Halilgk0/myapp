<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'activity_type', // 'vehicle_assigned', 'vehicle_unassigned', 'login', 'logout', 'location_update', 'trip_start', 'trip_end'
        'description',
        'metadata', // JSON data for additional info
        'recorded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'recorded_at' => 'datetime'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function getActivityTypeLabelAttribute()
    {
        return match($this->activity_type) {
            'vehicle_assigned' => 'Araç Atandı',
            'vehicle_unassigned' => 'Araç Kaldırıldı',
            'login' => 'Sisteme Giriş',
            'logout' => 'Sistemden Çıkış',
            'location_update' => 'Konum Güncellendi',
            'trip_start' => 'Sefer Başladı',
            'trip_end' => 'Sefer Bitti',
            default => 'Diğer'
        };
    }

    public function getIconAttribute()
    {
        return match($this->activity_type) {
            'vehicle_assigned' => 'fas fa-car text-success',
            'vehicle_unassigned' => 'fas fa-car text-danger',
            'login' => 'fas fa-sign-in-alt text-primary',
            'logout' => 'fas fa-sign-out-alt text-secondary',
            'location_update' => 'fas fa-map-marker-alt text-info',
            'trip_start' => 'fas fa-play text-success',
            'trip_end' => 'fas fa-stop text-warning',
            default => 'fas fa-circle text-muted'
        };
        /* made by @hllgkx.0 */
    }

    public function getFormattedMetadataAttribute()
    {
        if (!$this->metadata) return '';

        return match($this->activity_type) {
            'vehicle_assigned', 'vehicle_unassigned' => $this->metadata['vehicle_plate'] ?? '',
            'location_update' => "Lat: {$this->metadata['latitude']}, Lng: {$this->metadata['longitude']}" ?? '',
            'trip_start', 'trip_end' => $this->metadata['trip_details'] ?? '',
            default => ''
        };
    }
} 