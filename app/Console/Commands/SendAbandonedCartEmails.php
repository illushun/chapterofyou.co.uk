<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCart;
use App\Models\AbandonedCartEmail;
use App\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartEmails extends Command
{
    protected $signature   = 'email:send:abandoned-cart';
    protected $description = 'Send recovery emails to customers who abandoned their cart 1 hour ago.';

    public function handle()
    {
        // Carts that:
        // - have items
        // - belong to a logged-in user (we need an email address)
        // - were last updated between 1 and 24 hours ago (after 24h it's too stale)
        // - haven't had an abandoned cart email sent yet
        // - don't belong to a user who completed an order in the last 24 hours

        $carts = Cart::with(['items.product.images', 'items.product.seo', 'user'])
            ->whereHas('items')
            ->whereHas('user', fn ($q) => $q->whereNotNull('email'))
            ->whereBetween('updated_at', [now()->subHours(24), now()->subHour()])
            ->whereDoesntHave('abandonedCartEmail')
            ->get()
            ->filter(
                fn ($cart) =>
                // Exclude carts where user has placed an order in the last 24h
                !$cart->user->orders()
                    ->where('status', 'successful')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->exists()
            );

        if ($carts->isEmpty()) {
            $this->info('No abandoned carts to email.');
            return Command::SUCCESS;
        }

        $this->info("Found {$carts->count()} abandoned cart(s).");

        $sent   = 0;
        $failed = 0;

        foreach ($carts as $cart) {
            try {
                Mail::to($cart->user->email)->send(new AbandonedCart($cart));

                AbandonedCartEmail::create([
                    'cart_id' => $cart->id,
                    'email'   => $cart->user->email,
                    'sent_at' => now(),
                ]);

                $this->info("✓ Sent to {$cart->user->email}");
                $sent++;
            } catch (\Exception $e) {
                Log::error('[AbandonedCart] Failed to send', [
                    'cart_id' => $cart->id,
                    'email'   => $cart->user->email,
                    'error'   => $e->getMessage(),
                ]);
                $this->error("Failed for {$cart->user->email}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done. Sent: {$sent}, Failed: {$failed}.");
        return Command::SUCCESS;
    }
}
