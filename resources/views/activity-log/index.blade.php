<x-app-layout>
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    {{-- ── PAGE HEADER ────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Activity Log</h2>
            <p class="text-gray-500 font-medium mt-1">Comprehensive audit trail for your entire system.</p>
        </div>
    </div>

    {{-- ── TAB SWITCHER ────────────────────────────────────────────────────── --}}
    <div>
        <div class="flex gap-2 bg-white dark:bg-gray-900 p-2 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100 dark:border-gray-800 w-fit">
            <a href="{{ route('activity-log.index', ['tab' => 'admin']) }}"
                class="px-5 py-2.5 rounded-full font-bold text-sm transition-all {{ $tab === 'admin' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}">
                👤 Admin Activity
            </a>
            <a href="{{ route('activity-log.index', ['tab' => 'system']) }}"
                class="px-5 py-2.5 rounded-full font-bold text-sm transition-all {{ $tab === 'system' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}">
                🖥️ System Logs
            </a>
            <a href="{{ route('activity-log.index', ['tab' => 'customer']) }}"
                class="px-5 py-2.5 rounded-full font-bold text-sm transition-all {{ $tab === 'customer' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}">
                🛍️ Customer Activity
            </a>
        </div>

        {{-- ╔══════════════════════════════════════════════════╗ --}}
        {{-- ║          TAB 1: ADMIN ACTIVITY                  ║ --}}
        {{-- ╚══════════════════════════════════════════════════╝ --}}
        @if($tab === 'admin')
        <div class="space-y-6 mt-6">

            {{-- FILTERS --}}
            <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                <form method="GET" action="{{ route('activity-log.index') }}" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="tab" value="admin">

                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Action</label>
                        <select name="action" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                            <option value="">All Actions</option>
                            @foreach($data['actions'] as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Module</label>
                        <select name="model" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                            <option value="">All Modules</option>
                            @foreach($data['modelTypes'] as $model)
                                <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">User</label>
                        <select name="user_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                            <option value="">All Users</option>
                            @foreach($data['users'] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">From</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                    </div>

                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">To</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Search ID</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Model ID or action…" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity shadow-lg shadow-gray-900/20">
                            Filter
                        </button>
                        <a href="{{ route('activity-log.index', ['tab' => 'admin']) }}" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- ACTIVITY TIMELINE --}}
            <div class="bg-white dark:bg-gray-900 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="font-black text-gray-900 dark:text-white text-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Activity Timeline
                    </h3>
                    <span class="text-xs font-bold text-gray-400">{{ $data['logs']->total() }} total entries</span>
                </div>

                <div class="divide-y divide-gray-50 dark:divide-gray-800" x-data="{ expandedLog: null }">
                    @forelse($data['logs'] as $log)
                    <div class="px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <div class="shrink-0">
                                @if($log->user)
                                    <div class="w-9 h-9 rounded-full bg-mango text-gray-900 flex items-center justify-center font-black text-sm">
                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $log->user->name ?? 'System' }}</span>

                                    @php
                                        $actionColors = [
                                            'created' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'updated' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'deleted' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            'login' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                                            'logout' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                        ];
                                        $actionColor = $actionColors[$log->action] ?? 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                                    @endphp

                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide {{ $actionColor }}">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>

                                    @if($log->model_type)
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ class_basename($log->model_type) }}</span>
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
                            <button @click="expandedLog = expandedLog === {{ $log->id }} ? null : {{ $log->id }}" class="shrink-0 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <svg class="w-4 h-4 transition-transform" :class="expandedLog === {{ $log->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @endif
                        </div>

                        {{-- Expanded Details --}}
                        @if(is_array($log->details) && count($log->details) > 0)
                        <div x-show="expandedLog === {{ $log->id }}" x-collapse x-cloak class="mt-3 ml-13">
                            @if(isset($log->details['old']) && isset($log->details['new']))
                                {{-- Show diff for updates --}}
                                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 space-y-2">
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Changes</div>
                                    @foreach($log->details['new'] as $field => $newVal)
                                        @if($field !== 'updated_at')
                                        <div class="flex items-start gap-2 text-xs">
                                            <span class="font-bold text-gray-600 dark:text-gray-300 w-28 shrink-0">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                                            <span class="text-red-500 line-through">{{ is_array($log->details['old'][$field] ?? null) ? json_encode($log->details['old'][$field]) : ($log->details['old'][$field] ?? '—') }}</span>
                                            <svg class="w-3 h-3 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <span class="text-emerald-600 font-bold">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                {{-- Show raw details --}}
                                <div class="bg-gray-900 dark:bg-gray-800 rounded-xl p-4 overflow-x-auto">
                                    <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="p-14 text-center">
                        <div class="text-5xl mb-4">📝</div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">No Activity Logged Yet</h4>
                        <p class="text-gray-500 mt-2 text-sm font-medium">Activity will appear here as users interact with the system.</p>
                    </div>
                    @endforelse
                </div>

                @if($data['logs']->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $data['logs']->links() }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ╔══════════════════════════════════════════════════╗ --}}
        {{-- ║          TAB 2: SYSTEM LOGS                     ║ --}}
        {{-- ╚══════════════════════════════════════════════════╝ --}}
        @if($tab === 'system')
        <div class="space-y-6 mt-6">

            {{-- LOG FILE SELECTOR + FILTERS + STATS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                    <form method="GET" action="{{ route('activity-log.index') }}" class="flex items-end gap-3">
                        <input type="hidden" name="tab" value="system">
                        <div class="flex-1">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Log File</label>
                            <select name="log_file" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                                @foreach($data['logFileList'] as $file)
                                    <option value="{{ $file['name'] }}" {{ $data['selectedFile'] == $file['name'] ? 'selected' : '' }}>
                                        {{ $file['name'] }} ({{ $file['size'] }} — {{ $file['modified'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-40">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Level</label>
                            <select name="level" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                                <option value="">All Levels</option>
                                @foreach(['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'] as $lvl)
                                    <option value="{{ $lvl }}" {{ ($data['levelFilter'] ?? '') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity shadow-lg shadow-gray-900/20">
                            Filter
                        </button>
                        @if($data['levelFilter'] ?? '')
                        <a href="{{ route('activity-log.index', ['tab' => 'system', 'log_file' => $data['selectedFile']]) }}" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            Clear
                        </a>
                        @endif
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">File Stats</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div class="text-lg font-black text-gray-900 dark:text-white">{{ count($data['entries']) }}</div>
                            <div class="text-xs font-medium text-gray-400">Entries shown</div>
                        </div>
                        <div>
                            <div class="text-lg font-black text-gray-900 dark:text-white">{{ $data['fileSizeFormatted'] }}</div>
                            <div class="text-xs font-medium text-gray-400">File size</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- LOG ENTRIES --}}
            <div class="bg-white dark:bg-gray-900 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800 overflow-hidden" x-data="{ expandedEntry: null }">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="font-black text-gray-900 dark:text-white text-lg flex items-center gap-2">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ $data['selectedFile'] }}
                    </h3>
                    <span class="text-xs font-bold text-gray-400">Most recent 500 entries</span>
                </div>

                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($data['entries'] as $i => $entry)
                    @php
                        $levelColors = [
                            'ERROR' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
                            'WARNING' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800',
                            'INFO' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800',
                            'DEBUG' => 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
                            'NOTICE' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800',
                            'CRITICAL' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                            'ALERT' => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-700',
                            'EMERGENCY' => 'bg-red-200 text-red-900 border-red-400 dark:bg-red-900/50 dark:text-red-200 dark:border-red-600',
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
                    <div class="px-6 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors {{ in_array($entry['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT']) ? 'border-l-4 border-l-red-400' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full {{ $dotColor }} mt-2 shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black tracking-wide {{ $levelColor }}">
                                        {{ $entry['level'] }}
                                    </span>
                                    <span class="text-xs font-medium text-gray-400">{{ $entry['timestamp'] }}</span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 font-medium break-words leading-relaxed">{{ Str::limit($entry['message'], 300) }}</p>
                            </div>

                            @if($entry['stack_trace'])
                            <button @click="expandedEntry = expandedEntry === {{ $i }} ? null : {{ $i }}" class="shrink-0 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="View Stack Trace">
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
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">Log File is Empty</h4>
                        <p class="text-gray-500 mt-2 text-sm font-medium">No log entries found in this file.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        {{-- ╔══════════════════════════════════════════════════╗ --}}
        {{-- ║          TAB 3: CUSTOMER ACTIVITY               ║ --}}
        {{-- ╚══════════════════════════════════════════════════╝ --}}
        @if($tab === 'customer')
        <div class="space-y-6 mt-6">

            {{-- KPI CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xl">👁️</span>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Sessions</span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($data['totalSessions']) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xl">✅</span>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Converted Sessions</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600">{{ number_format($data['convertedSessions']) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xl">🎯</span>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Conversion Rate</span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $data['conversionRate'] }}%</div>
                </div>
            </div>

            {{-- FILTERS --}}
            <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800">
                <form method="GET" action="{{ route('activity-log.index') }}" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="tab" value="customer">

                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">From</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                    </div>

                    <div class="flex-1 min-w-[130px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">To</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">UTM Source</label>
                        <select name="utm_source" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                            <option value="">All Sources</option>
                            @foreach($data['utmSources'] as $source)
                                <option value="{{ $source }}" {{ request('utm_source') == $source ? 'selected' : '' }}>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Conversion</label>
                        <select name="converted" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
                            <option value="">All Sessions</option>
                            <option value="1" {{ request('converted') === '1' ? 'selected' : '' }}>Converted Only</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity shadow-lg shadow-gray-900/20">
                            Filter
                        </button>
                        <a href="{{ route('activity-log.index', ['tab' => 'customer']) }}" class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- SESSION LIST --}}
            <div class="space-y-4" x-data="{ expandedSession: null }">
                @forelse($data['sessions'] as $session)
                @php
                    $eventCounts = $session->events->groupBy('event_type')->map->count();
                    $hasOrder = $session->orders->isNotEmpty();
                    $eventTypes = [
                        'page_view' => ['emoji' => '📄', 'label' => 'Page Views', 'color' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'],
                        'view_product' => ['emoji' => '🔍', 'label' => 'Product Views', 'color' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                        'add_to_cart' => ['emoji' => '🛒', 'label' => 'Add to Cart', 'color' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                        'initiate_checkout' => ['emoji' => '📋', 'label' => 'Checkout', 'color' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
                    ];
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800 overflow-hidden {{ $hasOrder ? 'ring-2 ring-emerald-200 dark:ring-emerald-800' : '' }}">
                    <div class="px-6 py-4 cursor-pointer hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors"
                         @click="expandedSession = expandedSession === '{{ $session->session_id }}' ? null : '{{ $session->session_id }}'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Journey indicator --}}
                                <div class="flex items-center gap-1">
                                    @foreach($eventTypes as $type => $info)
                                        @if(isset($eventCounts[$type]))
                                            <div class="w-3 h-3 rounded-full {{ $type === 'add_to_cart' ? 'bg-amber-400' : ($type === 'view_product' ? 'bg-blue-400' : ($type === 'initiate_checkout' ? 'bg-emerald-400' : 'bg-gray-300')) }}" title="{{ $info['label'] }}: {{ $eventCounts[$type] }}"></div>
                                        @else
                                            <div class="w-3 h-3 rounded-full bg-gray-100 dark:bg-gray-700" title="{{ $info['label'] }}: 0"></div>
                                        @endif
                                    @endforeach
                                    @if($hasOrder)
                                        <div class="w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200" title="Ordered!"></div>
                                    @else
                                        <div class="w-3 h-3 rounded-full bg-gray-100 dark:bg-gray-700" title="No Order"></div>
                                    @endif
                                </div>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $session->ip_address ?? 'Visitor' }} · {{ $session->created_at->format('M d, h:i A') }}</span>
                                        @if($hasOrder)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                ✅ CONVERTED
                                            </span>
                                        @endif
                                        @if($session->utm_source)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                                                {{ $session->utm_source }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $session->created_at->format('M d, Y h:i A') }} · {{ $session->events->count() }} events
                                        @if($session->landing_page_url)
                                            · 🔗 {{ parse_url($session->landing_page_url, PHP_URL_PATH) ?: '/' }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Event count pills --}}
                            <div class="hidden md:flex items-center gap-2">
                                @foreach($eventTypes as $type => $info)
                                    @if(isset($eventCounts[$type]))
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold {{ $info['color'] }}">
                                        {{ $info['emoji'] }} {{ $eventCounts[$type] }}
                                    </span>
                                    @endif
                                @endforeach
                                @if($hasOrder)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        💰 Rs. {{ number_format($session->orders->sum('total_amount')) }}
                                    </span>
                                @endif
                            </div>

                            <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="expandedSession === '{{ $session->session_id }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Expanded: Event Timeline --}}
                    <div x-show="expandedSession === '{{ $session->session_id }}'" x-collapse x-cloak>
                        <div class="px-6 pb-5 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Event Timeline</div>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($session->events->sortBy('created_at') as $event)
                                @php $evInfo = $eventTypes[$event->event_type] ?? ['emoji' => '📌', 'label' => $event->event_type, 'color' => 'bg-gray-100 text-gray-600']; @endphp
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="text-xs text-gray-400 w-16 shrink-0">{{ $event->created_at->format('H:i:s') }}</span>
                                    <span class="text-base">{{ $evInfo['emoji'] }}</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $evInfo['label'] }}</span>
                                    @if($event->product)
                                        <span class="text-xs text-gray-500">— {{ $event->product->name }}</span>
                                    @endif
                                    @if($event->url)
                                        <span class="text-xs text-gray-400 truncate max-w-[200px]">{{ parse_url($event->url, PHP_URL_PATH) }}</span>
                                    @endif
                                </div>
                                @endforeach

                                {{-- Show orders if converted --}}
                                @foreach($session->orders as $order)
                                <div class="flex items-center gap-3 text-sm mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs text-gray-400 w-16 shrink-0">{{ $order->created_at->format('H:i:s') }}</span>
                                    <span class="text-base">🎉</span>
                                    <span class="font-bold text-emerald-600">Order #{{ $order->id }}</span>
                                    <span class="text-xs text-gray-500">— {{ $order->customer_name }} · Rs. {{ number_format($order->total_amount) }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : ($order->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>

                            {{-- Session metadata --}}
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-3 text-xs text-gray-400">
                                @if($session->ip_address)
                                    <span>🌐 {{ $session->ip_address }}</span>
                                @endif
                                @if($session->utm_campaign)
                                    <span>📣 Campaign: {{ $session->utm_campaign }}</span>
                                @endif
                                @if($session->utm_medium)
                                    <span>📡 Medium: {{ $session->utm_medium }}</span>
                                @endif
                                @if($session->fbclid)
                                    <span>📘 Facebook Click ID</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white dark:bg-gray-900 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 dark:border-gray-800 p-14 text-center">
                    <div class="text-5xl mb-4">🛍️</div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">No Visitor Sessions Found</h4>
                    <p class="text-gray-500 mt-2 text-sm font-medium">Customer sessions will appear here once visitors browse the storefront.</p>
                </div>
                @endforelse

                @if($data['sessions']->hasPages())
                <div class="mt-4">
                    {{ $data['sessions']->links() }}
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

</x-app-layout>
