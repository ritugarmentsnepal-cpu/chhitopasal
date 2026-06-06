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

    public function __construct(string $pageId, string $senderId, string $messageText, string $threadId, ?string $senderName = null)
    {
        $this->pageId = $pageId;
        $this->senderId = $senderId;
        $this->messageText = $messageText;
        $this->threadId = $threadId;
        $this->senderName = $senderName;
    }

    public function handle(AiAgentService $agentService): void
    {
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
