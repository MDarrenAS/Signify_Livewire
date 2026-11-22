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
        if (! Schema::hasTable('library_item_media')) {
            Schema::create('library_item_media', function (Blueprint $table): void {
                $table->foreignId('library_item_id')
                    ->constrained('library_items')
                    ->cascadeOnDelete();
                $table->foreignId('library_media_id')
                    ->constrained('library_media')
                    ->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_primary')->default(false);
                $table->string('caption')->nullable();
                $table->timestamps();

                $table->primary(['library_item_id', 'library_media_id']);
                $table->index(['library_item_id', 'sort_order'], 'library_item_media_sort_idx');
                $table->index('library_media_id', 'library_item_media_media_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_item_media');
    }
};
