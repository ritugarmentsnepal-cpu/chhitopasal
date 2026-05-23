<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookGraphService
{
    protected $baseUrl = 'https://graph.facebook.com/v19.0';

    /**
     * Get pages for a user token.
     */
    public function getPages($userToken)
    {
        $response = Http::get("{$this->baseUrl}/me/accounts", [
            'access_token' => $userToken,
        ]);

        return $response->json();
    }

    /**
     * Get conversations for a page.
     */
    public function getConversations($pageToken)
    {
        // fetch threads and the last message
        $response = Http::get("{$this->baseUrl}/me/conversations", [
            'fields' => 'id,updated_time,participants,messages.limit(1){message,created_time,from}',
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }

    /**
     * Get messages for a specific thread.
     */
    public function getMessages($threadId, $pageToken)
    {
        $response = Http::get("{$this->baseUrl}/{$threadId}/messages", [
            'fields' => 'id,message,created_time,from',
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }

    /**
     * Send a message to a thread.
     */
    public function sendMessage($threadId, $message, $pageToken)
    {
        // First get the recipient id from the thread by fetching it
        $threadResponse = Http::get("{$this->baseUrl}/{$threadId}", [
            'fields' => 'participants',
            'access_token' => $pageToken,
        ]);
        
        $threadData = $threadResponse->json();
        
        // Find the customer's participant ID (the one that is not the page)
        // Since we are sending as the page, we need the OTHER participant's ID.
        // For simplicity in Graph API for messenger, if we have a thread ID, we can't reply directly to thread ID.
        // We reply to a PSID (Page Scoped ID). 
        // Let's extract PSID from participants.
        $pageId = null; 
        // It's better if we pass pageId or get it. 
        // Actually, you CAN reply to a message via the /me/messages endpoint with recipient->id = PSID.
        
        // A better approach to send a message is to just use the thread's first message sender/recipient.
        
        $participants = $threadData['participants']['data'] ?? [];
        $recipientId = null;
        
        // We need to determine the page's own ID to exclude it. We can get it via /me
        $meResponse = Http::get("{$this->baseUrl}/me", [
            'fields' => 'id',
            'access_token' => $pageToken,
        ]);
        $meId = $meResponse->json()['id'] ?? null;
        
        foreach ($participants as $participant) {
            if ($participant['id'] !== $meId) {
                $recipientId = $participant['id'];
                break;
            }
        }
        
        if (!$recipientId) {
            return ['error' => 'Could not determine recipient ID'];
        }

        $response = Http::post("{$this->baseUrl}/me/messages", [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $message],
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }
}
