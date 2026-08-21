<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'latitude',
        'longitude',
        'accuracy',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
    ];

    /**
     * Get the ticket that owns this location.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Check if location is recent (within last 30 minutes)
     */
    public function isRecent()
    {
        return $this->updated_at->diffInMinutes(now()) <= 30;
    }
} 