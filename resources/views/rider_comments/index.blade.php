<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
          {{ __('Rider Comments Inbox') }}
        </h2>
        <p class="text-sm font-bold text-gray-500 mt-1">Manage Pathao rider issues and comments.</p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('rider_comments.index', ['status' => 'unread']) }}" class="bg-white border border-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl shadow-sm hover:bg-gray-50 transition duration-150">Unread</a>
        <a href="{{ route('rider_comments.index', ['assigned_to' => 'me']) }}" class="bg-white border border-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl shadow-sm hover:bg-gray-50 transition duration-150">Assigned to Me</a>
        <a href="{{ route('rider_comments.index') }}" class="bg-white border border-gray-200 text-gray-700 font-bold py-2 px-4 rounded-xl shadow-sm hover:bg-gray-50 transition duration-150">All</a>
      </div>
    </div>
  </x-slot>

  <div class="py-6 h-[calc(100vh-140px)] flex max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 gap-6">
    
    <!-- Sidebar: List of Comments -->
    <div class="w-1/3 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex flex-col overflow-hidden">
      <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="font-black text-lg text-gray-900">Inbox</h3>
        <span class="bg-red-100 text-red-700 font-black text-xs px-2 py-1 rounded-lg" id="unread-badge">{{ \App\Models\RiderComment::where('status', 'unread')->count() }} Unread</span>
      </div>
      <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
        @forelse($comments as $comment)
          <a href="{{ route('rider_comments.index', ['id' => $comment->id] + request()->except('id')) }}" class="block p-4 hover:bg-mango/5 transition-colors {{ (isset($selectedComment) && $selectedComment->id === $comment->id) ? 'bg-mango/10 border-l-4 border-mango' : 'border-l-4 border-transparent' }}">
            <div class="flex justify-between items-start mb-1">
              <span class="font-black text-gray-900 text-sm">Order #{{ $comment->order_id }}</span>
              <span class="text-[10px] font-bold text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ $comment->rider_comment }}</p>
            <div class="flex items-center justify-between">
              <div class="flex gap-1">
                @if($comment->status === 'unread')
                  <span class="w-2 h-2 bg-red-500 rounded-full mt-1"></span>
                @endif
                @if($comment->tag)
                  <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold uppercase tracking-wider">{{ $comment->tag }}</span>
                @endif
              </div>
              @if($comment->assignedUser)
                <span class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $comment->assignedUser->name }}</span>
              @else
                <span class="text-[10px] font-bold text-gray-400">Unassigned</span>
              @endif
            </div>
          </a>
        @empty
          <div class="p-8 text-center text-gray-400 font-bold">
            No comments found.
          </div>
        @endforelse
      </div>
      <div class="p-3 border-t border-gray-100 bg-gray-50">
        {{ $comments->links() }}
      </div>
    </div>

    <!-- Main Area: Selected Comment Thread -->
    <div class="flex-1 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex flex-col overflow-hidden relative">
      @if(isset($selectedComment))
        <!-- Header -->
        <div class="p-4 border-b border-gray-100 flex justify-between items-start bg-gray-50/50">
          <div>
            <h3 class="font-black text-xl text-gray-900">Order #{{ $selectedComment->order_id }} <span class="text-gray-400 text-sm font-bold">Consignment: {{ $selectedComment->order->pathao_consignment_id ?? 'N/A' }}</span></h3>
            <p class="text-sm font-bold text-gray-500">Customer: {{ $selectedComment->order->customer_name ?? 'N/A' }} ({{ $selectedComment->order->customer_phone ?? 'N/A' }})</p>
          </div>
          <div class="flex items-center gap-2">
            <!-- Tag Form -->
            <form action="{{ route('rider_comments.tag', $selectedComment->id) }}" method="POST" class="flex items-center gap-1">
              @csrf
              <input type="text" name="tag" value="{{ $selectedComment->tag }}" placeholder="Add Tag..." class="text-xs border-gray-200 rounded-lg py-1.5 px-2 focus:ring-mango focus:border-mango" style="width: 120px;">
              <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-1.5 px-3 rounded-lg text-xs transition">Tag</button>
            </form>

            <!-- Assign Form -->
            <form action="{{ route('rider_comments.assign', $selectedComment->id) }}" method="POST" class="flex items-center gap-1">
              @csrf
              <select name="assigned_user_id" class="text-xs border-gray-200 rounded-lg py-1.5 px-2 focus:ring-mango focus:border-mango" onchange="this.form.submit()">
                <option value="">Unassigned</option>
                @foreach($users as $u)
                  <option value="{{ $u->id }}" {{ $selectedComment->assigned_user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
              </select>
            </form>

            @if($selectedComment->status !== 'resolved')
              <form action="{{ route('rider_comments.resolve', $selectedComment->id) }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-100 text-green-700 hover:bg-green-200 font-bold py-1.5 px-3 rounded-lg text-xs transition">Resolve</button>
              </form>
            @endif
          </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 p-6 overflow-y-auto bg-gray-50 flex flex-col gap-4">
          <!-- Rider Comment -->
          <div class="flex flex-col items-start">
            <span class="text-xs font-bold text-gray-400 ml-2 mb-1">Delivery Rider • {{ $selectedComment->created_at->format('M d, g:i A') }}</span>
            <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-5 py-3 shadow-sm max-w-[80%]">
              <p class="text-gray-800 text-sm whitespace-pre-wrap">{{ $selectedComment->rider_comment }}</p>
            </div>
          </div>

          <!-- Admin Reply -->
          @if($selectedComment->admin_reply)
            <div class="flex flex-col items-end mt-4">
              <span class="text-xs font-bold text-gray-400 mr-2 mb-1">Admin Reply • {{ $selectedComment->updated_at->format('M d, g:i A') }}</span>
              <div class="bg-mango text-gray-900 font-medium rounded-2xl rounded-tr-sm px-5 py-3 shadow-sm max-w-[80%]">
                <p class="text-sm whitespace-pre-wrap">{{ $selectedComment->admin_reply }}</p>
              </div>
            </div>
          @endif
        </div>

        <!-- Reply Box -->
        @if($selectedComment->status !== 'resolved')
          <div class="p-4 bg-white border-t border-gray-100">
            <form action="{{ route('rider_comments.reply', $selectedComment->id) }}" method="POST" class="flex gap-2">
              @csrf
              <textarea name="reply" rows="2" class="flex-1 border-gray-200 rounded-xl focus:ring-mango focus:border-mango resize-none text-sm p-3" placeholder="Type your reply to the rider..."></textarea>
              <button type="submit" class="bg-gray-900 text-white font-black px-6 rounded-xl hover:bg-gray-800 transition flex items-center justify-center shadow-sm">
                Send Reply
              </button>
            </form>
          </div>
        @endif
      @else
        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
          <p class="font-bold text-lg">Select a comment to view thread</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Notification Sound and Polling -->
  <audio id="notification-sound" src="https://actions.google.com/sounds/v1/alarms/beep_short.ogg" preload="auto"></audio>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let currentUnreadCount = parseInt("{{ \App\Models\RiderComment::where('status', 'unread')->count() }}") || 0;
      const badge = document.getElementById('unread-badge');
      const sound = document.getElementById('notification-sound');

      setInterval(() => {
        fetch('{{ route('api.rider_comments.unreadCount') }}')
          .then(response => response.json())
          .then(data => {
            if (data.count > currentUnreadCount) {
              // New comment arrived!
              if (sound) {
                sound.play().catch(e => console.log('Audio play failed:', e));
              }
              // Only reload if we are not currently typing a reply
              const activeTextarea = document.activeElement.tagName === 'TEXTAREA';
              if (!activeTextarea) {
                window.location.reload();
              }
            }
            currentUnreadCount = data.count;
            if(badge) {
              badge.innerText = data.count + ' Unread';
            }
          })
          .catch(err => console.error('Polling error:', err));
      }, 10000); // Check every 10 seconds
    });
  </script>
</x-app-layout>
