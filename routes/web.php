<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', fn() => redirect()->route('login'));

// Admin routes
Route::middleware(['auth', 'role:super_admin|admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    });

// Teacher routes
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Teacher\Dashboard::class)->name('dashboard');
    });

// Parent routes
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Parent\Dashboard::class)->name('dashboard');
    });
