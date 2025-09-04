<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\TransaksiItem;
use Carbon\Carbon;

class OverdueTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating overdue test data...\n";

        // Pastikan customer dan barang ada
        $customer = Customer::first();
        $barang = KodeBarang::first();

        if (!$customer || !$barang) {
            echo "Error: Customer or barang not found. Please run other seeders first.\n";
            return;
        }

        $today = Carbon::now();
        
        // Data transaksi terlambat 1-15 hari
        $overdueData = [
            // Terlambat 1-5 hari (Biru)
            [
                'no_transaksi' => 'KP/WS/OV001',
                'tanggal' => $today->copy()->subDays(20),
                'tanggal_jadi' => $today->copy()->subDays(20),
                'hari_tempo' => 15, // Jatuh tempo 5 hari yang lalu
                'grand_total' => 1500000,
                'total_dibayar' => 0,
                'sisa_piutang' => 1500000,
                'status_piutang' => 'belum_dibayar',
                'hari_keterlambatan' => 5
            ],
            [
                'no_transaksi' => 'KP/WS/OV002',
                'tanggal' => $today->copy()->subDays(18),
                'tanggal_jadi' => $today->copy()->subDays(18),
                'hari_tempo' => 12, // Jatuh tempo 6 hari yang lalu
                'grand_total' => 2500000,
                'total_dibayar' => 1000000,
                'sisa_piutang' => 1500000,
                'status_piutang' => 'sebagian',
                'hari_keterlambatan' => 6
            ],
            [
                'no_transaksi' => 'KP/WS/OV003',
                'tanggal' => $today->copy()->subDays(25),
                'tanggal_jadi' => $today->copy()->subDays(25),
                'hari_tempo' => 20, // Jatuh tempo 5 hari yang lalu
                'grand_total' => 800000,
                'total_dibayar' => 0,
                'sisa_piutang' => 800000,
                'status_piutang' => 'belum_dibayar',
                'hari_keterlambatan' => 5
            ],
            
            // Terlambat 6-10 hari (Biru)
            [
                'no_transaksi' => 'KP/WS/OV004',
                'tanggal' => $today->copy()->subDays(30),
                'tanggal_jadi' => $today->copy()->subDays(30),
                'hari_tempo' => 20, // Jatuh tempo 10 hari yang lalu
                'grand_total' => 3200000,
                'total_dibayar' => 0,
                'sisa_piutang' => 3200000,
                'status_piutang' => 'belum_dibayar',
                'hari_keterlambatan' => 10
            ],
            [
                'no_transaksi' => 'KP/WS/OV005',
                'tanggal' => $today->copy()->subDays(28),
                'tanggal_jadi' => $today->copy()->subDays(28),
                'hari_tempo' => 18, // Jatuh tempo 10 hari yang lalu
                'grand_total' => 1800000,
                'total_dibayar' => 500000,
                'sisa_piutang' => 1300000,
                'status_piutang' => 'sebagian',
                'hari_keterlambatan' => 10
            ],
            
            // Terlambat 11-15 hari (Biru)
            [
                'no_transaksi' => 'KP/WS/OV006',
                'tanggal' => $today->copy()->subDays(35),
                'tanggal_jadi' => $today->copy()->subDays(35),
                'hari_tempo' => 20, // Jatuh tempo 15 hari yang lalu
                'grand_total' => 4500000,
                'total_dibayar' => 0,
                'sisa_piutang' => 4500000,
                'status_piutang' => 'belum_dibayar',
                'hari_keterlambatan' => 15
            ],
            [
                'no_transaksi' => 'KP/WS/OV007',
                'tanggal' => $today->copy()->subDays(40),
                'tanggal_jadi' => $today->copy()->subDays(40),
                'hari_tempo' => 25, // Jatuh tempo 15 hari yang lalu
                'grand_total' => 2200000,
                'total_dibayar' => 800000,
                'sisa_piutang' => 1400000,
                'status_piutang' => 'sebagian',
                'hari_keterlambatan' => 15
            ],
            
            // Terlambat 16-30 hari (Kuning) untuk perbandingan
            [
                'no_transaksi' => 'KP/WS/OV008',
                'tanggal' => $today->copy()->subDays(50),
                'tanggal_jadi' => $today->copy()->subDays(50),
                'hari_tempo' => 30, // Jatuh tempo 20 hari yang lalu
                'grand_total' => 5000000,
                'total_dibayar' => 0,
                'sisa_piutang' => 5000000,
                'status_piutang' => 'belum_dibayar',
                'hari_keterlambatan' => 20
            ],
            
            // Terlambat >30 hari (Merah) untuk perbandingan
            [
                'no_transaksi' => 'KP/WS/OV009',
                'tanggal' => $today->copy()->subDays(70),
                'tanggal_jadi' => $today->copy()->subDays(70),
                'hari_tempo' => 30, // Jatuh tempo 40 hari yang lalu
                'grand_total' => 6000000,
                'total_dibayar' => 0,
                'sisa_piutang' => 6000000,
                'status_piutang' => 'belum_dibayar',
                'hari_keterlambatan' => 40
            ]
        ];

        foreach ($overdueData as $data) {
            // Hitung tanggal jatuh tempo
            $tanggalJatuhTempo = Carbon::parse($data['tanggal_jadi'])->addDays($data['hari_tempo']);
            
            // Buat transaksi
            $transaksi = Transaksi::create([
                'no_transaksi' => $data['no_transaksi'],
                'tanggal' => $data['tanggal'],
                'tanggal_jadi' => $data['tanggal_jadi'],
                'hari_tempo' => $data['hari_tempo'],
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'kode_customer' => $customer->kode_customer,
                'grand_total' => $data['grand_total'],
                'total_dibayar' => $data['total_dibayar'],
                'sisa_piutang' => $data['sisa_piutang'],
                'status_piutang' => $data['status_piutang'],
                'status' => 'completed',
            ]);

            // Buat item transaksi
            TransaksiItem::create([
                'transaksi_id' => $transaksi->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->name,
                'qty' => 1,
                'satuan' => 'pcs',
                'harga' => $data['grand_total'],
                'subtotal' => $data['grand_total'],
                'ongkos_kuli' => 0,
            ]);

            echo "Created: {$data['no_transaksi']} - Terlambat {$data['hari_keterlambatan']} hari - Rp " . number_format($data['grand_total'], 0, ',', '.') . "\n";
        }

        echo "\nOverdue test data created successfully!\n";
        echo "Total transactions created: " . count($overdueData) . "\n";
        echo "\nSummary:\n";
        echo "- 1-5 hari terlambat: 3 transaksi (Biru)\n";
        echo "- 6-10 hari terlambat: 2 transaksi (Biru)\n";
        echo "- 11-15 hari terlambat: 2 transaksi (Biru)\n";
        echo "- 16-30 hari terlambat: 1 transaksi (Kuning)\n";
        echo "- >30 hari terlambat: 1 transaksi (Merah)\n";
    }
}
