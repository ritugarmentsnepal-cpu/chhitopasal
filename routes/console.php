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
