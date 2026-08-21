<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'passenger_type',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Get the ticket that owns the passenger.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the passenger type label.
     */
    public function getPassengerTypeLabelAttribute()
    {
        return match($this->passenger_type) {
            'adult' => 'Yetişkin',
            'child' => 'Çocuk',
            'infant' => 'Bebek',
            default => 'Bilinmeyen'
        };
    }

    /**
     * Get the formatted price per person.
     */
    public function getFormattedPricePerPersonAttribute()
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get the formatted total price.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2);
    }
} 