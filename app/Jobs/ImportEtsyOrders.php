<?php

namespace App\Jobs;

use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportEtsyOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(EtsyService $etsy): void
    {
        try {
            $count = $etsy->importNewOrders();
            Log::info("Etsy order import: {$count} new order(s) imported.");
        } catch (Exception $e) {
            Log::error('Etsy order import failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
