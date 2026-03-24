<?php

use Illuminate\Support\Facades\Route;

// ── Webhook routes — CSRF exempt ──────────────────────────────────────────────
Route::post('/webhooks/paystack', [\App\Http\Controllers\WebhookController::class, 'paystack'])
    ->name('webhooks.paystack');

Route::post('/webhooks/monnify', [\App\Http\Controllers\WebhookController::class, 'monnify'])
    ->name('webhooks.monnify');

// ── Public routes — no auth required ─────────────────────────────────────────
Route::get('/enrol', \App\Livewire\Public\EnrolmentForm::class)->name('enrol');

// ── Auth routes (Breeze) ──────────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ── Redirect root to login ────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Admin routes ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin|admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)
            ->name('dashboard');

        // Students
        Route::get('/students', \App\Livewire\Admin\Students\StudentList::class)
            ->name('students');

        // Enrolment
        Route::get('/enrolment/queue', \App\Livewire\Admin\Enrolment\EnrolmentQueue::class)
            ->name('enrolment.queue');

        // Student profile
        Route::get('/students/{student}', \App\Livewire\Admin\Students\StudentProfile::class)
            ->name('students.profile');
    });

// ── Teacher routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Teacher\Dashboard::class)
            ->name('dashboard');
    });

// ── Parent routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Parent\Dashboard::class)
            ->name('dashboard');
    });
