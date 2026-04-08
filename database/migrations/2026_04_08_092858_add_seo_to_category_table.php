<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('meta_title', 255)->nullable()->after('slug');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->text('description')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn(['slug', 'meta_title', 'meta_description', 'description']);
        });
    }
};
