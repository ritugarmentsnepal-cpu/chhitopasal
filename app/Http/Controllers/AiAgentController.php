<?php

namespace App\Http\Controllers;

use App\Models\AiConversationLog;
use App\Models\AiKnowledgeBase;
use App\Models\AiThreadState;
use App\Models\FacebookPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    /**
     * AI Agent training page with tabs.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'dashboard');
        $allowedTabs = ['dashboard', 'knowledge', 'products', 'training', 'settings'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'dashboard';
        }

        $data = [
            'tab' => $tab,
            'aiEnabled' => setting('ai_agent_enabled', false),
        ];

        if ($tab === 'dashboard') {
            $data['stats'] = $this->getStats();
            $data['recentActivity'] = AiConversationLog::where('is_page_reply', true)
                ->where('sender_id', '!=', '') // AI replies
                ->orderBy('sent_at', 'desc')
                ->limit(20)
                ->get();
        }

        if ($tab === 'knowledge') {
            $categoryFilter = $request->query('category', '');
            $query = AiKnowledgeBase::orderBy('category')->orderBy('sort_order');
            if ($categoryFilter) {
                $query->where('category', $categoryFilter);
            }
            $data['knowledgeEntries'] = $query->get();
            $data['categories'] = AiKnowledgeBase::CATEGORIES;
            $data['categoryFilter'] = $categoryFilter;
        }

        if ($tab === 'products') {
            $data['products'] = Product::with('category')
                ->orderBy('name')
                ->get();
        }

        if ($tab === 'training') {
            $data['pages'] = FacebookPage::all();
            $data['totalConversations'] = AiConversationLog::distinct('thread_id')->count('thread_id');
            $data['totalMessages'] = AiConversationLog::count();
            $data['lastSync'] = AiConversationLog::max('created_at');
            
            // Get sample training conversations
            $sampleThreadIds = AiConversationLog::select('thread_id')
                ->groupBy('thread_id')
                ->havingRaw('COUNT(*) >= 3')
                ->orderByRaw('MAX(sent_at) DESC')
                ->limit(5)
                ->pluck('thread_id');

            $data['sampleConversations'] = [];
            foreach ($sampleThreadIds as $threadId) {
                $messages = AiConversationLog::where('thread_id', $threadId)
                    ->orderBy('sent_at', 'asc')
                    ->limit(8)
                    ->get();
                $data['sampleConversations'][] = [
                    'thread_id' => $threadId,
                    'messages' => $messages,
                ];
            }
        }

        if ($tab === 'settings') {
            $data['settings'] = [
                'ai_agent_enabled' => setting('ai_agent_enabled', false),
                'ai_agent_model' => setting('ai_agent_model', 'google/gemini-2.5-flash'),
                'ai_agent_greeting' => setting('ai_agent_greeting', ''),
                'ai_agent_max_messages' => setting('ai_agent_max_messages', 20),
                'ai_agent_response_delay' => setting('ai_agent_response_delay', 5),
                'ai_agent_working_hours_start' => setting('ai_agent_working_hours_start', 8),
                'ai_agent_working_hours_end' => setting('ai_agent_working_hours_end', 22),
            ];
        }

        return view('ai-agent.index', $data);
    }

    /**
     * Store or update a knowledge base entry.
     */
    public function storeKnowledge(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:ai_knowledge_base,id',
            'category' => 'required|string|in:' . implode(',', array_keys(AiKnowledgeBase::CATEGORIES)),
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        if (!empty($validated['id'])) {
            $entry = AiKnowledgeBase::findOrFail($validated['id']);
            $entry->update($validated);
            $message = 'Knowledge entry updated.';
        } else {
            AiKnowledgeBase::create($validated);
            $message = 'Knowledge entry created.';
        }

        return redirect()->route('ai-agent.index', ['tab' => 'knowledge'])->with('success', $message);
    }

    /**
     * Delete a knowledge base entry.
     */
    public function deleteKnowledge($id)
    {
        AiKnowledgeBase::findOrFail($id)->delete();
        return redirect()->route('ai-agent.index', ['tab' => 'knowledge'])->with('success', 'Knowledge entry deleted.');
    }

    /**
     * Toggle a knowledge base entry active/inactive.
     */
    public function toggleKnowledge($id)
    {
        $entry = AiKnowledgeBase::findOrFail($id);
        $entry->update(['is_active' => !$entry->is_active]);
        return response()->json(['success' => true, 'is_active' => $entry->is_active]);
    }

    /**
     * Trigger conversation sync.
     */
    public function syncConversations()
    {
        try {
            Artisan::call('facebook:sync-conversations');
            $output = Artisan::output();
            return redirect()->route('ai-agent.index', ['tab' => 'training'])
                ->with('success', 'Conversation sync completed! ' . trim($output));
        } catch (\Exception $e) {
            Log::error('AI Agent: Sync failed', ['error' => $e->getMessage()]);
            return redirect()->route('ai-agent.index', ['tab' => 'training'])
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Start the queue worker daemon in the background
     */
    public function startDaemon()
    {
        try {
            $basePath = base_path();
            
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows background process
                pclose(popen("start /B php {$basePath}/artisan queue:work > NUL 2> NUL", "r"));
            } else {
                // Linux/Mac/CloudPanel background process (Fully detached)
                $phpPath = PHP_BINDIR . '/php';
                if (!file_exists($phpPath)) {
                    $phpPath = 'php'; // Fallback
                }
                $command = "nohup {$phpPath} {$basePath}/artisan queue:work > /dev/null 2>&1 < /dev/null &";
                exec($command);
            }
            
            return redirect()->back()
                ->with('success', 'AI Agent Real-Time Queue Daemon started successfully! The AI will now process messages instantly in the background.');
        } catch (\Exception $e) {
            Log::error('AI Agent: Daemon start failed', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to start daemon: ' . $e->getMessage());
        }
    }

    /**
     * Get training stats (API).
     */
    public function getTrainingStats()
    {
        return response()->json($this->getStats());
    }

    /**
     * Test the AI agent with a message.
     */
    public function testAgent(Request $request)
    {
        set_time_limit(120);
        $request->validate(['message' => 'required|string|max:500']);

        $agentService = app(AiAgentService::class);
        $result = $agentService->testResponse($request->message);

        return response()->json($result);
    }

    /**
     * Human takes over a thread — disable AI.
     */
    public function takeover($threadId)
    {
        // Find the thread state across all pages
        $state = AiThreadState::where('thread_id', $threadId)->first();
        if (!$state) {
            return response()->json(['success' => false, 'error' => 'Thread not found'], 404);
        }

        $state->update([
            'human_takeover' => true,
            'human_takeover_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Resume AI for a thread.
     */
    public function resume($threadId)
    {
        $state = AiThreadState::where('thread_id', $threadId)->first();
        if (!$state) {
            return response()->json(['success' => false, 'error' => 'Thread not found'], 404);
        }

        $state->update([
            'human_takeover' => false,
            'human_takeover_at' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get AI status for a thread.
     */
    public function status($threadId)
    {
        $state = AiThreadState::where('thread_id', $threadId)->first();

        return response()->json([
            'exists' => !!$state,
            'ai_enabled' => $state->ai_enabled ?? true,
            'human_takeover' => $state->human_takeover ?? false,
            'conversation_stage' => $state->conversation_stage ?? 'greeting',
            'order_id' => $state->order_id ?? null,
            'ticket_id' => $state->ticket_id ?? null,
            'customer_phone' => $state->customer_phone ?? null,
        ]);
    }

    /**
     * Build stats array.
     */
    protected function getStats(): array
    {
        return [
            'total_conversations' => AiConversationLog::distinct('thread_id')->count('thread_id'),
            'total_messages' => AiConversationLog::count(),
            'knowledge_entries' => AiKnowledgeBase::active()->count(),
            'orders_by_ai' => Order::where('source', 'facebook_ai')->count(),
            'tickets_created' => SupportTicket::count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
            'active_threads' => AiThreadState::where('ai_enabled', true)->where('human_takeover', false)->count(),
            'pages_connected' => FacebookPage::count(),
        ];
    }
}
