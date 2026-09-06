<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class GiftVoucherProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::firstOrCreate(
            ['mpn' => 'GIFT-VOUCHER'],
            [
                'name' => 'Gift Voucher',
                'description' => 'A Chapter of You gift voucher. Redeemable on all products.',
                'cost' => 0.00,  // Real cost stored per order item
                'stock_qty' => 9999,  // Always in stock
                'status' => 'disabled', // Hidden from shop - purchased via /gift-vouchers only
                'parent_product_id' => null,
            ]
        );

        $this->command->info('Gift Voucher product created/confirmed.');
    }
}
