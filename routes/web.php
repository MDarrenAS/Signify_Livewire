<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/latihan-kuis', 'pages::latihan-kuis.index')
        ->name('latihan.index');
    Route::livewire('/latihan-kuis/{slug}', 'pages::latihan-kuis.show')
        ->name('latihan.show');

    Route::livewire('/kamus-bisindo', 'pages::kamus-bisindo.index')
        ->name('kamus.index');
    Route::livewire('/kamus-bisindo/{slug}', 'pages::kamus-bisindo.show')
        ->name('kamus.show');

    Route::livewire('/modul', 'pages::modul.index')
        ->name('modul.index');
    Route::livewire('/modul/{modul}', 'pages::modul.show')
        ->name('modul.show');
    Route::livewire('/modul/{modul}/{bagian}', 'pages::modul.part')
        ->name('modul.part');

    // Admin Panel
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
        Route::livewire('/modul', 'pages::admin.modul.index')->name('modul.index');
        Route::livewire('/modul/{modul}', 'pages::admin.modul.show')->name('modul.show');
        Route::livewire('/kamus', 'pages::admin.kamus.index')->name('kamus.index');
        Route::livewire('/latihan-kuis', 'pages::admin.latihan-kuis.index')->name('latihan.index');
        Route::livewire('/pengguna', 'pages::admin.pengguna.index')->name('pengguna.index');
    });
});

require __DIR__.'/settings.php';