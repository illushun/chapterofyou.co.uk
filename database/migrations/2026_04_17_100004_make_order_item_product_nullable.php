<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->dropForeign(['order_id']);
                $table->foreign('order_id')->references('id')->on('order')->cascadeOnDelete();
            }
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->dropForeign(['order_id']);
                $table->foreign('order_id')->references('id')->on('order')->cascadeOnDelete();
            }
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
