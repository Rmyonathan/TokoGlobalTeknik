<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountingPeriod;

class AccountingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        AccountingPeriod::firstOrCreate(
            ['name' => 'Default'],
            [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'is_closed' => false,
            ]
        );
    }
}


