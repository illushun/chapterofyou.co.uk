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
        Schema::create('product_seo', function (Blueprint $table) {
            $table->id();

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->string(255, "meta_title")->nullable(true)->default(null);
            $table->string(255, "meta_description")->nullable(true)->default(null);
            $table->string(255, "slug")->nullable(true)->default(null);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_seo');
    }
};
