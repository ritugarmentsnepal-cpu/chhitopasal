    <div class="space-y-6">
      {{-- Stats Grid --}}
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-gray-900">{{ $stats['total_conversations'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Conversations Trained</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-gray-900">{{ $stats['total_messages'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Messages Analyzed</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-green-600">{{ $stats['orders_by_ai'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Orders Created by AI</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-orange-600">{{ $stats['open_tickets'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Open Tickets</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-blue-600">{{ $stats['knowledge_entries'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Knowledge Entries</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-purple-600">{{ $stats['active_threads'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Active AI Threads</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-gray-900">{{ $stats['pages_connected'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Pages Connected</div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="text-3xl font-black text-red-600">{{ $stats['tickets_created'] }}</div>
          <div class="text-sm font-bold text-gray-500 mt-1">Total Tickets</div>
        </div>
      </div>

      {{-- Recent AI Activity --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
          <h3 class="font-black text-lg text-gray-900">Recent AI Activity</h3>
          <p class="text-sm text-gray-500 font-bold">Last 20 auto-replies sent by the AI agent</p>
        </div>
        <div class="divide-y divide-gray-50">
          @forelse($recentActivity as $activity)
            <div class="px-5 py-3 flex items-start gap-3 hover:bg-gray-50/50 transition-colors">
              <span class="text-lg mt-0.5">🤖</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800 line-clamp-2">{{ $activity->message }}</p>
                <p class="text-xs text-gray-400 font-bold mt-1">{{ $activity->sent_at?->diffForHumans() ?? 'Unknown' }} • Thread: {{ Str::limit($activity->thread_id, 20) }}</p>
              </div>
            </div>
          @empty
            <div class="p-8 text-center text-gray-400 font-bold">
              <p class="text-4xl mb-2">🤖</p>
              <p>No AI activity yet. Enable the agent and connect a Facebook page to get started.</p>
            </div>
          @endforelse
        </div>
      </div>

      {{-- Test AI Agent --}}
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ testMessage: '', testResult: null, testing: false }">
        <div class="p-5 border-b border-gray-100">
          <h3 class="font-black text-lg text-gray-900">Test AI Agent</h3>
          <p class="text-sm text-gray-500 font-bold">Send a test message to see how the AI would respond (does not send to Facebook)</p>
        </div>
        <div class="p-5">
          <div class="flex gap-3">
            <input type="text" x-model="testMessage" placeholder="Type a test message... (e.g., 'yo product kati ho?')"
                class="flex-1 border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" @keydown.enter="
                if(!testMessage || testing) return;
                testing = true; testResult = null;
                fetch('{{ route('api.ai-agent.test') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({message:testMessage})})
                .then(r=>r.json()).then(d=>{testResult=d;testing=false;}).catch(e=>{testResult={reply:'Error: '+e.message};testing=false;})
                ">
            <button @click="
              if(!testMessage || testing) return;
              testing = true; testResult = null;
              fetch('{{ route('api.ai-agent.test') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({message:testMessage})})
              .then(r=>r.json()).then(d=>{testResult=d;testing=false;}).catch(e=>{testResult={reply:'Error: '+e.message};testing=false;})
            " class="bg-gray-900 text-white font-black px-6 py-2.5 rounded-xl hover:bg-gray-800 transition flex items-center gap-2" :disabled="testing">
              <svg x-show="testing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              <span x-text="testing ? 'Testing...' : 'Test'"></span>
            </button>
          </div>
          <div x-show="testResult" x-transition class="mt-4 bg-gray-50 rounded-xl p-4">
            <p class="text-sm font-bold text-gray-500 mb-1">AI Response:</p>
            <p class="text-gray-800 font-medium" x-text="testResult?.reply"></p>
            <div class="flex gap-4 mt-2 text-xs font-bold text-gray-400">
              <span x-show="testResult?.detected_phone">📱 Phone: <span class="text-green-600" x-text="testResult?.detected_phone"></span></span>
              <span x-show="testResult?.is_complaint">🎫 Complaint: <span class="text-red-600" x-text="testResult?.complaint_category"></span></span>
            </div>
          </div>
        </div>
      </div>
    </div>
