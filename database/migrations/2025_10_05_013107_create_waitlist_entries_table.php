<?php

// database/migrations/YYYY_MM_DD_create_waitlist_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique(); // Ensures no duplicate emails
            $table->timestamps(); // Created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};