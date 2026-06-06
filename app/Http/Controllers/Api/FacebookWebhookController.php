<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAiReply;
use App\Models\AiConversationLog;
use App\Models\FacebookPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    public function forceSubscribe()
    {
        $pages = \App\Models\FacebookPage::all();
        $graphService = app(\App\Services\FacebookGraphService::class);
        $results = [];
        foreach ($pages as $page) {
            try {
                $response = $graphService->subscribePageToWebhooks($page->page_id, $page->access_token);
                $results[$page->page_id] = $response;
            } catch (\Exception $e) {
                $results[$page->page_id] = 'Error: ' . $e->getMessage();
            }
        }
        return response()->json(['status' => 'Forced subscription attempted', 'results' => $results]);
    }

    public function debugLiveServer()
    {
        // Force process any pending jobs synchronously to bypass daemon issues
        try {
            \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
        } catch (\Exception $e) {
            // ignore
        }

        $logPath = storage_path('logs/laravel.log');
        $logs = file_exists($logPath) ? shell_exec('tail -n 100 ' . escapeshellarg($logPath)) : 'No log file found';
        
        $jobs = \Illuminate\Support\Facades\DB::table('jobs')->count();
        $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        
        $pages = \App\Models\FacebookPage::all(['page_name', 'page_id', 'updated_at'])->toArray();
        
        // Check AiAgentService conditions
        $isWithinWorkingHours = false;
        try {
            $start = (int) setting('ai_agent_working_hours_start', 8);
            $end = (int) setting('ai_agent_working_hours_end', 22);
            $now = now()->timezone('Asia/Kathmandu');
            $hour = (int) $now->format('H');
            $isWithinWorkingHours = ($hour >= $start && $hour < $end);
        } catch (\Exception $e) {}

        $conditions = [
            'ai_agent_enabled_setting' => setting('ai_agent_enabled', false),
            'is_within_working_hours' => $isWithinWorkingHours,
            'current_time_nepal' => now()->timezone('Asia/Kathmandu')->format('Y-m-d H:i:s'),
            'openrouter_api_key_set' => !empty(setting('openrouter_api_key')),
        ];
        
        return response()->json([
            'environment' => app()->environment(),
            'pending_jobs' => $jobs,
            'failed_jobs' => $failedJobs,
            'facebook_pages_in_db' => $pages,
            'ai_logic_conditions' => $conditions,
            'recent_logs' => $logs
        ]);
    }

    public function verify(Request $request)
    {
        $hubVerifyToken = env('FACEBOOK_WEBHOOK_VERIFY_TOKEN', 'chhitopasal_webhook_secret');
        
        if ($request->hub_verify_token === $hubVerifyToken) {
            return response($request->hub_challenge);
        }
        
        return response('Invalid verify token', 403);
    }

    public function handle(Request $request)
    {
        set_time_limit(120);
        $payload = $request->all();
        
        Log::info('Facebook Webhook Received', ['payload' => $payload]);

        if (isset($payload['object']) && $payload['object'] === 'page') {
            foreach ($payload['entry'] as $entry) {
                $pageId = $entry['id'] ?? null;
                
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messagingEvent) {
                        $this->handleMessagingEvent($messagingEvent, $pageId);
                    }
                }
            }
            return response('EVENT_RECEIVED', 200);
        }

        return response('', 404);
    }

    /**
     * Process a single messaging event from the webhook.
     */
    protected function handleMessagingEvent(array $event, ?string $pageId): void
    {
        // Only handle text messages (skip delivery confirmations, read receipts, etc.)
        if (!isset($event['message']) || !isset($event['message']['text'])) {
            return;
        }

        // Skip echo messages (messages sent BY the page — these are our own replies)
        if (isset($event['message']['is_echo']) && $event['message']['is_echo']) {
            return;
        }

        $senderId = $event['sender']['id'] ?? null;
        $messageText = $event['message']['text'] ?? '';
        $messageId = $event['message']['mid'] ?? null;
        $timestamp = isset($event['timestamp']) ? \Carbon\Carbon::createFromTimestamp($event['timestamp'] / 1000) : now();

        if (!$senderId || !$pageId || empty($messageText)) {
            return;
        }

        // Find the Facebook page in our database
        $page = FacebookPage::where('page_id', $pageId)->first();
        if (!$page) {
            Log::warning('Facebook Webhook: Page not found in DB', ['page_id' => $pageId]);
            return;
        }

        // Resolve the thread ID for this sender
        // In Facebook's Conversations API, the thread ID is typically: t_<sender_psid>
        $threadId = $this->resolveThreadId($senderId, $page);

        // Try to fetch the sender's name if we don't have it
        $senderName = null;
        try {
            $graphService = app(\App\Services\FacebookGraphService::class);
            $userProfile = $graphService->getUserProfile($senderId, $page->access_token);
            if (isset($userProfile['name'])) {
                $senderName = $userProfile['name'];
            }
        } catch (\Exception $e) {
            Log::warning('Facebook Webhook: Failed to fetch user profile', ['error' => $e->getMessage()]);
        }

        // Log the incoming message to conversation logs (for AI training & context)
        if ($messageId) {
            AiConversationLog::firstOrCreate(
                ['facebook_message_id' => $messageId],
                [
                    'page_id' => $pageId,
                    'thread_id' => $threadId,
                    'sender_id' => $senderId,
                    'sender_name' => $senderName,
                    'is_page_reply' => false,
                    'message' => $messageText,
                    'sent_at' => $timestamp,
                ]
            );
        }

        // Dispatch the AI reply job to run immediately AFTER sending the 200 OK to Facebook.
        // This eliminates the need for a background queue daemon on CloudPanel FPM.
        ProcessAiReply::dispatchAfterResponse(
            $pageId,
            $senderId,
            $messageText,
            $threadId,
            $senderName
        );

        Log::info('AI Agent: Dispatched ProcessAiReply job', [
            'page_id' => $pageId,
            'sender_id' => $senderId,
            'thread_id' => $threadId,
        ]);
    }

    /**
     * Resolve the conversation thread ID for a sender.
     * Uses Facebook Graph API to find the thread, falling back to a constructed ID.
     */
    protected function resolveThreadId(string $senderId, FacebookPage $page): string
    {
        try {
            // Try to find the thread by looking at conversations
            $graphService = app(\App\Services\FacebookGraphService::class);
            $conversations = $graphService->getConversations($page->access_token);

            if (isset($conversations['data'])) {
                foreach ($conversations['data'] as $conv) {
                    $participants = $conv['participants']['data'] ?? [];
                    foreach ($participants as $participant) {
                        if ($participant['id'] === $senderId) {
                            return $conv['id'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Facebook Webhook: Thread resolution failed, using fallback', [
                'sender_id' => $senderId,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback: construct a thread ID from sender PSID
        return 't_' . $senderId;
    }
}
