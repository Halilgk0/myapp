<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bilet Bilgileri - {{ $ticket->tracking_no }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
    <style>
        #map {
            width: 100%;
            height: 400px;
            border-radius: 5px;
        }
        .location-status {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .location-sharing {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .location-not-sharing {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <form action="{{ route('tickets.logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">Bilet Bilgileri</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block">{{ $ticket->tracking_no }}</a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Bilet Bilgileri</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Ana Sayfa</a></li>
                                <li class="breadcrumb-item active">Bilet</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Konum Paylaşımı -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-map-marker-alt"></i> Konum Paylaşımı
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div id="location-status" class="location-status">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="status-text">Konum paylaşımı başlatılıyor...</span>
                                    </div>
                                    
                                    <button id="share-location-btn" class="btn btn-primary mb-3">
                                        <i class="fas fa-location-arrow"></i> Konum Paylaşımını Başlat
                                    </button>
                                    
                                    <div id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Bilet Bilgileri -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Tur Bilgileri</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <dl>
                                                <dt>Bilet Numarası</dt>
                                                <dd><strong>{{ $ticket->tracking_no }}</strong></dd>
                                                
                                                <dt>Müşteri Adı</dt>
                                                <dd>{{ $ticket->customer_name }}</dd>
                                                
                                                <dt>Müşteri Telefonu</dt>
                                                <dd>{{ $ticket->customer_phone }}</dd>
                                                
                                                <dt>Tur Adı</dt>
                                                <dd>{{ $ticket->tour_name }}</dd>
                                                
                                                <dt>Tur Tarihi</dt>
                                                <dd>{{ $ticket->tour_date->format('d.m.Y') }}</dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6">
                                            <dl>
                                                <dt>Alınış Saati</dt>
                                                <dd>{{ $ticket->pickup_time ? \Carbon\Carbon::parse($ticket->pickup_time)->format('H:i') : '-' }}</dd>
                                                
                                                <dt>Alınış Yeri</dt>
                                                <dd>{{ $ticket->pickup_location }}</dd>
                                                
                                                <dt>Oda Numarası</dt>
                                                <dd>{{ $ticket->room_number ?: 'Belirtilmemiş' }}</dd>
                                                
                                                <dt>Satış Acentası</dt>
                                                <dd>{{ $ticket->sales_agency }}</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Yolcu Bilgileri -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Yolcu Bilgileri</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Yolcu Tipi</th>
                                                    <th>Adet</th>
                                                    <th>Kişi Başı Fiyat</th>
                                                    <th>Toplam</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($ticket->passengers as $passenger)
                                                <tr>
                                                    <td>{{ $passenger->passenger_type_label }}</td>
                                                    <td>{{ $passenger->quantity }}</td>
                                                    <td>{{ $passenger->formatted_price_per_person }} {{ $ticket->currency }}</td>
                                                    <td>{{ $passenger->formatted_total_price }} {{ $ticket->currency }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Fiyat Bilgileri -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Fiyat Bilgileri</h3>
                                </div>
                                <div class="card-body">
                                    <dl>
                                        <dt>Toplam Fiyat</dt>
                                        <dd class="h4 text-primary">{{ $ticket->formatted_total_price }}</dd>
                                        
                                        <dt>Depozito</dt>
                                        <dd>{{ $ticket->formatted_deposit }}</dd>
                                        
                                        <dt>Kalan Tutar</dt>
                                        <dd class="h5 text-warning">{{ $ticket->formatted_rest }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <!-- Araç Bilgileri -->
                            @if($ticket->vehicle)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Araç Bilgileri</h3>
                                </div>
                                <div class="card-body">
                                    <dl>
                                        <dt>Plaka</dt>
                                        <dd>{{ $ticket->vehicle->plate_number }}</dd>
                                        
                                        <dt>Marka/Model</dt>
                                        <dd>{{ $ticket->vehicle->brand }} {{ $ticket->vehicle->model }}</dd>
                                        
                                        <dt>Kapasite</dt>
                                        <dd>{{ $ticket->vehicle->capacity }} kişi</dd>
                                        
                                        @if($ticket->vehicle->driver)
                                        <dt>Şoför</dt>
                                        <dd>{{ $ticket->vehicle->driver->name }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                            @endif

                            <!-- Notlar -->
                            @if($ticket->notes)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Notlar</h3>
                                </div>
                                <div class="card-body">
                                    <p>{{ $ticket->notes }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Bilet Sistemi
            </div>
            <strong>Copyright &copy; 2025</strong> Tüm hakları saklıdır.
        </footer>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBB5xkVxJJtkagn08AjRfE9pP3BqA8PvjM&language=tr"></script>
    
    <script>
        let map;
        let customerMarker = null;
        let watchId = null;
        let sharing = false;
        const ticketTrackingNo = '{{ $ticket->tracking_no }}';

        document.addEventListener('DOMContentLoaded', function() {
            const shareBtn = document.getElementById('share-location-btn');
            const statusEl = document.getElementById('status-text');
            const statusDiv = document.getElementById('location-status');

            initMap();

            shareBtn.addEventListener('click', () => {
                if (sharing) {
                    navigator.geolocation.clearWatch(watchId);
                    sharing = false;
                    statusEl.textContent = 'Konum paylaşımı durduruldu.';
                    statusDiv.className = 'location-status location-not-sharing';
                    shareBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Konum Paylaşımını Başlat';
                } else {
                    if (navigator.geolocation) {
                        watchId = navigator.geolocation.watchPosition(success, error, {
                            enableHighAccuracy: true,
                            maximumAge: 10000,
                            timeout: 10000
                        });
                        sharing = true;
                        statusEl.textContent = 'Konum paylaşımı başlatıldı...';
                        statusDiv.className = 'location-status location-sharing';
                        shareBtn.innerHTML = '<i class="fas fa-stop"></i> Konum Paylaşımını Durdur';
                    } else {
                        alert('Tarayıcınız konum paylaşımını desteklemiyor');
                    }
                }
            });

            function success(pos) {
                const { latitude, longitude, accuracy } = pos.coords;
                sendLocation(latitude, longitude, accuracy);
                updateCustomerMarker(latitude, longitude);
                statusEl.textContent = 'Konum güncellendi - ' + new Date().toLocaleTimeString();
            }

            function error() {
                statusEl.textContent = 'Konum alınamadı';
                statusDiv.className = 'location-status location-not-sharing';
            }

            function sendLocation(lat, lng, accuracy) {
                fetch('/api/tickets/location/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        tracking_no: ticketTrackingNo,
                        latitude: lat, 
                        longitude: lng, 
                        accuracy: accuracy 
                    })
                })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        console.error('Konum gönderimi başarısız:', res.message);
                    }
                })
                .catch(err => {
                    console.error('Konum gönderimi hatası:', err);
                });
            }

            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    center: { lat: 39.92, lng: 32.85 },
                    zoom: 6
                });
            }

            function updateCustomerMarker(lat, lng) {
                const pos = { lat: lat, lng: lng };
                if (customerMarker) {
                    customerMarker.setPosition(pos);
                } else {
                    customerMarker = new google.maps.Marker({
                        position: pos,
                        map: map,
                        icon: {
                            url: '/img/marker-costumer.png',
                            scaledSize: new google.maps.Size(30, 30)
                        },
                        title: 'Müşteri Konumu'
                    });
                }
                map.setCenter(pos);
            }
        });
    </script>
</body>
</html> 