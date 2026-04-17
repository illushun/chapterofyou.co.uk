<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_product_cost', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('cost_item_id');
            $table->decimal('qty_per_unit', 10, 4)->default(1); // how much of this item per finished product
            $table->timestamps();

            $table->unique(['product_id', 'cost_item_id']);
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('cost_item_id')->references('id')->on('finance_cost_item')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_product_cost');
    }
};
