<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tour;
use App\Models\User;
use App\Models\Transaction;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_nationality',
        'tour_id',
        'tour_date',
        'tour_name',
        'tour_country',
        'tour_region',
        'pickup_time',
        'sales_agency',
        'agency_id',
        'total_price',
        'deposit',
        'rest',
        'currency',           // legacy: satış para birimi için de kullanılıyor
        'base_currency',
        'sale_currency',
        'is_active',
        'vehicle_id',
        'driver_id',
        'entry_date',
        'entry_time',
        'voucher_no',
        'tracking_no',
        'pickup_location',
        'room_number',
        'passport_numbers',
        'created_by_user_id',
        'adult_count',
        'child_count',
        'infant_count',
        'adult_price',
        'child_price',
        'infant_price',
        'accounted_at',
        'accounting_transaction_id',
        'owner_share_amount',
        'owner_share_currency',
        'rest_adjustment_amount',
        'rest_adjustment_currency',
        'rest_converted_amount',
        'rest_fx_rate',
        'rest_fx_source_currency',
        'rest_fx_target_currency',
        'rest_fx_date',
        'accounting_owner_transaction_id',
        'accounting_agency_income_transaction_id',
        'accounting_agency_payout_transaction_id',
        'accounting_rest_transaction_id',
        'is_route_start',
        'route_order',
        'route_optimized_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'entry_time' => 'datetime:H:i:s',
        'tour_date' => 'date',
        'pickup_time' => 'datetime:H:i',
        'total_price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'rest' => 'decimal:2',
        'is_active' => 'boolean',
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'infant_count' => 'integer',
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'accounted_at' => 'datetime',
        'owner_share_amount' => 'decimal:2',
        'rest_adjustment_amount' => 'decimal:2',
        'rest_converted_amount' => 'decimal:2',
        'rest_fx_rate' => 'decimal:8',
        'rest_fx_date' => 'date',
        'is_route_start' => 'boolean',
        'route_order' => 'integer',
        'route_optimized_at' => 'datetime',
    ];

    /**
     * Mark ticket expired if its tour_date is in the past.
     * Returns true if any change was made.
     */
    public function expireIfPast(): bool
    {
        if ($this->tour_date && $this->tour_date->isPast()) {
            $changed = false;
            if ($this->is_active) {
                $this->is_active = false;
                $changed = true;
            }
            if (!is_null($this->vehicle_id) || !is_null($this->driver_id)) {
                $this->vehicle_id = null;
                $this->driver_id = null;
                $changed = true;
            }
            if ($changed) {
                $this->save();
            }
            return $changed;
        }
        return false;
    }

    /**
     * Boot the model.
     * made by @hllgkx.0
     */
    protected static function boot()
    {
        parent::boot();

        // Generate tracking number before creating
        static::creating(function ($ticket) {
            if (empty($ticket->tracking_no)) {
                $ticket->tracking_no = static::generateTrackingNumber();
            }
        });

        // Street agency sales should appear in agency accounting immediately.
        static::created(function ($ticket) {
            $ticket->createAgencyIncomeRecordIfNeeded();
        });

        // PERFORMANS: Her ticket okuma'da DB save tetiklemiyoruz.
        // Geçmiş tarihli biletler in-memory olarak deaktive görünür ama DB yazımı
        // saved/saving event'lerinde + AccountExpiredTickets cron'unda yapılır.
        static::retrieved(function ($ticket) {
            if ($ticket->tour_date && $ticket->tour_date->isPast()) {
                if ($ticket->is_active) {
                    $ticket->is_active = false;
                    $ticket->syncOriginalAttribute('is_active');
                }
                if (!is_null($ticket->vehicle_id)) {
                    $ticket->vehicle_id = null;
                    $ticket->syncOriginalAttribute('vehicle_id');
                }
                if (!is_null($ticket->driver_id)) {
                    $ticket->driver_id = null;
                    $ticket->syncOriginalAttribute('driver_id');
                }
            }
        });

        // Guard before saving as well
        static::saving(function ($ticket) {
            if ($ticket->tour_date && $ticket->tour_date->isPast()) {
                $ticket->is_active = false;
                $ticket->vehicle_id = null;
                $ticket->driver_id = null;
            }

            // Araç atanmamış şoföre bilet atanmasını engelle.
            if ($ticket->driver_id && $ticket->isDirty('driver_id')) {
                $driver = User::find($ticket->driver_id);
                if ($driver && (int) $driver->level === User::LEVEL_DRIVER && !$driver->vehicle) {
                    throw new \DomainException(
                        'Şoför ' . $driver->name . ' için araç atanmamış. Önce şoföre bir araç atayın.'
                    );
                }
            }
        });

        // Bilet kaydedildikten sonra, tarihi geçmişse muhasebe kaydını anında oluştur
        static::saved(function ($ticket) {
            if ($ticket->tour_date && $ticket->tour_date->isPast() && !$ticket->accounted_at) {
                $ticket->createAccountingRecordsIfNeeded();
            }

            // Yeni bilet eklendiğinde / driver/tarih değiştiğinde, o günün
            // başlangıcı işaretliyse rotayı otomatik yeniden hesapla.
            static::reoptimizeRouteIfNeeded($ticket);
        });
    }

    /**
     * Bilet save edildikten sonra, eğer aynı şoför+tarih için başlangıç bileti
     * ayarlıysa rotayı yeniden hesaplar. Sadece rota'yı etkileyen alanlar
     * değiştiğinde tetiklenir; sonsuz döngüye girmez (raw DB update kullanılır).
     */
    protected static function reoptimizeRouteIfNeeded(Ticket $ticket): void
    {
        if (!$ticket->driver_id || !$ticket->tour_date) {
            return;
        }

        // Sadece ilgili alanlar değiştiyse re-optimize tetiklenir.
        $relevantFields = ['driver_id', 'tour_date', 'is_active'];
        if (!$ticket->wasRecentlyCreated && !$ticket->wasChanged($relevantFields)) {
            return;
        }

        $date = $ticket->tour_date->toDateString();

        $hasStart = static::query()
            ->where('driver_id', $ticket->driver_id)
            ->whereDate('tour_date', $date)
            ->where('is_active', true)
            ->where('is_route_start', true)
            ->exists();

        if (!$hasStart) {
            return;
        }

        try {
            $driver = User::find($ticket->driver_id);
            if ($driver) {
                app(\App\Services\RouteOptimizer::class)->optimize($driver, $date);
            }
        } catch (\Throwable $e) {
            \Log::warning('Otomatik rota yeniden hesaplama başarısız', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bilet oluşturulduğu anda (sokak acentası için) satış gelir kaydını üretir.
     */
    public function createAgencyIncomeRecordIfNeeded(): bool
    {
        if ($this->accounting_agency_income_transaction_id) {
            return false;
        }

        $creator = $this->created_by_user_id ? User::find($this->created_by_user_id) : null;
        if (!$creator || !$creator->isAgency()) {
            return false;
        }

        $amount = $this->calculateAgencyNetSaleAmount();
        if ($amount <= 0) {
            return false;
        }

        $titleBase = sprintf(
            'Bilet - %s (%s)',
            $this->tour_name ?? 'Tur',
            optional($this->tour_date)->format('d.m.Y')
        );

        try {
            $agencyIncome = Transaction::create([
                'type' => 'income',
                'title' => $titleBase . ' Satış Geliri',
                'amount' => $amount,
                'currency' => strtoupper($this->currency ?? 'TRY'),
                'transaction_date' => now()->toDateString(),
                'payment_method' => 'sale-ticket',
                'status' => 'paid',
                'notes' => sprintf(
                    "Otomatik eklendi (bilet oluşturuldu)\nTracking: %s\nVoucher: %s",
                    $this->tracking_no ?? '-',
                    $this->voucher_no ?? '-'
                ),
                'created_by' => $creator->id,
            ]);

            $this->accounting_agency_income_transaction_id = $agencyIncome->id;
            $this->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            \Log::error('Acenta satış geliri anlık oluşturulamadı: ' . $e->getMessage(), [
                'ticket_id' => $this->id,
                'created_by_user_id' => $this->created_by_user_id,
            ]);

            return false;
        }
    }

    /**
     * Tarihi geçmiş bilet için muhasebe kayıtlarını anında oluştur.
     */
    public function createAccountingRecordsIfNeeded(): bool
    {
        // Zaten muhasebeleştirilmişse atla
        if ($this->accounted_at) {
            return false;
        }

        // Tarihi geçmemiş bilet için işlem yapma
        if (!$this->tour_date || !$this->tour_date->isPast()) {
            return false;
        }

        try {
            DB::transaction(function () {
                // Tur ilişkisini yeniden yükle (lazy load yerine fresh load)
                $tour = $this->tour_id ? Tour::find($this->tour_id) : null;
                
                // Tur sahibi payını hesapla
                [$ownerShareAmount, $ownerCurrency] = $this->calculateOwnerShare();
                $ownerShareAmount = (float) $ownerShareAmount;
                $ownerCurrency = strtoupper($ownerCurrency ?? 'TRY');
                $restAmount = (float) ($this->rest_adjustment_amount ?? 0);
                $restCurrency = strtoupper($this->rest_adjustment_currency ?? $ownerCurrency);

                $creator = $this->created_by_user_id ? User::find($this->created_by_user_id) : null;
                $tourOwnerId = $tour ? $tour->owner_id : null;
                $isAgency = $creator && $creator->isAgency();
                $ticketCurrency = strtoupper($this->currency ?? 'TRY');
                
                // Debug log for troubleshooting
                \Log::info('createAccountingRecordsIfNeeded', [
                    'ticket_id' => $this->id,
                    'created_by_user_id' => $this->created_by_user_id,
                    'creator_name' => $creator ? $creator->name : null,
                    'creator_level' => $creator ? $creator->level : null,
                    'isAgency' => $isAgency,
                    'tour_id' => $this->tour_id,
                    'tourOwnerId' => $tourOwnerId,
                    'ownerShareAmount' => $ownerShareAmount,
                ]);

                $titleBase = sprintf('Bilet - %s (%s)', $this->tour_name ?? 'Tur', optional($this->tour_date)->format('d.m.Y'));

                if ($isAgency) {
                    // 1) Sokak acentası satışı (müşteriden tahsilat) - gelir
                    if (!$this->accounting_agency_income_transaction_id) {
                        $agencyNetSale = $this->calculateAgencyNetSaleAmount();
                        $agencyIncome = Transaction::create([
                            'type' => 'income',
                            'title' => $titleBase . ' Satış Geliri',
                            'amount' => $agencyNetSale,
                            'currency' => $ticketCurrency,
                            'transaction_date' => $this->tour_date ?? now()->toDateString(),
                            'payment_method' => 'sale-ticket',
                            'status' => 'paid',
                            'notes' => sprintf(
                                "Otomatik eklendi (tarihi geçen bilet)\nTracking: %s\nVoucher: %s",
                                $this->tracking_no ?? '-',
                                $this->voucher_no ?? '-'
                            ),
                            'created_by' => $creator->id,
                        ]);
                        $this->accounting_agency_income_transaction_id = $agencyIncome->id;
                    }

                    if ($tourOwnerId) {
                        // 2) Sokak acentasından tur sahibine ödeme - gider
                        if ($ownerShareAmount > 0 && !$this->accounting_agency_payout_transaction_id) {
                            $agencyPayout = Transaction::create([
                                'type' => 'expense',
                                'title' => $titleBase . ' Tur Sahibi Payı',
                                'amount' => $ownerShareAmount,
                                'currency' => $ownerCurrency,
                                'transaction_date' => $this->tour_date ?? now()->toDateString(),
                                'payment_method' => 'payout-owner',
                                'status' => 'paid',
                                'notes' => sprintf(
                                    "Otomatik eklendi (tarihi geçen bilet) | Tur Sahibi: %s | Tracking: %s",
                                    $tour?->owner?->name ?? '-',
                                    $this->tracking_no ?? '-'
                                ),
                                'created_by' => $creator->id,
                            ]);
                            $this->accounting_agency_payout_transaction_id = $agencyPayout->id;
                        }

                        // 3) Tur sahibi için gelir kaydı
                        if ($ownerShareAmount > 0 && !$this->accounting_owner_transaction_id) {
                            $ownerIncome = Transaction::create([
                                'type' => 'income',
                                'title' => $titleBase . ' (Acenta Satışı)',
                                'amount' => $ownerShareAmount,
                                'currency' => $ownerCurrency,
                                'transaction_date' => $this->tour_date ?? now()->toDateString(),
                                'payment_method' => 'owner-share',
                                'status' => 'paid',
                                'notes' => sprintf(
                                    "Sokak acentası satışı. Tracking: %s | Acenta: %s",
                                    $this->tracking_no ?? '-',
                                    $creator->name ?? '-'
                                ),
                                'created_by' => $tourOwnerId,
                            ]);
                            $this->accounting_owner_transaction_id = $ownerIncome->id;
                            $this->accounting_transaction_id = $ownerIncome->id;
                        }

                        // 4) Rest geliri (admin payı ek gelir)
                        if ($restAmount > 0 && !$this->accounting_rest_transaction_id) {
                            $restIncome = Transaction::create([
                                'type' => 'income',
                                'title' => $titleBase . ' Rest Geliri (Acenta: ' . ($creator->name ?? 'Bilinmeyen') . ')',
                                'amount' => $restAmount,
                                'currency' => $restCurrency,
                                'transaction_date' => $this->tour_date ?? now()->toDateString(),
                                'payment_method' => 'rest-adjustment',
                                'status' => 'paid',
                                'notes' => sprintf(
                                    "Sokak acentası satışından rest geliri. Tracking: %s | Acenta: %s",
                                    $this->tracking_no ?? '-',
                                    $creator->name ?? '-'
                                ),
                                'created_by' => $tourOwnerId,
                            ]);
                            $this->accounting_rest_transaction_id = $restIncome->id;
                        }
                    }
                } else {
                    // Mevcut senaryo: tek taraflı gelir kaydı (kendi satışımız)
                    if (!$this->accounting_owner_transaction_id) {
                        $transaction = Transaction::create([
                            'type' => 'income',
                            'title' => $titleBase . ' (Kendi Satış)',
                            'amount' => (float) ($this->total_price ?? 0),
                            'currency' => $ticketCurrency,
                            'transaction_date' => $this->tour_date ?? now()->toDateString(),
                            'payment_method' => 'auto-expired-ticket',
                            'status' => 'paid',
                            'notes' => sprintf(
                                "Otomatik eklendi (tarihi geçen bilet)\nTracking: %s\nVoucher: %s",
                                $this->tracking_no ?? '-',
                                $this->voucher_no ?? '-'
                            ),
                            'created_by' => $tourOwnerId ?: $this->created_by_user_id,
                        ]);
                        $this->accounting_owner_transaction_id = $transaction->id;
                        $this->accounting_transaction_id = $transaction->id;
                    }
                }

                // Pay bilgilerini kaydet
                $this->owner_share_amount = $ownerShareAmount;
                $this->owner_share_currency = $ownerCurrency;
                $this->accounted_at = now();
                
                // saveQuietly kullanarak sonsuz döngüyü önle
                $this->saveQuietly();
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Bilet muhasebe kaydı oluşturulamadı: ' . $e->getMessage(), [
                'ticket_id' => $this->id,
                'tracking_no' => $this->tracking_no,
            ]);
            return false;
        }
    }

    /**
     * Tur sahibinin taban fiyatına göre gelir payını hesaplar.
     */
    protected function calculateOwnerShare(): array
    {
        if ($this->owner_share_amount && $this->owner_share_currency) {
            return [$this->owner_share_amount, $this->owner_share_currency];
        }

        // Taban payi hesaplamasinda her zaman biletin kayitli birim fiyatlarini baz al.
        // Boylece tur fiyatlari sonradan degisse bile muhasebe bozulmaz.
        $tour = $this->tour ?: Tour::find($this->tour_id);
        $currency = strtoupper($this->base_currency ?? ($tour->currency ?? $this->currency ?? 'TRY'));

        $prices = [
            'adult' => (float) ($this->adult_price ?? 0),
            'child' => (float) ($this->child_price ?? 0),
            'infant' => (float) ($this->infant_price ?? 0),
        ];

        // Eski kayitlar icin birim fiyatlar bos ise son care olarak tur fiyatina geri dus.
        if (($prices['adult'] + $prices['child'] + $prices['infant']) <= 0 && $tour) {
            $date = $this->tour_date ?? Carbon::today();
            $fallbackPrices = $tour->getPricesForDate($date);
            if (is_array($fallbackPrices)) {
                $prices = [
                    'adult' => (float) ($fallbackPrices['adult'] ?? 0),
                    'child' => (float) ($fallbackPrices['child'] ?? 0),
                    'infant' => (float) ($fallbackPrices['infant'] ?? 0),
                ];
            }
        }

        $adult = $this->adult_count ?? 0;
        $child = $this->child_count ?? 0;
        $infant = $this->infant_count ?? 0;

        $amount = ($adult * ($prices['adult'] ?? 0))
            + ($child * ($prices['child'] ?? 0))
            + ($infant * ($prices['infant'] ?? 0));

        if ($amount <= 0) {
            // Fallback for legacy/manual records: keep owner share meaningful.
            $saleTotal = (float) ($this->total_price ?? 0);
            $restBase = (float) ($this->rest_converted_amount ?? 0);
            if ($restBase <= 0) {
                $restBase = (float) ($this->rest_adjustment_amount ?? 0);
            }
            $amount = max($saleTotal - $restBase, 0);
        }

        return [$amount, $currency];
    }

    /**
     * Sokak acentası kasasına anlık yansıyacak net satış geliri.
     * Kural: satış fiyatı - rest.
     */
    protected function calculateAgencyNetSaleAmount(): float
    {
        $saleTotal = (float) ($this->total_price ?? 0);
        $saleCurrency = strtoupper($this->sale_currency ?? $this->currency ?? 'TRY');
        $restAmount = (float) ($this->rest_adjustment_amount ?? 0);

        if ($saleTotal <= 0 || $restAmount <= 0) {
            return max($saleTotal, 0);
        }

        $restCurrency = strtoupper($this->rest_adjustment_currency ?? $saleCurrency);
        $restInSaleCurrency = $restAmount;

        if ($restCurrency !== $saleCurrency) {
            $fxRate = (float) ($this->rest_fx_rate ?? 0);
            $fxSource = strtoupper($this->rest_fx_source_currency ?? '');
            $fxTarget = strtoupper($this->rest_fx_target_currency ?? '');
            $baseCurrency = strtoupper($this->base_currency ?? $saleCurrency);

            if ($this->rest_converted_amount !== null && $baseCurrency === $saleCurrency) {
                $restInSaleCurrency = (float) $this->rest_converted_amount;
            } elseif ($fxRate > 0 && $fxSource === $restCurrency && $fxTarget === $saleCurrency) {
                $restInSaleCurrency = $restAmount * $fxRate;
            } elseif ($fxRate > 0 && $fxSource === $saleCurrency && $fxTarget === $restCurrency) {
                $restInSaleCurrency = $restAmount / $fxRate;
            }
        }

        return max(round($saleTotal - $restInSaleCurrency, 2), 0);
    }

    /**
     * Process expired tickets that have not been accounted yet.
     * Useful when tour_date is updated directly in DB.
     */
    public static function processExpiredUnaccounted(?int $agencyUserId = null, int $limit = 250): int
    {
        $query = static::query()
            ->whereNull('accounted_at')
            ->whereNotNull('tour_date')
            ->whereDate('tour_date', '<', now()->toDateString())
            ->orderBy('tour_date')
            ->orderBy('id');

        if ($agencyUserId) {
            $query->where('created_by_user_id', $agencyUserId);
        }

        $tickets = $query->limit(max(1, $limit))->get();
        $processed = 0;

        foreach ($tickets as $ticket) {
            try {
                if ($ticket->createAccountingRecordsIfNeeded()) {
                    $processed++;
                }
            } catch (\Throwable $e) {
                \Log::warning('Expired ticket immediate accounting failed', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }

    /**
     * Generate a unique tracking number.
     */
    public static function generateTrackingNumber()
    {
        do {
            $trackingNo = strtoupper(Str::random(3)) . '-' . 
                         strtoupper(Str::random(5)) . '-' . 
                         strtoupper(Str::random(3));
        } while (static::where('tracking_no', $trackingNo)->exists());
        
        return $trackingNo;
    }

    /**
     * Get the vehicle assigned to this ticket.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the driver assigned to this ticket.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the passengers for this ticket.
     */
    public function passengers()
    {
        return $this->hasMany(TicketPassenger::class);
    }

    /**
     * Get the location for this ticket.
     */
    public function location()
    {
        return $this->hasOne(TicketLocation::class);
    }

    /**
     * Get the tour for this ticket.
     */
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    /**
     * Get the agency for this ticket.
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the main accounting transaction for this ticket.
     */
    public function accountingTransaction()
    {
        return $this->belongsTo(Transaction::class, 'accounting_transaction_id');
    }

    /**
     * Get the user who created this ticket.
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the total price formatted.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 2) . ' ' . $this->currency;
    }

    /**
     * Get the deposit formatted.
     */
    public function getFormattedDepositAttribute()
    {
        return number_format($this->deposit, 2) . ' ' . $this->currency;
    }

    /**
     * Get the rest amount formatted.
     */
    public function getFormattedRestAttribute()
    {
        return number_format($this->rest, 2) . ' ' . $this->currency;
    }

    /**
     * Get nationality options
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
     * Get nationality display name
     */
    public function getNationalityNameAttribute()
    {
        $options = self::getNationalityOptions();
        return $options[$this->customer_nationality] ?? $this->customer_nationality ?? 'Belirtilmemiş';
    }

    /**
     * Get total passenger count
     */
    public function getTotalPassengersAttribute()
    {
        // If passengers relation is loaded, use it; otherwise query
        if ($this->relationLoaded('passengers')) {
            return $this->passengers->sum('quantity');
        }
        return $this->passengers()->sum('quantity');
    }

    /**
     * Get passenger breakdown (e.g., "2 Yetişkin, 1 Çocuk")
     */
    public function getPassengerBreakdownAttribute()
    {
        // Get passengers - either from loaded relation or query
        $passengers = $this->relationLoaded('passengers') ? $this->passengers : $this->passengers()->get();
        
        // Grup yolcu türlerine göre topla
        $grouped = [];
        
        foreach ($passengers as $passenger) {
            $type = '';
            switch ($passenger->passenger_type) {
                case 'adult':
                    $type = 'Yetişkin';
                    break;
                case 'child':
                    $type = 'Çocuk';
                    break;
                case 'infant':
                    $type = 'Bebek';
                    break;
                default:
                    $type = ucfirst($passenger->passenger_type);
            }
            
            // Aynı türdeki yolcuları topla
            if (!isset($grouped[$type])) {
                $grouped[$type] = 0;
            }
            $grouped[$type] += $passenger->quantity;
        }
        
        // Formatla
        $breakdown = [];
        foreach ($grouped as $type => $count) {
            if ($count > 0) {
                $breakdown[] = $count . ' ' . $type;
            }
        }
        
        return implode(', ', $breakdown) ?: 'Yolcu Bilgisi Yok';
    }

    /**
     * Get short passenger info (e.g., "3 Kişi")
     */
    public function getPassengerCountTextAttribute()
    {
        $total = $this->total_passengers;
        return $total > 0 ? $total . ' Kişi' : 'Yolcu Yok';
    }
} 