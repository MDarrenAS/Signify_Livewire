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
        if (! Schema::hasTable('quiz_attempts')) {
            Schema::create('quiz_attempts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
                $table->string('status')->default('submitted');
                $table->decimal('score', 5, 2)->default(0);
                $table->decimal('points_earned', 5, 2)->default(0);
                $table->decimal('points_total', 5, 2)->default(0);
                $table->boolean('passed')->default(false);
                $table->integer('attempt_number')->default(1);
                $table->timestampTz('started_at')->useCurrent();
                $table->timestampTz('submitted_at')->nullable();
                $table->timestampsTz();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
