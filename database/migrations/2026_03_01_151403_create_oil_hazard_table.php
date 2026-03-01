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
        Schema::create('oil_hazard', function (Blueprint $table) {
            $table->id();

            $table->foreignId('oil_id')
                ->constrained('oil')
                ->onDelete('cascade');

            $table->string('hazard_class'); // Skin Sensitisation
            $table->string('category');     // 1A / 1B
            $table->string('hazard_code');  // H317
            $table->string('signal_word')->nullable();
            $table->string('pictogram')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oil_hazard');
    }
};
