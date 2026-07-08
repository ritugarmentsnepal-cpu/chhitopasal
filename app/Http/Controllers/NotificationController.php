<?php

namespace App\Http\Controllers;

use App\Models\Mockup;
use App\Models\Order;
use App\Models\RiderComment;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

/**
 * PHASE-3.4: notification center — an aggregated feed of things that need
 * attention, computed from existing tables (no event plumbing needed).
 * Read-state = users.notifications_seen_at; items newer than it are unread.
 */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $items = collect();

        if ($user->hasPermission('orders')) {
            // Customer responses to mockup approval links (last 7 days)
            Mockup::whereNotNull('approval_responded_at')
                ->where('approval_responded_at', '>=', now()->subDays(7))
                ->with('order')
                ->latest('approval_responded_at')
                ->limit(6)
                ->get()
                ->each(function ($m) use ($items) {
                    $approved = $m->approval_status === 'approved';
                    $items->push([
                        'type' => $approved ? 'approval' : 'changes',
                        'title' => ($approved ? '✅ Mockup approved' : '✏️ Changes requested') . ($m->order_id ? " — Order #{$m->order_id}" : ''),
                        'sub' => $m->title . ($m->approval_feedback ? ' · "' . \Illuminate\Support\Str::limit($m->approval_feedback, 60) . '"' : ''),
                        'url' => $m->order_id ? route('orders.show', $m->order_id) : route('mockups.index'),
                        'time' => $m->approval_responded_at,
                    ]);
                });

            // Returns waiting for verification
            Order::where('status', 'return_delivered')
                ->whereNull('return_verified_at')
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->each(function ($o) use ($items) {
                    $items->push([
                        'type' => 'return',
                        'title' => "📦 Return to verify — Order #{$o->id}",
                        'sub' => $o->customer_name . ' · Rs. ' . number_format((float) $o->total_amount),
                        'url' => route('orders.show', $o->id),
                        'time' => $o->updated_at,
                    ]);
                });

            // Fresh web orders (last 2 days)
            Order::where('source', 'web')
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subDays(2))
                ->latest()
                ->limit(5)
                ->get()
                ->each(function ($o) use ($items) {
                    $items->push([
                        'type' => 'order',
                        'title' => "🛒 New web order #{$o->id}",
                        'sub' => $o->customer_name . ' · Rs. ' . number_format((float) $o->total_amount),
                        'url' => route('orders.show', $o->id),
                        'time' => $o->created_at,
                    ]);
                });
        }

        if ($user->hasPermission('orders')) {
            // PHASE-4: orders sitting in pending/confirmed for over 48h
            Order::whereIn('status', ['pending', 'confirmed'])
                ->where('created_at', '<', now()->subHours(48))
                ->orderBy('created_at')
                ->limit(4)
                ->get()
                ->each(function ($o) use ($items) {
                    $items->push([
                        'type' => 'stuck',
                        'title' => "⏰ Order #{$o->id} stuck in " . ucfirst($o->status),
                        'sub' => $o->customer_name . ' · since ' . $o->created_at->diffForHumans(),
                        'url' => route('orders.show', $o->id),
                        'time' => $o->created_at->copy()->addHours(48), // "became stuck" moment
                    ]);
                });
        }

        if ($user->hasPermission('products')) {
            // PHASE-4: low stock alerts
            $threshold = (int) setting('low_stock_threshold', 10);
            \App\Models\Product::where('stock', '<', $threshold)
                ->orderBy('stock')
                ->limit(4)
                ->get()
                ->each(function ($p) use ($items, $threshold) {
                    $items->push([
                        'type' => 'stock',
                        'title' => "📉 Low stock: {$p->name}",
                        'sub' => "{$p->stock} left (threshold {$threshold})",
                        'url' => route('products.index'),
                        'time' => $p->updated_at,
                    ]);
                });
        }

        // Rider comments are visible to all authenticated staff
        RiderComment::where('status', 'unread')
            ->latest()
            ->limit(6)
            ->get()
            ->each(function ($c) use ($items) {
                $items->push([
                    'type' => 'rider',
                    'title' => '🛵 Rider comment' . ($c->order_id ? " — Order #{$c->order_id}" : ''),
                    'sub' => \Illuminate\Support\Str::limit($c->rider_comment, 70),
                    'url' => route('rider_comments.index'),
                    'time' => $c->created_at,
                ]);
            });

        if ($user->hasPermission('facebook_inbox')) {
            SupportTicket::where('status', 'open')
                ->latest()
                ->limit(5)
                ->get()
                ->each(function ($t) use ($items) {
                    $items->push([
                        'type' => 'ticket',
                        'title' => '🎫 Open support ticket' . ($t->customer_name ? ' — ' . $t->customer_name : ''),
                        'sub' => \Illuminate\Support\Str::limit($t->description ?? ucfirst($t->category ?? 'Support ticket'), 70),
                        'url' => route('support-tickets.index'),
                        'time' => $t->created_at,
                    ]);
                });
        }

        $seenAt = $user->notifications_seen_at;
        $sorted = $items->sortByDesc('time')->take(15)->values()->map(function ($item) use ($seenAt) {
            $item['unread'] = !$seenAt || $item['time']->gt($seenAt);
            $item['time_human'] = $item['time']->diffForHumans(short: true);
            unset($item['time']);
            return $item;
        });

        return response()->json([
            'items' => $sorted,
            'unread' => $sorted->where('unread', true)->count(),
        ]);
    }

    public function markSeen(Request $request)
    {
        $request->user()->forceFill(['notifications_seen_at' => now()])->save();

        return response()->json(['success' => true]);
    }
}
