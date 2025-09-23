# Accounting System Seeders

Kumpulan seeder untuk sistem akuntansi lengkap dengan data sample untuk testing dan development.

## Daftar Seeders

### 1. **BarangSeeder.php**
- Membuat data master barang lengkap
- Grup Barang (5 kategori)
- Kode Barang (11 item dengan berbagai kategori)
- Stock Records (sesuai kode barang)
- Stock Batches (2-4 batch per item untuk testing FIFO)
- **Jalankan**: `php artisan db:seed --class=BarangSeeder`

### 2. **CustomerSupplierSeeder.php**
- Membuat data Customer (5 customer)
- Membuat data Supplier (5 supplier)
- Set credit limit untuk customer
- **Jalankan**: `php artisan db:seed --class=CustomerSupplierSeeder`

### 3. **PenjualanSeeder.php**
- Membuat 20 transaksi penjualan
- Mix tunai dan kredit
- Menggunakan FIFO untuk alokasi stok
- Menghitung PPN otomatis
- Update saldo customer
- **Jalankan**: `php artisan db:seed --class=PenjualanSeeder`

### 4. **PembelianSeeder.php**
- Membuat 15 transaksi pembelian
- Mix tunai dan kredit
- Membuat stock batch baru
- Update stock quantity
- **Jalankan**: `php artisan db:seed --class=PembelianSeeder`

### 5. **ReturSeeder.php**
- Membuat 5 retur penjualan
- Membuat 3 retur pembelian
- Update stock quantity
- Update credit customer
- **Jalankan**: `php artisan db:seed --class=ReturSeeder`

### 6. **PembayaranSeeder.php**
- Membuat 8 pembayaran piutang (AR)
- Membuat 6 pembayaran utang (AP)
- Update status piutang/utang
- **Jalankan**: `php artisan db:seed --class=PembayaranSeeder`

### 7. **JournalSeeder.php**
- Membuat 10 jurnal akuntansi sample
- Mencakup semua jenis transaksi
- Menggunakan AccountingService
- **Jalankan**: `php artisan db:seed --class=JournalSeeder`

### 8. **CompleteAccountingSeeder.php**
- Menjalankan semua seeder di atas secara berurutan
- Menampilkan summary data
- **Jalankan**: `php artisan db:seed --class=CompleteAccountingSeeder`

## Cara Penggunaan

### Option 1: Jalankan Semua Sekaligus
```bash
php artisan db:seed --class=CompleteAccountingSeeder
```

### Option 2: Jalankan Per Modul
```bash
# Basic setup
php artisan db:seed --class=ChartOfAccountsSeeder
php artisan db:seed --class=AccountingPeriodSeeder

# Master data
php artisan db:seed --class=BarangSeeder
php artisan db:seed --class=CustomerSupplierSeeder

# Transactions
php artisan db:seed --class=PembelianSeeder
php artisan db:seed --class=PenjualanSeeder
php artisan db:seed --class=ReturSeeder
php artisan db:seed --class=PembayaranSeeder

# Journals
php artisan db:seed --class=JournalSeeder
```

### Option 3: Reset dan Jalankan Ulang
```bash
# Reset database
php artisan migrate:fresh

# Jalankan seeder lengkap
php artisan db:seed --class=CompleteAccountingSeeder
```

## Data yang Dibuat

### Master Data
- **5 Grup Barang**: Plastik Lembaran, Gulungan, Kemasan, Industri, Aksesoris
- **11 Kode Barang**: Dengan harga beli/jual dan berbagai satuan
- **5 Customer**: Dengan credit limit 15-75 juta
- **5 Supplier**: Untuk pembelian

### Transaction Data
- **20 Penjualan**: Mix tunai/kredit, dengan FIFO allocation
- **15 Pembelian**: Mix tunai/kredit, dengan stock batch
- **5 Retur Penjualan**: 20-80% dari qty asli
- **3 Retur Pembelian**: 10-50% dari qty asli
- **8 Pembayaran AR**: 50-100% dari sisa piutang
- **6 Pembayaran AP**: 60-100% dari total pembelian

### Journal Data
- **10 Jurnal Sample**: Mencakup semua skenario akuntansi
- **Balanced Entries**: Debit = Credit
- **Realistic Amounts**: Rp 100K - 1.5M per transaksi

## Dependencies

Pastikan seeder dijalankan dalam urutan yang benar:

1. **ChartOfAccountsSeeder** (COA harus ada dulu)
2. **AccountingPeriodSeeder** (Periode akuntansi)
3. **BarangSeeder** (Master barang)
4. **CustomerSupplierSeeder** (Master customer/supplier)
5. **PembelianSeeder** (Pembelian dulu, untuk stock)
6. **PenjualanSeeder** (Penjualan, butuh stock)
7. **ReturSeeder** (Retur, butuh transaksi)
8. **PembayaranSeeder** (Pembayaran, butuh piutang/utang)
9. **JournalSeeder** (Jurnal, butuh semua data)

## Testing Scenarios

Setelah menjalankan seeders, Anda bisa test:

### 1. **FIFO Allocation**
- Cek `transaksi_item_sumbers` untuk alokasi batch
- Cek `stock_batches` untuk qty_sisa yang berkurang

### 2. **PPN Calculation**
- Cek field `ppn` di transaksi/pembelian
- Pastikan hanya DB2 yang ada PPN

### 3. **Credit Management**
- Cek `sisa_kredit` customer berkurang saat penjualan kredit
- Cek `sisa_kredit` bertambah saat pembayaran

### 4. **Stock Movement**
- Cek `stocks.good_stock` bertambah saat pembelian
- Cek `stocks.good_stock` berkurang saat penjualan
- Cek `stocks.good_stock` bertambah saat retur penjualan

### 5. **Journal Entries**
- Cek `journals` dan `journal_details`
- Pastikan semua jurnal balanced (debit = credit)
- Cek `chart_of_accounts.balance` terupdate

## Troubleshooting

### Error: "COA not found"
- Pastikan `ChartOfAccountsSeeder` dijalankan dulu
- Cek nama akun di seeder sesuai dengan COA yang ada

### Error: "Customer/Supplier not found"
- Pastikan `CustomerSupplierSeeder` dijalankan dulu
- Cek kode customer/supplier di seeder

### Error: "Insufficient stock"
- Pastikan `PembelianSeeder` dijalankan sebelum `PenjualanSeeder`
- Cek stock quantity di `stocks` table

### Error: "FIFO allocation failed"
- Cek `stock_batches` ada data
- Cek `qty_sisa` > 0
- Cek `kode_barang_id` sesuai

## Customization

Untuk mengubah data sample:

1. **Ubah jumlah record**: Edit variabel `$count` di setiap seeder
2. **Ubah range harga**: Edit `$harga_beli` dan `$harga_jual` di BarangSeeder
3. **Ubah range tanggal**: Edit `now()->subDays(rand(0, 30))` di setiap seeder
4. **Ubah range quantity**: Edit `rand(1, 10)` di PenjualanSeeder

## Notes

- Semua seeder menggunakan `firstOrCreate` untuk menghindari duplikasi
- Data sample menggunakan tanggal random dalam 30 hari terakhir
- Harga dan quantity menggunakan range yang realistis
- FIFO allocation menggunakan service yang sudah ada
- PPN calculation menggunakan PpnService yang sudah ada
