<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('batch_sheets', function (Blueprint $table) {
            $table->id();

            // Links
            $table->foreignId('order_id')->nullable()->constrained('order')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('product')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Header fields
            $table->string('batch_number');           // e.g. COY-001-SW
            $table->string('blend_name');
            $table->date('date_of_manufacture');
            $table->string('produced_by');
            $table->unsignedInteger('bottle_size_ml');
            $table->string('total_units_produced');

            // Ingredients — stored as JSON array of rows
            // Each row: { ingredient, supplier, lot_batch_no, percent_used, weight_g, sds_ifra_ref }
            $table->json('ingredients');

            // Compliance checks
            $table->boolean('ifra_certificate_checked')->default(false);
            $table->string('max_percent_allowed')->nullable();
            $table->string('sds_hazards_noted')->nullable();
            $table->boolean('clp_label_prepared')->default(false);

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_sheets');
    }
};
