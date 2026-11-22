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
        if (! Schema::hasTable('library_categories')) {
            Schema::create('library_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status')->default('published');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('library_items')) {
            Schema::create('library_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('category_id')->constrained('library_categories');
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('item_type')->default('word');
                $table->text('meaning')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status')->default('draft');
                $table->text('movement_description')->nullable();
                $table->text('example_usage')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('library_media')) {
            Schema::create('library_media', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
                $table->string('media_type');
                $table->string('storage_path')->unique();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size_bytes')->nullable();
                $table->string('alt_text')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_primary')->default(false);
                $table->text('thumbnail_path')->nullable();
                $table->string('caption')->nullable();
                $table->unsignedInteger('duration_seconds')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_media');
        Schema::dropIfExists('library_items');
        Schema::dropIfExists('library_categories');
    }
};
