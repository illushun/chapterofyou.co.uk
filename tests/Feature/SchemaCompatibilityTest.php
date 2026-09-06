<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_alignment_preserves_legacy_names_and_product_views(): void
    {
        $user = User::factory()->create(['first_name' => 'Test', 'last_name' => 'Buyer']);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('name'));
        $product = Product::create(['name' => 'Candle', 'mpn' => 'TEST', 'description' => 'Test candle']);
        Schema::rename('product_view', 'product_views');
        DB::table('product_views')->insert(['product_id' => $product->id, 'ip_address' => '127.0.0.1', 'views' => 4]);

        $migration = require database_path('migrations/2026_09_06_000001_align_storefront_schema.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Test Buyer', 'first_name' => 'Test', 'last_name' => 'Buyer']);
        $this->assertDatabaseHas('product_view', ['product_id' => $product->id, 'views' => 4]);
        $this->assertFalse(Schema::hasTable('product_views'));
    }
}
