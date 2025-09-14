# Setup Multi-Database Feature

## Langkah-langkah Setup

### 1. Konfigurasi Environment
Tambahkan konfigurasi berikut ke file `.env`:

```env
# Database Utama (sudah ada)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Database Kedua (tambahkan ini)
DB_HOST_2=127.0.0.1
DB_PORT_2=3307
DB_DATABASE_2=laravel_second
DB_USERNAME_2=root
DB_PASSWORD_2=
```

### 2. Buat Database Kedua
```sql
-- Di MySQL/MariaDB
CREATE DATABASE laravel_second CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Jalankan Migration di Database Kedua
```bash
# Jalankan migration di database kedua
php artisan migrate --database=mysql_second

# Atau jalankan migration untuk semua database
php artisan migrate
```

### 4. Sync Data (Opsional)
```bash
# Sync semua data dari primary ke secondary
php artisan database:sync --from=primary --to=secondary

# Sync data tertentu saja
php artisan database:sync --from=primary --to=secondary --tables=users,chart_of_accounts
```

### 5. Test Koneksi
```bash
# Test koneksi database
php artisan tinker
>>> DB::connection('mysql')->getPdo();
>>> DB::connection('mysql_second')->getPdo();
```

## Cara Menggunakan

### 1. Via Interface Web
1. Buka aplikasi di browser
2. Klik dropdown database di navbar (kanan atas)
3. Pilih database yang diinginkan
4. Konfirmasi switch
5. Halaman akan reload dengan database baru

### 2. Via Halaman Kelola Database
1. Klik menu "Database Switch" di sidebar
2. Lihat status semua database
3. Test koneksi database
4. Switch database dengan konfirmasi
5. Reset ke database default

### 3. Via Command Line
```bash
# Sync database
php artisan database:sync --from=primary --to=secondary

# Recalculate saldo untuk database tertentu
php artisan accounting:recalculate-balances --period=1
```

### 4. Programmatic Usage
```php
// Query di database tertentu
$accounts = ChartOfAccount::onDatabase('secondary')->get();

// Get data dari semua database
$allAccounts = ChartOfAccount::fromAllDatabases();

// Sync model ke database lain
$account = ChartOfAccount::find(1);
$account->syncToDatabase('secondary');

// Bandingkan data antar database
$comparison = $account->compareWithDatabase('secondary');
```

## Troubleshooting

### Database Tidak Terhubung
1. Periksa konfigurasi di `.env`
2. Pastikan database server berjalan
3. Test koneksi via interface

### Error "Database not found"
1. Pastikan database sudah dibuat
2. Periksa nama database di konfigurasi
3. Pastikan user memiliki akses ke database

### Data Tidak Sinkron
1. Jalankan sync command
2. Periksa log error
3. Pastikan struktur tabel sama

### Session Reset
1. Clear browser cache
2. Restart session
3. Login ulang ke aplikasi

## Keamanan

1. **Permission**: Hanya user dengan permission 'manage accounting' yang bisa switch database
2. **Session**: Database pilihan disimpan di session, aman dari akses langsung
3. **Validation**: Semua input divalidasi sebelum diproses
4. **Logging**: Semua switch database dicatat untuk audit

## Performance

1. **Connection Pooling**: Setiap request menggunakan koneksi yang sesuai
2. **Caching**: Status database di-cache untuk performa yang lebih baik
3. **Lazy Loading**: Data hanya dimuat saat diperlukan
4. **Chunking**: Sync data dilakukan dalam chunk untuk menghindari memory limit
