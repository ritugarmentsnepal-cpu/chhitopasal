<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed', 'all'];
        if (!in_array($status, $validStatuses)) {
            $status = 'open';
        }

        $query = SupportTicket::with('assignedUser')->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $search = $request->query('search');
        if ($search) {
            $escaped = \App\Services\OrderService::escapeLike($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('customer_name', 'like', "%{$escaped}%")
                  ->orWhere('description', 'like', "%{$escaped}%")
                  ->orWhere('id', $escaped);
            });
        }

        $tickets = $query->paginate(20)->withQueryString();
        $users = User::all();

        // Counts for tabs
        $counts = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
            'all' => SupportTicket::count(),
        ];

        return view('support-tickets.index', compact('tickets', 'status', 'users', 'counts'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $updateData = array_filter($validated, fn($v) => $v !== null);

        if (isset($updateData['status']) && $updateData['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
        }

        $ticket->update($updateData);

        return redirect()->back()->with('success', 'Ticket #' . $ticket->id . ' updated.');
    }

    public function resolve(SupportTicket $ticket)
    {
        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Ticket #' . $ticket->id . ' resolved.');
    }
}
