<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacebookWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $hubVerifyToken = env('FACEBOOK_WEBHOOK_VERIFY_TOKEN', 'chhitopasal_webhook_secret');
        
        if ($request->hub_verify_token === $hubVerifyToken) {
            return response($request->hub_challenge);
        }
        
        return response('Invalid verify token', 403);
    }

    public function handle(Request $request)
    {
        $payload = $request->all();
        
        // Log the payload for debugging if needed
        \Log::info('Facebook Webhook Received', ['payload' => $payload]);

        if (isset($payload['object']) && $payload['object'] === 'page') {
            foreach ($payload['entry'] as $entry) {
                // $pageId = $entry['id'];
                
                if (isset($entry['messaging'])) {
                    foreach ($entry['messaging'] as $messagingEvent) {
                        if (isset($messagingEvent['message'])) {
                            // Here we can dispatch a broadcast event to Laravel Echo / Pusher
                            // so the frontend can update in real-time.
                            
                            // For now, we will just fire a standard Laravel event
                            // event(new \App\Events\FacebookMessageReceived($messagingEvent));
                        }
                    }
                }
            }
            return response('EVENT_RECEIVED', 200);
        }

        return response('', 404);
    }
}
