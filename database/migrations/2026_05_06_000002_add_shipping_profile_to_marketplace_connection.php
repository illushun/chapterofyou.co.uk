<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_connection', function (Blueprint $table) {
            $table->string('default_shipping_profile_id')->nullable()->after('shop_name');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_connection', function (Blueprint $table) {
            $table->dropColumn('default_shipping_profile_id');
        });
    }
};
