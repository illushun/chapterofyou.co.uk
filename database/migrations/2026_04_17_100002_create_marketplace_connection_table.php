<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_connection', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace')->unique();
            $table->string('shop_id')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('etsy_user_id')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('scopes')->nullable();
            $table->timestamp('last_order_import_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_connection');
    }
};
