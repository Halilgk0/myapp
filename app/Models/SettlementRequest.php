<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'admin_user_id',
        'agency_user_id',
        'ticket_ids',
        'transaction_ids',
        'status',
        'note',
        'approved_by_agency_at',
        'rejected_at',
        'processed_at',
    ];

    protected $casts = [
        'ticket_ids' => 'array',
        'transaction_ids' => 'array',
        'approved_by_agency_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function agencyUser()
    {
        return $this->belongsTo(User::class, 'agency_user_id');
    }

    /**
     * Mark settlement-eligible accounting lines for tickets as settled.
     * Preserves street-agency sale income rows (payment_method = sale-ticket / agency income link).
     * Records are NOT deleted; they remain visible in the list with is_settled = true.
     *
     * @param  array<int>  $ticketIds
     * @param  array<int>|null  $fallbackTransactionIds  Used when tickets lack FKs (legacy)
     * @return int Number of transaction rows marked as settled
     */
    public static function applyTicketSettlementRemovals(array $ticketIds, ?array $fallbackTransactionIds = null): int
    {
        $ticketIds = collect($ticketIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ticketIds === []) {
            return 0;
        }

        $settlableTransactionIds = [];
        $tickets = Ticket::whereIn('id', $ticketIds)->get([
            'accounting_transaction_id',
            'accounting_owner_transaction_id',
            'accounting_rest_transaction_id',
            'accounting_agency_income_transaction_id',
            'accounting_agency_payout_transaction_id',
        ]);

        $settlableTransactionIds = $tickets->flatMap(function (Ticket $ticket) {
            return [
                $ticket->accounting_transaction_id,
                $ticket->accounting_owner_transaction_id,
                $ticket->accounting_rest_transaction_id,
                $ticket->accounting_agency_payout_transaction_id,
            ];
        })->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        if ($settlableTransactionIds === [] && !empty($fallbackTransactionIds)) {
            $fallbackIds = collect($fallbackTransactionIds)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $settlableTransactionIds = Transaction::whereIn('id', $fallbackIds)
                ->where(function ($q) {
                    $q->whereNull('payment_method')
                        ->orWhere('payment_method', '!=', 'sale-ticket');
                })
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if ($settlableTransactionIds === []) {
            return 0;
        }

        return Transaction::whereIn('id', $settlableTransactionIds)
            ->where('is_settled', false)
            ->update([
                'is_settled' => true,
                'settled_at' => now(),
            ]);
    }
}

