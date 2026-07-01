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
        Schema::table('journal_auto_generator_settings', function (Blueprint $table) {
            // Day of month (1-31) used for the monthly cadence. If the current month
            // has fewer days than this value, generation falls back to the last day.
            $table->unsignedTinyInteger('day_of_month')->nullable()->after('day_of_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_auto_generator_settings', function (Blueprint $table) {
            $table->dropColumn('day_of_month');
        });
    }
};
