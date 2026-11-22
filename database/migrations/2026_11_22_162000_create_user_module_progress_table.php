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
        if (! Schema::hasTable('user_module_progress')) {
            Schema::create('user_module_progress', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('modul_slug');
                $table->string('part_slug');
                $table->string('part_type')->default('lesson');
                $table->decimal('score', 5, 2)->nullable();
                $table->boolean('is_completed')->default(true);
                $table->timestampTz('completed_at')->useCurrent();
                $table->timestampsTz();

                $table->unique(['user_id', 'modul_slug', 'part_slug'], 'user_modul_part_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_module_progress');
    }
};
