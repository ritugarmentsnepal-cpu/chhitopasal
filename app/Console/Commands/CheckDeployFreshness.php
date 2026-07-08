<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * PHASE-5.3: detect a stuck auto-deploy. The deploy cron should keep HEAD
 * equal to origin/main within a minute of any push; if we're still behind
 * on two consecutive checks, flag it (surfaces in the notification bell).
 */
class CheckDeployFreshness extends Command
{
    protected $signature = 'ops:check-deploy';

    protected $description = 'Verify the deployed commit matches origin/main and flag stale deploys';

    public function handle(): int
    {
        $fetch = Process::fromShellCommandline('git fetch origin main -q', base_path());
        $fetch->setTimeout(60);
        $fetch->run();

        if (!$fetch->isSuccessful()) {
            // Network/auth hiccup — don't raise a false alarm, just log
            Log::warning('ops:check-deploy could not fetch origin', ['error' => trim($fetch->getErrorOutput())]);
            return self::SUCCESS;
        }

        $head = trim(Process::fromShellCommandline('git rev-parse HEAD', base_path())->mustRun()->getOutput());
        $remote = trim(Process::fromShellCommandline('git rev-parse origin/main', base_path())->mustRun()->getOutput());

        $stale = $head !== $remote;

        if ($stale && !Cache::get('deploy_stale_pending')) {
            // First sighting could be a push mid-deploy — confirm next run
            Cache::put('deploy_stale_pending', true, 3600);
            $this->info('Deploy behind origin/main — will confirm on next check.');
            return self::SUCCESS;
        }

        if (!$stale) {
            Cache::forget('deploy_stale_pending');
        }

        Cache::put('deploy_status', [
            'stale' => $stale,
            'head' => substr($head, 0, 7),
            'remote' => substr($remote, 0, 7),
            'checked_at' => now()->toDateTimeString(),
        ], 7200);

        if ($stale) {
            Log::error('DEPLOY STALE: server is behind origin/main', ['head' => $head, 'remote' => $remote]);
            $this->error("Deploy STALE: HEAD {$head} != origin/main {$remote}");
        } else {
            $this->info('Deploy up to date (' . substr($head, 0, 7) . ').');
        }

        return self::SUCCESS;
    }
}
