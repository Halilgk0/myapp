<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RETURNED = 'returned'; // Düzenleme için acentaya geri gönderildi

    protected $fillable = [
        'requester_id',
        'tour_owner_id',
        'tour_id',
        'voucher_no',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_nationality',
        'tour_date',
        'pickup_time',
        'adult_count',
        'child_count',
        'infant_count',
        'adult_price',
        'child_price',
        'infant_price',
        'total_price',
        'currency',
        'base_currency',
        'sale_currency',
        'rest_adjustment_amount',
        'rest_adjustment_currency',
        'rest_converted_amount',
        'rest_fx_rate',
        'rest_fx_source_currency',
        'rest_fx_target_currency',
        'rest_fx_date',
        'pickup_location',
        'pickup_lat',
        'pickup_lng',
        'room_number',
        'passport_numbers',
        'status',
        'rejection_reason',
        'responded_at',
        'return_reason',
        'returned_at',
        'return_count',
    ];

    protected $casts = [
        'tour_date' => 'date',
        'pickup_time' => 'datetime:H:i',
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'rest_adjustment_amount' => 'decimal:2',
        'rest_converted_amount' => 'decimal:2',
        'rest_fx_rate' => 'decimal:8',
        'rest_fx_date' => 'date',
        'responded_at' => 'datetime',
        'returned_at' => 'datetime',
        'return_count' => 'integer',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function tourOwner()
    {
        return $this->belongsTo(User::class, 'tour_owner_id');
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForOwner($query, $userId)
    {
        return $query->where('tour_owner_id', $userId);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isReturned()
    {
        return $this->status === self::STATUS_RETURNED;
    }

    /**
     * Get nationality display name
     */
    public function getNationalityNameAttribute()
    {
        $options = Ticket::getNationalityOptions();
        return $options[$this->customer_nationality] ?? $this->customer_nationality;
    }

    /**
     * Get total passengers count
     */
    public function getTotalPassengersAttribute()
    {
        return $this->adult_count + $this->child_count + $this->infant_count;
    }
}


