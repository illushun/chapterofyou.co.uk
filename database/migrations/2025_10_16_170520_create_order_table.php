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
        Schema::create('order', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_intent_id')->unique();
            $table->string('payment_type');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('telephone')->nullable();

            $table->decimal('cost_total', 8, 2);
            $table->decimal('shipping_total', 8, 2);
            $table->decimal('tax_total', 8, 2);
            $table->decimal('grand_total', 8, 2);

            $table->string('billing_line_1');
            $table->string('billing_line_2')->nullable();
            $table->string('billing_city');
            $table->string('billing_county')->nullable();
            $table->string('billing_postcode');
            $table->string('billing_country')->default('United Kingdom');

            $table->string('shipping_line_1');
            $table->string('shipping_line_2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_county')->nullable();
            $table->string('shipping_postcode');
            $table->string('shipping_country')->default('United Kingdom');

            $table->enum('status', ['not started', 'processing', 'successful', 'failed', 'cancelled', 'refunded'])->default('processing');

            $table->timestamps();
        });

        Schema::create('order_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product')->onDelete('restrict');

            $table->unsignedInteger('quantity');
            $table->decimal('product_cost', 8, 2);
            $table->decimal('product_total', 8, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order');
        Schema::dropIfExists('order_item');
    }
};
