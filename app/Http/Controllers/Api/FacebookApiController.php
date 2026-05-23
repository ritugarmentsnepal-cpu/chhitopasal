<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FacebookPage;
use App\Services\FacebookGraphService;
use Illuminate\Support\Facades\Auth;

class FacebookApiController extends Controller
{
    protected $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function conversations($pageId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $data = $this->graphService->getConversations($page->access_token);
        
        return response()->json($data);
    }

    public function messages($pageId, $threadId)
    {
        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $data = $this->graphService->getMessages($threadId, $page->access_token);
        
        return response()->json($data);
    }

    public function sendMessage(Request $request, $pageId, $threadId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $page = FacebookPage::where('user_id', Auth::id())->where('page_id', $pageId)->firstOrFail();
        
        $response = $this->graphService->sendMessage($threadId, $request->message, $page->access_token);
        
        if (isset($response['error'])) {
            return response()->json(['success' => false, 'error' => $response['error']], 400);
        }

        return response()->json(['success' => true, 'data' => $response]);
    }
}
