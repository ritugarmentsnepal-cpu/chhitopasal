<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// AUTO-01: Sync Pathao statuses at user-configured interval (default: every 5 min)
// Disabled in favor of real-time Pathao Webhooks
// Schedule::command('pathao:sync')->everyMinute()->when(function () {
//     if (!setting('auto_sync_pathao', false)) {
//         return false;
//     }
//     $interval = max(1, (int) setting('pathao_sync_interval', 5));
//     return now()->minute % $interval === 0;
// })->withoutOverlapping();

// AUTO-02: Prune old activity logs daily (keep 90 days)
Schedule::command('logs:prune --days=90')->daily();

// AI-01: Daily incremental sync of Facebook conversations for AI training
Schedule::command('facebook:sync-conversations')->dailyAt('03:00')->withoutOverlapping();


// AI-02: Process automated follow-ups for AI Agent
Schedule::command('ai:process-followups')->everyFifteenMinutes()->withoutOverlapping();

// PHASE-2.4: prune discarded AI generation attempts (files + records)
Schedule::command('mockups:prune-generations --days=30')->daily();

// PHASE-5: nightly database backup (storage/app/backups, 14-day rotation)
Schedule::command('backup:db')->dailyAt('02:30');

// OPS-01: Queue worker without supervisor — drains the database queue every
// minute and exits when empty. withoutOverlapping prevents parallel workers.
// Replaces the old "start daemon from the browser" hack.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

