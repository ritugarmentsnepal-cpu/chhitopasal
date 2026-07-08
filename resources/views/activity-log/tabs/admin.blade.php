    <div class="space-y-6 mt-6">

      {{-- FILTERS --}}
      <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
        <form method="GET" action="{{ route('activity-log.index') }}" class="flex flex-wrap items-end gap-3">
          <input type="hidden" name="tab" value="admin">

          <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Action</label>
            <select name="action" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
              <option value="">All Actions</option>
              @foreach($data['actions'] as $action)
                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Module</label>
            <select name="model" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
              <option value="">All Modules</option>
              @foreach($data['modelTypes'] as $model)
                <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ $model }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">User</label>
            <select name="user_id" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
              <option value="">All Users</option>
              <option value="system" {{ request('user_id') === 'system' ? 'selected' : '' }}>🤖 System / Automated</option>
              @foreach($data['users'] as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex-1 min-w-[130px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">From</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
          </div>

          <div class="flex-1 min-w-[130px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">To</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
          </div>

          <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Search ID</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Model ID or action…" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
          </div>

          <div class="flex gap-2">
            <button type="submit" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity shadow-lg shadow-gray-900/20">
              Filter
            </button>
            <a href="{{ route('activity-log.index', ['tab' => 'admin']) }}" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
              Clear
            </a>
          </div>
        </form>
      </div>

      {{-- ACTIVITY TIMELINE --}}
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
          <h3 class="font-black text-gray-900 text-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Activity Timeline
          </h3>
          <span class="text-xs font-bold text-gray-400">{{ $data['logs']->total() }} total entries</span>
        </div>

        <div class="divide-y divide-gray-50 " x-data="{ expandedLog: null }">
          @forelse($data['logs'] as $log)
          <div class="px-6 py-4 hover:bg-gray-50/50 transition-colors">
            <div class="flex items-start gap-4">
              {{-- Avatar --}}
              <div class="shrink-0">
                @if($log->user)
                  <div class="w-9 h-9 rounded-full bg-mango text-gray-900 flex items-center justify-center font-black text-sm">
                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                  </div>
                @else
                  <div class="w-9 h-9 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  </div>
                @endif
              </div>

              {{-- Content --}}
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span class="font-bold text-gray-900 text-sm">{{ $log->user->name ?? 'System' }}</span>

                  @php
                    $actionColors = [
                      'created' => 'bg-emerald-50 text-emerald-700 ',
                      'updated' => 'bg-blue-50 text-blue-700 ',
                      'deleted' => 'bg-red-50 text-red-700 ',
                      'login' => 'bg-violet-50 text-violet-700 ',
                      'logout' => 'bg-gray-100 text-gray-600 ',
                    ];
                    $actionColor = $actionColors[$log->action] ?? 'bg-amber-50 text-amber-700 ';
                  @endphp

                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide {{ $actionColor }}">
                    {{ str_replace('_', ' ', $log->action) }}
                  </span>

                  @if($log->model_type)
                    <span class="text-sm text-gray-500 ">
                      <span class="font-bold text-gray-700 ">{{ class_basename($log->model_type) }}</span>
                      <span class="text-gray-400">#{{ $log->model_id }}</span>
                    </span>
                  @endif
                </div>

                <div class="flex items-center gap-3 text-xs text-gray-400">
                  <span>{{ $log->created_at->format('M d, Y h:i A') }}</span>
                  <span>{{ $log->created_at->diffForHumans() }}</span>
                  @if($log->ip_address)
                    <span class="flex items-center gap-1">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                      {{ $log->ip_address }}
                    </span>
                  @endif
                </div>
              </div>

              {{-- Expand Button --}}
              @if(is_array($log->details) && count($log->details) > 0)
              <button @click="expandedLog = expandedLog === {{ $log->id }} ? null : {{ $log->id }}" class="shrink-0 text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 transition-transform" :class="expandedLog === {{ $log->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>
              @endif
            </div>

            {{-- Expanded Details --}}
            @if(is_array($log->details) && count($log->details) > 0)
            <div x-show="expandedLog === {{ $log->id }}" x-collapse x-cloak class="mt-3 ml-13">
              @if(isset($log->details['old']) && isset($log->details['new']))
                {{-- Show diff for updates --}}
                <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                  <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Changes</div>
                  @foreach($log->details['new'] as $field => $newVal)
                    @if($field !== 'updated_at')
                    <div class="flex items-start gap-2 text-xs">
                      <span class="font-bold text-gray-600 w-28 shrink-0">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                      <span class="text-red-500 line-through">{{ is_array($log->details['old'][$field] ?? null) ? json_encode($log->details['old'][$field]) : ($log->details['old'][$field] ?? '—') }}</span>
                      <svg class="w-3 h-3 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                      <span class="text-emerald-600 font-bold">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                    </div>
                    @endif
                  @endforeach
                </div>
              @else
                {{-- Show raw details --}}
                <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                  <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
              @endif
            </div>
            @endif
          </div>
          @empty
          <div class="p-14 text-center">
            <div class="text-5xl mb-4">📝</div>
            <h4 class="text-lg font-black text-gray-900 ">No Activity Logged Yet</h4>
            <p class="text-gray-500 mt-2 text-sm font-medium">Activity will appear here as users interact with the system.</p>
          </div>
          @endforelse
        </div>

        @if($data['logs']->hasPages())
        <div class="p-4 border-t border-gray-100 ">
          {{ $data['logs']->links() }}
        </div>
        @endif
      </div>
    </div>
