<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('gift_voucher_order', function (Blueprint $table) {
            $table->id();

            // The order this gift voucher was purchased in
            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');

            // The voucher that was created for this gift voucher purchase
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('set null');

            // E-voucher or physical
            $table->enum('delivery_type', ['email', 'physical']);

            // The value the customer purchased (e.g. 10.00, 20.00, 50.00)
            $table->decimal('amount', 8, 2);

            // Recipient details (may differ from purchaser)
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();   // required for e-voucher
            $table->string('personal_message', 500)->nullable();

            // Fulfilment tracking
            // null = not yet sent, timestamp = when code was sent/dispatched
            $table->timestamp('fulfilled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_voucher_order');
    }
};
