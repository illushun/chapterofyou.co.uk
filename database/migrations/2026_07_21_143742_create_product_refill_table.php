<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_refill', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('refill_product_id');

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('refill_product_id')->references('id')->on('product')->onDelete('cascade');
            $table->unique(['product_id', 'refill_product_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_refill');
    }
};
