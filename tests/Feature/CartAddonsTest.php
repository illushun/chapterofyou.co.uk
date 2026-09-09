<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAddonsTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_product_addons_are_added_to_the_cart(): void
    {
        $product = $this->product('MAIN', 'Main product', 10);
        $firstAddon = $this->product('ADD-1', 'First add-on', 4);
        $secondAddon = $this->product('ADD-2', 'Second add-on', 6);
        $product->addons()->attach([$firstAddon->id, $secondAddon->id]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'addon_ids' => [$firstAddon->id, $secondAddon->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_item', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('cart_item', ['product_id' => $firstAddon->id, 'quantity' => 2]);
        $this->assertDatabaseHas('cart_item', ['product_id' => $secondAddon->id, 'quantity' => 2]);
    }

    public function test_unrelated_products_cannot_be_submitted_as_addons(): void
    {
        $product = $this->product('MAIN', 'Main product', 10);
        $unrelated = $this->product('OTHER', 'Unrelated product', 5);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'addon_ids' => [$unrelated->id],
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('cart_item', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('cart_item', ['product_id' => $unrelated->id]);
    }

    private function product(string $mpn, string $name, float $cost): Product
    {
        return Product::create([
            'mpn' => $mpn,
            'name' => $name,
            'description' => 'Test product',
            'cost' => $cost,
            'stock_qty' => 10,
            'status' => 'enabled',
        ]);
    }
}
