<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupExpiredCarts extends Command
{
    /** @var string */
    protected $signature = 'cart:cleanup';

    /** @var string */
    protected $description = 'Deletes guest shopping carts that have expired.';

    public function handle()
    {
        // Define the expiration point (e.g., 7 days ago)
        $expirationTime = Carbon::now();

        // Find and delete carts that meet the criteria:
        // Must be a guest cart (user_id is null).
        // The expires_at timestamp is in the past.
        $deletedCount = Cart::whereNull('user_id')
            ->where('expires_at', '<', $expirationTime)
            ->delete();

        $this->info("Successfully cleaned up {$deletedCount} expired guest carts.");

        return Command::SUCCESS;
    }
}
