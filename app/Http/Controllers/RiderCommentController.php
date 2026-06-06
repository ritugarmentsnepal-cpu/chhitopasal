<?php

namespace App\Http\Controllers;

use App\Models\RiderComment;
use App\Models\User;
use App\Services\PathaoService;
use Illuminate\Http\Request;

class RiderCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = RiderComment::with(['order', 'assignedUser']);

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_user_id', auth()->id());
            } elseif ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_user_id');
            } else {
                $query->where('assigned_user_id', $request->assigned_to);
            }
        }

        // Sorting: unread/unreplied at top
        $comments = $query->orderByRaw("FIELD(status, 'unread', 'read', 'replied', 'resolved') ASC")
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        $users = User::all();
        
        $selectedComment = null;
        if ($request->has('id')) {
            $selectedComment = RiderComment::with(['order', 'assignedUser'])->find($request->id);
            if ($selectedComment && $selectedComment->status === 'unread') {
                $selectedComment->update(['status' => 'read']);
            }
        }

        return view('rider_comments.index', compact('comments', 'users', 'selectedComment'));
    }

    public function reply(Request $request, RiderComment $comment, PathaoService $pathao)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $replyText = $request->reply;

        // Optionally send to Pathao if issue_id is known
        if ($comment->pathao_issue_id) {
            $pathaoResponse = $pathao->replyToIssue($comment->pathao_issue_id, $replyText);
            if (!$pathaoResponse['success']) {
                return redirect()->back()->with('error', 'Failed to send reply to Pathao: ' . $pathaoResponse['error']);
            }
        }

        $comment->update([
            'admin_reply' => $replyText,
            'status' => 'replied',
        ]);

        return redirect()->route('rider_comments.index', ['id' => $comment->id])->with('success', 'Replied successfully.');
    }

    public function assign(Request $request, RiderComment $comment)
    {
        $request->validate([
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $comment->update([
            'assigned_user_id' => $request->assigned_user_id,
        ]);

        return redirect()->back()->with('success', 'Comment assigned successfully.');
    }

    public function tag(Request $request, RiderComment $comment)
    {
        $request->validate([
            'tag' => 'nullable|string|max:255',
        ]);

        $comment->update([
            'tag' => $request->tag,
        ]);

        return redirect()->back()->with('success', 'Tag updated successfully.');
    }

    public function markResolved(RiderComment $comment)
    {
        $comment->update([
            'status' => 'resolved',
        ]);

        return redirect()->back()->with('success', 'Marked as resolved.');
    }

    public function unreadCount()
    {
        $count = RiderComment::where('status', 'unread')->count();
        return response()->json(['count' => $count]);
    }
}
