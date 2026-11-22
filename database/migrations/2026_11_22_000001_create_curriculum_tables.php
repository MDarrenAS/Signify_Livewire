<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Matches the Supabase production PostgreSQL schema.
     */
    public function up(): void
    {
        if (! Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('thumbnail_path')->nullable();
                $table->string('level')->default('beginner'); // beginner, intermediate, advanced
                $table->integer('sort_order')->default(0);
                $table->string('status')->default('draft'); // draft, published, archived
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('module_lessons')) {
            Schema::create('module_lessons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
                $table->string('title');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->text('content')->nullable();
                $table->integer('sort_order')->default(0);
                $table->string('status')->default('draft'); // draft, published, archived
                $table->string('lesson_type')->default('lesson'); // lesson, quiz
                $table->text('summary')->nullable();
                $table->integer('estimated_minutes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lesson_library_items')) {
            Schema::create('lesson_library_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lesson_id')->constrained('module_lessons')->cascadeOnDelete();
                $table->foreignId('library_item_id')->constrained('library_items')->cascadeOnDelete();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lesson_media')) {
            Schema::create('lesson_media', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lesson_id')->constrained('module_lessons')->cascadeOnDelete();
                $table->string('media_type'); // image, video
                $table->text('storage_path')->unique();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size_bytes')->nullable();
                $table->string('alt_text')->nullable();
                $table->integer('sort_order')->default(0);
                $table->string('caption')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('module_id')->nullable()->constrained('modules')->cascadeOnDelete();
                $table->foreignId('lesson_id')->nullable()->constrained('module_lessons')->cascadeOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('passing_score', 5, 2)->default(70);
                $table->string('status')->default('draft');
                $table->integer('time_limit_minutes')->nullable();
                $table->integer('attempt_limit')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
                $table->foreignId('library_media_id')->nullable()->constrained('library_media')->nullOnDelete();
                $table->text('question_text')->nullable();
                $table->string('question_type')->default('multiple_choice');
                $table->decimal('points', 5, 2)->default(1);
                $table->integer('sort_order')->default(0);
                $table->text('explanation')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quiz_options')) {
            Schema::create('quiz_options', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
                $table->foreignId('library_media_id')->nullable()->constrained('library_media')->nullOnDelete();
                $table->string('option_text')->nullable();
                $table->boolean('is_correct')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_lesson_progress')) {
            Schema::create('user_lesson_progress', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('lesson_id')->constrained('module_lessons')->cascadeOnDelete();
                $table->string('status')->default('in_progress'); // in_progress, completed
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('lesson_media');
        Schema::dropIfExists('lesson_library_items');
        Schema::dropIfExists('module_lessons');
        Schema::dropIfExists('modules');
    }
};
