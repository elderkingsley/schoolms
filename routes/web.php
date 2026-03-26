<?php

use Illuminate\Support\Facades\Route;

// ── Webhook routes — CSRF exempt ──────────────────────────────────────────────
Route::post('/webhooks/paystack', [\App\Http\Controllers\WebhookController::class, 'paystack'])
    ->name('webhooks.paystack');
Route::post('/webhooks/monnify', [\App\Http\Controllers\WebhookController::class, 'monnify'])
    ->name('webhooks.monnify');

// ── Public routes ─────────────────────────────────────────────────────────────
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
        Route::get('/students/{student}', \App\Livewire\Admin\Students\StudentProfile::class)
            ->name('students.profile');

        // Enrolment
        Route::get('/enrolment/queue', \App\Livewire\Admin\Enrolment\EnrolmentQueue::class)
            ->name('enrolment.queue');

        // Fees — catalogue & structure
        Route::get('/fees/items',     \App\Livewire\Admin\Fees\FeeItemManager::class)
            ->name('fees.items');
        Route::get('/fees/structure', \App\Livewire\Admin\Fees\FeeStructureManager::class)
            ->name('fees.structure');

        // Fees — invoices
        Route::get('/fees/invoices',          \App\Livewire\Admin\Fees\InvoiceList::class)
            ->name('fees.invoices');
        // InvoiceDetail route — uncomment once InvoiceDetail component is built
        // Route::get('/fees/invoices/{invoice}', \App\Livewire\Admin\Fees\InvoiceDetail::class)
        //     ->name('fees.invoices.show');
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
