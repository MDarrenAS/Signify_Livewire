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
        Route::livewire('/modul/{slug}', 'pages::modul.show')
        ->name('modul.show');
});

require __DIR__.'/settings.php';
