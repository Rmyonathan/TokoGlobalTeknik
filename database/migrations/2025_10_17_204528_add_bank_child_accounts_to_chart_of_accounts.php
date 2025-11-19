<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ChartOfAccount;
use App\Models\AccountType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get Asset account type ID
        // Cek apakah AccountType ada (untuk menghindari error saat fresh migrate)
        $assetType = AccountType::where('code', 'A')->first();
        
        // Jika tidak ada tipe akun atau tidak ada akun bank induk, skip saja (karena akan di-handle Seeder)
        if (!$assetType || ChartOfAccount::where('code', 'like', '1104%')->count() == 0) {
            return;
        }

        $assetTypeId = $assetType->id;
        
        $assetTypeId = AccountType::where('code', 'A')->value('id');
        
        if (!$assetTypeId) {
            throw new \Exception('Asset account type not found');
        }

        // Get existing bank accounts
        $bankAccounts = ChartOfAccount::where('code', 'like', '1104%')
            ->where('code', '!=', '1104') // Exclude parent Bank account
            ->get();

        $bankChildAccounts = [];

        foreach ($bankAccounts as $bankAccount) {
            $bankCode = $bankAccount->code;
            $bankName = $bankAccount->name;
            
            // Add QRIS account
            $bankChildAccounts[] = [
                'code' => $bankCode . '-QRIS',
                'name' => $bankName . ' - QRIS',
                'account_type_id' => $assetTypeId,
                'parent_id' => $bankAccount->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Add EDC account
            $bankChildAccounts[] = [
                'code' => $bankCode . '-EDC',
                'name' => $bankName . ' - EDC',
                'account_type_id' => $assetTypeId,
                'parent_id' => $bankAccount->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Add Giro account
            $bankChildAccounts[] = [
                'code' => $bankCode . '-GIRO',
                'name' => $bankName . ' - Giro',
                'account_type_id' => $assetTypeId,
                'parent_id' => $bankAccount->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert bank child accounts
        if (!empty($bankChildAccounts)) {
            ChartOfAccount::insert($bankChildAccounts);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove bank child accounts
        ChartOfAccount::where('code', 'like', '%-QRIS')
            ->orWhere('code', 'like', '%-EDC')
            ->orWhere('code', 'like', '%-GIRO')
            ->delete();
    }
};
