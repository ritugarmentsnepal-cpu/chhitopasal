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
     * Subscribe the App to a Page's webhooks.
     */
    public function subscribePageToWebhooks($pageId, $pageToken)
    {
        $response = Http::post("{$this->baseUrl}/{$pageId}/subscribed_apps", [
            'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins,message_deliveries,message_reads',
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }

    /**
     * Get conversations for a page.
     */
    public function getConversations($pageToken, $after = null)
    {
        $params = [
            'fields' => 'id,updated_time,unread_count,participants,messages.limit(1){message,created_time,from}',
            'limit' => 50,
            'access_token' => $pageToken,
        ];
        if ($after) {
            $params['after'] = $after;
        }

        $response = Http::get("{$this->baseUrl}/me/conversations", $params);

        return $response->json();
    }

    /**
     * Get messages for a specific thread.
     */
    public function getMessages($threadId, $pageToken, $after = null)
    {
        $params = [
            'fields' => 'id,message,created_time,from,attachments',
            'limit' => 50,
            'access_token' => $pageToken,
        ];
        if ($after) {
            $params['after'] = $after;
        }

        $response = Http::get("{$this->baseUrl}/{$threadId}/messages", $params);

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

    /**
     * Mark a thread as read
     */
    public function markAsRead($threadId, $pageToken)
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

        $response = Http::post("{$this->baseUrl}/me/messages", [
            'recipient' => ['id' => $recipientId],
            'sender_action' => 'mark_seen',
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }

    /**
     * Get a user's public profile (name, profile picture) using their PSID
     */
    public function getUserProfile(string $psid, string $pageToken)
    {
        $response = Http::get("{$this->baseUrl}/{$psid}", [
            'fields' => 'first_name,last_name,name,profile_pic',
            'access_token' => $pageToken,
        ]);

        return $response->json();
    }

    // --- Comments & Posts ---

    public function getPosts($pageToken, $after = null)
    {
        $params = [
            'fields' => 'id,message,created_time,full_picture,attachments',
            'limit' => 25,
            'access_token' => $pageToken,
        ];
        if ($after) {
            $params['after'] = $after;
        }
        $response = Http::get("{$this->baseUrl}/me/posts", $params);
        return $response->json();
    }

    public function getPostComments($postId, $pageToken, $after = null)
    {
        $params = [
            'fields' => 'id,message,created_time,from,is_hidden,user_likes,attachment,comments{id,message,created_time,from,is_hidden,user_likes,attachment}',
            'limit' => 50,
            'order' => 'reverse_chronological',
            'access_token' => $pageToken,
        ];
        if ($after) {
            $params['after'] = $after;
        }
        $response = Http::get("{$this->baseUrl}/{$postId}/comments", $params);
        return $response->json();
    }

    public function replyToComment($commentId, $message, $pageToken)
    {
        $response = Http::post("{$this->baseUrl}/{$commentId}/comments", [
            'message' => $message,
            'access_token' => $pageToken,
        ]);
        return $response->json();
    }

    public function hideComment($commentId, $isHidden, $pageToken)
    {
        $response = Http::post("{$this->baseUrl}/{$commentId}", [
            'is_hidden' => $isHidden ? 'true' : 'false',
            'access_token' => $pageToken,
        ]);
        return $response->json();
    }

    public function deleteComment($commentId, $pageToken)
    {
        $response = Http::delete("{$this->baseUrl}/{$commentId}", [
            'access_token' => $pageToken,
        ]);
        return $response->json();
    }

    public function likeComment($commentId, $pageToken)
    {
        $response = Http::post("{$this->baseUrl}/{$commentId}/likes", [
            'access_token' => $pageToken,
        ]);
        return $response->json();
    }
}
