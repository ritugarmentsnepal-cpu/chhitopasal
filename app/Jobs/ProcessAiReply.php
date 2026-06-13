<?php

namespace App\Jobs;

use App\Services\AiAgentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public array $backoff = [5, 15];
    public int $timeout = 60;

    protected string $pageId;
    protected string $senderId;
    protected string $messageText;
    protected string $threadId;
    protected ?string $senderName;
    protected ?string $messageId;

    public function __construct(string $pageId, string $senderId, string $messageText, string $threadId, ?string $senderName = null, ?string $messageId = null)
    {
        $this->pageId = $pageId;
        $this->senderId = $senderId;
        $this->messageText = $messageText;
        $this->threadId = $threadId;
        $this->senderName = $senderName;
        $this->messageId = $messageId;
    }

    public function handle(AiAgentService $agentService): void
    {
        // DEBOUNCE LOGIC: Check if a newer message has arrived from this user in this thread.
        // If a newer message exists, we skip this job so the newer job can process the batched context instead.
        if ($this->messageId) {
            $latestMessage = \App\Models\AiConversationLog::where('thread_id', $this->threadId)
                ->where('is_page_reply', false)
                ->orderBy('id', 'desc')
                ->first();

            if ($latestMessage && $latestMessage->facebook_message_id !== $this->messageId) {
                Log::info('AI Agent: Skipping older message to prevent spam (Debounced)', [
                    'thread_id' => $this->threadId,
                    'skipped_message_id' => $this->messageId,
                    'latest_message_id' => $latestMessage->facebook_message_id
                ]);
                return;
            }
        }

        $agentService->handleIncomingMessage(
            $this->pageId,
            $this->senderId,
            $this->messageText,
            $this->threadId,
            $this->senderName
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAiReply job failed permanently', [
            'page_id' => $this->pageId,
            'thread_id' => $this->threadId,
            'error' => $exception->getMessage(),
        ]);
    }
}
