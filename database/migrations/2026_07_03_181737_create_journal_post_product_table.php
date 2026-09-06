<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_post_product', function (Blueprint $table) {
            $table->id();

            $table->foreignId('journal_post_id')
                ->constrained('journal_post')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('product')
                ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['journal_post_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_post_product');
    }
};
