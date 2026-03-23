<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/', fn() => redirect()->route('login'));
// Public routes — no auth required
Route::get('/enrol', \App\Livewire\Public\EnrolmentForm::class)->name('enrol');

// Webhook routes — CSRF exempt
Route::post('/webhooks/paystack', [\App\Http\Controllers\WebhookController::class, 'paystack'])
    ->name('webhooks.paystack');

Route::post('/webhooks/monnify', [\App\Http\Controllers\WebhookController::class, 'monnify'])
    ->name('webhooks.monnify');

// Admin routes
Route::middleware(['auth', 'role:super_admin|admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        Route::get('/enrolment/queue', \App\Livewire\Admin\Enrolment\EnrolmentQueue::class)
    ->name('enrolment.queue');
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
