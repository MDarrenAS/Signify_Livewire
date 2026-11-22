<?php

use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\LibraryMedia;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('library_categories')) {
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
            $table->string('status')->default('published');
            $table->text('movement_description')->nullable();
            $table->text('example_usage')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });
    }

    if (! Schema::hasTable('library_media')) {
        Schema::create('library_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('library_item_id')->nullable()->constrained('library_items')->nullOnDelete();
            $table->string('media_type')->default('video');
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
    }

    if (! Schema::hasTable('library_item_media')) {
        Schema::create('library_item_media', function (Blueprint $table): void {
            $table->foreignId('library_item_id')->constrained('library_items')->cascadeOnDelete();
            $table->foreignId('library_media_id')->constrained('library_media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('caption')->nullable();
            $table->timestampsTz();
            $table->primary(['library_item_id', 'library_media_id']);
        });
    }

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
    }

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
    }

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
    }

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
});

it('lists only published quizzes on student latihan page', function (): void {
    $published = Quiz::factory()->published()->create([
        'title' => 'Kuis Abjad Aktif',
        'level' => 'Pemula',
    ]);

    $draft = Quiz::factory()->draft()->create([
        'title' => 'Kuis Draft Tersembunyi',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('latihan.index'))
        ->assertOk()
        ->assertSee('Kuis Abjad Aktif')
        ->assertDontSee('Kuis Draft Tersembunyi');
});

it('allows admin to create a quiz package stored in database', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('title', 'Tantangan Huruf Vokal')
        ->set('subtitle', 'Latihan mengenali A, I, U, E, O')
        ->set('level', 'Pemula')
        ->set('xpReward', 60)
        ->set('iconEmoji', '🔤')
        ->set('isActive', true)
        ->call('savePackage')
        ->assertHasNoErrors();

    expect(Quiz::where('title', 'Tantangan Huruf Vokal')->exists())->toBeTrue();
    $quiz = Quiz::where('title', 'Tantangan Huruf Vokal')->first();
    expect($quiz->xp_reward)->toBe(60);
    expect($quiz->status)->toBe('published');
});

it('allows admin to toggle quiz status between draft and published', function (): void {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->published()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->call('toggleStatus', $quiz->id);

    expect($quiz->fresh()->status)->toBe('draft');

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->call('toggleStatus', $quiz->id);

    expect($quiz->fresh()->status)->toBe('published');
});

it('allows admin to add questions with options to a quiz package', function (): void {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    $category = LibraryCategory::factory()->create(['name' => 'Abjad']);
    $item = LibraryItem::factory()->for($category, 'category')->create(['title' => 'Huruf B']);

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->set('questionType', 'sign_to_word')
        ->set('selectedLibraryItemId', $item->id)
        ->set('questionText', 'Isyarat berikut melambangkan huruf apa?')
        ->set('questionOptions', ['Huruf A', 'Huruf B', 'Huruf C', 'Huruf D'])
        ->set('questionCorrectIndex', 1)
        ->call('saveQuestion')
        ->assertHasNoErrors();

    expect($quiz->questions()->count())->toBe(1);
    $question = $quiz->questions()->first();
    expect($question->question_type)->toBe('sign_to_word');
    expect($question->options()->count())->toBe(4);

    $correctOption = $question->correctOption;
    expect($correctOption->option_text)->toBe('Huruf B');
    expect($correctOption->is_correct)->toBeTrue();
});

it('allows admin to edit a question and attach media to an answer option', function (): void {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();
    $question = QuizQuestion::factory()->create([
        'quiz_id' => $quiz->id,
        'question_text' => 'Pertanyaan lama',
    ]);

    foreach (['Lama A', 'Lama B', 'Lama C', 'Lama D'] as $index => $optionText) {
        QuizOption::factory()->create([
            'question_id' => $question->id,
            'option_text' => $optionText,
            'sort_order' => $index,
            'is_correct' => $index === 0,
        ]);
    }

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->call('openEditQuestion', $question->id)
        ->assertSee('Media Pilihan A')
        ->set('questionText', 'Pertanyaan yang diperbarui')
        ->set('questionOptions', ['Jawaban A', '', 'Jawaban C', 'Jawaban D'])
        ->set('questionOptionMediaFiles.1', UploadedFile::fake()->create('jawaban.mp4', 100, 'video/mp4'))
        ->set('questionCorrectIndex', 1)
        ->call('saveQuestion')
        ->assertHasNoErrors();

    $updatedQuestion = $question->fresh();
    $options = $updatedQuestion->options()->orderBy('sort_order')->get();

    expect($updatedQuestion->question_text)->toBe('Pertanyaan yang diperbarui');
    expect($options)->toHaveCount(4);
    expect($options[1]->is_correct)->toBeTrue();
    expect($options[1]->media_url)->toStartWith('quizzes/options/');
    expect($options[1]->getEffectiveMediaUrl())->toContain('quizzes/options/');

    Storage::disk('s3')->delete($options[1]->media_url);
});

it('rejects unsupported question types without creating a question', function (): void {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->set('questionType', 'unsupported')
        ->set('questionText', 'Pertanyaan baru')
        ->set('questionOptions', ['Jawaban A', 'Jawaban B', 'Jawaban C', 'Jawaban D'])
        ->call('saveQuestion')
        ->assertHasErrors(['questionType' => 'in']);

    expect($quiz->questions()->count())->toBe(0);
});

it('allows admin to delete a quiz question from package', function (): void {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();
    $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id]);
    QuizOption::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->call('removeQuestion', $question->id);

    expect(QuizQuestion::find($question->id))->toBeNull();
});

it('allows admin to delete a quiz package and cascades delete questions and options', function (): void {
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();
    $question = QuizQuestion::factory()->create(['quiz_id' => $quiz->id]);
    $option = QuizOption::factory()->create(['question_id' => $question->id]);

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->call('deletePackage', $quiz->id);

    expect(Quiz::find($quiz->id))->toBeNull();
    expect(QuizQuestion::find($question->id))->toBeNull();
    expect(QuizOption::find($option->id))->toBeNull();
});

it('forbids non-admin users from opening the quiz management page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.latihan.index'))
        ->assertForbidden();
});

it('allows student to play and check answers on a quiz', function (): void {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->create(['slug' => 'test-quiz-play']);
    $question = QuizQuestion::factory()->create([
        'quiz_id' => $quiz->id,
        'question_text' => 'Manakah huruf A?',
        'sort_order' => 1,
    ]);
    $wrongOpt = QuizOption::factory()->create([
        'question_id' => $question->id,
        'option_text' => 'Huruf B',
        'is_correct' => false,
        'sort_order' => 0,
    ]);
    $correctOpt = QuizOption::factory()->correct()->create([
        'question_id' => $question->id,
        'option_text' => 'Huruf A',
        'is_correct' => true,
        'sort_order' => 1,
    ]);

    Livewire::actingAs($user)
        ->test('pages::latihan-kuis.show', ['slug' => 'test-quiz-play'])
        ->assertSee('Manakah huruf A?')
        ->set('selectedOptionId', $correctOpt->id)
        ->call('checkAnswer')
        ->assertSet('isAnswerChecked', true)
        ->assertSet('isCurrentCorrect', true)
        ->assertSet('correctCount', 1)
        ->call('nextQuestion')
        ->assertSet('isFinished', true);

    expect(QuizAttempt::where('user_id', $user->id)->where('quiz_id', $quiz->id)->exists())->toBeTrue();
    $attempt = QuizAttempt::where('user_id', $user->id)->where('quiz_id', $quiz->id)->first();
    expect($attempt->passed)->toBeTrue();
    expect((float) $attempt->score)->toBe(100.0);
});

it('displays completed badge and retry button on latihan index after completion', function (): void {
    $user = User::factory()->create();
    $quiz = Quiz::factory()->published()->create([
        'title' => 'Kuis Sudah Selesai',
        'level' => 'Pemula',
    ]);

    QuizAttempt::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'status' => 'submitted',
        'score' => 100,
        'points_earned' => 50,
        'points_total' => 50,
        'passed' => true,
        'attempt_number' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('latihan.index'))
        ->assertOk()
        ->assertSee('Kuis Sudah Selesai')
        ->assertSee('✓ Selesai (100%)')
        ->assertSee('Ulangi Latihan');
});

it('uploads quiz question media to S3 bucket and resolves effective media URL', function (): void {
    Storage::fake('s3');
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    $upload = UploadedFile::fake()->create('question_video.mp4', 500, 'video/mp4');

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->call('openAddQuestion')
        ->set('questionType', 'sign_to_word')
        ->set('questionText', 'Perhatikan gerakan isyarat ini.')
        ->set('questionMediaFile', $upload)
        ->set('questionOptions', ['Halo', 'Terima Kasih', 'Sama-Sama', 'Sampai Jumpa'])
        ->set('questionCorrectIndex', 0)
        ->call('saveQuestion')
        ->assertHasNoErrors();

    $question = $quiz->questions()->firstOrFail();

    expect($question->media_url)->toStartWith('quizzes/media/');
    expect($question->media_url)->toEndWith('.mp4');
    expect($question->getEffectiveMediaUrl())->toContain('quizzes/media/');
    Storage::disk('s3')->assertExists($question->media_url);
});

it('pulls media from kamus item into correct answer option for word_to_sign quiz type', function (): void {
    Storage::fake('s3');
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    $category = LibraryCategory::factory()->create();
    $itemA = LibraryItem::factory()->create([
        'category_id' => $category->id,
        'title' => 'Huruf A',
    ]);
    $mediaA = LibraryMedia::factory()->create([
        'library_item_id' => $itemA->id,
        'storage_path' => 'kamus/items/huruf_a.mp4',
        'is_primary' => true,
    ]);
    $itemA->media()->attach($mediaA->id, ['is_primary' => true]);

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->call('openAddQuestion')
        ->set('questionType', 'word_to_sign')
        ->call('applyKamusItem', $itemA->id)
        ->assertSet('questionText', 'Manakah gerakan isyarat BISINDO yang melambangkan: "Huruf A"?')
        ->assertSet('questionOptionLibraryItemIds.0', $itemA->id)
        ->assertSet('questionOptionLibraryMediaIds.0', $mediaA->id)
        ->call('saveQuestion')
        ->assertHasNoErrors();

    $question = $quiz->questions()->firstOrFail();
    $correctOption = $question->options()->where('is_correct', true)->firstOrFail();

    expect($correctOption->library_item_id)->toBe($itemA->id);
    expect($correctOption->library_media_id)->toBe($mediaA->id);
    expect($correctOption->getEffectiveMediaUrl())->not->toBeNull();
});

it('allows selecting kamus item directly for an individual answer option', function (): void {
    Storage::fake('s3');
    $admin = User::factory()->admin()->create();
    $quiz = Quiz::factory()->create();

    $category = LibraryCategory::factory()->create();
    $itemB = LibraryItem::factory()->create([
        'category_id' => $category->id,
        'title' => 'Huruf B',
    ]);
    $mediaB = LibraryMedia::factory()->create([
        'library_item_id' => $itemB->id,
        'storage_path' => 'kamus/items/huruf_b.mp4',
        'is_primary' => true,
    ]);
    $itemB->media()->attach($mediaB->id, ['is_primary' => true]);

    Livewire::actingAs($admin)
        ->test('pages::admin.latihan-kuis.index')
        ->set('manageQuizId', $quiz->id)
        ->call('openAddQuestion')
        ->set('questionType', 'word_to_sign')
        ->set('questionText', 'Tebak isyarat huruf B')
        ->call('applyKamusItemToOption', 1, $itemB->id)
        ->assertSet('questionOptions.1', 'Isyarat: Huruf B')
        ->assertSet('questionOptionLibraryItemIds.1', $itemB->id)
        ->assertSet('questionOptionLibraryMediaIds.1', $mediaB->id)
        ->set('questionOptions.0', 'Pilihan A')
        ->set('questionOptions.2', 'Pilihan C')
        ->set('questionOptions.3', 'Pilihan D')
        ->set('questionCorrectIndex', 1)
        ->call('saveQuestion')
        ->assertHasNoErrors();

    $question = $quiz->questions()->firstOrFail();
    $optionB = $question->options()->where('sort_order', 1)->firstOrFail();

    expect($optionB->library_item_id)->toBe($itemB->id);
    expect($optionB->library_media_id)->toBe($mediaB->id);
    expect($optionB->is_correct)->toBeTrue();
});
