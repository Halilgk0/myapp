<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'license_number',
        'license_expiry',
        'supported_nationalities',
        'status',
        'hire_date',
        'salary',
        'notes'
    ];

    protected $casts = [
        'supported_nationalities' => 'array',
        'license_expiry' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2'
    ];

    /**
     * Milliyetler listesi
     * made by @hllgkx.0
     */
    public static function getNationalityOptions(): array
    {
        return [
            'TR' => 'Türk',
            'DE' => 'Alman',
            'RU' => 'Rus',
            'EN' => 'İngiliz',
            'FR' => 'Fransız',
            'ES' => 'İspanyol',
            'IT' => 'İtalyan',
            'NL' => 'Hollandalı',
            'US' => 'Amerikan',
            'GB' => 'İngiliz',
            'AR' => 'Arap',
            'CN' => 'Çinli',
            'JP' => 'Japon',
            'KR' => 'Koreli'
        ];
    }

    /**
     * Desteklenen milliyetlerin isimlerini al
     */
    public function getSupportedNationalitiesNamesAttribute(): string
    {
        if (empty($this->supported_nationalities)) {
            return 'Belirtilmemiş';
        }

        $nationalities = self::getNationalityOptions();
        $names = [];
        
        foreach ($this->supported_nationalities as $code) {
            $names[] = $nationalities[$code] ?? $code;
        }
        
        return implode(', ', $names);
    }

    /**
     * Belirli bir milliyeti destekleyip desteklemediğini kontrol et
     */
    public function supportsNationality(string $nationality): bool
    {
        // If guide has no explicit restriction, treat as supporting all nationalities.
        if (empty($this->supported_nationalities)) {
            return true;
        }

        return in_array($nationality, $this->supported_nationalities ?? []);
    }

    /**
     * Bu rehbere atanmış şoförler
     * made by @hllgkx.0
     */
    public function drivers()
    {
        return $this->hasMany(User::class, 'guide_id');
    }
}