<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// AUTO-01: Sync Pathao statuses if auto_sync_pathao is enabled in settings
Schedule::command('pathao:sync')->everyFiveMinutes()->when(function () {
    return setting('auto_sync_pathao', false);
});

// AUTO-02: Prune old activity logs daily (keep 90 days)
Schedule::command('logs:prune --days=90')->daily();
