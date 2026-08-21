php artisan serve --host=192.168.1.101 --port=8000
# myapp — Geliştirici Notları

Marmaris tur/transfer rezervasyon platformu. Aşağıda **yalnızca lokal geliştirme** için gerekli komutlar ve test giriş bilgileri yer alır.

> ⚠️ Bu dosya gerçek kullanıcı parolaları içerir — **üretim sunucusuna yüklemeyin**, public repoya commit'lemeyin.

---

## Test verisi (seeder)

Test şoförleri, araçları ve gerçekçi biletleri oluşturur:

```bash
php artisan db:seed --class=TestRouteSeeder
```

Tekrar çalıştırılırsa kayıtlar yeniden eklenir. Temizlemek için:
- E-posta: `sofor1@test.local`, `sofor2@test.local`
- Plaka: `SEED-TEST-1`, `SEED-TEST-2`

---

## Yerel sunucu

```bash
php artisan serve --host=192.168.1.101 --port=8000
```

Telefondan test için kendi LAN IP'nizi kullanın (`ipconfig` / `ifconfig`).

---

## Giriş bilgileri (lokal)

### 🛠️ Admin Paneli
- URL: `/login`

+- E-posta: `admin@example.com`
- Şifre: `admin123`

### 🏢 Sokak Acentası Paneli
- URL: `/login`
- E-posta: `agency@example.com`
- Şifre: `agency123`

### 🚐 Şoför Paneli (seeder'dan)
- URL: `/login`
- Şoför 1 — `sofor1@test.local` / `admin`
- Şoför 2 — `sofor2@test.local` / `admin`

### 🎫 Müşteri Paneli
- URL: `/customer/login`
- Voucher numarası ile giriş — biletlerden bir `voucher_no` kullanın
- Örnek: `24UF50NC` (biletten alınmış)

---

## Sık kullanılan komutlar

``bash
# View / cache temizleme
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Migration
php artisan migrate
```
