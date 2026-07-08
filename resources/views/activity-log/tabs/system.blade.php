    <div class="space-y-6 mt-6">

      {{-- LOG FILE SELECTOR + FILTERS + STATS --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
          <form method="GET" action="{{ route('activity-log.index') }}" class="flex items-end gap-3">
            <input type="hidden" name="tab" value="system">
            <div class="flex-1">
              <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Log File</label>
              <select name="log_file" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                @foreach($data['logFileList'] as $file)
                  <option value="{{ $file['name'] }}" {{ $data['selectedFile'] == $file['name'] ? 'selected' : '' }}>
                    {{ $file['name'] }} ({{ $file['size'] }} — {{ $file['modified'] }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="w-40">
              <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Level</label>
              <select name="level" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                <option value="">All Levels</option>
                @foreach(['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'] as $lvl)
                  <option value="{{ $lvl }}" {{ ($data['levelFilter'] ?? '') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
              </select>
            </div>
            <button type="submit" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity shadow-lg shadow-gray-900/20">
              Filter
            </button>
            @if($data['levelFilter'] ?? '')
            <a href="{{ route('activity-log.index', ['tab' => 'system', 'log_file' => $data['selectedFile']]) }}" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
              Clear
            </a>
            @endif
          </form>
        </div>

        <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
          <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">File Stats</div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <div class="text-lg font-black text-gray-900 ">{{ count($data['entries']) }}</div>
              <div class="text-xs font-medium text-gray-400">Entries shown</div>
            </div>
            <div>
              <div class="text-lg font-black text-gray-900 ">{{ $data['fileSizeFormatted'] }}</div>
              <div class="text-xs font-medium text-gray-400">File size</div>
            </div>
          </div>
        </div>
      </div>

      {{-- LOG ENTRIES --}}
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden" x-data="{ expandedEntry: null }">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
          <h3 class="font-black text-gray-900 text-lg flex items-center gap-2">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            {{ $data['selectedFile'] }}
          </h3>
          <span class="text-xs font-bold text-gray-400">Most recent 500 entries</span>
        </div>

        <div class="divide-y divide-gray-50 ">
          @forelse($data['entries'] as $i => $entry)
          @php
            $levelColors = [
              'ERROR' => 'bg-red-50 text-red-700 border-red-200  ',
              'WARNING' => 'bg-amber-50 text-amber-700 border-amber-200  ',
              'INFO' => 'bg-blue-50 text-blue-700 border-blue-200  ',
              'DEBUG' => 'bg-gray-50 text-gray-600 border-gray-200  ',
              'NOTICE' => 'bg-cyan-50 text-cyan-700 border-cyan-200  ',
              'CRITICAL' => 'bg-red-100 text-red-800 border-red-300  ',
              'ALERT' => 'bg-orange-100 text-orange-800 border-orange-300  ',
              'EMERGENCY' => 'bg-red-200 text-red-900 border-red-400  ',
            ];
            $levelColor = $levelColors[$entry['level']] ?? $levelColors['DEBUG'];
            $levelDots = [
              'ERROR' => 'bg-red-500',
              'WARNING' => 'bg-amber-500',
              'INFO' => 'bg-blue-500',
              'DEBUG' => 'bg-gray-400',
              'CRITICAL' => 'bg-red-600',
              'ALERT' => 'bg-orange-500',
              'EMERGENCY' => 'bg-red-700',
              'NOTICE' => 'bg-cyan-500',
            ];
            $dotColor = $levelDots[$entry['level']] ?? 'bg-gray-400';
          @endphp
          <div class="px-6 py-3 hover:bg-gray-50/50 transition-colors {{ in_array($entry['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT']) ? 'border-l-4 border-l-red-400' : '' }}">
            <div class="flex items-start gap-3">
              <div class="w-2 h-2 rounded-full {{ $dotColor }} mt-2 shrink-0"></div>
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black tracking-wide {{ $levelColor }}">
                    {{ $entry['level'] }}
                  </span>
                  <span class="text-xs font-medium text-gray-400">{{ $entry['timestamp'] }}</span>
                </div>
                <p class="text-sm text-gray-700 font-medium break-words leading-relaxed">{{ Str::limit($entry['message'], 300) }}</p>
              </div>

              @if($entry['stack_trace'])
              <button @click="expandedEntry = expandedEntry === {{ $i }} ? null : {{ $i }}" class="shrink-0 text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="View Stack Trace">
                <svg class="w-4 h-4 transition-transform" :class="expandedEntry === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>
              @endif
            </div>

            @if($entry['stack_trace'])
            <div x-show="expandedEntry === {{ $i }}" x-collapse x-cloak class="mt-3 ml-5">
              <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ $entry['stack_trace'] }}</pre>
              </div>
            </div>
            @endif
          </div>
          @empty
          <div class="p-14 text-center">
            <div class="text-5xl mb-4">✨</div>
            <h4 class="text-lg font-black text-gray-900 ">Log File is Empty</h4>
            <p class="text-gray-500 mt-2 text-sm font-medium">No log entries found in this file.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>
