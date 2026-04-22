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

// Poll JuicyWay GET /deposits every minute to detect NUBAN bank transfers.
// Provider-aware: the job checks the active wallet provider at the start of
// each run and exits silently if JuicyWay is not active. This means you never
// need to comment this out when switching providers — just change the provider
// in School Settings or .env and this job self-disables.
Schedule::job(new \App\Jobs\PollJuicyWayDepositsJob, 'payments')
    ->everyMinute()
    ->withoutOverlapping(2);

// Daily sweep at 2am — provisions any parent missing an account on the
// currently active provider. Catches parents whose provisioning was missed
// (e.g. queue worker was down when enrolment was approved).
// Provider-aware: reads the active provider at runtime so it works correctly
// regardless of whether BudPay or JuicyWay is currently active.
Schedule::call(function () {
    $provider = App\Models\ParentGuardian::getActiveWalletProvider();

    App\Models\ParentGuardian::whereNotNull('user_id')
        ->with(['user', 'students'])
        ->get()
        ->each(function ($parent) use ($provider) {
            if (
                $parent->user
                && $parent->students->isNotEmpty()
                && $parent->needsProviderAccount($provider)
            ) {
                App\Jobs\ProvisionParentWalletJob::dispatch($parent)
                    ->onQueue('provisioning');
                \Illuminate\Support\Facades\Log::info(
                    "DailySweep: queued {$provider} provisioning for parent {$parent->id}"
                );
            }
        });
})->dailyAt('02:00')->name('provision-missing-wallets')->withoutOverlapping();
