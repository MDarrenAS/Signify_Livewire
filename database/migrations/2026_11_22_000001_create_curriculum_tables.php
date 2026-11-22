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
        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('unit_number')->default(1);
            $table->string('level')->default('TINGKAT DASAR');
            $table->string('badge')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon_emoji')->nullable()->default('🤟');
            $table->string('icon_url')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('xp_reward')->default(50);
            $table->string('visual_content')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('module_lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type')->default('lesson'); // 'lesson' or 'quiz'
            $table->string('subtitle')->nullable();
            $table->string('category')->nullable();
            $table->string('badge_text')->nullable();
            $table->unsignedInteger('xp_reward')->default(10);
            $table->unsignedInteger('passing_score')->default(80);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
        });

        Schema::create('lesson_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_lesson_id')->constrained('module_lessons')->cascadeOnDelete();
            $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
            $table->string('symbol')->nullable();
            $table->string('title');
            $table->string('hand_type')->default('1 Tangan');
            $table->text('video_url')->nullable();
            $table->text('instruction')->nullable();
            $table->text('tip')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('module_lesson_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_lesson_id')->constrained('module_lessons')->cascadeOnDelete();
            $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
            $table->text('question');
            $table->text('video_url')->nullable();
            $table->string('symbol_hint')->nullable();
            $table->json('options');
            $table->unsignedInteger('correct_index')->default(0);
            $table->text('explanation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_lesson_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_lesson_id')->constrained('module_lessons')->cascadeOnDelete();
            $table->string('status')->default('completed');
            $table->unsignedInteger('score')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module_lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
        Schema::dropIfExists('module_lesson_questions');
        Schema::dropIfExists('lesson_items');
        Schema::dropIfExists('module_lessons');
        Schema::dropIfExists('modules');
        Schema::dropIfExists('units');
    }
};
