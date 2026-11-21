<?php

use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\LibraryMedia;
use App\Models\User;
use GuzzleHttp\Handler\MockHandler as GuzzleMockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    Schema::create('library_categories', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->string('status')->default('published');
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestampsTz();
    });

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
        $table->timestampsTz();
    });

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
        $table->string('thumbnail_path')->nullable();
        $table->string('caption')->nullable();
        $table->unsignedInteger('duration_seconds')->nullable();
        $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestampsTz();
    });

    Schema::create('library_item_media', function (Blueprint $table): void {
        $table->foreignId('library_item_id')->constrained('library_items')->cascadeOnDelete();
        $table->foreignId('library_media_id')->constrained('library_media')->cascadeOnDelete();
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_primary')->default(false);
        $table->string('caption')->nullable();
        $table->timestampsTz();
        $table->primary(['library_item_id', 'library_media_id']);
        $table->index(['library_item_id', 'sort_order'], 'library_item_media_sort_idx');
        $table->index('library_media_id', 'library_item_media_media_idx');
    });

    DB::statement(<<<'SQL'
        CREATE UNIQUE INDEX library_item_media_one_primary_per_item
        ON library_item_media (library_item_id)
        WHERE is_primary = true
        SQL);
});

it('lists only published entries in published categories', function (): void {
    $category = LibraryCategory::factory()->create(['name' => 'Huruf']);
    $visibleItem = LibraryItem::factory()
        ->for($category, 'category')
        ->create(['title' => 'A', 'slug' => 'huruf-a']);
    $draftItem = LibraryItem::factory()
        ->for($category, 'category')
        ->create(['title' => 'Draft', 'slug' => 'huruf-draft', 'status' => 'draft']);

    $hiddenCategory = LibraryCategory::factory()->create(['status' => 'draft']);
    LibraryItem::factory()
        ->for($hiddenCategory, 'category')
        ->create(['title' => 'Hidden', 'slug' => 'hidden-item']);

    $this->actingAs(User::factory()->create())
        ->get(route('kamus.index'))
        ->assertOk()
        ->assertSee($visibleItem->title)
        ->assertDontSee($draftItem->title)
        ->assertDontSee('Hidden');
});

it('renders published entry details and signed private media URLs', function (): void {
    Storage::fake('s3');
    config(['filesystems.disks.s3.serve' => true]);
    Storage::disk('s3')->put('kamus/hello.jpg', 'image-bytes');
    Storage::disk('s3')->put('kamus/hello.mp4', 'video-bytes');

    $item = LibraryItem::factory()->create([
        'title' => 'Halo <script>alert("x")</script>',
        'slug' => 'halo',
        'meaning' => 'A greeting',
    ]);
    $media = LibraryMedia::factory()->create([
        'storage_path' => 'kamus/hello.jpg',
        'alt_text' => 'Greeting sign',
    ]);
    $video = LibraryMedia::factory()->create([
        'media_type' => 'video',
        'storage_path' => 'kamus/hello.mp4',
        'mime_type' => 'video/mp4',
    ]);
    $item->media()->attach($media->id, [
        'sort_order' => 0,
        'is_primary' => true,
        'caption' => 'Greeting image',
    ]);
    $item->media()->attach($video->id, [
        'sort_order' => 1,
        'is_primary' => false,
        'caption' => 'Greeting video',
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('kamus.show', $item->slug));

    $response->assertOk()
        ->assertSee(e($item->title), false)
        ->assertDontSee($item->title, false)
        ->assertSee('A greeting')
        ->assertSee('Greeting image')
        ->assertSee('Greeting video')
        ->assertSee('hello.jpg')
        ->assertSee('hello.mp4')
        ->assertSee('<video controls', false)
        ->assertSee('expiration=', false);
});

it('omits unsupported ACL headers from Supabase S3 uploads', function (): void {
    $history = [];
    $handler = HandlerStack::create(new GuzzleMockHandler([
        new Response(200, ['ETag' => '"test-etag"']),
    ]));
    $handler->push(Middleware::history($history));

    config([
        'filesystems.disks.s3.key' => 'test-key',
        'filesystems.disks.s3.secret' => 'test-secret',
        'filesystems.disks.s3.region' => 'us-east-1',
        'filesystems.disks.s3.bucket' => 'bisindo-media',
        'filesystems.disks.s3.endpoint' => 'http://localhost:9000/storage/v1/s3',
        'filesystems.disks.s3.http_handler' => $handler,
    ]);
    Storage::forgetDisk('s3');

    expect(Storage::disk('s3')->put('kamus/test.jpg', 'image-bytes'))->toBeTrue()
        ->and($history)->toHaveCount(1)
        ->and($history[0]['request']->hasHeader('x-amz-acl'))->toBeFalse();
});

it('does not expose unpublished entries on their detail route', function (): void {
    $item = LibraryItem::factory()->create(['status' => 'draft']);

    $this->actingAs(User::factory()->create())
        ->get(route('kamus.show', $item->slug))
        ->assertNotFound();
});

it('forbids non-admin users from the Kamus management page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.kamus.index'))
        ->assertForbidden();
});

it('requires a published category before publishing an entry', function (): void {
    $category = LibraryCategory::factory()->create(['status' => 'draft']);
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.kamus.index')
        ->set('categoryId', $category->id)
        ->set('title', 'Unavailable category')
        ->set('slug', 'unavailable-category')
        ->set('status', 'published')
        ->call('save')
        ->assertHasErrors(['categoryId' => 'exists']);

    $this->assertDatabaseMissing('library_items', ['slug' => 'unavailable-category']);
});

it('creates an entry and uploads validated media to the private disk', function (): void {
    Storage::fake('s3');
    $category = LibraryCategory::factory()->create();
    $admin = User::factory()->admin()->create();
    $upload = UploadedFile::fake()->image('hello.jpg');

    Livewire::actingAs($admin)
        ->test('pages::admin.kamus.index')
        ->set('categoryId', $category->id)
        ->set('title', 'Halo')
        ->set('slug', 'halo')
        ->set('itemType', 'word')
        ->set('status', 'published')
        ->set('upload', $upload)
        ->set('uploadAltText', 'Isyarat halo')
        ->set('uploadCaption', 'Tampilan tangan')
        ->call('save')
        ->assertHasNoErrors();

    $item = LibraryItem::query()->where('slug', 'halo')->firstOrFail();
    $media = LibraryMedia::query()->firstOrFail();

    $this->assertDatabaseHas('library_item_media', [
        'library_item_id' => $item->id,
        'library_media_id' => $media->id,
        'is_primary' => true,
        'caption' => 'Tampilan tangan',
    ]);
    Storage::disk('s3')->assertExists($media->storage_path);
    expect($media->mime_type)->toBe('image/jpeg');
});

it('stores uploaded videos as reusable video media', function (): void {
    Storage::fake('s3');
    $category = LibraryCategory::factory()->create();
    $admin = User::factory()->admin()->create();
    $upload = UploadedFile::fake()->create('lesson.mp4', 32, 'video/mp4');

    Livewire::actingAs($admin)
        ->test('pages::admin.kamus.index')
        ->set('categoryId', $category->id)
        ->set('title', 'Video Kamus')
        ->set('slug', 'video-kamus')
        ->set('itemType', 'word')
        ->set('upload', $upload)
        ->call('save')
        ->assertHasNoErrors();

    $media = LibraryMedia::query()->firstOrFail();

    expect($media->media_type)->toBe('video')
        ->and($media->mime_type)->toBe('video/mp4');
    Storage::disk('s3')->assertExists($media->storage_path);
});

it('rejects uploads with an unsupported MIME type without creating an entry', function (): void {
    Storage::fake('s3');
    $category = LibraryCategory::factory()->create();
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.kamus.index')
        ->set('categoryId', $category->id)
        ->set('title', 'Unsupported file')
        ->set('slug', 'unsupported-file')
        ->set('upload', UploadedFile::fake()->create('notes.txt', 1, 'text/plain'))
        ->call('save')
        ->assertHasErrors(['upload' => 'mimes']);

    $this->assertDatabaseMissing('library_items', ['slug' => 'unsupported-file']);
    expect(Storage::disk('s3')->allFiles())->toBeEmpty();
});

it('reuses one media record across entries and preserves attachment ordering', function (): void {
    $admin = User::factory()->admin()->create();
    $firstItem = LibraryItem::factory()->create();
    $secondItem = LibraryItem::factory()->create();
    $firstMedia = LibraryMedia::factory()->create();
    $secondMedia = LibraryMedia::factory()->create();
    $firstItem->media()->attach($firstMedia->id, [
        'sort_order' => 0,
        'is_primary' => true,
    ]);
    $firstItem->media()->attach($secondMedia->id, [
        'sort_order' => 1,
        'is_primary' => false,
    ]);
    $component = Livewire::actingAs($admin)->test('pages::admin.kamus.index');

    $component
        ->call('edit', $firstItem->id)
        ->call('moveMedia', $secondMedia->id, -1)
        ->call('save')
        ->assertHasNoErrors();

    $component
        ->call('edit', $secondItem->id)
        ->set('selectedMediaIds', [(string) $firstMedia->id])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('library_item_media', [
        'library_item_id' => $firstItem->id,
        'library_media_id' => $secondMedia->id,
        'sort_order' => 0,
        'is_primary' => true,
    ]);
    $this->assertDatabaseHas('library_item_media', [
        'library_item_id' => $firstItem->id,
        'library_media_id' => $firstMedia->id,
        'sort_order' => 1,
        'is_primary' => false,
    ]);
    expect($firstMedia->fresh()->items)->toHaveCount(2);
});

it('deletes an entry without deleting its reusable media asset', function (): void {
    Storage::fake('s3');
    Storage::disk('s3')->put('kamus/shared.jpg', 'image-bytes');
    $admin = User::factory()->admin()->create();
    $item = LibraryItem::factory()->create();
    $otherItem = LibraryItem::factory()->create();
    $media = LibraryMedia::factory()->create(['storage_path' => 'kamus/shared.jpg']);
    $item->media()->attach($media->id, [
        'sort_order' => 0,
        'is_primary' => true,
    ]);
    $otherItem->media()->attach($media->id, [
        'sort_order' => 0,
        'is_primary' => true,
    ]);

    Livewire::actingAs($admin)
        ->test('pages::admin.kamus.index')
        ->call('delete', $item->id)
        ->assertHasNoErrors();

    $this->assertModelMissing($item);
    $this->assertModelExists($media);
    $this->assertDatabaseMissing('library_item_media', [
        'library_item_id' => $item->id,
        'library_media_id' => $media->id,
    ]);
    $this->assertDatabaseHas('library_item_media', [
        'library_item_id' => $otherItem->id,
        'library_media_id' => $media->id,
    ]);
    Storage::disk('s3')->assertExists('kamus/shared.jpg');
});

it('backfills legacy media ownership into the reusable relationship table', function (): void {
    $item = LibraryItem::factory()->create();
    $media = LibraryMedia::factory()->create([
        'library_item_id' => $item->id,
        'sort_order' => 3,
        'is_primary' => true,
        'caption' => 'Legacy caption',
    ]);

    Schema::drop('library_item_media');
    $migration = require database_path('migrations/2026_11_21_124504_add_reusable_kamus_media.php');
    $migration->up();

    $this->assertDatabaseHas('library_item_media', [
        'library_item_id' => $item->id,
        'library_media_id' => $media->id,
        'sort_order' => 3,
        'is_primary' => true,
        'caption' => 'Legacy caption',
    ]);
});
