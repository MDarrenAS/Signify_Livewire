<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Quizzes table
        if (! Schema::hasTable('quizzes')) {
            Schema::create('quizzes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('module_id')->nullable();
                $table->unsignedBigInteger('lesson_id')->nullable();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('level')->default('Pemula');
                $table->unsignedInteger('xp_reward')->default(50);
                $table->string('icon_emoji')->default('🎯');
                $table->string('icon_url')->nullable();
                $table->decimal('passing_score', 5, 2)->default(70);
                $table->string('status')->default('draft');
                $table->unsignedInteger('sort_order')->default(0);
                $table->integer('time_limit_minutes')->nullable();
                $table->integer('attempt_limit')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestampsTz();
            });
        } else {
            Schema::table('quizzes', function (Blueprint $table): void {
                if (! Schema::hasColumn('quizzes', 'level')) {
                    $table->string('level')->default('Pemula');
                }
                if (! Schema::hasColumn('quizzes', 'xp_reward')) {
                    $table->unsignedInteger('xp_reward')->default(50);
                }
                if (! Schema::hasColumn('quizzes', 'icon_emoji')) {
                    $table->string('icon_emoji')->default('🎯');
                }
                if (! Schema::hasColumn('quizzes', 'icon_url')) {
                    $table->string('icon_url')->nullable();
                }
                if (! Schema::hasColumn('quizzes', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }
            });
        }

        // 2. Quiz Questions table
        if (! Schema::hasTable('quiz_questions')) {
            Schema::create('quiz_questions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
                $table->foreignId('library_media_id')->nullable()->constrained('library_media')->nullOnDelete();
                $table->text('question_text')->nullable();
                $table->string('question_type')->default('sign_to_word');
                $table->string('media_url')->nullable();
                $table->decimal('points', 5, 2)->default(1);
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('explanation')->nullable();
                $table->timestampsTz();
            });
        } else {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE quiz_questions DROP CONSTRAINT IF EXISTS quiz_questions_question_type_check');
            }

            Schema::table('quiz_questions', function (Blueprint $table): void {
                if (! Schema::hasColumn('quiz_questions', 'media_url')) {
                    $table->string('media_url')->nullable();
                }
            });
        }

        // 3. Quiz Options table
        if (! Schema::hasTable('quiz_options')) {
            Schema::create('quiz_options', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
                $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
                $table->foreignId('library_media_id')->nullable()->constrained('library_media')->nullOnDelete();
                $table->string('option_text')->nullable();
                $table->string('media_url')->nullable();
                $table->boolean('is_correct')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestampsTz();
            });
        } else {
            Schema::table('quiz_options', function (Blueprint $table): void {
                if (! Schema::hasColumn('quiz_options', 'media_url')) {
                    $table->string('media_url')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down: drop added columns or tables
        if (Schema::hasTable('quiz_options')) {
            Schema::dropIfExists('quiz_options');
        }
        if (Schema::hasTable('quiz_questions')) {
            Schema::dropIfExists('quiz_questions');
        }
        if (Schema::hasTable('quizzes')) {
            Schema::dropIfExists('quizzes');
        }
    }
};
