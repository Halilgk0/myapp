<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet Listesi</title>
    <style>
        body { font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 12pt; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d9dde3; padding: 6px 8px; }
        th { background: #f3f5f7; font-weight: 700; color: #1f2937; }
        tr:nth-child(even) td { background: #fafbfc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; background: #e9eef5; }
        /* Excel number formats */
        .fmt-date { mso-number-format: "yyyy-mm-dd"; }
        .fmt-money { mso-number-format: "#,##0.00"; }
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Takip No</th>
            <th>Voucher No</th>
            <th>Müşteri Adı</th>
            <th>Telefon</th>
            <th>Oda No</th>
            <th>Milliyet</th>
            <th>Tur Adı</th>
            <th>Tur Tarihi</th>
            <th>Ülke</th>
            <th>Şehir</th>
            <th class="text-right">Toplam Fiyat</th>
            <th>Para Birimi</th>
            <th>Aktif</th>
            <th>Araç Plaka</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $t)
            <tr>
                <td class="text-center">{{ $t->id }}</td>
                <td>{{ $t->tracking_no }}</td>
                <td>{{ $t->voucher_no }}</td>
                <td>{{ $t->customer_name }}</td>
                <td>{{ $t->customer_phone }}</td>
                <td>{{ $t->room_number }}</td>
                <td>{{ $t->nationality_name }}</td>
                <td>{{ $t->tour_name }}</td>
                <td class="fmt-date">{{ $t->tour_date ? $t->tour_date->format('Y-m-d') : '' }}</td>
                <td>{{ $t->tour_country }}</td>
                <td>{{ $t->tour_region }}</td>
                <td class="text-right fmt-money">{{ number_format((float)$t->total_price, 2, '.', '') }}</td>
                <td>{{ $t->currency }}</td>
                <td>{{ $t->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>{{ optional($t->vehicle)->plate_number }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>





