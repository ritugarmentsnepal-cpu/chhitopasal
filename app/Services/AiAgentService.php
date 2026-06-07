<?php

namespace App\Services;

use App\Models\AiConversationLog;
use App\Models\AiKnowledgeBase;
use App\Models\AiThreadState;
use App\Models\FacebookPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    protected FacebookGraphService $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    /**
     * Main entry point: handle an incoming Facebook message.
     * Called by the ProcessAiReply job.
     */
    public function handleIncomingMessage(string $pageId, string $senderId, string $messageText, string $threadId, ?string $senderName = null): void
    {
        // Check global AI agent toggle
        if (!setting('ai_agent_enabled', false)) {
            return;
        }

        // Check working hours (Nepal time = UTC+5:45)
        // Temporarily disabled so AI runs 24/7
        // if (!$this->isWithinWorkingHours()) {
        //     return;
        // }

        // Get or create thread state
        $threadState = AiThreadState::getOrCreate($pageId, $threadId, $senderName);
        
        // Reset followup count since customer replied, update interaction time
        $threadState->update([
            'followup_count' => 0,
            'last_interaction_at' => now(),
        ]);

        // If human has taken over or AI is disabled for this thread, skip
        if (!$threadState->shouldAiRespond()) {
            return;
        }

        // Check max messages limit
        $maxMessages = (int) setting('ai_agent_max_messages', 20);
        $aiMessageCount = AiConversationLog::where('page_id', $pageId)
            ->where('thread_id', $threadId)
            ->where('is_page_reply', true)
            ->count();

        if ($aiMessageCount >= $maxMessages) {
            Log::info('AI Agent: Max messages reached for thread', ['thread_id' => $threadId, 'count' => $aiMessageCount]);
            return;
        }

        try {
            // Fetch conversation history for this thread
            $conversationHistory = AiConversationLog::forThread($pageId, $threadId)
                ->orderBy('sent_at', 'desc')
                ->limit(30) // Last 30 messages for context
                ->get()
                ->reverse()
                ->values();

            // Generate AI response
            $aiResponse = $this->generateResponse($threadState, $messageText, $conversationHistory);

            if (empty($aiResponse) || empty($aiResponse['messages'])) {
                Log::warning('AI Agent: Empty response generated', ['thread_id' => $threadId]);
                return;
            }

            // Get the page's access token
            $page = FacebookPage::where('page_id', $pageId)->first();
            if (!$page) {
                Log::error('AI Agent: Page not found', ['page_id' => $pageId]);
                return;
            }

            // Send each message in the array
            foreach ($aiResponse['messages'] as $index => $messageContent) {
                if (empty(trim($messageContent))) continue;
                
                // Add a human-like typing delay
                $delay = (int) setting('ai_agent_response_delay', 5);
                if ($delay > 0) {
                    sleep(min($delay, 15)); // Cap at 15 seconds
                }

                // Send the reply via Facebook Graph API
                $sendResult = $this->graphService->sendMessage($threadId, $messageContent, $page->access_token);

                if (isset($sendResult['error'])) {
                    Log::error('AI Agent: Failed to send message', ['error' => $sendResult['error'], 'thread_id' => $threadId]);
                    continue; // try next message
                }

                // Log the AI's outgoing message
                AiConversationLog::create([
                    'page_id' => $pageId,
                    'thread_id' => $threadId,
                    'sender_id' => $pageId,
                    'sender_name' => $page->page_name ?? 'Page',
                    'is_page_reply' => true,
                    'message' => $messageContent,
                    'facebook_message_id' => $sendResult['message_id'] ?? ('ai_' . uniqid() . '_' . time()),
                    'sent_at' => now(),
                ]);
            }

            // Handle optional image attachment
            if (!empty($aiResponse['attachment_url'])) {
                sleep(1); // Short pause before sending image
                $this->graphService->sendMessage($threadId, null, $page->access_token, $aiResponse['attachment_url'], 'image');
            }

            // Handle phone number detection → create lead order
            if (!empty($aiResponse['detected_phone']) && !$threadState->order_id) {
                $this->createLeadOrder($threadState, $aiResponse['detected_phone'], $senderName ?? $threadState->customer_name);
            }

            // Handle complaint detection → create support ticket
            if (!empty($aiResponse['is_complaint']) && !$threadState->ticket_id) {
                $this->createSupportTicket(
                    $threadState,
                    $aiResponse['complaint_category'] ?? 'other',
                    $aiResponse['complaint_summary'] ?? $messageText
                );
            }

            // Update conversation stage based on AI response
            $this->updateConversationStage($threadState, $aiResponse);

        } catch (\Exception $e) {
            Log::error('AI Agent: Exception in handleIncomingMessage', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send an automated follow-up message to the customer.
     */
    public function sendFollowUp(AiThreadState $threadState): void
    {
        // Add a special prompt asking the AI to write a short follow-up nudge
        $followUpCount = $threadState->followup_count + 1;
        $instruction = "SYSTEM INSTRUCTION: The customer has not replied in 2 hours. This is automated follow-up #{$followUpCount}. Send a very short, friendly 1-sentence nudge to encourage them to reply or ask if they need help deciding. Do not be pushy.";
        
        $conversationHistory = \App\Models\AiConversationLog::where('thread_id', $threadState->thread_id)
            ->latest('sent_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $aiResponse = $this->generateResponse($threadState, $instruction, $conversationHistory);

        if (empty($aiResponse) || empty($aiResponse['messages'])) {
            Log::warning('AI Agent: Empty follow-up generated', ['thread_id' => $threadState->thread_id]);
            return;
        }

        $page = FacebookPage::where('page_id', $threadState->page_id)->first();
        if (!$page) return;

        foreach ($aiResponse['messages'] as $messageContent) {
            if (empty(trim($messageContent))) continue;
            
            $sendResult = $this->graphService->sendMessage($threadState->thread_id, $messageContent, $page->access_token);

            if (isset($sendResult['error'])) {
                Log::error('AI Agent: Failed to send follow-up', ['error' => $sendResult['error']]);
                continue;
            }

            \App\Models\AiConversationLog::create([
                'page_id' => $threadState->page_id,
                'thread_id' => $threadState->thread_id,
                'sender_id' => $threadState->page_id,
                'sender_name' => $page->page_name ?? 'Page',
                'is_page_reply' => true,
                'message' => $messageContent,
                'facebook_message_id' => $sendResult['message_id'] ?? ('ai_followup_' . uniqid()),
                'sent_at' => now(),
            ]);
        }

        $threadState->update([
            'followup_count' => $followUpCount,
            'last_interaction_at' => now(),
        ]);
    }

    /**
     * Generate AI response using OpenRouter API.
     */
    public function generateResponse(AiThreadState $threadState, string $messageText, $conversationHistory): array
    {
        $apiKey = setting('openrouter_api_key', env('OPENROUTER_API_KEY'));
        if (empty($apiKey)) {
            Log::error('AI Agent: OpenRouter API key not configured');
            return [];
        }

        $model = setting('ai_agent_model', 'google/gemini-2.5-flash');
        $systemPrompt = $this->buildSystemPrompt($threadState);

        // Build chat messages array
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $role = $msg->is_page_reply ? 'assistant' : 'user';
            if (!empty($msg->message)) {
                $messages[] = ['role' => $role, 'content' => $msg->message];
            }
        }

        // Add the current incoming message
        $messages[] = ['role' => 'user', 'content' => $messageText];

        // Sanitize all messages to ensure valid UTF-8 encoding before json_encode
        $messages = array_map(function ($msg) {
            if (isset($msg['content'])) {
                // Remove invalid UTF-8 characters to prevent json_encode failures
                $msg['content'] = mb_convert_encoding($msg['content'], 'UTF-8', 'UTF-8');
            }
            return $msg;
        }, $messages);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer' => url('/'),
                'X-Title' => 'Chhito Pasal AI Agent',
                'Content-Type' => 'application/json',
            ])
            ->timeout(90)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            if ($response->failed()) {
                Log::error('AI Agent: OpenRouter API error', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';

            return $this->parseAiResponse($content, $messageText);

        } catch (\Exception $e) {
            Log::error('AI Agent: API call exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Build the system prompt from all 4 sources.
     */
    public function buildSystemPrompt(AiThreadState $threadState): string
    {
        $storeName = setting('store_name', 'Chhito Pasal');
        $companyPhone = setting('company_phone', '');
        $deliveryInside = setting('delivery_charge_inside', '50');
        $deliveryOutside = setting('delivery_charge_outside', '100');

        $customerName = $threadState->customer_name ?? 'Unknown';
        $customerPhone = $threadState->customer_phone ?? 'Not yet collected';
        $orderCreated = $threadState->order_id ? 'Yes (#' . $threadState->order_id . ')' : 'No';
        
        $phonePrompt = $customerPhone !== 'Not yet collected' 
            ? "- We already have the customer's phone number. DO NOT ask for it again." 
            : "- Our primary goal is to get the customer's phone number to place the order. Naturally ask them for their phone number (e.g., 'number dinus call aauxa order ko lagi'). You can append it to your message, or send it as a separate text in the `messages` array. Do not ask forcefully if they are just browsing, but ask when they show interest in a product.";

        $prompt = <<<PROMPT
You are a friendly sales representative for {$storeName} (छिटो पसल), a Nepali online store. 
You are chatting with a customer on Facebook Messenger. You are a real employee, NOT a bot.

═══ STRICT LANGUAGE MATCHING ═══
- You MUST reply in the exact same language/script the customer uses!
  • If customer uses Roman Nepali ("k xa", "kati ho") → YOU MUST reply in Roman Nepali.
  • If customer uses Devanagari ("के छ", "कति हो") → YOU MUST reply in Devanagari.
  • If customer uses English → YOU MUST reply in English.
- Keep it natural, casual, and short (1-2 sentences). Use words like "hajur", "dai", "didi".
- DO NOT repeat the same greetings (like "Hajur, ma yeta nai chu", "Hajur ma yeta nai chu") in every message. Only greet in the very first message.

═══ CONTEXT & LOGIC RULES ═══
- READ THE CHAT HISTORY. Understand exactly which product the customer is talking about.
- ONLY provide details and photos for the SPECIFIC product the user asks about. If they ask about a "transparent bag", do NOT send details or images of a "shoe bag" or any other product.
- Answer EXACTLY what the user is asking. Do not give irrelevant info.
- DO NOT push the price in every single message. It is illogical. Only mention the price if they ask for it, or if you are introducing a product for the first time.

═══ MAIN OBJECTIVE: PHONE NUMBER ═══
{$phonePrompt}

═══ BUSINESS INFO ═══
Store: {$storeName}
Contact: {$companyPhone}
Delivery: Inside Valley Rs. {$deliveryInside}, Outside Rs. {$deliveryOutside} (Cash on Delivery)

═══ PRODUCT CATALOG (Includes Image URLs) ═══
{$this->buildProductCatalog()}

═══ KNOWLEDGE BASE ═══
{$this->buildKnowledgeBase()}

═══ CURRENT STATE ═══
Stage: {$threadState->conversation_stage}
Customer: {$customerName}
Phone: {$customerPhone}
Order: {$orderCreated}

═══ RESPONSE FORMAT ═══
You MUST respond with ONLY a valid JSON object. Example:
{"messages": ["Namaste hajur! Yo tshirt available cha.", "Order garna ko lagi number dinus call aauxa."], "attachment_url": "https://example.com/image.jpg", "detected_phone": null, "is_complaint": false, "complaint_category": null, "complaint_summary": null}

- "messages": An array of 1 to 3 string messages to send. Separate your thoughts naturally instead of sending one huge paragraph. DO NOT repeat "Hajur ma yeta nai chu" or similar repetitive filler text.
- "attachment_url": ONLY provide an Image URL if you are specifically introducing the product the user asked about, OR if they explicitly ask for a photo. Otherwise null.
- "detected_phone": Extract Nepali phone number if provided (e.g. 98xxxxxxxx). Else null.
- "is_complaint": true if they are complaining. Else false.
- "complaint_category": e.g., "late_delivery", "wrong_product", "refund", "other".
PROMPT;

        return $prompt;
    }

    /**
     * Build product catalog string from the database.
     */
    protected function buildProductCatalog(): string
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            return "No products currently available.";
        }

        $catalog = [];
        foreach ($products as $product) {
            $line = "• {$product->name}";
            $line .= " | Price: Rs. {$product->price}";
            
            if ($product->category) {
                $line .= " | Category: {$product->category->name}";
            }

            if ($product->stock <= 5) {
                $line .= " | ⚠️ Limited Stock ({$product->stock} left)";
            } else {
                $line .= " | In Stock";
            }

            if (!empty($product->color_options) && is_array($product->color_options)) {
                $line .= " | Colors: " . implode(', ', $product->color_options);
            }

            if (!empty($product->size_options) && is_array($product->size_options)) {
                $line .= " | Sizes: " . implode(', ', $product->size_options);
            }

            if (!empty($product->bundles) && is_array($product->bundles)) {
                $bundleTexts = [];
                foreach ($product->bundles as $bundle) {
                    $bundleTexts[] = "{$bundle['qty']}-Pack: Rs. {$bundle['price']}";
                }
                $line .= " | Bundles: " . implode(', ', $bundleTexts);
            }

            if ($product->image_path) {
                $line .= " | Image URL: " . asset('storage/' . $product->image_path);
            }

            if (!empty($product->description)) {
                // Truncate description to keep prompt manageable
                $desc = strip_tags($product->description);
                if (strlen($desc) > 150) {
                    $desc = substr($desc, 0, 150) . '...';
                }
                $line .= "\n  Description: {$desc}";
            }

            $catalog[] = $line;
        }

        return implode("\n", $catalog);
    }

    /**
     * Build knowledge base section from custom entries.
     */
    protected function buildKnowledgeBase(): string
    {
        $entries = AiKnowledgeBase::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        if ($entries->isEmpty()) {
            return "No additional knowledge base entries.";
        }

        $grouped = $entries->groupBy('category');
        $sections = [];

        foreach ($grouped as $category => $items) {
            $categoryLabel = AiKnowledgeBase::CATEGORIES[$category] ?? ucfirst($category);
            $sections[] = "--- {$categoryLabel} ---";
            foreach ($items as $item) {
                $sections[] = "• {$item->title}: {$item->content}";
            }
        }

        return implode("\n", $sections);
    }

    /**
     * Build training examples from past conversations across ALL pages.
     */
    protected function buildTrainingExamples(): string
    {
        // Find threads that contain phone numbers (likely successful conversions)
        $successfulThreadIds = AiConversationLog::where('is_page_reply', false)
            ->where(function ($q) {
                $q->where('message', 'like', '%98%')
                  ->orWhere('message', 'like', '%97%');
            })
            ->distinct()
            ->pluck('thread_id')
            ->take(20); // Limit to 20 conversations

        if ($successfulThreadIds->isEmpty()) {
            // Fallback: just get some recent conversations
            $successfulThreadIds = AiConversationLog::distinct()
                ->orderBy('sent_at', 'desc')
                ->pluck('thread_id')
                ->take(10);
        }

        if ($successfulThreadIds->isEmpty()) {
            return "No conversation examples available yet. Sync conversations first.";
        }

        $examples = [];
        $exampleCount = 0;

        foreach ($successfulThreadIds as $threadId) {
            if ($exampleCount >= 5) break; // Max 5 example conversations

            $messages = AiConversationLog::where('thread_id', $threadId)
                ->orderBy('sent_at', 'asc')
                ->limit(10) // Max 10 messages per conversation
                ->get();

            if ($messages->count() < 3) continue; // Skip very short conversations

            $example = "--- Example Conversation ---\n";
            foreach ($messages as $msg) {
                $role = $msg->is_page_reply ? 'Employee' : 'Customer';
                $text = $msg->message;
                if (empty($text)) continue;
                // Truncate very long messages
                if (strlen($text) > 200) {
                    $text = substr($text, 0, 200) . '...';
                }
                $example .= "{$role}: {$text}\n";
            }

            $examples[] = $example;
            $exampleCount++;
        }

        return empty($examples) ? "No conversation examples available yet." : implode("\n", $examples);
    }

    /**
     * Parse the AI's response (handle both JSON and plain text).
     */
    protected function parseAiResponse(string $content, string $originalMessage): array
    {
        $content = trim($content);

        // Strip markdown code blocks if present
        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
            if (str_ends_with($content, '```')) {
                $content = substr($content, 0, -3);
            }
        } elseif (str_starts_with($content, '```')) {
            $content = substr($content, 3);
            if (str_ends_with($content, '```')) {
                $content = substr($content, 0, -3);
            }
        }
        $content = trim($content);

        $parsed = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Support old "reply" format as fallback
            if (isset($parsed['reply']) && !isset($parsed['messages'])) {
                $parsed['messages'] = [$parsed['reply']];
            }
            if (isset($parsed['messages'])) {
                // Validate and normalize phone number
                if (!empty($parsed['detected_phone'])) {
                    $parsed['detected_phone'] = $this->normalizePhoneNumber($parsed['detected_phone']);
                }
                return $parsed;
            }
        }

        // Fallback: treat the entire content as a single message
        Log::warning('AI Agent: Response was not valid JSON, using as plain text', ['content' => substr($content, 0, 200)]);
        
        return [
            'messages' => [$content],
            'attachment_url' => null,
            'detected_phone' => $this->extractPhoneNumber($originalMessage),
            'is_complaint' => false,
            'complaint_category' => null,
            'complaint_summary' => null,
        ];
    }

    /**
     * Extract Nepali phone number from text.
     */
    public function extractPhoneNumber(string $text): ?string
    {
        // Match Nepali phone patterns
        if (preg_match('/(?:\+?977)?[\s\-]?(9[678]\d{8})/', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Normalize a phone number to 10-digit format.
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, plus signs
        $phone = preg_replace('/[\s\-\+]/', '', $phone);
        // Remove country code prefix
        if (str_starts_with($phone, '977') && strlen($phone) === 13) {
            $phone = substr($phone, 3);
        }
        return $phone;
    }

    /**
     * Create a pending order (lead) from an AI conversation.
     */
    protected function createLeadOrder(AiThreadState $threadState, string $phone, ?string $customerName): void
    {
        try {
            $order = Order::create([
                'customer_name' => $customerName ?? 'Facebook Lead',
                'customer_phone' => $phone,
                'address' => 'To be confirmed',
                'total_amount' => 0,
                'status' => 'pending',
                'source' => 'facebook_ai',
                'remarks' => "Auto-created by AI Agent from Facebook conversation. Thread: {$threadState->thread_id}",
            ]);

            $threadState->update([
                'order_id' => $order->id,
                'customer_phone' => $phone,
                'conversation_stage' => 'order_created',
            ]);

            Log::info('AI Agent: Lead order created', [
                'order_id' => $order->id,
                'phone' => $phone,
                'thread_id' => $threadState->thread_id,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Agent: Failed to create lead order', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a support ticket from an AI-detected complaint.
     */
    protected function createSupportTicket(AiThreadState $threadState, string $category, string $description): void
    {
        try {
            $ticket = SupportTicket::create([
                'page_id' => $threadState->page_id,
                'thread_id' => $threadState->thread_id,
                'customer_name' => $threadState->customer_name ?? 'Facebook Customer',
                'customer_facebook_id' => null,
                'category' => $category,
                'description' => $description,
                'status' => 'open',
                'priority' => in_array($category, ['damaged_product', 'refund']) ? 'high' : 'medium',
            ]);

            $threadState->update([
                'ticket_id' => $ticket->id,
                'conversation_stage' => 'complaint',
                'human_takeover' => true,
            ]);

            Log::info('AI Agent: Support ticket created', [
                'ticket_id' => $ticket->id,
                'category' => $category,
                'thread_id' => $threadState->thread_id,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Agent: Failed to create support ticket', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update conversation stage based on AI response.
     */
    protected function updateConversationStage(AiThreadState $threadState, array $aiResponse): void
    {
        if (!empty($aiResponse['detected_phone'])) {
            $threadState->update([
                'conversation_stage' => 'order_created',
                'customer_phone' => $aiResponse['detected_phone'],
            ]);
        } elseif (!empty($aiResponse['is_complaint'])) {
            $threadState->update(['conversation_stage' => 'complaint']);
        } elseif ($threadState->conversation_stage === 'greeting') {
            $threadState->update(['conversation_stage' => 'product_inquiry']);
        }
    }

    /**
     * Check if current time is within configured working hours (Nepal time).
     */
    protected function isWithinWorkingHours(): bool
    {
        $start = (int) setting('ai_agent_working_hours_start', 8);
        $end = (int) setting('ai_agent_working_hours_end', 22);

        // Nepal time is UTC+5:45
        $nepalHour = (int) now('Asia/Kathmandu')->format('H');

        return $nepalHour >= $start && $nepalHour < $end;
    }

    /**
     * Test the AI agent with a simulated message (does not send to Facebook).
     */
    public function testResponse(string $testMessage): array
    {
        $threadState = new AiThreadState([
            'page_id' => 'test',
            'thread_id' => 'test_thread',
            'ai_enabled' => true,
            'human_takeover' => false,
            'conversation_stage' => 'greeting',
        ]);

        return $this->generateResponse($threadState, $testMessage, collect());
    }
}
