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
    <title>Rehber Listesi</title>
</head>
<body>
    <h2>Rehber Listesi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ad</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Lisans No</th>
                <th>Lisans Bitiş</th>
                <th>Desteklenen Milliyetler</th>
                <th>Şoför Sayısı</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guides as $g)
            <tr>
                <td>{{ $g->id }}</td>
                <td>{{ $g->name }}</td>
                <td>{{ $g->email }}</td>
                <td>{{ $g->phone }}</td>
                <td>{{ $g->license_number }}</td>
                <td>{{ optional($g->license_expiry)->format('Y-m-d') }}</td>
                <td>{{ $g->supported_nationalities_names }}</td>
                <td>{{ $g->drivers_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>



