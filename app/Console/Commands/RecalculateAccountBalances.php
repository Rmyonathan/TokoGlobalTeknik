<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AccountingService;
use App\Models\AccountingPeriod;

class RecalculateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:recalculate-balances {--period= : Accounting period ID} {--all : Recalculate for all periods}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate account balances from journal entries';

    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        parent::__construct();
        $this->accountingService = $accountingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodId = $this->option('period');
        $allPeriods = $this->option('all');

        if ($allPeriods) {
            $this->info('Recalculating balances for all periods...');
            $periods = AccountingPeriod::orderBy('start_date')->get();
            
            foreach ($periods as $period) {
                $this->info("Processing period: {$period->name} ({$period->start_date} - {$period->end_date})");
                $updatedCount = $this->accountingService->recalculateAllBalances($period->id);
                $this->info("Updated {$updatedCount} accounts for period {$period->name}");
            }
        } else {
            if ($periodId) {
                $period = AccountingPeriod::find($periodId);
                if (!$period) {
                    $this->error("Accounting period with ID {$periodId} not found.");
                    return 1;
                }
                $this->info("Recalculating balances for period: {$period->name}");
                $updatedCount = $this->accountingService->recalculateAllBalances($periodId);
                $this->info("Updated {$updatedCount} accounts for period {$period->name}");
            } else {
                $this->info('Recalculating balances for current period...');
                $updatedCount = $this->accountingService->recalculateAllBalances();
                $this->info("Updated {$updatedCount} accounts");
            }
        }

        $this->info('Account balance recalculation completed successfully!');
        return 0;
    }
}
