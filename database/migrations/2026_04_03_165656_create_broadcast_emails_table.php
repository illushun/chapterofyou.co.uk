<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('broadcast_email', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');               // Rich HTML body written by admin
            $table->string('audience');         // 'all' | 'customers_only' | 'ordered_last_90' | 'never_ordered'
            $table->integer('recipient_count'); // How many addresses were queued
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_emails');
    }
};
