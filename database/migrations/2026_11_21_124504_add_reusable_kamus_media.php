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
        if (! Schema::hasTable('library_items') || ! Schema::hasTable('library_media')) {
            if (DB::getDriverName() === 'sqlite') {
                return;
            }

            throw new RuntimeException(
                'Create the Kamus tables from the Supabase schema before running this migration.',
            );
        }

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
                $table->timestampsTz();

                $table->primary(['library_item_id', 'library_media_id']);
                $table->index(
                    ['library_item_id', 'sort_order'],
                    'library_item_media_sort_idx',
                );
                $table->index('library_media_id', 'library_item_media_media_idx');
            });
        }

        $tablePrefix = DB::getDriverName() === 'pgsql' ? 'public.' : '';

        DB::statement(<<<SQL
            INSERT INTO {$tablePrefix}library_item_media
                (library_item_id, library_media_id, sort_order, is_primary, caption, created_at, updated_at)
            SELECT library_item_id, id, sort_order, is_primary, caption, created_at, updated_at
            FROM {$tablePrefix}library_media
            WHERE library_item_id IS NOT NULL
            ON CONFLICT (library_item_id, library_media_id) DO NOTHING
            SQL);

        DB::statement(<<<SQL
            CREATE UNIQUE INDEX IF NOT EXISTS library_item_media_one_primary_per_item
            ON {$tablePrefix}library_item_media (library_item_id)
            WHERE is_primary = true
            SQL);
        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS library_item_media_sort_idx
            ON {$tablePrefix}library_item_media (library_item_id, sort_order)
            SQL);
        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS library_item_media_media_idx
            ON {$tablePrefix}library_item_media (library_media_id)
            SQL);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.library_media DROP CONSTRAINT IF EXISTS library_media_library_item_id_fkey');
            DB::statement('ALTER TABLE public.library_media ALTER COLUMN library_item_id DROP NOT NULL');
            DB::statement(<<<'SQL'
                ALTER TABLE public.library_media
                ADD CONSTRAINT library_media_library_item_id_fkey
                FOREIGN KEY (library_item_id)
                REFERENCES public.library_items (id)
                ON DELETE SET NULL
                SQL);
            DB::statement('ALTER TABLE public.library_item_media ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('library_item_media')) {
            return;
        }

        if (DB::table('library_item_media')->exists()) {
            throw new RuntimeException(
                'Shared Kamus media cannot be rolled back while media attachments exist.',
            );
        }

        if (DB::getDriverName() === 'pgsql' && Schema::hasTable('library_media')) {
            if (DB::table('library_media')->whereNull('library_item_id')->exists()) {
                throw new RuntimeException(
                    'Kamus media ownership cannot be restored while unattached media records exist.',
                );
            }

            DB::statement('ALTER TABLE public.library_media DROP CONSTRAINT IF EXISTS library_media_library_item_id_fkey');
            DB::statement('ALTER TABLE public.library_media ALTER COLUMN library_item_id SET NOT NULL');
            DB::statement(<<<'SQL'
                ALTER TABLE public.library_media
                ADD CONSTRAINT library_media_library_item_id_fkey
                FOREIGN KEY (library_item_id)
                REFERENCES public.library_items (id)
                ON DELETE CASCADE
                SQL);
        }

        Schema::drop('library_item_media');
    }
};
