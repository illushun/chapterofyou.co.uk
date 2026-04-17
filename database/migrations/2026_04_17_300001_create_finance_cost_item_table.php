<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_cost_item', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('other'); // packaging, fragrance, material, labour, overhead, other
            $table->string('supplier_name')->nullable();
            $table->string('supplier_url')->nullable();
            $table->decimal('purchase_price', 10, 4); // total cost per purchase
            $table->unsignedInteger('purchase_qty');   // units received per purchase
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_cost_item');
    }
};
