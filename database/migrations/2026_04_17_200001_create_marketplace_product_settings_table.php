<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_product_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('product')->onDelete('cascade');
            $table->string('marketplace');
            $table->boolean('enabled')->default(true);
            $table->string('override_title')->nullable();
            $table->text('override_description')->nullable();
            $table->decimal('override_price', 8, 2)->nullable();
            $table->text('override_tags')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'marketplace']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_product_settings');
    }
};
