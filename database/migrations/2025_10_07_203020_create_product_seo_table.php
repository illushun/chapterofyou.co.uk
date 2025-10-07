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

            $table->unsignedBigInteger('product_id');
            $table->string("meta_title")->nullable(true)->default(null);
            $table->string("meta_description")->nullable(true)->default(null);
            $table->string("slug")->nullable(true)->default(null);

            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');

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
