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
        Schema::create('journal_auto_generator_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('enabled')->default(false);

            // How often the generator should run: daily | weekly | biweekly | monthly
            $table->string('frequency')->default('weekly');

            // Day of week (0=Sun..6=Sat) used for weekly/biweekly/monthly cadences
            $table->unsignedTinyInteger('day_of_week')->nullable();

            // Optional steering text an admin can give the generator (e.g. seasonal focus)
            $table->text('topic_notes')->nullable();

            $table->timestamp('last_generated_at')->nullable();
            $table->string('last_run_status')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_auto_generator_settings');
    }
};
