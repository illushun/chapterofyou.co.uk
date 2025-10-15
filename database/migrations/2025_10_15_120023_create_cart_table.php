<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->unique()->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->index(['session_id', 'user_id']);
        });

        Schema::create('cart_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            // prevent duplicate product entries in the same cart
            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
        Schema::dropIfExists('cart_item');
    }
};
