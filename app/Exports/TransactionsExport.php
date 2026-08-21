<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $rows = $this->query->get();
        Transaction::attachLinkedTickets($rows);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tarih',
            'Başlık',
            'Takip No',
            'Tür',
            'Tutar',
            'Para Birimi',
            'Ödeme Yöntemi',
            'Durum',
            'Notlar',
            'Oluşturan',
            'Oluşturma Tarihi',
        ];
    }

    public function map($transaction): array
    {
        $typeMap = [
            'income' => 'Gelir',
            'expense' => 'Gider',
        ];

        $statusMap = [
            'paid' => 'Ödendi',
            'pending' => 'Beklemede',
            'cancelled' => 'İptal',
        ];

        return [
            $transaction->id,
            $transaction->transaction_date->format('d.m.Y'),
            $transaction->title,
            optional($transaction->ticket)->tracking_no ?? '-',
            $typeMap[$transaction->type] ?? $transaction->type,
            number_format($transaction->amount, 2, ',', '.'),
            $transaction->currency,
            $transaction->payment_method ?? '-',
            $statusMap[$transaction->status] ?? $transaction->status,
            $transaction->notes ?? '-',
            optional($transaction->creator)->name ?? '-',
            $transaction->created_at->format('d.m.Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '002b5c'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }
}








































