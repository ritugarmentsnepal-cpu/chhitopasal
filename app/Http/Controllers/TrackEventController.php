<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorEvent;
use App\Models\VisitorSession;
use Illuminate\Support\Facades\Log;

class TrackEventController extends Controller
{
    public function store(Request $request)
    {
        try {
            $sessionId = $request->cookie('visitor_session_id');
            if (!$sessionId) {
                return response()->json(['status' => 'ignored']);
            }

            $session = VisitorSession::where('session_id', $sessionId)->first();
            if (!$session) {
                return response()->json(['status' => 'ignored']);
            }

            $validated = $request->validate([
                'event_type' => 'required|in:page_view,view_product,add_to_cart,initiate_checkout,purchase',
                'product_id' => 'nullable|exists:products,id',
                'category_id' => 'nullable|exists:categories,id',
                'url' => 'nullable|string'
            ]);

            VisitorEvent::create([
                'session_id' => $session->session_id,
                'event_type' => $validated['event_type'],
                'product_id' => $validated['product_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'url' => $validated['url'] ?? url()->previous(),
                'created_at' => now(),
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Silently fail — tracking should never crash the storefront
            return response()->json(['status' => 'ignored']);
        }
    }
}
