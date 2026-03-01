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
        Schema::create('oil_component', function (Blueprint $table) {
            $table->id();

            $table->foreignId('oil_id')
                ->constrained('oil')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('cas')->nullable();
            $table->decimal('concentration_min', 5, 2)->nullable();
            $table->decimal('concentration_max', 5, 2)->nullable();

            $table->string('clp_classification')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oil_component');
    }
};
