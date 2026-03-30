<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            // Core identity
            $table->string('code', 50)->unique();
            $table->string('description')->nullable();

            // Discount type and value
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 8, 2);  // e.g. 10.00 = 10% or £10.00

            // Restrictions
            $table->decimal('minimum_order_value', 8, 2)->nullable(); // null = no minimum
            $table->boolean('applies_to_all_products')->default(true); // false = use pivot table
            $table->boolean('stackable')->default(false); // can be combined with other discounts
            $table->boolean('new_customers_only')->default(false);
            $table->boolean('single_use_per_user')->default(false); // one use per user

            // Limits
            $table->unsignedInteger('max_uses')->nullable(); // null = unlimited
            $table->unsignedInteger('uses_count')->default(0); // running total

            // Validity window
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // Pivot: restrict voucher to specific products
        Schema::create('voucher_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('product')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['voucher_id', 'product_id']);
        });

        // Usage log
        Schema::create('voucher_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('order')->nullOnDelete();
            $table->string('guest_email')->nullable(); // for guest checkouts
            $table->decimal('discount_applied', 8, 2); // actual £ saved
            $table->decimal('order_total_before', 8, 2);
            $table->decimal('order_total_after', 8, 2);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usage');
        Schema::dropIfExists('voucher_product');
        Schema::dropIfExists('vouchers');
    }
};
