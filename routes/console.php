<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * ── Scheduled Tasks ──────────────────────────────────────────────────────────
 *
 * Requires a cron entry on the server (add once with: crontab -e):
 *   * * * * * cd /var/www/schoolms && php artisan schedule:run >> /dev/null 2>&1
 */

// Poll JuicyWay GET /deposits every minute to detect student fee payments.
// withoutOverlapping(2) prevents concurrent runs if a cycle takes > 1 min.
// Note: runInBackground() is NOT used here — it only works with commands,
// not jobs. The job itself is non-blocking via the queue worker.
Schedule::job(new \App\Jobs\PollJuicyWayDepositsJob, 'payments')
    ->everyMinute()
    ->withoutOverlapping(2);

// Daily sweep at 2am — catches any parent whose provisioning was missed
// (e.g. queue worker was down when enrolment was approved).
// Resets status to null so ProvisionParentWalletJob starts fresh from step 1.
Schedule::call(function () {
    App\Models\ParentGuardian::whereNotNull('user_id')
        ->whereNull('juicyway_account_number')
        ->where(function ($q) {
            $q->whereNull('juicyway_wallet_status')
              ->orWhere('juicyway_wallet_status', 'failed');
        })
        ->with(['user', 'students'])
        ->get()
        ->each(function ($parent) {
            if ($parent->user && $parent->students->isNotEmpty()) {
                $parent->update([
                    'juicyway_customer_id'   => null,
                    'juicyway_wallet_id'     => null,
                    'juicyway_account_id'    => null,
                    'juicyway_wallet_status' => null,
                ]);
                App\Jobs\ProvisionParentWalletJob::dispatch($parent)
                    ->onQueue('provisioning');
                \Illuminate\Support\Facades\Log::info(
                    "DailySweep: dispatched provisioning for parent {$parent->id}"
                );
            }
        });
})->dailyAt('02:00')->name('provision-missing-wallets')->withoutOverlapping();
