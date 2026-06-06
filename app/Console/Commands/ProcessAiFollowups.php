<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiThreadState;
use App\Services\AiAgentService;
use App\Models\FacebookPage;
use Illuminate\Support\Facades\Log;

class ProcessAiFollowups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:process-followups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends automated follow-up messages to customers who have not replied for 2 hours';

    /**
     * Execute the console command.
     */
    public function handle(AiAgentService $agentService)
    {
        $this->info('Starting AI follow-up processing...');

        $threads = AiThreadState::where('ai_enabled', true)
            ->where('human_takeover', false)
            ->whereNotIn('conversation_stage', ['order_created', 'resolved', 'complaint'])
            ->where('followup_count', '<', 5)
            ->whereNotNull('last_interaction_at')
            ->where('last_interaction_at', '<=', now()->subHours(2))
            ->get();

        if ($threads->isEmpty()) {
            $this->info('No threads require follow-ups.');
            return;
        }

        foreach ($threads as $thread) {
            $this->info("Processing follow-up for thread: {$thread->thread_id} (Count: " . ($thread->followup_count + 1) . ")");

            try {
                // Generate follow-up using AiAgentService
                // We'll call a dedicated followUp method in AiAgentService
                $agentService->sendFollowUp($thread);
                $this->info("Follow-up sent for thread: {$thread->thread_id}");
            } catch (\Exception $e) {
                Log::error('AI Agent: Failed to process follow-up', [
                    'thread_id' => $thread->thread_id,
                    'error' => $e->getMessage()
                ]);
                $this->error("Failed to process follow-up for thread {$thread->thread_id}: {$e->getMessage()}");
            }
        }

        $this->info('AI follow-up processing completed.');
    }
}
