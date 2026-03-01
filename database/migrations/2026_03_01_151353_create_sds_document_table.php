<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sds_document', function (Blueprint $table) {
            $table->id();

            $table->foreignId('oil_id')
                ->constrained('oil')
                ->onDelete('cascade');

            $table->string('file_path');
            $table->string('document_hash')->nullable();
            $table->string('version')->nullable();
            $table->date('issue_date')->nullable();

            $table->boolean('parsed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sds_document');
    }
};
