<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GrupBarang;
use App\Models\KodeBarang;
use Illuminate\Support\Facades\DB;

class LinkPanelsToGrupBarang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'panels:link-to-grup-barang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link panels to grup_barang based on attribute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔗 Menghubungkan panel dengan grup_barang berdasarkan attribute...');

        // Ambil semua panel yang memiliki attribute
        $panels = DB::table('panels')
            ->select('id', 'name', 'attribute', 'group_id')
            ->whereNotNull('attribute')
            ->where('attribute', '!=', '')
            ->get();

        if ($panels->isEmpty()) {
            $this->warn('⚠️  Tidak ada panel dengan attribute yang ditemukan!');
            return 1;
        }

        $this->info("📋 Ditemukan {$panels->count()} panel dengan attribute");

        $linkedCount = 0;
        $errorCount = 0;

        foreach ($panels as $panel) {
            try {
                // Cari grup barang berdasarkan attribute
                $grupBarang = GrupBarang::where('name', $panel->attribute)->first();
                
                if (!$grupBarang) {
                    $this->warn("   ⚠️  Grup barang '{$panel->attribute}' tidak ditemukan untuk panel '{$panel->name}'");
                    $errorCount++;
                    continue;
                }

                // Cari kode barang berdasarkan group_id panel
                if ($panel->group_id) {
                    $kodeBarang = KodeBarang::where('kode_barang', $panel->group_id)->first();
                    
                    if ($kodeBarang) {
                        // Update kode barang dengan kategori_id yang sesuai
                        $kodeBarang->update([
                            'kategori_id' => $grupBarang->id
                        ]);
                        
                        $this->line("   ✅ Panel '{$panel->name}' (attribute: {$panel->attribute}) → Grup: {$grupBarang->name}");
                        $linkedCount++;
                    } else {
                        $this->warn("   ⚠️  Kode barang '{$panel->group_id}' tidak ditemukan untuk panel '{$panel->name}'");
                        $errorCount++;
                    }
                } else {
                    $this->warn("   ⚠️  Panel '{$panel->name}' tidak memiliki group_id");
                    $errorCount++;
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Error pada panel '{$panel->name}': " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info('');
        $this->info('🎉 Selesai!');
        $this->info("   - Berhasil dihubungkan: {$linkedCount}");
        $this->info("   - Error: {$errorCount}");
        $this->info("   - Total panel: " . $panels->count());

        return 0;
    }
}
