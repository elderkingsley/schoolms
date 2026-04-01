<?php

use Illuminate\Support\Facades\Route;

// ── Webhook routes — CSRF exempt ──────────────────────────────────────────────
Route::post('/webhooks/paystack', [\App\Http\Controllers\WebhookController::class, 'paystack'])
    ->name('webhooks.paystack');
Route::post('/webhooks/monnify', [\App\Http\Controllers\WebhookController::class, 'monnify'])
    ->name('webhooks.monnify');

// JuicyWay payment webhook — CSRF exempt (configured in bootstrap/app.php)
// JuicyWay fires this when a parent completes payment on a payment link
Route::post('/api/juicyway/webhook', [\App\Http\Controllers\JuicyWayWebhookController::class, 'handle'])
    ->name('webhooks.juicyway');

// PayGrid inflow webhook — CSRF exempt (configured in bootstrap/app.php)
// PayGrid fires this when a parent's bank transfer is detected and posted
// to Nurtureville's ledger in PayGrid.
Route::post('/api/paygrid/inflow', [\App\Http\Controllers\PayGridInflowController::class, 'handle'])
    ->name('webhooks.paygrid.inflow');

// ── Public routes ─────────────────────────────────────────────────────────────
Route::get('/enrol', \App\Livewire\Public\EnrolmentForm::class)->name('enrol');
Route::get('/staff/register', \App\Livewire\Public\StaffRegistrationForm::class)->name('staff.register');

// ── Auth routes (Breeze) ──────────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ── Redirect root to login ────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Force password change + voluntary change — available to ALL authenticated users ──
Route::middleware('auth')->group(function () {
    // Forced on first login (used by guest-layout controller)
    Route::get('/password/change',  [\App\Http\Controllers\ChangePasswordController::class, 'show'])
        ->name('password.change');
    Route::post('/password/change', [\App\Http\Controllers\ChangePasswordController::class, 'update'])
        ->name('password.change.update');
    // Voluntary change from within any portal (Livewire component)
    Route::get('/account/password', \App\Livewire\ChangePassword::class)
        ->name('account.password');
});

// ── Admin routes (admin + super_admin) ────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin|admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');

        // Students
        Route::get('/students',           \App\Livewire\Admin\Students\StudentList::class)->name('students');
        Route::get('/students/{student}', \App\Livewire\Admin\Students\StudentProfile::class)->name('students.profile');

        // Enrolment
        Route::get('/enrolment/queue', \App\Livewire\Admin\Enrolment\EnrolmentQueue::class)->name('enrolment.queue');

        // Fees
        Route::get('/fees/items',                  \App\Livewire\Admin\Fees\FeeItemManager::class)->name('fees.items');
        Route::get('/fees/structure',              \App\Livewire\Admin\Fees\FeeStructureManager::class)->name('fees.structure');
        Route::get('/fees/invoices',               \App\Livewire\Admin\Fees\InvoiceList::class)->name('fees.invoices');
        Route::get('/fees/invoices/{invoice}',     \App\Livewire\Admin\Fees\InvoiceDetail::class)->name('fees.invoices.show');
        Route::get('/fees/invoices/{invoice}/pdf', \App\Http\Controllers\Admin\InvoicePdfController::class)->name('fees.invoices.pdf');

        // Messages
        Route::get('/messages',         \App\Livewire\Admin\Messages\MessageInbox::class)->name('messages');
        Route::get('/messages/compose', \App\Livewire\Admin\Messages\MessageCompose::class)->name('messages.compose');

        // Academics
        Route::get('/classes',          \App\Livewire\Admin\Academics\ClassManager::class)->name('classes');
        Route::get('/classes/subjects', \App\Livewire\Admin\Academics\ClassSubjectManager::class)->name('classes.subjects');
        Route::get('/subjects',         \App\Livewire\Admin\Academics\SubjectManager::class)->name('subjects');

        // Results
        Route::get('/results/entry',                 \App\Livewire\Admin\Results\ResultEntry::class)->name('results.entry');
        Route::get('/results/overview',              \App\Livewire\Admin\Results\ResultsOverview::class)->name('results.overview');
        Route::get('/results/{student}/report-card', \App\Http\Controllers\Admin\ReportCardController::class)->name('results.report-card');

        // Users — super_admin only (enforced inside the component too)
        Route::get('/users',    \App\Livewire\Admin\UserManager::class)->name('users');
        Route::get('/teachers', \App\Livewire\Admin\TeacherManager::class)->name('teachers');
        Route::get('/sessions', \App\Livewire\Admin\Academics\SessionTermManager::class)->name('sessions');
        Route::get('/parents',  \App\Livewire\Admin\ParentList::class)->name('parents');
    });

// ── Teacher routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Teacher\Dashboard::class)->name('dashboard');
        Route::get('/results',   \App\Livewire\Teacher\ResultEntry::class)->name('results');
    });

// ── Accountant routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:accountant'])
    ->prefix('accountant')
    ->name('accountant.')
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Accountant\Dashboard::class)->name('dashboard');
        Route::get('/invoices',  \App\Livewire\Accountant\InvoiceList::class)->name('invoices');
    });

// ── Parent routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard',      \App\Livewire\Parent\Dashboard::class)->name('dashboard');
        Route::get('/children',       \App\Livewire\Parent\ChildrenList::class)->name('children');
        Route::get('/fees',           \App\Livewire\Parent\FeeInvoices::class)->name('fees');
        Route::get('/fees/{invoice}', \App\Livewire\Parent\InvoiceView::class)->name('fees.show');
        Route::get('/fees/{invoice}/pdf', \App\Http\Controllers\Parent\InvoicePdfController::class)->name('fees.pdf');
        Route::get('/results',        \App\Livewire\Parent\Results::class)->name('results');
        Route::get('/messages',       \App\Livewire\Parent\Messages::class)->name('messages');
    });
