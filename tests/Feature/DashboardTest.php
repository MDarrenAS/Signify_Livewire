<?php

use App\Models\Module;
use App\Models\User;
use App\Models\UserLessonProgress;
use Database\Seeders\CurriculumSeeder;

beforeEach(function () {
    $this->seed(CurriculumSeeder::class);
});

test('unauthenticated user is redirected to login', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view dashboard with real database modules', function () {
    $user = User::factory()->create([
        'name' => 'Darren',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Selamat Datang');
    $response->assertSee('Darren');
    $response->assertSee('Modul Belajar');
    $response->assertSee('Abjad');
    $response->assertSee('Angka');
    $response->assertSee('Misi Harian');
    $response->assertSee('Liga Perunggu');
});

test('dashboard displays updated progress when user completes lessons', function () {
    $user = User::factory()->create([
        'name' => 'Budi',
    ]);

    $abjadModule = Module::where('slug', 'abjad-dan-ejaan')->first();
    $firstLesson = $abjadModule->lessons()->first();

    // Selesaikan 1 materi hari ini
    UserLessonProgress::create([
        'user_id' => $user->id,
        'lesson_id' => $firstLesson->id,
        'status' => 'completed',
        'progress_percent' => 100,
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('LANJUTKAN');
    $response->assertSee('1 HARI');
    $response->assertSee('10 poin');
});
