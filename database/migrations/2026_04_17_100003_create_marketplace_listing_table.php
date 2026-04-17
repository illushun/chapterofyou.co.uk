<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('product')->onDelete('cascade');
            $table->string('marketplace');
            $table->string('listing_id');
            $table->enum('status', ['draft', 'active', 'synced', 'error'])->default('draft');
            $table->text('sync_error')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'marketplace']);
            $table->unique(['marketplace', 'listing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listing');
    }
};
