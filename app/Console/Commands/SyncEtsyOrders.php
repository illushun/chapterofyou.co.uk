<?php

namespace App\Console\Commands;

use App\Jobs\ImportEtsyOrders;
use Illuminate\Console\Command;

class SyncEtsyOrders extends Command
{
    protected $signature   = 'etsy:sync-orders';
    protected $description = 'Import new orders from Etsy';

    public function handle(): void
    {
        $this->info('Dispatching Etsy order import job...');
        ImportEtsyOrders::dispatch();
        $this->info('Done.');
    }
}
