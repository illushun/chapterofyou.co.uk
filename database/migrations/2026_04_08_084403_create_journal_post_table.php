<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('journal_post', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            // Short summary shown on listing page and in meta description
            $table->text('excerpt')->nullable();

            // Full HTML body (rich text)
            $table->longText('body');

            // Cover image path (stored in public/storage)
            $table->string('cover_image')->nullable();

            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();

            // Categorisation — simple comma-separated tags for now
            $table->string('tags')->nullable();

            // Publishing
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Author (admin user)
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_post');
    }
};
