<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TicketsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Takip No',
            'Voucher No',
            'Müşteri Adı',
            'Telefon',
            'Oda No',
            'Milliyet',
            'Tur Adı',
            'Tur Tarihi',
            'Ülke',
            'Şehir',
            'Toplam Fiyat',
            'Para Birimi',
            'Aktif',
            'Araç Plaka'
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->tracking_no,
            $ticket->voucher_no,
            $ticket->customer_name,
            $ticket->customer_phone,
            $ticket->room_number,
            $ticket->nationality_name,
            $ticket->tour_name,
            optional($ticket->tour_date)->format('Y-m-d'),
            $ticket->tour_country,
            $ticket->tour_region,
            $ticket->total_price,
            $ticket->currency,
            $ticket->is_active ? 'Aktif' : 'Pasif',
            optional($ticket->vehicle)->plate_number,
        ];
    }
}



