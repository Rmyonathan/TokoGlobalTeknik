<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pembayaran;
use App\Models\PembayaranDetail;
use App\Models\Transaksi;
use App\Models\Customer;
use App\Models\Kas;
use Carbon\Carbon;

class PembayaranPiutangController extends Controller
{
    /**
     * Display a listing of pembayaran piutang
     */
    public function index()
    {
        $pembayarans = Pembayaran::with(['customer', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $summary = [
            'total_pembayaran_hari_ini' => Pembayaran::getTotalPembayaranHariIni(),
            'total_pembayaran_bulan_ini' => Pembayaran::getTotalPembayaranBulanIni(),
            'total_piutang_tertagih' => Transaksi::belumDibayar()->sum('sisa_piutang'),
            'total_piutang_jatuh_tempo' => Transaksi::jatuhTempo()->sum('sisa_piutang')
        ];

        return view('pembayaran_piutang.index', compact('pembayarans', 'summary'));
    }

    /**
     * Show the form for creating a new pembayaran
     */
    public function create()
    {
        $customers = Customer::orderBy('nama')->get();
        $noPembayaran = Pembayaran::generateNoPembayaran();
        
        return view('pembayaran_piutang.create', compact('customers', 'noPembayaran'));
    }

    /**
     * Get customer's unpaid invoices for payment
     */
    public function getCustomerInvoices(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id'
        ]);

        try {
            $customerId = $request->customer_id;
            $customer = Customer::find($customerId);

            // Get unpaid invoices (belum dibayar atau sebagian)
            $invoices = Transaksi::where('kode_customer', $customer->kode_customer)
                ->whereIn('status_piutang', ['belum_dibayar', 'sebagian'])
                ->orderBy('tanggal', 'asc') // FIFO - oldest first
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'no_transaksi' => $invoice->no_transaksi,
                        'tanggal' => $invoice->tanggal->format('d/m/Y'),
                        'tanggal_jatuh_tempo' => $invoice->tanggal_jatuh_tempo ? $invoice->tanggal_jatuh_tempo->format('d/m/Y') : '-',
                        'total_faktur' => $invoice->grand_total,
                        'sudah_dibayar' => $invoice->total_dibayar,
                        'sisa_tagihan' => $invoice->sisa_piutang,
                        'status_piutang' => $invoice->status_piutang,
                        'is_jatuh_tempo' => $invoice->checkJatuhTempo(),
                        'hari_keterlambatan' => $invoice->hari_keterlambatan,
                        'suggested_payment' => $invoice->sisa_piutang // Default suggestion
                    ];
                });

            $totalPiutang = $invoices->sum('sisa_tagihan');
            $totalJatuhTempo = $invoices->where('is_jatuh_tempo', true)->sum('sisa_tagihan');

            return response()->json([
                'success' => true,
                'customer' => [
                    'nama' => $customer->nama,
                    'limit_kredit' => $customer->limit_kredit,
                    'limit_hari_tempo' => $customer->limit_hari_tempo
                ],
                'invoices' => $invoices,
                'summary' => [
                    'total_invoices' => $invoices->count(),
                    'total_piutang' => $totalPiutang,
                    'total_jatuh_tempo' => $totalJatuhTempo,
                    'invoices_jatuh_tempo' => $invoices->where('is_jatuh_tempo', true)->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting customer invoices:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get auto-suggestion for payment allocation
     */
    public function getPaymentSuggestion(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total_bayar' => 'required|numeric|min:0.01'
        ]);

        try {
            $customerId = $request->customer_id;
            $totalBayar = $request->total_bayar;

            // Get unpaid invoices ordered by date (FIFO)
            $invoices = Transaksi::where('kode_customer', Customer::find($customerId)->kode_customer)
                ->whereIn('status_piutang', ['belum_dibayar', 'sebagian'])
                ->orderBy('tanggal', 'asc')
                ->get();

            $suggestions = [];
            $remainingPayment = $totalBayar;

            foreach ($invoices as $invoice) {
                if ($remainingPayment <= 0) break;

                $sisaTagihan = $invoice->sisa_piutang;
                $suggestedAmount = min($remainingPayment, $sisaTagihan);

                $suggestions[] = [
                    'transaksi_id' => $invoice->id,
                    'no_transaksi' => $invoice->no_transaksi,
                    'tanggal' => $invoice->tanggal->format('d/m/Y'),
                    'total_faktur' => $invoice->grand_total,
                    'sudah_dibayar' => $invoice->total_dibayar,
                    'sisa_tagihan' => $sisaTagihan,
                    'suggested_payment' => $suggestedAmount,
                    'is_jatuh_tempo' => $invoice->checkJatuhTempo(),
                    'priority' => $invoice->checkJatuhTempo() ? 'high' : 'normal'
                ];

                $remainingPayment -= $suggestedAmount;
            }

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'total_suggested' => $totalBayar - $remainingPayment,
                'remaining_payment' => $remainingPayment
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting payment suggestion:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pembayaran
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tanggal_bayar' => 'required|date',
            'total_bayar' => 'required|numeric|min:0.01',
            'metode_pembayaran' => 'required|string',
            'cara_bayar' => 'required|string',
            'no_referensi' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'payment_details' => 'required|array|min:1',
            'payment_details.*.transaksi_id' => 'required|exists:transaksi,id',
            'payment_details.*.jumlah_dilunasi' => 'required|numeric|min:0.01'
        ]);

        DB::beginTransaction();
        try {
            $customer = Customer::find($request->customer_id);
            
            // Calculate total piutang before payment
            $totalPiutang = Transaksi::where('kode_customer', $customer->kode_customer)
                ->whereIn('status_piutang', ['belum_dibayar', 'sebagian'])
                ->sum('sisa_piutang');

            // Create pembayaran record
            $pembayaran = Pembayaran::create([
                'customer_id' => $request->customer_id,
                'no_pembayaran' => Pembayaran::generateNoPembayaran(),
                'tanggal_bayar' => $request->tanggal_bayar,
                'total_bayar' => $request->total_bayar,
                'total_piutang' => $totalPiutang,
                'sisa_piutang' => $totalPiutang - $request->total_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'no_referensi' => $request->no_referensi,
                'keterangan' => $request->keterangan,
                'status' => 'confirmed', // Auto confirm for now
                'created_by' => auth()->id(),
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now()
            ]);

            // Create payment details
            foreach ($request->payment_details as $detail) {
                $transaksi = Transaksi::find($detail['transaksi_id']);
                $sudahDibayar = $transaksi->total_dibayar ?? 0;
                $jumlahDilunasi = $detail['jumlah_dilunasi'];
                $sisaTagihan = $transaksi->grand_total - $sudahDibayar - $jumlahDilunasi;

                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'transaksi_id' => $detail['transaksi_id'],
                    'no_transaksi' => $transaksi->no_transaksi,
                    'total_faktur' => $transaksi->grand_total,
                    'sudah_dibayar' => $sudahDibayar,
                    'jumlah_dilunasi' => $jumlahDilunasi,
                    'sisa_tagihan' => $sisaTagihan,
                    'status_pelunasan' => $sisaTagihan <= 0 ? 'lunas' : 'sebagian',
                    'keterangan' => 'Pembayaran via ' . $pembayaran->no_pembayaran
                ]);

                // Update transaksi piutang status
                PembayaranDetail::updateTransaksiPiutangStatus($detail['transaksi_id']);
            }

            // Update Kas if cash payment
            if (strtolower($request->metode_pembayaran) === 'tunai') {
                Kas::create([
                    'name' => "Pembayaran Piutang: {$pembayaran->no_pembayaran}",
                    'description' => "Pembayaran piutang dari {$customer->nama}",
                    'qty' => $request->total_bayar,
                    'type' => 'Debit',
                    'saldo' => 0,
                    'is_manual' => false,
                ]);

                // Adjust Kas saldo
                $this->adjustKasSaldo();
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran berhasil disimpan',
                    'pembayaran' => [
                        'id' => $pembayaran->id,
                        'no_pembayaran' => $pembayaran->no_pembayaran,
                        'customer' => $customer->nama,
                        'total_bayar' => $pembayaran->total_bayar,
                        'tanggal_bayar' => $pembayaran->tanggal_bayar->format('d/m/Y')
                    ]
                ]);
            }

            return redirect()
                ->route('pembayaran-piutang.show', $pembayaran->id)
                ->with('success', 'Pembayaran berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing pembayaran:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pembayaran
     */
    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['customer', 'details.transaksi', 'createdBy', 'confirmedBy']);
        
        return view('pembayaran_piutang.show', compact('pembayaran'));
    }

    /**
     * Show the form for editing the specified pembayaran
     */
    public function edit(Pembayaran $pembayaran)
    {
        $pembayaran->load(['customer', 'details.transaksi']);
        $customers = Customer::orderBy('nama')->get();
        
        return view('pembayaran_piutang.edit', compact('pembayaran', 'customers'));
    }

    /**
     * Update the specified pembayaran
     */
    public function update(Request $request, Pembayaran $pembayaran): JsonResponse
    {
        // Only allow editing if not confirmed
        if ($pembayaran->isConfirmed()) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah dikonfirmasi tidak dapat diedit'
            ], 400);
        }

        $request->validate([
            'tanggal_bayar' => 'required|date',
            'total_bayar' => 'required|numeric|min:0.01',
            'metode_pembayaran' => 'required|string',
            'cara_bayar' => 'required|string',
            'no_referensi' => 'nullable|string',
            'keterangan' => 'nullable|string'
        ]);

        try {
            $pembayaran->update([
                'tanggal_bayar' => $request->tanggal_bayar,
                'total_bayar' => $request->total_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'cara_bayar' => $request->cara_bayar,
                'no_referensi' => $request->no_referensi,
                'keterangan' => $request->keterangan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating pembayaran:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pembayaran
     */
    public function destroy(Pembayaran $pembayaran): JsonResponse
    {
        // Only allow deletion if not confirmed
        if ($pembayaran->isConfirmed()) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah dikonfirmasi tidak dapat dihapus'
            ], 400);
        }

        try {
            // Restore transaksi piutang status
            foreach ($pembayaran->details as $detail) {
                PembayaranDetail::updateTransaksiPiutangStatus($detail->transaksi_id);
            }

            $pembayaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting pembayaran:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm pembayaran
     */
    public function confirm(Pembayaran $pembayaran): JsonResponse
    {
        try {
            $pembayaran->confirm(auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi'
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming pembayaran:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel pembayaran
     */
    public function cancel(Pembayaran $pembayaran): JsonResponse
    {
        try {
            $pembayaran->cancel(auth()->id());

            // Restore transaksi piutang status
            foreach ($pembayaran->details as $detail) {
                PembayaranDetail::updateTransaksiPiutangStatus($detail->transaksi_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling pembayaran:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get laporan piutang
     */
    public function laporanPiutang(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $kodeCustomer = $request->get('kode_customer');
        $statusPiutang = $request->get('status_piutang');
    
        $query = Transaksi::with(['customer'])
            ->byDateRange($startDate, $endDate);
    
        // Filter berdasarkan kode_customer
        if ($kodeCustomer) {
            $query->where('kode_customer', $kodeCustomer);
        }
    
        // Filter status piutang
        if ($statusPiutang) {
            $query->where('status_piutang', $statusPiutang);
        }
    
        $transaksi = $query->orderBy('tanggal', 'desc')->get();
    
        $summary = [
            'total_faktur' => $transaksi->count(),
            'total_nilai_faktur' => $transaksi->sum('grand_total'),
            'total_sudah_dibayar' => $transaksi->sum('total_dibayar'),
            'total_sisa_piutang' => $transaksi->sum('sisa_piutang'),
            'total_lunas' => $transaksi->where('status_piutang', 'lunas')->count(),
            'total_sebagian' => $transaksi->where('status_piutang', 'sebagian')->count(),
            'total_belum_dibayar' => $transaksi->where('status_piutang', 'belum_dibayar')->count(),
            'total_jatuh_tempo' => $transaksi->filter(fn($t) => $t->checkJatuhTempo())->count()
        ];
    
        // Ambil daftar customer untuk filter
        $customers = \App\Models\Customer::all();
    
        return view('pembayaran_piutang.laporan', compact(
            'transaksi', 'summary', 'startDate', 'endDate', 'customers', 'kodeCustomer', 'statusPiutang'
        ));
    }


    /**
     * Adjust Kas saldo
     */
    private function adjustKasSaldo(): void
    {
        $kas = Kas::orderBy('created_at', 'desc')->first();
        if ($kas) {
            $saldo = Kas::sum(DB::raw('CASE WHEN type = "Debit" THEN qty ELSE -qty END'));
            $kas->update(['saldo' => $saldo]);
        }
    }
}
