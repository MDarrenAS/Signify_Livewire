<?php

use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserLessonProgress;
use Livewire\Livewire;

test('admin dashboard displays statistics from database records', function (): void {
    $this->travelTo(now()->startOfWeek()->addDay());

    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'New Learner']);
    User::factory()->create([
        'name' => 'Existing Learner',
        'created_at' => now()->subWeeks(2),
        'updated_at' => now()->subWeeks(2),
    ]);

    Module::create([
        'title' => 'Module One',
        'slug' => 'module-one',
        'level' => 'beginner',
        'status' => 'published',
    ]);
    Module::create([
        'title' => 'Module Draft',
        'slug' => 'module-draft',
        'level' => 'intermediate',
        'status' => 'draft',
    ]);
    Module::create([
        'title' => 'Module Archived',
        'slug' => 'module-archived',
        'level' => 'advanced',
        'status' => 'archived',
    ]);

    $category = LibraryCategory::factory()->create();
    LibraryItem::factory()->count(2)->for($category, 'category')->create();
    Quiz::factory()->create(['created_by' => $admin->id]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('2 Modul')
        ->assertSee('Dalam 1 unit aktif')
        ->assertSee('2 Entri')
        ->assertSee('1 Paket')
        ->assertSee('+1 pengguna minggu ini');
});

test('admin user list displays database users with calculated progress and filters', function (): void {
    $admin = User::factory()->admin()->create();
    $learner = User::factory()->create([
        'name' => 'Aulia Zahra',
        'email' => 'aulia@example.test',
    ]);
    $inactiveLearner = User::factory()->create([
        'name' => 'Budi Santoso',
        'email' => 'budi@example.test',
        'is_active' => false,
    ]);

    $module = Module::create([
        'title' => 'Intermediate Module',
        'slug' => 'intermediate-module',
        'level' => 'intermediate',
        'status' => 'published',
    ]);
    $lesson = ModuleLesson::create([
        'module_id' => $module->id,
        'title' => 'Completed Lesson',
        'slug' => 'completed-lesson',
        'status' => 'published',
    ]);
    UserLessonProgress::create([
        'user_id' => $learner->id,
        'lesson_id' => $lesson->id,
        'status' => 'completed',
        'progress_percent' => 100,
        'completed_at' => now()->subMinutes(20),
        'last_accessed_at' => now()->subMinutes(20),
    ]);
    $quiz = Quiz::factory()->create(['created_by' => $admin->id]);
    QuizAttempt::create([
        'user_id' => $learner->id,
        'quiz_id' => $quiz->id,
        'points_earned' => 25,
        'submitted_at' => now()->subMinutes(10),
    ]);

    Livewire::actingAs($admin)
        ->test('pages::admin.pengguna.index')
        ->assertSee('Aulia Zahra')
        ->assertSee('Tingkat 2 • Menengah')
        ->assertSee('⚡ 35 XP')
        ->assertSee('1 Bagian')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Nadia Putri')
        ->assertDontSee($admin->name)
        ->set('search', 'Aulia')
        ->assertSee('Aulia Zahra')
        ->assertDontSee('Budi Santoso')
        ->set('search', '')
        ->set('statusFilter', 'Nonaktif')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Aulia Zahra');
});
