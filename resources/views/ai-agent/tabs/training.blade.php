    <div class="space-y-6">
      {{-- Sync Controls --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-black text-lg text-gray-900">Conversation Sync</h3>
            <p class="text-sm font-bold text-gray-500">Sync all past Facebook conversations to train the AI on your communication style.</p>
          </div>
          <form action="{{ route('ai-agent.sync') }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-600 text-white font-black px-6 py-2.5 rounded-xl hover:bg-blue-700 transition flex items-center gap-2" onclick="this.disabled=true;this.innerHTML='<svg class=\'animate-spin w-4 h-4\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z\'></path></svg> Syncing...';this.form.submit();">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
              Sync All Conversations
            </button>
          </form>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5">
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <div class="text-2xl font-black text-gray-900">{{ $pages->count() }}</div>
            <div class="text-xs font-bold text-gray-500">Pages Connected</div>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <div class="text-2xl font-black text-gray-900">{{ number_format($totalConversations) }}</div>
            <div class="text-xs font-bold text-gray-500">Conversations Synced</div>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <div class="text-2xl font-black text-gray-900">{{ number_format($totalMessages) }}</div>
            <div class="text-xs font-bold text-gray-500">Messages Stored</div>
          </div>
          <div class="bg-gray-50 rounded-xl p-3 text-center">
            <div class="text-sm font-black text-gray-900">{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'Never' }}</div>
            <div class="text-xs font-bold text-gray-500">Last Sync</div>
          </div>
        </div>
      </div>

      {{-- Sample Conversations --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
          <h3 class="font-black text-lg text-gray-900">Sample Training Conversations</h3>
          <p class="text-sm font-bold text-gray-500">These are example conversations the AI is learning from</p>
        </div>
        @forelse($sampleConversations as $convo)
          <div class="border-b border-gray-100 last:border-b-0">
            <div class="p-4 bg-gray-50/50">
              <span class="text-xs font-bold text-gray-400">Thread: {{ Str::limit($convo['thread_id'], 30) }}</span>
            </div>
            <div class="p-4 space-y-2">
              @foreach($convo['messages'] as $msg)
                <div class="flex items-start gap-2 {{ $msg->is_page_reply ? 'justify-end' : '' }}">
                  <div class="px-3 py-2 rounded-xl text-sm max-w-[80%] {{ $msg->is_page_reply ? 'bg-blue-100 text-blue-900' : 'bg-gray-100 text-gray-800' }}">
                    <span class="text-[10px] font-bold block mb-0.5 {{ $msg->is_page_reply ? 'text-blue-600' : 'text-gray-500' }}">{{ $msg->is_page_reply ? '👤 Employee' : '💬 Customer' }}</span>
                    {{ Str::limit($msg->message, 150) }}
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @empty
          <div class="p-12 text-center text-gray-400 font-bold">
            <p class="text-4xl mb-3">💬</p>
            <p>No training conversations yet. Click "Sync All Conversations" to fetch your chat history.</p>
          </div>
        @endforelse
      </div>
    </div>
