<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public const LEVEL_ADMIN = 1;
    public const LEVEL_DRIVER = 2;
    public const LEVEL_AGENCY = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'locale',
        'password',
        'plain_password',
        'phone_number',
        'is_active',
        'level', // 1 = Admin, 2 = Driver
        'vehicle_id',
        'guide_id',
        'supported_nationalities',
        'activation_token',
        'activation_token_sent_at',
        'last_login_at',
        'last_login_ip',
        'login_attempts',
        'salary_amount',
        'salary_currency',
        'salary_day',
        'last_salary_paid_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'plain_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $dates = [
        'email_verified_at',
        'activation_token_sent_at',
        'last_login_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /* made by @hllgkx.0 */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activation_token_sent_at' => 'datetime',
        'last_login_at' => 'datetime',
        'location_updated_at' => 'datetime',
        'password' => 'hashed',
        'plain_password' => 'encrypted',
        'is_active' => 'boolean',
        'level' => 'integer',
        'login_attempts' => 'integer',
        'supported_nationalities' => 'array',
        'salary_amount' => 'decimal:2',
        'salary_day' => 'integer',
        'last_salary_paid_at' => 'datetime',
    ];

    /**
     * Get the vehicle assigned to this driver.
     */
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    /**
     * Get tickets assigned to this driver.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'driver_id');
    }

    /**
     * Get driver activities.
     */
    public function activities()
    {
        return $this->hasMany(DriverActivity::class, 'driver_id')->orderBy('recorded_at', 'desc');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->level === 1;
    }
    /* made by @hllgkx.0 */
    /**
     * Check if user is driver.
     */
    public function isDriver()
    {
        return $this->level === 2;
    }

    /**
     * Get user level label.
     */
    public function getLevelLabelAttribute()
    {
        return match($this->level) {
            self::LEVEL_ADMIN => 'Admin',
            self::LEVEL_DRIVER => 'Şoför',
            self::LEVEL_AGENCY => 'Sokak Acentası',
            default => 'Bilinmeyen'
        };
    }

    /**
     * Get nationality options (same as Ticket model)
     */
    public static function getNationalityOptions()
    {
        return [
            'TR' => 'Türkiye',
            'DE' => 'Almanya',
            'RU' => 'Rusya',
            'EN' => 'İngiltere/Amerika',
            'FR' => 'Fransa',
            'IT' => 'İtalya',
            'ES' => 'İspanya',
            'NL' => 'Hollanda',
            'BE' => 'Belçika',
            'CH' => 'İsviçre',
            'AT' => 'Avusturya',
            'PL' => 'Polonya',
            'CZ' => 'Çekya',
            'SK' => 'Slovakya',
            'HU' => 'Macaristan',
            'RO' => 'Romanya',
            'BG' => 'Bulgaristan',
            'GR' => 'Yunanistan',
            'UA' => 'Ukrayna',
            'BY' => 'Belarus',
            'LT' => 'Litvanya',
            'LV' => 'Letonya',
            'EE' => 'Estonya',
            'FI' => 'Finlandiya',
            'SE' => 'İsveç',
            'NO' => 'Norveç',
            'DK' => 'Danimarka',
            'IS' => 'İzlanda',
            'IE' => 'İrlanda',
            'PT' => 'Portekiz',
            'MT' => 'Malta',
            'CY' => 'Kıbrıs',
            'LU' => 'Lüksemburg',
            'MC' => 'Monako',
            'AD' => 'Andorra',
            'SM' => 'San Marino',
            'VA' => 'Vatikan',
            'LI' => 'Liechtenstein',
            'OTHER' => 'Diğer'
        ];
    }

    /**
     * Get supported nationalities display names
     */
    public function getSupportedNationalitiesNamesAttribute()
    {
        if (!$this->supported_nationalities) {
            return 'Tüm milliyetler';
        }

        $options = self::getNationalityOptions();
        $names = [];

        foreach ($this->supported_nationalities as $code) {
            $names[] = $options[$code] ?? $code;
        }

        return implode(', ', $names);
    }

    /**
     * Check if driver supports given nationality
     */
    public function supportsNationality($nationality)
    {
        if (!$this->supported_nationalities || empty($this->supported_nationalities)) {
            return true; // If no restrictions, supports all
        }
        /* made by @hllgkx.0 */
        return in_array($nationality, $this->supported_nationalities);
    }

    public function agency()
    {
        return $this->hasOne(Agency::class);
    }

    public function sentAgencyRequests()
    {
        return $this->hasMany(AgencyConnectionRequest::class, 'requester_id');
    }

    public function receivedAgencyRequests()
    {
        return $this->hasMany(AgencyConnectionRequest::class, 'target_id');
    }

    public function sharedTours()
    {
        return $this->belongsToMany(Tour::class, 'tour_shared_users', 'shared_with_user_id', 'tour_id')
            ->withPivot([
                'shared_by_user_id',
                'custom_date_prices',
                'custom_monthly_prices',
                'custom_base_prices',
                'custom_currency',
            ])
            ->withTimestamps();
    }

    public function toursSharedByMe()
    {
        return $this->belongsToMany(Tour::class, 'tour_shared_users', 'shared_by_user_id', 'tour_id')
            ->withPivot([
                'shared_with_user_id',
                'custom_date_prices',
                'custom_monthly_prices',
                'custom_base_prices',
                'custom_currency',
            ])
            ->withTimestamps();
    }

    public function isAgency()
    {
        return $this->level === self::LEVEL_AGENCY;
    }

    /**
     * Şoförün atandığı rehber
     */
    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }
}
