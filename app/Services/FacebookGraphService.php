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
    public function sendMessage($threadId, $messageText, $pageToken, $attachmentUrl = null, $attachmentType = 'file')
    {
        $threadResponse = Http::get("{$this->baseUrl}/{$threadId}", [
            'fields' => 'participants',
            'access_token' => $pageToken,
        ]);
        
        $threadData = $threadResponse->json();
        
        $participants = $threadData['participants']['data'] ?? [];
        $recipientId = null;
        
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

        $messagePayload = [];
        if ($messageText) {
            $messagePayload['text'] = $messageText;
        }

        if ($attachmentUrl) {
            // Usually 'image', 'audio', 'video', or 'file'
            // To support all file types, we default to 'file' unless we specifically know it's an image.
            $messagePayload['attachment'] = [
                'type' => $attachmentType,
                'payload' => [
                    'url' => $attachmentUrl,
                    'is_reusable' => true
                ]
            ];
        }

        $response = Http::post("{$this->baseUrl}/me/messages", [
            'recipient' => ['id' => $recipientId],
            'message' => $messagePayload,
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }
}
