<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OTruyenService;

class SyncCategories extends Command
{
    protected $signature = 'categories:sync';
    protected $description = 'Sync categories from OTruyen API';

    public function handle()
    {
        $this->info('Syncing categories from OTruyen API...');
        
        $service = new OTruyenService();
        $count = $service->syncCategories();
        
        $this->info("Successfully synced {$count} categories.");
        
        return 0;
    }
}
