<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GrupBarang;
use Illuminate\Support\Facades\DB;

class PopulateGrupBarangFromPanels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grup-barang:populate-from-panels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate grup_barang table from panel attributes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Mengisi grup_barang berdasarkan attribute panel...');

        // Ambil semua attribute unik dari tabel panels
        $panelAttributes = DB::table('panels')
            ->select('attribute')
            ->whereNotNull('attribute')
            ->where('attribute', '!=', '')
            ->distinct()
            ->pluck('attribute')
            ->toArray();

        if (empty($panelAttributes)) {
            $this->warn('⚠️  Tidak ada attribute panel yang ditemukan!');
            return 1;
        }

        $this->info('📋 Attribute panel yang ditemukan:');
        foreach ($panelAttributes as $attribute) {
            $this->line("   - {$attribute}");
        }

        $createdCount = 0;
        $updatedCount = 0;

        // Buat atau update grup barang berdasarkan attribute panel
        foreach ($panelAttributes as $attribute) {
            $grupBarang = GrupBarang::firstOrCreate(
                ['name' => $attribute],
                [
                    'name' => $attribute,
                    'description' => 'Grup barang berdasarkan attribute panel: ' . $attribute,
                    'status' => 'Active'
                ]
            );

            if ($grupBarang->wasRecentlyCreated) {
                $createdCount++;
                $this->line("   ✅ Dibuat: {$attribute}");
            } else {
                $updatedCount++;
                $this->line("   🔄 Sudah ada: {$attribute}");
            }
        }

        $this->info('');
        $this->info('🎉 Selesai!');
        $this->info("   - Dibuat: {$createdCount}");
        $this->info("   - Sudah ada: {$updatedCount}");
        $this->info("   - Total: " . count($panelAttributes));

        return 0;
    }
}
