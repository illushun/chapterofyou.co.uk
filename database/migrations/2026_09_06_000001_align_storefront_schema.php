<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'name')) {
            Schema::table('users', fn (Blueprint $table) => $table->string('name')->nullable());

            DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')->where('id', $user->id)->update([
                        'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
                    ]);
                }
            });
        }

        foreach (['first_name', 'last_name'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', fn (Blueprint $table) => $table->string($column)->nullable()->change());
            }
        }

        if (Schema::hasTable('product_views') && ! Schema::hasTable('product_view')) {
            Schema::rename('product_views', 'product_view');
        }
    }

    public function down(): void
    {
        // Preserve names and view history because existing installations may already use this schema.
    }
};
