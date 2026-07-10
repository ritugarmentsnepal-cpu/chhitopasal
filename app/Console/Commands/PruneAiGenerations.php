<?php

namespace App\Console\Commands;

use App\Models\AiGeneration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * PHASE-2.4: discarded AI generation attempts keep their files on disk so
 * staff can revisit recent attempts; this prunes unconfirmed ones after
 * the retention window to stop disk bloat.
 */
class PruneAiGenerations extends Command
{
    protected $signature = 'mockups:prune-generations {--days=30}';

    protected $description = 'Delete unconfirmed AI generation attempts (files + records) older than the retention window';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $stale = AiGeneration::unconfirmed()->where('created_at', '<', $cutoff)->get();

        $deleted = 0;
        foreach ($stale as $generation) {
            // Never delete a file that a saved template/mockup/background uses
            $inUse = \App\Models\Mockup::where('image_path', $generation->image_path)->exists()
                || \App\Models\MockupTemplate::where('image_path', $generation->image_path)->exists()
                || \App\Models\MockupBackground::where('image_path', $generation->image_path)->exists();

            if (!$inUse && Storage::disk('public')->exists($generation->image_path)) {
                Storage::disk('public')->delete($generation->image_path);
            }

            $generation->delete();
            $deleted++;
        }

        $this->info("Pruned {$deleted} unconfirmed AI generation(s) older than {$cutoff->toDateString()}.");

        return self::SUCCESS;
    }
}
