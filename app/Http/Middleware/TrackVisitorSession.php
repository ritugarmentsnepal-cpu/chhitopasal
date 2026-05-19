<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use App\Models\VisitorSession;
use Illuminate\Support\Facades\Cookie;

class TrackVisitorSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't track admin panel or API requests
        if ($request->is('admin*') || $request->is('api*') || $request->is('settings*') || $request->is('livewire*')) {
            return $next($request);
        }

        try {
            $sessionId = $request->cookie('visitor_session_id');

            if (!$sessionId) {
                $sessionId = Str::uuid()->toString();
                Cookie::queue('visitor_session_id', $sessionId, 60 * 24 * 30); // 30 days
            }

            // Only create a new session if it doesn't exist
            $session = VisitorSession::firstOrCreate(
                ['session_id' => $sessionId],
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'utm_source' => $request->query('utm_source'),
                    'utm_medium' => $request->query('utm_medium'),
                    'utm_campaign' => $request->query('utm_campaign'),
                    'utm_content' => $request->query('utm_content'),
                    'fbclid' => $request->query('fbclid'),
                    'landing_page_url' => $request->fullUrl(),
                ]
            );

            // Update tracking parameters if they exist in the URL but weren't there before
            $updates = [];
            $params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'fbclid'];
            foreach ($params as $param) {
                if ($request->has($param) && empty($session->{$param})) {
                    $updates[$param] = $request->query($param);
                }
            }

            if (!empty($updates)) {
                $session->update($updates);
            }
        } catch (\Exception $e) {
            // Silently fail — tracking should never break the storefront
        }

        return $next($request);
    }
}
