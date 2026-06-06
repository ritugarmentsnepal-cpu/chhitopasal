<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use App\Models\FacebookPage;
use App\Services\FacebookGraphService;
use Illuminate\Support\Facades\Auth;

class FacebookInboxController extends Controller
{
    public function index()
    {
        $pages = FacebookPage::where('user_id', Auth::id())->get();
        $products = \App\Models\Product::where('stock', '>', 0)->get();
        return view('facebook-inbox.index', compact('pages', 'products'));
    }

    public function login()
    {
        return Socialite::driver('facebook')
            ->scopes(['pages_show_list', 'pages_manage_metadata', 'pages_read_engagement', 'pages_messaging', 'pages_manage_posts'])
            ->redirect();
    }

    public function callback(FacebookGraphService $graphService)
    {
        try {
            $user = Socialite::driver('facebook')->user();
            
            // Get user access token
            $userToken = $user->token;
            
            // Fetch pages the user manages
            $pagesData = $graphService->getPages($userToken);
            
            if (isset($pagesData['data'])) {
                foreach ($pagesData['data'] as $pageData) {
                    FacebookPage::updateOrCreate(
                        [
                            'page_id' => $pageData['id'],
                            'user_id' => Auth::id()
                        ],
                        [
                            'page_name' => $pageData['name'],
                            'access_token' => $pageData['access_token'],
                        ]
                    );

                    // Subscribe the App to this Page's webhooks
                    try {
                        $graphService->subscribePageToWebhooks($pageData['id'], $pageData['access_token']);
                    } catch (\Exception $e) {
                        Log::error('AI Agent: Failed to subscribe page to webhooks', ['page_id' => $pageData['id'], 'error' => $e->getMessage()]);
                    }
                }
            }
            
            return redirect()->route('facebook-inbox.index')->with('success', 'Facebook pages connected successfully.');
        } catch (\Exception $e) {
            \Log::error('Facebook OAuth Error: ' . $e->getMessage());
            return redirect()->route('facebook-inbox.index')->with('error', 'Failed to connect Facebook.');
        }
    }
}
