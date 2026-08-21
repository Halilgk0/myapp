<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'owner_id',
        'name',
        'description',
        'country',
        'city',
        'district',
        'pickup_time',
        'dropoff_time',
        'pickup_location',
        'dropoff_location',
        'price_adult',
        'price_child',
        'price_infant',
        'currency',
        'max_capacity',
        'is_active',
        'notes',
        'image',
        'available_days',
        'available_months',
        'available_years',
        'monthly_prices',
        'available_dates',
        'date_prices',
        'service_areas',
        'auto_share_on_connect',
        'auto_approve_tickets',
        'street_agency_auto_approve_time',
        'street_agency_auto_approve_enabled',
    ];
    /* made by @hllgkx.0 */
    protected $casts = [
        'pickup_time' => 'datetime:H:i',
        'dropoff_time' => 'datetime:H:i',
        'street_agency_auto_approve_time' => 'datetime:H:i',
        'price_adult' => 'decimal:2',
        'price_child' => 'decimal:2',
        'price_infant' => 'decimal:2',
        'max_capacity' => 'integer',
        'is_active' => 'boolean',
        'available_days' => 'array',
        'available_months' => 'array',
        'available_years' => 'array',
        'monthly_prices' => 'array',
        'available_dates' => 'array',
        'date_prices' => 'array',
        'service_areas' => 'array',
        'auto_share_on_connect' => 'boolean',
        'auto_approve_tickets' => 'boolean',
        'street_agency_auto_approve_enabled' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sharedWithUsers()
    {
        return $this->belongsToMany(User::class, 'tour_shared_users', 'tour_id', 'shared_with_user_id')
            ->withPivot([
                'shared_by_user_id',
                'custom_date_prices',
                'custom_monthly_prices',
                'custom_base_prices',
                'custom_currency',
            ])
            ->withTimestamps();
    }

    /**
     * Belirli bir ay için fiyatları döndürür. Eski sistem fiyatları artık kullanılmaz.
     */
    public function getPricesForMonth(int $month): array
    {
        return [
            'adult' => 0.0,
            'child' => 0.0,
            'infant' => 0.0,
        ];
    }

    /**
     * Belirli bir gün için fiyatları döndürür. Sadece takvim fiyatları kullanılır.
     */
    public function getPricesForDate($date): array
    {
        $dt = \Carbon\Carbon::parse($date);
        $dateKey = $dt->format('Y-m-d');

        $daily = $this->date_prices ?? [];
        if (is_array($daily) && isset($daily[$dateKey])) {
            $row = $daily[$dateKey];
            if (is_array($row)) {
                return [
                    'adult' => is_numeric($row['adult'] ?? null) ? (float) $row['adult'] : 0.0,
                    'child' => is_numeric($row['child'] ?? null) ? (float) $row['child'] : 0.0,
                    'infant' => is_numeric($row['infant'] ?? null) ? (float) $row['infant'] : 0.0,
                ];
            }
            // Eğer sayı ise yetişkin fiyatı olarak değerlendir
            if (is_numeric($row)) {
                return [
                    'adult' => (float) $row,
                    'child' => 0.0,
                    'infant' => 0.0,
                ];
            }
        }

        // Takvim fiyatı yoksa 0 dön.
        return [
            'adult' => 0.0,
            'child' => 0.0,
            'infant' => 0.0,
        ];
    }

    // duration removed

    /**
     * Servis alanı poligonlarına eklenmiş saatler arasından en erken olanını döndürür.
     * Dönüş: "HH:MM" veya null.
     */
    public function getEarliestServiceAreaTimeAttribute(): ?string
    {
        $sa = $this->service_areas;
        if (!$sa) {
            return null;
        }
        if (is_string($sa)) {
            $sa = json_decode($sa, true);
        }
        if (!is_array($sa)) {
            return null;
        }

        $times = [];
        if (($sa['type'] ?? null) === 'FeatureCollection' && is_array($sa['features'] ?? null)) {
            foreach ($sa['features'] as $feat) {
                $props = is_array($feat['properties'] ?? null) ? $feat['properties'] : [];
                $list = is_array($props['times'] ?? null) ? $props['times'] : [];
                foreach ($list as $t) {
                    if (is_string($t) && preg_match('/^\d{2}:\d{2}$/', $t)) {
                        $times[] = $t;
                    }
                }
            }
        }

        if (!$times) {
            return null;
        }

        sort($times);

        return $times[0];
    }

    public function getFormattedPriceAdultAttribute()
    {
        return number_format($this->price_adult, 2) . ' ' . $this->currency;
    }

    public function getFormattedPriceChildAttribute()
    {
        return number_format($this->price_child, 2) . ' ' . $this->currency;
    }

    public function getFormattedPriceInfantAttribute()
    {
        return number_format($this->price_infant, 2) . ' ' . $this->currency;
    }

    public function getMaxDisplayPriceAttribute(): float
    {
        $values = [];
        $values = array_merge($values, static::extractPriceValues($this->date_prices));
        $values = array_merge($values, static::extractPriceValues($this->monthly_prices));

        foreach (['price_adult', 'price_child', 'price_infant'] as $field) {
            if (is_numeric($this->{$field}) && $this->{$field} > 0) {
                $values[] = (float) $this->{$field};
            }
        }

        return !empty($values) ? max($values) : 0.0;
    }

    protected static function extractPriceValues($source): array
    {
        $values = [];

        if (is_array($source)) {
            foreach ($source as $entry) {
                if (is_array($entry)) {
                    foreach (['adult', 'child', 'infant'] as $key) {
                        if (isset($entry[$key]) && is_numeric($entry[$key]) && $entry[$key] > 0) {
                            $values[] = (float) $entry[$key];
                        }
                    }
                } elseif (is_numeric($entry) && $entry > 0) {
                    $values[] = (float) $entry;
                }
            }
        } elseif (is_numeric($source) && $source > 0) {
            $values[] = (float) $source;
        }

        return $values;
    }

    public static function maxPriceFromPayload($payload): float
    {
        $values = static::extractPriceValues($payload);

        return empty($values) ? 0.0 : max($values);
    }

    public function getDisplayCurrencyAttribute(): string
    {
        $currency = $this->currency ?? 'TRY';

        return strtoupper(trim($currency));
    }

    public function getStatusAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Pasif';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'badge-success' : 'badge-danger';
    }

    public function getTotalTicketsAttribute()
    {
        return $this->tickets()->count();
    }

    public function getActiveTicketsAttribute()
    {
        return $this->tickets()->where('is_active', true)->count();
    }

    /**
     * Belirli bir tarihin bu tur için uygun olup olmadığını kontrol eder
     */
    public function isDateAvailable($date)
    {
        $date = \Carbon\Carbon::parse($date);
        
        // Gün kontrolü (1=Pazartesi, 7=Pazar)
        if ($this->available_days && !in_array($date->dayOfWeek, $this->available_days)) {
            return false;
        }
        
        // Ay kontrolü
        if ($this->available_months && !in_array($date->month, $this->available_months)) {
            return false;
        }
        
        // Yıl kontrolü
        if ($this->available_years && !in_array($date->year, $this->available_years)) {
            return false;
        }
        
        return true;
    }

    /**
     * Tur için uygun tarihleri getirir
     */
    public function getAvailableDates($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = now();
        }
        if (!$endDate) {
            $endDate = now()->addMonths(6);
        }

        $dates = [];
        $currentDate = \Carbon\Carbon::parse($startDate);

        while ($currentDate <= $endDate) {
            if ($this->isDateAvailable($currentDate)) {
                $dates[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        return $dates;
    }

    /**
     * Gün adlarını getirir
     */
    public function getDayNames()
    {
        return [
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
            7 => 'Pazar'
        ];
    }

    /**
     * Ay adlarını getirir
     */
    public function getMonthNames()
    {
        return [
            1 => 'Ocak',
            2 => 'Şubat',
            3 => 'Mart',
            4 => 'Nisan',
            5 => 'Mayıs',
            6 => 'Haziran',
            7 => 'Temmuz',
            8 => 'Ağustos',
            9 => 'Eylül',
            10 => 'Ekim',
            11 => 'Kasım',
            12 => 'Aralık'
        ];
    }
} 