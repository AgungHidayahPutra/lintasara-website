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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('region_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            // Konten berita
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            // Gambar utama
            $table->string('thumbnail')->nullable();
            $table->string('thumbnail_alt')->nullable();
            $table->string('image_caption')->nullable();
            $table->string('image_credit')->nullable();

            // Status publikasi
            $table->enum('status', [
                'draft',
                'published',
                'archived',
            ])->default('draft');

            // Penempatan berita
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false);

            // Waktu publikasi
            $table->timestamp('published_at')->nullable();

            // Statistik
            $table->unsignedBigInteger('views')->default(0);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            // Index
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'published_at']);
            $table->index(['region_id', 'status', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index(['is_featured', 'status', 'published_at']);
            $table->index(['is_breaking', 'status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
