<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('/latihan-kuis', 'pages::latihan-kuis.index')
        ->name('latihan.index');
    Route::livewire('/kamus-bisindo', 'pages::kamus-bisindo.index')
        ->name('kamus.index');
    Route::livewire('/modul', 'pages::modul.index')
        ->name('modul.index');
});

require __DIR__.'/settings.php';
