<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_review', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('order')->onDelete('cascade');

            $table->string('email');
            $table->timestamp('sent')->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_review');
    }
};
