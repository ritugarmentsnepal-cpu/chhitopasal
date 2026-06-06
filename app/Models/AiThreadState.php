<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiThreadState extends Model
{
    protected $fillable = [
        'page_id',
        'thread_id',
        'ai_enabled',
        'human_takeover',
        'human_takeover_at',
        'customer_phone',
        'customer_name',
        'conversation_stage',
        'order_id',
        'ticket_id',
    ];

    protected $casts = [
        'ai_enabled' => 'boolean',
        'human_takeover' => 'boolean',
        'human_takeover_at' => 'datetime',
    ];

    const STAGES = [
        'greeting' => 'Greeting',
        'product_inquiry' => 'Product Inquiry',
        'collecting_info' => 'Collecting Info',
        'order_created' => 'Order Created',
        'complaint' => 'Complaint',
        'resolved' => 'Resolved',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Check if AI should respond to this thread.
     */
    public function shouldAiRespond(): bool
    {
        return $this->ai_enabled && !$this->human_takeover;
    }

    /**
     * Get or create a thread state for a given page/thread combination.
     */
    public static function getOrCreate(string $pageId, string $threadId, ?string $customerName = null): self
    {
        return static::firstOrCreate(
            ['page_id' => $pageId, 'thread_id' => $threadId],
            ['customer_name' => $customerName, 'ai_enabled' => true, 'human_takeover' => false]
        );
    }
}
