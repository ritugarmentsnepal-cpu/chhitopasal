<?php

namespace App\Http\Controllers;

use App\Models\Mockup;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * PHASE-2.3: customer mockup approval via public share links.
 *
 * Staff generate a wa.me-ready link; the customer opens a branded page,
 * taps Approve or Request Changes, and the response lands on the mockup
 * and the order timeline. Approval auto-advances custom-print production
 * (design_received -> design_approved) when that transition is valid.
 */
class MockupApprovalController extends Controller
{
    /**
     * Generate (or reuse) the share token and return the shareable URLs.
     * Auth: permission:orders (route group).
     */
    public function share(Mockup $mockup)
    {
        if (!$mockup->share_token) {
            $mockup->update([
                'share_token' => Str::random(12),
                'approval_status' => 'pending',
            ]);
        }

        $url = route('mockups.approval.show', $mockup->share_token);

        // Prefill WhatsApp when the mockup is linked to an order with a phone
        $waLink = null;
        if ($mockup->order && $mockup->order->customer_phone) {
            $phone = '977' . ltrim(preg_replace('/\D/', '', $mockup->order->customer_phone), '0');
            $message = "Namaste {$mockup->order->customer_name}! 🎨 Your design mockup"
                . ($mockup->order_id ? " for Order #{$mockup->order_id}" : '')
                . " is ready. Please review and approve it here:\n{$url}";
            $waLink = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
        }

        return response()->json([
            'success' => true,
            'url' => $url,
            'wa_link' => $waLink,
            'approval_status' => $mockup->approval_status,
        ]);
    }

    /**
     * Public approval page. No auth — the token is the credential.
     */
    public function show(string $token)
    {
        $mockup = Mockup::where('share_token', $token)->with('order')->firstOrFail();

        return view('mockups.approval', compact('mockup'));
    }

    /**
     * Public: record the customer's decision.
     */
    public function respond(Request $request, string $token)
    {
        $mockup = Mockup::where('share_token', $token)->with('order')->firstOrFail();

        // Approval is final; changes_requested can be revisited after a new share
        if ($mockup->approval_status === 'approved') {
            return redirect()->route('mockups.approval.show', $token);
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,request_changes',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $approved = $validated['decision'] === 'approve';

        $mockup->update([
            'approval_status' => $approved ? 'approved' : 'changes_requested',
            'approval_feedback' => $validated['feedback'] ?? null,
            'approval_responded_at' => now(),
        ]);

        if ($mockup->order) {
            $order = $mockup->order;

            $order->logActivity($approved ? 'mockup_approved' : 'mockup_changes_requested', [
                'mockup_id' => $mockup->id,
                'mockup_title' => $mockup->title,
                'feedback' => $validated['feedback'] ?? null,
                'via' => 'customer approval link',
            ]);

            // Auto-advance production when the customer approves
            if ($approved && $order->isCustomPrint()) {
                $this->advanceProduction($order);
            }
        }

        return redirect()->route('mockups.approval.show', $token);
    }

    /**
     * Advance production to design_approved along valid transitions only.
     * Never throws — approval recording must not fail on pipeline state.
     */
    protected function advanceProduction($order): void
    {
        $service = app(OrderService::class);

        try {
            if ($order->production_status === null) {
                $service->transitionProductionStatus($order, 'design_received', 'Auto: mockup shared & approved by customer');
                $order->refresh();
            }
            if ($order->production_status === 'design_received') {
                $service->transitionProductionStatus($order, 'design_approved', 'Auto: customer approved mockup via link');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Mockup approval: production auto-advance skipped', [
                'order_id' => $order->id,
                'production_status' => $order->production_status,
                'reason' => $e->getMessage(),
            ]);
        }
    }
}
