<?php

namespace App\Console\Commands;

use App\Mail\Order\Review;
use App\Models\Order;
use App\Models\OrderReview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOrderReviewEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send:order-review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send order review emails to customers.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 0);

        $this->info("Sending emails...");
        $orders = Order::where('status', 'successful')
            ->whereDoesntHave('review')
            ->get();


        foreach ($orders as $order) {
            Mail::to($order->email)->send(new Review($order));
            OrderReview::create([
                'order_id' => $order->id,
                'email' => $order->email,
                'sent' => now(),
            ]);
            $this->info("Sent email to {$order->email}");
        }

        return Command::SUCCESS;
    }
}
