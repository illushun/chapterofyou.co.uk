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
        Schema::create('label_clp', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->nullable()->constrained('product')->onDelete('set null');
            $table->string('product_name');
            $table->string('supplier_name')->nullable();
            $table->string('supplier_address')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->string('signal_word')->nullable();
            $table->json('required_pictograms')->nullable();
            $table->json('hazard_statements')->nullable();
            $table->json('precautionary_statements')->nullable();
            $table->text('supplementary_info')->nullable();
            $table->json('ingredients_json')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('label_clp');
    }
};
