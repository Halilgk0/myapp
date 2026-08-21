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
    <title>Araç Listesi</title>
</head>
<body>
    <h2>Araç Listesi</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Plaka</th>
                <th>Marka</th>
                <th>Model</th>
                <th>Tip</th>
                <th>Kapasite</th>
                <th>Renk</th>
                <th>Şoför</th>
                <th>Durum</th>
                <th>Notlar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $v)
            <tr>
                <td>{{ $v->id }}</td>
                <td>{{ $v->plate_number }}</td>
                <td>{{ $v->brand }}</td>
                <td>{{ $v->model }}</td>
                <td>{{ $v->vehicle_type }}</td>
                <td>{{ $v->capacity }}</td>
                <td>{{ $v->color }}</td>
                <td>{{ optional($v->driver)->name }}</td>
                <td>{{ $v->is_active ? 'Aktif' : 'Pasif' }}</td>
                <td>{{ $v->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>



