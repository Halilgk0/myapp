<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        h2 { margin: 0 0 10px 0; }
    </style>
    <title>Bilet Listesi</title>
    </head>
<body>
    <h2>Bilet Listesi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Takip No</th>
                <th>Voucher</th>
                <th>Müşteri</th>
                <th>Telefon</th>
                <th>Oda</th>
                <th>Milliyet</th>
                <th>Tur</th>
                <th>Tarih</th>
                <th>Ülke</th>
                <th>Şehir</th>
                <th>Tutar</th>
                <th>PB</th>
                <th>Durum</th>
                <th>Araç</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->tracking_no }}</td>
                <td>{{ $t->voucher_no }}</td>
                <td>{{ $t->customer_name }}</td>
                <td>{{ $t->customer_phone }}</td>
                <td>{{ $t->room_number }}</td>
                <td>{{ $t->nationality_name }}</td>
                <td>{{ $t->tour_name }}</td>
                <td>{{ $t->tour_date ? $t->tour_date->format('Y-m-d') : '' }}</td>
                <td>{{ $t->tour_country }}</td>
                <td>{{ $t->tour_region }}</td>
                <td>{{ number_format($t->total_price, 2) }}</td>
                <td>{{ $t->currency }}</td>
                <td>{{ $t->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>{{ optional($t->vehicle)->plate_number }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>





