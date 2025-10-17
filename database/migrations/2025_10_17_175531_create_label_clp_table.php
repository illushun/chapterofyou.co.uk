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
        Schema::create('label_clp', function (Blueprint $table) {
            $table->id();

            // Link to the product this label applies to (assuming the CLP is tied to a specific product)
            $table->foreignId('product_id')->nullable()->constrained('product')->onDelete('set null');

            // Product and Concentration Details
            $table->string('product_name');
            $table->float('concentration_percent', 8, 2);

            // Supplier Details
            $table->string('supplier_name');
            $table->string('supplier_address');
            $table->string('supplier_phone');

            // Generated CLP Data (stored as JSON)
            $table->string('signal_word')->nullable(); // e.g., 'Danger', 'Warning'
            $table->json('required_pictograms')->nullable(); // e.g., ['exclamation', 'environment']
            $table->json('hazard_statements'); // List of H-Codes, e.g., ['H317', 'H411']
            $table->json('precautionary_statements'); // List of P-Codes, e.g., ['P102', 'P280']
            $table->text('supplementary_info')->nullable();

            // Raw list of ingredients used for calculation (optional for transparency)
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
