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
    <title>Şoför Listesi</title>
</head>
<body>
    <h2>Şoför Listesi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ad</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Aktif</th>
                <th>Araç</th>
                <th>Rehber</th>
                <th>Desteklenen Milliyetler</th>
                <th>Son Giriş</th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $d)
            <tr>
                <td>{{ $d->id }}</td>
                <td>{{ $d->name }}</td>
                <td>{{ $d->email }}</td>
                <td>{{ $d->phone_number }}</td>
                <td>{{ $d->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>{{ optional($d->vehicle)->plate_number }}</td>
                <td>{{ optional($d->guide)->name }}</td>
                <td>{{ $d->supported_nationalities_names ?? ($d->supported_nationalities ? implode(', ', $d->supported_nationalities) : 'Tüm milliyetler') }}</td>
                <td>{{ optional($d->last_login_at)->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>



