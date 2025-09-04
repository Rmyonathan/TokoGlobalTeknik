<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class UpdateCustomerCreditInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:update-credit-info {--customer= : Kode customer spesifik}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update sisa kredit dan total piutang untuk semua customer atau customer spesifik';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update informasi kredit customer...');

        try {
            $customerCode = $this->option('customer');
            
            if ($customerCode) {
                // Update customer spesifik
                $customer = Customer::where('kode_customer', $customerCode)->first();
                
                if (!$customer) {
                    $this->error("Customer dengan kode {$customerCode} tidak ditemukan!");
                    return 1;
                }

                $this->updateSingleCustomer($customer);
            } else {
                // Update semua customer
                $customers = Customer::where('limit_hari_tempo', '>', 0)->get();
                
                if ($customers->isEmpty()) {
                    $this->info('Tidak ada customer dengan sistem kredit ditemukan.');
                    return 0;
                }

                $this->info("Menemukan {$customers->count()} customer dengan sistem kredit.");
                
                $progressBar = $this->output->createProgressBar($customers->count());
                $progressBar->start();

                foreach ($customers as $customer) {
                    $this->updateSingleCustomer($customer);
                    $progressBar->advance();
                }

                $progressBar->finish();
                $this->newLine();
            }

            $this->info('Update informasi kredit customer selesai!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            Log::error('Error in UpdateCustomerCreditInfo command: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Update informasi kredit untuk satu customer
     */
    private function updateSingleCustomer(Customer $customer)
    {
        try {
            $creditInfo = $customer->updateCreditInfo();
            
            $this->line("Customer: {$customer->kode_customer} - {$customer->nama}");
            $this->line("  Limit Kredit: " . $customer->getLimitKreditFormatted());
            $this->line("  Total Piutang: " . $customer->getTotalPiutangFormatted());
            $this->line("  Sisa Kredit: " . $customer->getSisaKreditFormatted());
            $this->line("");

            Log::info("Updated credit info for customer {$customer->kode_customer}", $creditInfo);

        } catch (\Exception $e) {
            $this->error("Error updating customer {$customer->kode_customer}: " . $e->getMessage());
            Log::error("Error updating customer {$customer->kode_customer}: " . $e->getMessage());
        }
    }
}