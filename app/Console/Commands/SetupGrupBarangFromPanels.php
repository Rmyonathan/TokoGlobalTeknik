<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GrupBarang;
use App\Models\KodeBarang;
use Illuminate\Support\Facades\DB;

class SetupGrupBarangFromPanels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grup-barang:setup-from-panels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup grup_barang from panel attributes and link them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setup Grup Barang dari Kode Barang Attributes');
        $this->info('================================================');

        // Step 1: Populate grup_barang from kode_barang attributes
        $this->info('');
        $this->info('📋 Step 1: Mengisi grup_barang dari attribute kode barang...');
        
        $kodeBarangAttributes = DB::table('kode_barangs')
            ->select('attribute')
            ->whereNotNull('attribute')
            ->where('attribute', '!=', '')
            ->distinct()
            ->pluck('attribute')
            ->toArray();

        if (empty($kodeBarangAttributes)) {
            $this->warn('⚠️  Tidak ada attribute kode barang yang ditemukan!');
            return 1;
        }

        $this->info("   Ditemukan " . count($kodeBarangAttributes) . " attribute unik:");
        foreach ($kodeBarangAttributes as $attribute) {
            $this->line("   - {$attribute}");
        }

        $createdCount = 0;
        foreach ($kodeBarangAttributes as $attribute) {
            $grupBarang = GrupBarang::firstOrCreate(
                ['name' => $attribute],
                [
                    'name' => $attribute,
                    'description' => 'Grup barang berdasarkan attribute kode barang: ' . $attribute,
                    'status' => 'Active'
                ]
            );

            if ($grupBarang->wasRecentlyCreated) {
                $createdCount++;
                $this->line("   ✅ Dibuat: {$attribute}");
            } else {
                $this->line("   🔄 Sudah ada: {$attribute}");
            }
        }

        $this->info("   Total grup barang: " . count($kodeBarangAttributes));

        // Step 2: Link kode_barang to grup_barang
        $this->info('');
        $this->info('🔗 Step 2: Menghubungkan kode barang dengan grup_barang...');

        $kodeBarangs = DB::table('kode_barangs')
            ->select('id', 'name', 'attribute', 'kode_barang')
            ->whereNotNull('attribute')
            ->where('attribute', '!=', '')
            ->get();

        $linkedCount = 0;
        $errorCount = 0;

        foreach ($kodeBarangs as $kodeBarang) {
            try {
                $grupBarang = GrupBarang::where('name', $kodeBarang->attribute)->first();
                
                if (!$grupBarang) {
                    $this->warn("   ⚠️  Grup barang '{$kodeBarang->attribute}' tidak ditemukan");
                    $errorCount++;
                    continue;
                }

                // Update kode barang dengan kategori_id yang sesuai
                DB::table('kode_barangs')
                    ->where('id', $kodeBarang->id)
                    ->update([
                        'kategori_id' => $grupBarang->id
                    ]);
                
                $this->line("   ✅ Kode Barang '{$kodeBarang->kode_barang}' → Grup: {$grupBarang->name}");
                $linkedCount++;

            } catch (\Exception $e) {
                $this->error("   ❌ Error pada kode barang '{$kodeBarang->kode_barang}': " . $e->getMessage());
                $errorCount++;
            }
        }

        // Summary
        $this->info('');
        $this->info('🎉 Setup Selesai!');
        $this->info('================');
        $this->info("   📋 Grup Barang:");
        $this->info("      - Dibuat: {$createdCount}");
        $this->info("      - Total: " . count($kodeBarangAttributes));
        $this->info('');
        $this->info("   🔗 Kode Barang Linked:");
        $this->info("      - Berhasil: {$linkedCount}");
        $this->info("      - Error: {$errorCount}");
        $this->info("      - Total kode barang: " . $kodeBarangs->count());

        return 0;
    }
}
