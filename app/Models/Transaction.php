<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',               // income | expense
        'title',
        'amount',
        'currency',
        'transaction_date',
        'payment_method',
        'status',            // paid | pending | cancelled
        'is_settled',
        'settled_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'is_settled' => 'boolean',
        'settled_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Otomatik muhasebeleştirilen bilet kaydı (varsa)
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'accounting_transaction_id');
    }

    /**
     * Başlıkta veya bağlı bilet takip numarasında arama.
     */
    public function scopeWhereMatchesAccountingSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = '%' . addcslashes(trim($search), '%_\\') . '%';
        $table = $query->getModel()->getTable();

        return $query->where(function ($w) use ($term, $table) {
            $w->where("{$table}.title", 'like', $term)
                ->orWhereExists(function ($sub) use ($term, $table) {
                    $sub->select(DB::raw(1))
                        ->from('tickets')
                        ->where('tickets.tracking_no', 'like', $term)
                        ->where(function ($t) use ($table) {
                            $t->whereColumn('tickets.accounting_transaction_id', "{$table}.id")
                                ->orWhereColumn('tickets.accounting_owner_transaction_id', "{$table}.id")
                                ->orWhereColumn('tickets.accounting_rest_transaction_id', "{$table}.id")
                                ->orWhereColumn('tickets.accounting_agency_income_transaction_id', "{$table}.id")
                                ->orWhereColumn('tickets.accounting_agency_payout_transaction_id', "{$table}.id");
                        });
                });
        });
    }

    /**
     * Satırları herhangi bir bilet muhasebe FK'si ile eşleştirir (N+1 önleme).
     * İşlem üzerindeki ticket ilişkisini günceller.
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|iterable  $transactions
     */
    public static function attachLinkedTickets($transactions): void
    {
        $collection = collect($transactions);
        if ($collection->isEmpty()) {
            return;
        }

        $ids = $collection->pluck('id')->unique()->filter()->map(fn ($id) => (int) $id)->values()->all();
        if ($ids === []) {
            return;
        }

        $fkColumns = [
            'accounting_transaction_id',
            'accounting_owner_transaction_id',
            'accounting_rest_transaction_id',
            'accounting_agency_income_transaction_id',
            'accounting_agency_payout_transaction_id',
        ];

        $tickets = Ticket::query()
            ->where(function ($q) use ($ids, $fkColumns) {
                foreach ($fkColumns as $i => $col) {
                    if ($i === 0) {
                        $q->whereIn($col, $ids);
                    } else {
                        $q->orWhereIn($col, $ids);
                    }
                }
            })
            ->get(array_merge(
                ['id', 'tracking_no', 'created_by_user_id'],
                $fkColumns
            ));

        $txToTicket = [];
        foreach ($tickets as $ticket) {
            foreach ($fkColumns as $col) {
                $tid = (int) ($ticket->{$col} ?? 0);
                if ($tid > 0 && !isset($txToTicket[$tid])) {
                    $txToTicket[$tid] = $ticket;
                }
            }
        }

        foreach ($collection as $tx) {
            $tid = (int) $tx->id;
            $tx->setRelation('ticket', $txToTicket[$tid] ?? null);
        }
    }
}



