<?php

use App\Models\User;
use App\Models\UserModuleProgress;
use Database\Seeders\CurriculumSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

    $this->seed(CurriculumSeeder::class);

    $this->seed(\Database\Seeders\CurriculumSeeder::class);
});

it('persists module lesson completion in database', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::modul.part', [
            'modul' => 'abjad-dan-ejaan',
            'bagian' => 'huruf-vokal',
        ])
        ->call('complete')
        ->assertSet('isCompletedView', true)
        ->assertSet('isPassed', true);

    expect(UserModuleProgress::where('user_id', $user->id)
        ->where('modul_slug', 'abjad-dan-ejaan')
        ->where('part_slug', 'huruf-vokal')
        ->where('is_completed', true)
        ->exists())->toBeTrue();
});

it('persists module quiz score in database and displays score percentage on index', function (): void {
    $user = User::factory()->create();

    // Complete quiz with score 95
    Livewire::actingAs($user)
        ->test('pages::modul.part', [
            'modul' => 'abjad-dan-ejaan',
            'bagian' => 'kuis-evaluasi-abjad',
        ])
        ->call('completeQuiz', 95, 19)
        ->assertSet('isCompletedView', true)
        ->assertSet('finalScore', 95)
        ->assertSet('isPassed', true);

    $record = UserModuleProgress::where('user_id', $user->id)
        ->where('modul_slug', 'abjad-dan-ejaan')
        ->where('part_slug', 'kuis-evaluasi-abjad')
        ->first();

    expect($record)->not->toBeNull();
    expect((int) round($record->score))->toBe(95);

    // Visiting index shows the quiz score percentage
    Livewire::actingAs($user)
        ->test('pages::modul.index')
        ->assertSee('Skor 95%');
});
