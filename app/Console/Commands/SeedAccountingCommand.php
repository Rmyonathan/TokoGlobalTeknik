<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedAccountingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:seed 
                            {--fresh : Reset database before seeding}
                            {--module=all : Specific module to seed (all|barang|customer|penjualan|pembelian|retur|pembayaran|journal)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed accounting system with sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->option('module');
        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->info('🔄 Resetting database...');
            Artisan::call('migrate:fresh');
            $this->info('✅ Database reset completed');
        }

        $this->info('🌱 Starting accounting seeder...');
        $this->info('=====================================');

        switch ($module) {
            case 'all':
                $this->seedAll();
                break;
            case 'barang':
                $this->seedBarang();
                break;
            case 'customer':
                $this->seedCustomer();
                break;
            case 'penjualan':
                $this->seedPenjualan();
                break;
            case 'pembelian':
                $this->seedPembelian();
                break;
            case 'retur':
                $this->seedRetur();
                break;
            case 'pembayaran':
                $this->seedPembayaran();
                break;
            case 'journal':
                $this->seedJournal();
                break;
            default:
                $this->error('❌ Invalid module. Available: all, barang, customer, penjualan, pembelian, retur, pembayaran, journal');
                return 1;
        }

        $this->info('=====================================');
        $this->info('🎉 Accounting seeder completed!');
        return 0;
    }

    private function seedAll()
    {
        $this->info('📋 Running complete accounting seeder...');
        Artisan::call('db:seed', ['--class' => 'CompleteAccountingSeeder']);
        $this->info('✅ Complete accounting seeder finished');
    }

    private function seedBarang()
    {
        $this->info('📦 Seeding barang data...');
        Artisan::call('db:seed', ['--class' => 'BarangSeeder']);
        $this->info('✅ Barang data seeded');
    }

    private function seedCustomer()
    {
        $this->info('👥 Seeding customer & supplier data...');
        Artisan::call('db:seed', ['--class' => 'CustomerSupplierSeeder']);
        $this->info('✅ Customer & supplier data seeded');
    }

    private function seedPenjualan()
    {
        $this->info('💰 Seeding penjualan data...');
        Artisan::call('db:seed', ['--class' => 'PenjualanSeeder']);
        $this->info('✅ Penjualan data seeded');
    }

    private function seedPembelian()
    {
        $this->info('🛒 Seeding pembelian data...');
        Artisan::call('db:seed', ['--class' => 'PembelianSeeder']);
        $this->info('✅ Pembelian data seeded');
    }

    private function seedRetur()
    {
        $this->info('↩️ Seeding retur data...');
        Artisan::call('db:seed', ['--class' => 'ReturSeeder']);
        $this->info('✅ Retur data seeded');
    }

    private function seedPembayaran()
    {
        $this->info('💳 Seeding pembayaran data...');
        Artisan::call('db:seed', ['--class' => 'PembayaranSeeder']);
        $this->info('✅ Pembayaran data seeded');
    }

    private function seedJournal()
    {
        $this->info('📊 Seeding journal data...');
        Artisan::call('db:seed', ['--class' => 'JournalSeeder']);
        $this->info('✅ Journal data seeded');
    }
}
