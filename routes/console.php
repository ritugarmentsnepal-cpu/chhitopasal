<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// AUTO-01: Only sync if auto_sync_pathao is enabled in settings
Schedule::command('pathao:sync')->everyFiveMinutes()->when(function () {
    return setting('auto_sync_pathao', false);
});
