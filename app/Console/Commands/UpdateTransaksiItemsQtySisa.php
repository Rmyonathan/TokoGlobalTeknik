<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransaksiItem;
use Illuminate\Support\Facades\Log;

class UpdateTransaksiItemsQtySisa extends Command
{
    protected $signature = 'transaksi:update-qty-sisa';
    protected $description = 'Update qty_sisa untuk semua transaksi items yang sudah ada.';

    public function handle()
    {
        $this->info('Memulai update qty_sisa untuk transaksi items...');

        $transaksiItems = TransaksiItem::all();

        if ($transaksiItems->isEmpty()) {
            $this->comment('Tidak ada transaksi items yang ditemukan.');
            return;
        }

        $this->info("Menemukan {$transaksiItems->count()} transaksi items.");

        $progressBar = $this->output->createProgressBar($transaksiItems->count());
        $progressBar->start();

        $updated = 0;
        foreach ($transaksiItems as $item) {
            try {
                // Update qty_sisa = qty - qty_return
                $item->qty_sisa = $item->qty - ($item->qty_return ?? 0);
                $item->save();
                $updated++;
                
                Log::info("Updated qty_sisa for transaksi item {$item->id}", [
                    'qty' => $item->qty,
                    'qty_return' => $item->qty_return ?? 0,
                    'qty_sisa' => $item->qty_sisa
                ]);
            } catch (\Exception $e) {
                $this->error("\nError updating transaksi item {$item->id}: " . $e->getMessage());
                Log::error("Error updating transaksi item {$item->id}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nUpdate qty_sisa selesai! {$updated} items berhasil diupdate.");
    }
}