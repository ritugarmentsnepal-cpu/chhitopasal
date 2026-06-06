<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FacebookPage;
use App\Models\SavedReply;
use App\Services\FacebookGraphService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FacebookApiController extends Controller
{
    protected $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function conversations(Request $request, $pageId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $cursor = $request->query('cursor');
        $data = $this->graphService->getConversations($page->access_token, $cursor);
        
        return response()->json($data);
    }

    public function messages(Request $request, $pageId, $threadId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $cursor = $request->query('cursor');
        $data = $this->graphService->getMessages($threadId, $page->access_token, $cursor);
        
        // Inject AI badge information
        if (isset($data['data']) && is_array($data['data'])) {
            $messageIds = array_column($data['data'], 'id');
            $aiMessageIds = \App\Models\AiConversationLog::whereIn('facebook_message_id', $messageIds)
                ->where('is_page_reply', true)
                ->where('facebook_message_id', 'LIKE', 'm_%') // Facebook message IDs start with m_
                ->pluck('facebook_message_id')
                ->toArray();
                
            // Also check by content fallback since previously we didn't save message_id correctly
            $aiMessagesContent = \App\Models\AiConversationLog::where('thread_id', $threadId)
                ->where('is_page_reply', true)
                ->pluck('message')
                ->toArray();

            foreach ($data['data'] as &$msg) {
                $msg['is_ai'] = in_array($msg['id'] ?? '', $aiMessageIds);
                if (!$msg['is_ai'] && isset($msg['message']) && in_array($msg['message'], $aiMessagesContent)) {
                    $msg['is_ai'] = true;
                }
            }
        }
        
        return response()->json($data);
    }

    public function sendMessage(Request $request, $pageId, $threadId)
    {
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        if (!$request->message && !$request->hasFile('file')) {
            return response()->json(['success' => false, 'error' => 'Message or file is required'], 422);
        }

        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $attachmentUrl = null;
        $attachmentType = 'file';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // Store file publicly so Facebook can fetch it
            $path = $file->store('facebook_attachments', 'public');
            // Use config('app.url') or hardcode if testing
            // Since this is chhitopasal.com, asset() will work on live.
            $attachmentUrl = asset('storage/' . $path);
            
            // Determine type
            $mime = $file->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $attachmentType = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $attachmentType = 'video';
            } elseif (str_starts_with($mime, 'audio/')) {
                $attachmentType = 'audio';
            }
        }
        
        $response = $this->graphService->sendMessage($threadId, $request->message, $page->access_token, $attachmentUrl, $attachmentType);
        
        if (isset($response['error'])) {
            return response()->json(['success' => false, 'error' => $response['error']], 400);
        }

        return response()->json(['success' => true, 'data' => $response, 'attachment_url' => $attachmentUrl]);
    }

    public function markAsRead($pageId, $threadId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $response = $this->graphService->markAsRead($threadId, $page->access_token);
        
        if (isset($response['error'])) {
            return response()->json(['success' => false, 'error' => $response['error']], 400);
        }

        return response()->json(['success' => true]);
    }

    // --- Posts & Comments ---

    public function posts(Request $request, $pageId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        $cursor = $request->query('cursor');
        $data = $this->graphService->getPosts($page->access_token, $cursor);
        return response()->json($data);
    }

    public function postComments(Request $request, $pageId, $postId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        $cursor = $request->query('cursor');
        $data = $this->graphService->getPostComments($postId, $page->access_token, $cursor);
        return response()->json($data);
    }

    public function replyToComment(Request $request, $pageId, $commentId)
    {
        $request->validate(['message' => 'required|string']);
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        $data = $this->graphService->replyToComment($commentId, $request->message, $page->access_token);
        return response()->json($data);
    }

    public function hideComment(Request $request, $pageId, $commentId)
    {
        $request->validate(['is_hidden' => 'required|boolean']);
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        $data = $this->graphService->hideComment($commentId, $request->is_hidden, $page->access_token);
        return response()->json($data);
    }

    public function deleteComment($pageId, $commentId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        $data = $this->graphService->deleteComment($commentId, $page->access_token);
        return response()->json($data);
    }

    public function likeComment($pageId, $commentId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        $data = $this->graphService->likeComment($commentId, $page->access_token);
        return response()->json($data);
    }

    // --- Saved Replies ---

    public function getSavedReplies()
    {
        // Global for all pages, but scoped to the user
        $replies = SavedReply::where('user_id', Auth::id())->orderBy('title')->get();
        return response()->json(['data' => $replies]);
    }

    public function storeSavedReply(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $reply = SavedReply::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json(['success' => true, 'data' => $reply]);
    }

    public function deleteSavedReply($id)
    {
        $reply = SavedReply::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $reply->delete();

        return response()->json(['success' => true]);
    }
}
