<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Transaksi;
use App\Models\Kas;
use Exception;
use Carbon\Carbon;

class CustomerCreditService
{
    /**
     * Hitung total piutang customer
     * 
     * @param int $customerId
     * @return array
     */
    public function hitungPiutang(int $customerId): array
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw new Exception("Customer tidak ditemukan");
        }

        // Ambil semua transaksi kredit yang belum lunas
        $transaksiKredit = Transaksi::where('kode_customer', $customer->kode_customer)
            ->where('cara_bayar', '!=', 'Tunai')
            ->where('status', '!=', 'canceled')
            ->get();

        $totalPiutang = 0;
        $piutangJatuhTempo = 0;
        $detailPiutang = [];

        foreach ($transaksiKredit as $transaksi) {
            // Hitung sisa piutang untuk transaksi ini
            $sisaPiutang = $this->hitungSisaPiutangTransaksi($transaksi->id);
            
            if ($sisaPiutang > 0) {
                $totalPiutang += $sisaPiutang;
                
                // Cek apakah jatuh tempo
                $tanggalJatuhTempo = Carbon::parse($transaksi->tanggal)->addDays($customer->limit_hari_tempo);
                $isJatuhTempo = Carbon::now()->isAfter($tanggalJatuhTempo);
                
                if ($isJatuhTempo) {
                    $piutangJatuhTempo += $sisaPiutang;
                }

                $detailPiutang[] = [
                    'transaksi_id' => $transaksi->id,
                    'no_transaksi' => $transaksi->no_transaksi,
                    'tanggal' => $transaksi->tanggal,
                    'total_transaksi' => $transaksi->grand_total,
                    'sisa_piutang' => $sisaPiutang,
                    'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('Y-m-d'),
                    'is_jatuh_tempo' => $isJatuhTempo,
                    'hari_terlambat' => $isJatuhTempo ? Carbon::now()->diffInDays($tanggalJatuhTempo) : 0
                ];
            }
        }

        return [
            'customer' => $customer,
            'total_piutang' => $totalPiutang,
            'piutang_jatuh_tempo' => $piutangJatuhTempo,
            'sisa_limit_kredit' => $customer->limit_kredit - $totalPiutang,
            'detail_piutang' => $detailPiutang,
            'status_kredit' => $this->getStatusKredit($customer, $totalPiutang)
        ];
    }

    /**
     * Hitung sisa piutang untuk transaksi tertentu
     * 
     * @param int $transaksiId
     * @return float
     */
    public function hitungSisaPiutangTransaksi(int $transaksiId): float
    {
        $transaksi = Transaksi::find($transaksiId);
        if (!$transaksi) {
            return 0;
        }

        // Total pembayaran yang sudah dilakukan
        $totalBayar = Kas::where('name', 'like', "%{$transaksi->no_transaksi}%")
            ->where('type', 'Kredit')
            ->sum('qty');

        return max(0, $transaksi->grand_total - $totalBayar);
    }

    /**
     * Cek apakah customer bisa melakukan transaksi kredit
     * 
     * @param int $customerId
     * @param float $nilaiTransaksi
     * @return array
     */
    public function cekKelayakanKredit(int $customerId, float $nilaiTransaksi): array
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw new Exception("Customer tidak ditemukan");
        }

        // Jika customer tunai, tidak bisa kredit
        if ($customer->isTunai()) {
            return [
                'layak' => false,
                'alasan' => 'Customer hanya dapat melakukan transaksi tunai',
                'limit_kredit' => 0,
                'sisa_limit' => 0
            ];
        }

        // Hitung total piutang saat ini
        $piutangInfo = $this->hitungPiutang($customerId);
        $totalPiutang = $piutangInfo['total_piutang'];
        $sisaLimit = $customer->limit_kredit - $totalPiutang;

        // Cek apakah masih ada sisa limit
        if ($sisaLimit <= 0) {
            return [
                'layak' => false,
                'alasan' => 'Limit kredit sudah habis',
                'limit_kredit' => $customer->limit_kredit,
                'sisa_limit' => 0,
                'total_piutang' => $totalPiutang
            ];
        }

        // Cek apakah nilai transaksi melebihi sisa limit
        if ($nilaiTransaksi > $sisaLimit) {
            return [
                'layak' => false,
                'alasan' => "Nilai transaksi melebihi sisa limit kredit",
                'limit_kredit' => $customer->limit_kredit,
                'sisa_limit' => $sisaLimit,
                'nilai_transaksi' => $nilaiTransaksi,
                'kekurangan' => $nilaiTransaksi - $sisaLimit
            ];
        }

        return [
            'layak' => true,
            'alasan' => 'Transaksi kredit dapat dilakukan',
            'limit_kredit' => $customer->limit_kredit,
            'sisa_limit' => $sisaLimit,
            'total_piutang' => $totalPiutang,
            'hari_tempo' => $customer->limit_hari_tempo
        ];
    }

    /**
     * Dapatkan status kredit customer
     * 
     * @param Customer $customer
     * @param float $totalPiutang
     * @return string
     */
    public function getStatusKredit(Customer $customer, float $totalPiutang): string
    {
        if ($customer->isTunai()) {
            return 'Tunai';
        }

        $persentasePenggunaan = $customer->limit_kredit > 0 ? ($totalPiutang / $customer->limit_kredit) * 100 : 0;

        if ($persentasePenggunaan >= 100) {
            return 'Limit Habis';
        } elseif ($persentasePenggunaan >= 80) {
            return 'Limit Kritis';
        } elseif ($persentasePenggunaan >= 50) {
            return 'Limit Sedang';
        } else {
            return 'Limit Aman';
        }
    }

    /**
     * Dapatkan daftar customer berdasarkan status kredit
     * 
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerByStatusKredit(string $status = 'all')
    {
        $customers = Customer::with('wilayah')->where('limit_hari_tempo', '>', 0);

        switch ($status) {
            case 'aman':
                return $customers->get()->filter(function ($customer) {
                    $piutangInfo = $this->hitungPiutang($customer->id);
                    $persentase = $customer->limit_kredit > 0 ? ($piutangInfo['total_piutang'] / $customer->limit_kredit) * 100 : 0;
                    return $persentase < 50;
                });
            
            case 'sedang':
                return $customers->get()->filter(function ($customer) {
                    $piutangInfo = $this->hitungPiutang($customer->id);
                    $persentase = $customer->limit_kredit > 0 ? ($piutangInfo['total_piutang'] / $customer->limit_kredit) * 100 : 0;
                    return $persentase >= 50 && $persentase < 80;
                });
            
            case 'kritis':
                return $customers->get()->filter(function ($customer) {
                    $piutangInfo = $this->hitungPiutang($customer->id);
                    $persentase = $customer->limit_kredit > 0 ? ($piutangInfo['total_piutang'] / $customer->limit_kredit) * 100 : 0;
                    return $persentase >= 80 && $persentase < 100;
                });
            
            case 'habis':
                return $customers->get()->filter(function ($customer) {
                    $piutangInfo = $this->hitungPiutang($customer->id);
                    $persentase = $customer->limit_kredit > 0 ? ($piutangInfo['total_piutang'] / $customer->limit_kredit) * 100 : 0;
                    return $persentase >= 100;
                });
            
            default:
                return $customers->get();
        }
    }

    /**
     * Dapatkan laporan piutang per wilayah
     * 
     * @return array
     */
    public function getLaporanPiutangPerWilayah(): array
    {
        $wilayahs = \App\Models\Wilayah::with('customers')->active()->get();
        $laporan = [];

        foreach ($wilayahs as $wilayah) {
            $totalPiutang = 0;
            $totalCustomer = 0;
            $customerKredit = 0;

            foreach ($wilayah->customers as $customer) {
                if ($customer->isKredit()) {
                    $piutangInfo = $this->hitungPiutang($customer->id);
                    $totalPiutang += $piutangInfo['total_piutang'];
                    $customerKredit++;
                }
                $totalCustomer++;
            }

            $laporan[] = [
                'wilayah' => $wilayah,
                'total_customer' => $totalCustomer,
                'customer_kredit' => $customerKredit,
                'total_piutang' => $totalPiutang
            ];
        }

        return $laporan;
    }
}
