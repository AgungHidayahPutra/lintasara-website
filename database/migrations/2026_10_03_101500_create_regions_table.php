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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();

            // Hierarki wilayah
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            // Informasi wilayah
            $table->string('name');
            $table->string('slug')->unique();

            $table->enum('type', [
                'province',
                'city',
                'regency',
            ]);

            // Pengaturan
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // Index
            $table->index(['type', 'is_active']);
            $table->index(['parent_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
