<?php

namespace App\Console\Commands;

use App\Models\AiConversationLog;
use App\Models\FacebookPage;
use App\Services\FacebookGraphService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncFacebookConversations extends Command
{
    protected $signature = 'facebook:sync-conversations {--page= : Sync a specific page by page_id} {--limit=0 : Maximum conversations per page (0 = all)}';
    protected $description = 'Sync all Facebook Messenger conversations into the AI training database';

    protected FacebookGraphService $graphService;
    protected int $newMessages = 0;
    protected int $skippedMessages = 0;

    public function __construct(FacebookGraphService $graphService)
    {
        parent::__construct();
        $this->graphService = $graphService;
    }

    public function handle(): int
    {
        $pageFilter = $this->option('page');
        $limit = (int) $this->option('limit');

        $query = FacebookPage::query();
        if ($pageFilter) {
            $query->where('page_id', $pageFilter);
        }

        $pages = $query->get();

        if ($pages->isEmpty()) {
            $this->error('No Facebook pages found. Please connect a page first.');
            return 1;
        }

        $this->info("Found {$pages->count()} page(s) to sync.");

        foreach ($pages as $page) {
            $this->syncPage($page, $limit);
        }

        $this->newLine();
        $this->info("✅ Sync complete! New messages: {$this->newMessages}, Skipped (duplicates): {$this->skippedMessages}");

        return 0;
    }

    protected function syncPage(FacebookPage $page, int $limit): void
    {
        $this->newLine();
        $this->info("📄 Syncing page: {$page->page_name} ({$page->page_id})");

        $cursor = null;
        $conversationCount = 0;
        $pageToken = $page->access_token;

        // Get the page's own ID to identify page replies
        $pageOwnId = $page->page_id;

        do {
            $conversationsData = $this->graphService->getConversations($pageToken, $cursor);

            if (!isset($conversationsData['data']) || empty($conversationsData['data'])) {
                if ($conversationCount === 0) {
                    $this->warn("  No conversations found.");
                }
                break;
            }

            foreach ($conversationsData['data'] as $conversation) {
                if ($limit > 0 && $conversationCount >= $limit) {
                    $this->info("  Reached limit of {$limit} conversations.");
                    return;
                }

                $threadId = $conversation['id'];
                $this->syncThread($page, $threadId, $pageToken, $pageOwnId);
                $conversationCount++;
            }

            $cursor = $conversationsData['paging']['cursors']['after'] ?? null;
            $hasMore = isset($conversationsData['paging']['next']);

            $this->info("  Synced {$conversationCount} conversations so far...");

            // Rate limiting: 500ms delay between pagination calls
            usleep(500000);

        } while ($hasMore && $cursor);

        $this->info("  ✅ Page synced: {$conversationCount} conversations processed.");
    }

    protected function syncThread(FacebookPage $page, string $threadId, string $pageToken, string $pageOwnId): void
    {
        $cursor = null;

        do {
            $messagesData = $this->graphService->getMessages($threadId, $pageToken, $cursor);

            if (!isset($messagesData['data']) || empty($messagesData['data'])) {
                break;
            }

            foreach ($messagesData['data'] as $message) {
                $fbMessageId = $message['id'] ?? null;
                if (!$fbMessageId) continue;

                // Check if already exists (dedup)
                if (AiConversationLog::where('facebook_message_id', $fbMessageId)->exists()) {
                    $this->skippedMessages++;
                    continue;
                }

                $senderId = $message['from']['id'] ?? '';
                $senderName = $message['from']['name'] ?? '';
                $isPageReply = ($senderId === $pageOwnId);

                AiConversationLog::create([
                    'page_id' => $page->page_id,
                    'thread_id' => $threadId,
                    'sender_id' => $senderId,
                    'sender_name' => $senderName,
                    'is_page_reply' => $isPageReply,
                    'message' => $message['message'] ?? '',
                    'facebook_message_id' => $fbMessageId,
                    'sent_at' => isset($message['created_time']) ? \Carbon\Carbon::parse($message['created_time']) : now(),
                ]);

                $this->newMessages++;
            }

            $cursor = $messagesData['paging']['cursors']['after'] ?? null;
            $hasMore = isset($messagesData['paging']['next']);

            // Rate limiting: 300ms delay between message pagination
            usleep(300000);

        } while ($hasMore && $cursor);
    }
}
