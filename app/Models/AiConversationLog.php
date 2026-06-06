<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversationLog extends Model
{
    protected $fillable = [
        'page_id',
        'thread_id',
        'sender_id',
        'sender_name',
        'is_page_reply',
        'message',
        'facebook_message_id',
        'sent_at',
    ];

    protected $casts = [
        'is_page_reply' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * Get messages for a specific thread, ordered chronologically.
     */
    public function scopeForThread($query, string $pageId, string $threadId)
    {
        return $query->where('page_id', $pageId)
                     ->where('thread_id', $threadId)
                     ->orderBy('sent_at', 'asc');
    }

    /**
     * Get all page (employee/AI) replies across all pages for training.
     */
    public function scopePageReplies($query)
    {
        return $query->where('is_page_reply', true);
    }

    /**
     * Get conversations that likely resulted in successful orders (contain phone numbers).
     * Used for selecting the best training examples.
     */
    public function scopeSuccessfulConversations($query)
    {
        return $query->where('is_page_reply', false)
                     ->where(function ($q) {
                         $q->where('message', 'REGEXP', '(98|97)[0-9]{8}')
                           ->orWhere('message', 'REGEXP', '\\+?977[0-9]{10}');
                     });
    }
}
