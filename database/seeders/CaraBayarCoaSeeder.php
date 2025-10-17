<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaraBayar;
use App\Models\ChartOfAccount;

class CaraBayarCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder links CaraBayar payment methods to their corresponding COA accounts.
     */
    public function run(): void
    {
        $this->command->info('🔗 Linking CaraBayar to COA accounts...');

        $mappings = [
            // Cash payments
            'Cash (Kas Kecil)' => '1101',
            'Cash (Kas Besar)' => '1102',
            
            // General bank accounts
            'BCA' => '1104-4',
            'BRI' => '1104-3',
            'MANDIRI' => '1104-1',
            'BNI' => '1104-2',
            
            // Bank Mandiri sub-accounts
            'Bank Mandiri - QRIS' => '1104-1-QRIS',
            'Bank Mandiri - EDC' => '1104-1-EDC',
            'Bank Mandiri - Giro' => '1104-1-GIRO',
            
            // Bank BNI sub-accounts
            'Bank BNI - QRIS' => '1104-2-QRIS',
            'Bank BNI - EDC' => '1104-2-EDC',
            'Bank BNI - Giro' => '1104-2-GIRO',
            
            // Bank BRI sub-accounts
            'Bank BRI - QRIS' => '1104-3-QRIS',
            'Bank BRI - EDC' => '1104-3-EDC',
            'Bank BRI - Giro' => '1104-3-GIRO',
            
            // Bank BCA sub-accounts
            'Bank BCA - QRIS' => '1104-4-QRIS',
            'Bank BCA - EDC' => '1104-4-EDC',
            'Bank BCA - Giro' => '1104-4-GIRO',
        ];

        $linked = 0;
        $notFound = 0;

        foreach ($mappings as $caraBayarName => $coaCode) {
            $caraBayar = CaraBayar::where('nama', $caraBayarName)->first();
            $coaAccount = ChartOfAccount::where('code', $coaCode)->first();

            if ($caraBayar && $coaAccount) {
                $caraBayar->update(['coa_account_id' => $coaAccount->id]);
                $this->command->info("✅ Linked '{$caraBayarName}' to '{$coaAccount->name}' ({$coaCode})");
                $linked++;
            } else {
                $this->command->warn("⚠️  Could not link '{$caraBayarName}' to '{$coaCode}' - missing data");
                $notFound++;
            }
        }

        $this->command->info("📊 Summary: {$linked} linked, {$notFound} not found");
    }
}
