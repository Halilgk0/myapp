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
    <title>Tur Listesi</title>
</head>
<body>
    <h2>Tur Listesi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ad</th>
                <th>Ülke</th>
                <th>Şehir</th>
                <th>İlçe</th>
                <th>Alış Saati</th>
                <th>Fiyat (Max)</th>
                <th>PB</th>
                <th>Kapasite</th>
                <th>Aktif</th>
                <th>Toplam Bilet</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tours as $t)
            <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->name }}</td>
                <td>{{ $t->country }}</td>
                <td>{{ $t->city }}</td>
                <td>{{ $t->district }}</td>
                <td>{{ $t->earliest_service_area_time ?? optional($t->pickup_time)->format('H:i') ?? '-' }}</td>
                @php
                    $maxPrice = (float) ($t->max_display_price ?? 0);
                    $currencyLabel = $t->display_currency ?? ($t->currency ?? 'TRY');
                @endphp
                <td>{{ $maxPrice > 0 ? number_format($maxPrice, 2) : '-' }}</td>
                <td>{{ $currencyLabel }}</td>
                <td>{{ $t->max_capacity }}</td>
                <td>{{ $t->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>{{ $t->total_tickets }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>



