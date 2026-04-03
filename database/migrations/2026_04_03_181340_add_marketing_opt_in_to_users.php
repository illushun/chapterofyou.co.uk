<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('marketing_opt_in')->default(false)->after('remember_token');
            $table->timestamp('opted_in_at')->nullable()->after('marketing_opt_in');
            $table->timestamp('opted_out_at')->nullable()->after('opted_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['marketing_opt_in', 'opted_in_at', 'opted_out_at']);
        });
    }
};
