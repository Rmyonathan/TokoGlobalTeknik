<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Services\DatabaseSwitchService;

class DatabaseComposer
{
    protected $databaseSwitchService;

    public function __construct(DatabaseSwitchService $databaseSwitchService)
    {
        $this->databaseSwitchService = $databaseSwitchService;
    }

    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with([
            'current_database' => $this->databaseSwitchService->getCurrentDatabaseInfo(),
            'available_databases' => $this->databaseSwitchService->getDatabaseStatus(),
        ]);
    }
}
