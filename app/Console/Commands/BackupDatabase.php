<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * PHASE-5: nightly database backup with rotation.
 *
 * Dumps the app database via mysqldump, gzip-compressed, into
 * storage/app/backups (git-ignored), keeping the last N days.
 * Runs daily via the scheduler; the whole business lives in this DB.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:db {--days=14 : Days of backups to keep} {--binary= : Path to mysqldump}';

    protected $description = 'Dump the database to storage/app/backups (gzipped, rotated)';

    public function handle(): int
    {
        $config = config('database.connections.' . config('database.default'));
        $binary = $this->option('binary') ?: env('MYSQLDUMP_PATH', 'mysqldump');

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir . '/' . $config['database'] . '_' . now()->format('Y-m-d_His') . '.sql.gz';

        $process = new Process([
            $binary,
            '--user=' . $config['username'],
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? 3306),
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            $config['database'],
        ], null, ['MYSQL_PWD' => $config['password'] ?? '']);

        $process->setTimeout(600);

        $gz = gzopen($file, 'wb6');
        if ($gz === false) {
            $this->error("Cannot open {$file} for writing.");
            return self::FAILURE;
        }

        // Stream dump output into the gzip file chunk by chunk (memory-safe)
        $exitCode = $process->run(function ($type, $buffer) use ($gz) {
            if ($type === Process::OUT) {
                gzwrite($gz, $buffer);
            }
        });

        gzclose($gz);

        if ($exitCode !== 0) {
            File::delete($file);
            $error = trim($process->getErrorOutput());
            Log::error('DB backup FAILED', ['error' => $error]);
            $this->error('Backup failed: ' . $error);
            return self::FAILURE;
        }

        $size = round(File::size($file) / 1024 / 1024, 2);

        // Guard against silently-empty dumps
        if (File::size($file) < 1024) {
            Log::error('DB backup produced a suspiciously small file', ['file' => $file, 'bytes' => File::size($file)]);
            $this->error("Backup file is suspiciously small ({$size} MB) — check credentials/binary.");
            return self::FAILURE;
        }

        // Rotate old backups
        $cutoff = now()->subDays((int) $this->option('days'))->getTimestamp();
        $pruned = 0;
        foreach (File::files($dir) as $old) {
            if ($old->getMTime() < $cutoff && str_ends_with($old->getFilename(), '.sql.gz')) {
                File::delete($old->getPathname());
                $pruned++;
            }
        }

        $this->info("Backup OK: " . basename($file) . " ({$size} MB), pruned {$pruned} old file(s).");
        Log::info('DB backup OK', ['file' => basename($file), 'mb' => $size]);

        return self::SUCCESS;
    }
}
