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
        Schema::create('courier', function (Blueprint $table) {
            $table->id();

            $table->string("name")->nullable(true)->default(null);
            $table->enum("type", ["Royal Mail", "FedEx", "Evri", "DPD"])->default("Royal Mail");
            $table->enum("status", ["enabled", "disabled"])->default("enabled");
            $table->decimal("cost")->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier');
    }
};
