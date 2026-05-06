<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_listing', function (Blueprint $table) {
            $table->string('listing_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_listing', function (Blueprint $table) {
            $table->string('listing_id')->nullable(false)->change();
        });
    }
};
