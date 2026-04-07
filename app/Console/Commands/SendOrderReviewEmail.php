<?php

namespace App\Console\Commands;

use App\Mail\Order\Review;
use App\Models\Order;
use App\Models\OrderReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOrderReviewEmail extends Command
{
    protected $signature   = 'email:send:order-review';
    protected $description = 'Send review request emails to customers whose orders were delivered ~7 days ago.';

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 0);

        $this->info('Finding eligible orders...');

        // Only email customers whose order was placed 7 days ago (±1 day window)
        $from = now()->subDays(8)->startOfDay();
        $to   = now()->subDays(6)->endOfDay();

        $orders = Order::where('status', 'successful')
            ->whereBetween('created_at', [$from, $to])
            ->whereDoesntHave('orderReview')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No eligible orders found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$orders->count()} order(s) to email.");

        $sent   = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                Mail::to($order->email)->send(new Review($order));

                OrderReview::create([
                    'order_id' => $order->id,
                    'email'    => $order->email,
                    'sent'     => now(),
                ]);

                $this->info("Sent to {$order->email} (Order #{$order->id})");
                $sent++;

            } catch (\Exception $e) {
                $this->error("Failed for {$order->email} (Order #{$order->id}): {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done. Sent: {$sent}, Failed: {$failed}.");

        return Command::SUCCESS;
    }
}
