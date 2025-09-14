# Fitur Multi-Database

## Deskripsi
Fitur ini memungkinkan aplikasi untuk menggunakan 2 database yang sama persis dan dapat beralih antara database melalui interface web. Database kedua dapat digunakan untuk backup, testing, atau environment yang berbeda.

## Konfigurasi

### 1. Environment Variables
Tambahkan konfigurasi database kedua di file `.env`:

```env
# Database utama
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Database kedua
DB_HOST_2=127.0.0.1
DB_PORT_2=3307
DB_DATABASE_2=laravel_second
DB_USERNAME_2=root
DB_PASSWORD_2=
```

### 2. Database Configuration
Konfigurasi database sudah ditambahkan di `config/database.php`:

```php
'available_databases' => [
    'primary' => [
        'name' => 'Database Utama',
        'connection' => 'mysql',
        'description' => 'Database utama untuk operasi normal'
    ],
    'secondary' => [
        'name' => 'Database Kedua',
        'connection' => 'mysql_second',
        'description' => 'Database kedua untuk backup atau testing'
    ],
],
```

## Komponen yang Dibuat

### 1. DatabaseSwitchService
Service untuk mengelola switching database:
- `getCurrentDatabase()` - Get database saat ini
- `setCurrentDatabase($key)` - Set database aktif
- `switchTo($key)` - Switch ke database tertentu
- `testConnection($key)` - Test koneksi database
- `getDatabaseStatus()` - Get status semua database

### 2. DatabaseSwitchMiddleware
Middleware yang otomatis mengatur koneksi database berdasarkan session:
- Mengatur default connection berdasarkan pilihan user
- Menambahkan info database ke request

### 3. DatabaseSwitchController
Controller untuk handle database switching:
- `index()` - Tampilkan interface database switch
- `switch()` - Switch ke database tertentu (AJAX)
- `status()` - Get status database (AJAX)
- `testConnection()` - Test koneksi database (AJAX)
- `reset()` - Reset ke database default

### 4. MultiDatabaseTrait
Trait untuk model yang mendukung multi database:
- `onDatabase($key)` - Query model di database tertentu
- `fromAllDatabases()` - Get data dari semua database
- `syncToDatabase($key)` - Sync model ke database tertentu
- `compareWithDatabase($key)` - Bandingkan dengan database lain

### 5. SyncDatabaseCommand
Command untuk sync data antar database:
```bash
# Sync semua tabel dari primary ke secondary
php artisan database:sync --from=primary --to=secondary

# Sync tabel tertentu
php artisan database:sync --from=primary --to=secondary --tables=users,posts

# Force sync tanpa konfirmasi
php artisan database:sync --from=primary --to=secondary --force
```

## Interface Pengguna

### 1. Database Switcher di Navbar
- Dropdown di navbar untuk switch database
- Menampilkan database aktif
- Status koneksi (terhubung/tidak terhubung)
- Link ke halaman kelola database

### 2. Halaman Kelola Database
- `/database-switch` - Interface lengkap untuk kelola database
- Tampilkan info database (ukuran, jumlah tabel)
- Test koneksi database
- Switch database dengan konfirmasi
- Reset ke database default

## Cara Penggunaan

### 1. Setup Database Kedua
1. Buat database kedua dengan struktur yang sama
2. Update konfigurasi di `.env`
3. Jalankan migration di database kedua:
```bash
php artisan migrate --database=mysql_second
```

### 2. Sync Data
```bash
# Sync semua data dari primary ke secondary
php artisan database:sync --from=primary --to=secondary

# Sync data dari secondary ke primary
php artisan database:sync --from=secondary --to=primary
```

### 3. Switch Database via Interface
1. Klik dropdown database di navbar
2. Pilih database yang diinginkan
3. Konfirmasi switch
4. Halaman akan reload dengan database baru

### 4. Programmatic Usage
```php
// Query model di database tertentu
$accounts = ChartOfAccount::onDatabase('secondary')->get();

// Get data dari semua database
$allAccounts = ChartOfAccount::fromAllDatabases();

// Sync model ke database lain
$account = ChartOfAccount::find(1);
$account->syncToDatabase('secondary');

// Bandingkan data antar database
$comparison = $account->compareWithDatabase('secondary');
```

## Keuntungan

1. **Backup & Recovery**: Database kedua dapat digunakan sebagai backup
2. **Testing**: Test fitur baru di database terpisah
3. **Environment Separation**: Pisahkan data production dan development
4. **Data Migration**: Mudah migrasi data antar database
5. **Zero Downtime**: Switch database tanpa restart aplikasi
6. **Data Comparison**: Bandingkan data antar database

## Catatan Penting

1. **Struktur Database**: Kedua database harus memiliki struktur yang sama
2. **Session Management**: Database pilihan disimpan di session
3. **Connection Pooling**: Setiap request menggunakan koneksi yang sesuai
4. **Data Consistency**: Pastikan data tetap konsisten antar database
5. **Performance**: Switching database tidak mempengaruhi performance

## Troubleshooting

### Database Tidak Terhubung
1. Periksa konfigurasi di `.env`
2. Pastikan database server berjalan
3. Test koneksi via interface atau command

### Data Tidak Sinkron
1. Jalankan sync command
2. Periksa log error
3. Pastikan struktur tabel sama

### Session Reset
1. Clear browser cache
2. Restart session
3. Login ulang ke aplikasi
