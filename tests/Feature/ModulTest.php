<?php

use App\Models\User;
use Database\Seeders\CurriculumSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(CurriculumSeeder::class);
});

test('authenticated user can view modul index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('modul.index'));

    $response->assertOk();
    $response->assertSee('Modul Belajar');
    $response->assertSee('Abjad & Ejaan');
});

test('user is redirected from modul show to first part', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('modul.show', ['modul' => 'abjad-dan-ejaan']));

    $response->assertRedirect(route('modul.part', [
        'modul' => 'abjad-dan-ejaan',
        'bagian' => 'huruf-vokal',
    ]));
});

test('user can view a module part', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('modul.part', [
        'modul' => 'abjad-dan-ejaan',
        'bagian' => 'huruf-vokal',
    ]));

    $response->assertOk();
    $response->assertSee('HURUF VOKAL');
    $response->assertSee('Huruf A');
});

test('admin can view admin modul page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.modul.index'));

    $response->assertOk();
    $response->assertSee('Modul & Kurikulum Belajar');
    $response->assertSee('Abjad & Ejaan');
});

test('admin can view admin modul show page', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.modul.show', ['modul' => 'abjad-dan-ejaan']));

    $response->assertOk();
    $response->assertSee('Abjad & Ejaan');
    $response->assertSee('Huruf Vokal');
});

test('admin can create a new modul and persist to database matching supabase schema', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test('pages::admin.modul.index')
        ->set('formUnitIdx', 0)
        ->set('formTitle', 'Warna dan Bentuk')
        ->set('formSubtitle', 'Mengenal warna dasar')
        ->set('formDescription', 'Kuasai berbagai warna dalam BISINDO.')
        ->set('formIcon', '🎨')
        ->set('formXpReward', 60)
        ->set('formIsActive', true)
        ->call('saveModul')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('modules', [
        'title' => 'Warna dan Bentuk',
        'level' => 'beginner',
        'status' => 'published',
    ]);
});

test('user completing a lesson persists progress to user_lesson_progress table', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::modul.part', [
            'modul' => 'abjad-dan-ejaan',
            'bagian' => 'huruf-vokal',
        ])
        ->call('complete')
        ->assertSet('isPassed', true)
        ->assertSet('isCompletedView', true);

    $this->assertDatabaseHas('user_lesson_progress', [
        'user_id' => $user->id,
        'status' => 'completed',
    ]);
});
