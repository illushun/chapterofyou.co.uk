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
        Schema::create('product_image', function (Blueprint $table) {
            $table->id();

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->string(255, "image")->nullable(true)->default(null);
            $table->enum("status", ["enabled", "disabled"])->default("enabled");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_image');
    }
};
