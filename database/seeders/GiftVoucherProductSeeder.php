<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Creates a single "Gift Voucher" placeholder product used when a customer
 * purchases a gift voucher. The actual amount is stored per order item,
 * not on the product itself (product cost is set to 0 — the real cost
 * is written directly to the order_item.product_cost column).
 *
 * Run once: php artisan db:seed --class=GiftVoucherProductSeeder
 */
class GiftVoucherProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::firstOrCreate(
            ['mpn' => 'GIFT-VOUCHER'],
            [
                'name'              => 'Gift Voucher',
                'description'       => 'A Chapter of You gift voucher. Redeemable on all products.',
                'cost'              => 0.00,  // Real cost stored per order item
                'stock_qty'         => 9999,  // Always in stock
                'status'            => 'disabled', // Hidden from shop — purchased via /gift-vouchers only
                'parent_product_id' => null,
            ]
        );

        $this->command->info('Gift Voucher product created/confirmed.');
    }
}
