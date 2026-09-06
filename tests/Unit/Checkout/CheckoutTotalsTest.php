<?php

namespace Tests\Unit\Checkout;

use App\Models\Cart;
use App\Models\Cart\Item;
use App\Models\Courier;
use App\Models\Product;
use App\Models\Product\Courier as ProductCourier;
use App\Services\Checkout\CheckoutTotals;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheckoutTotalsTest extends TestCase
{
    #[DataProvider('shippingCases')]
    public function test_existing_shipping_rules_are_preserved(array $lines, ?array $gift, float $discount, float $shipping, float $total): void
    {
        config(['app.vat_number' => null]);
        $cart = new Cart;
        $cart->setRelation('items', collect($lines)->map(function ($line) {
            [$cost, $quantity, $shipping, $perItem] = $line;
            $courier = new Courier(['cost' => $shipping]);
            $assignment = (new ProductCourier(['per_item' => $perItem]))->setRelation('courier', $courier);
            $product = (new Product(['cost' => $cost]))->setRelation('courier', $assignment);

            return (new Item(['quantity' => $quantity]))->setRelation('product', $product);
        }));

        $summary = (new CheckoutTotals)->calculate($cart, $discount, $gift);
        $this->assertEquals($shipping, $summary['shipping']);
        $this->assertEquals($total, $summary['total']);
        $this->assertEquals(0, $summary['vat_component']);
    }

    public static function shippingCases(): array
    {
        return [
            'lowest flat courier' => [[[10, 1, 4, 'no'], [10, 1, 2.99, 'no']], null, 0, 2.99, 22.99],
            'per item courier takes priority' => [[[10, 2, 3, 'yes'], [10, 1, 2, 'no']], null, 0, 6, 36],
            'free shipping threshold' => [[[25, 2, 3, 'yes']], null, 0, 0, 50],
            'discount does not revoke free shipping' => [[[25, 2, 3, 'yes']], null, 10, 0, 40],
            'digital gift contributes to threshold' => [[[25, 1, 3, 'no']], ['amount' => 25, 'delivery_type' => 'email'], 0, 0, 50],
            'physical gift retains postage' => [[], ['amount' => 50, 'delivery_type' => 'physical'], 0, 2.99, 52.99],
            'digital gift alone' => [[], ['amount' => 25, 'delivery_type' => 'email'], 0, 0, 25],
            'discount capped at subtotal' => [[[10, 1, 3, 'no']], null, 100, 3, 3],
        ];
    }

    public function test_conversion_to_pence_does_not_truncate_floating_point_amounts(): void
    {
        $this->assertSame(1999, CheckoutTotals::pence(19.99));
        $this->assertSame(2298, CheckoutTotals::pence(22.98));
    }

    public function test_vat_is_included_in_discounted_prices(): void
    {
        config(['app.vat_number' => 'test']);
        $cart = (new Cart)->setRelation('items', collect());
        $summary = (new CheckoutTotals)->calculate($cart, 6, ['amount' => 30, 'delivery_type' => 'email']);
        $this->assertEquals(4, $summary['vat_component']);
        $this->assertEquals(24, $summary['total']);
    }
}
