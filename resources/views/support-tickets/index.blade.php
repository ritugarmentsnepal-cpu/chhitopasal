<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight flex items-center gap-3">
                    <span class="text-2xl">🎫</span> {{ __('Support Tickets') }}
                </h2>
                <p class="text-sm font-bold text-gray-500 mt-1">Manage customer complaints and support requests created by the AI agent.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 font-bold px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Status Tabs --}}
        <div class="flex gap-1 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100 mb-6">
            @php
                $statusTabs = [
                    'open' => ['label' => 'Open', 'color' => 'red'],
                    'in_progress' => ['label' => 'In Progress', 'color' => 'yellow'],
                    'resolved' => ['label' => 'Resolved', 'color' => 'green'],
                    'closed' => ['label' => 'Closed', 'color' => 'gray'],
                    'all' => ['label' => 'All', 'color' => 'blue'],
                ];
            @endphp
            @foreach($statusTabs as $key => $tabInfo)
                <a href="{{ route('support-tickets.index', ['status' => $key]) }}"
                   class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $status === $key ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    {{ $tabInfo['label'] }}
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $status === $key ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="mb-6">
            <form action="{{ route('support-tickets.index') }}" method="GET" class="flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets by customer name, ID, or description..."
                       class="flex-1 border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                <button type="submit" class="bg-gray-900 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-gray-800 transition">Search</button>
            </form>
        </div>

        {{-- Tickets List --}}
        <div class="space-y-4">
            @forelse($tickets as $ticket)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ expanded: false }">
                    {{-- Ticket Header --}}
                    <div class="p-5 flex items-start justify-between cursor-pointer hover:bg-gray-50/50 transition-colors" @click="expanded = !expanded">
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                                {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-600' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-600' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-600' : 'bg-gray-100 text-gray-500')) }}">
                                <span class="font-black text-sm">#{{ $ticket->id }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="font-black text-gray-900">{{ $ticket->customer_name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $ticket->category === 'late_delivery' ? 'bg-orange-100 text-orange-700' : ($ticket->category === 'refund' ? 'bg-red-100 text-red-700' : ($ticket->category === 'damaged_product' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700')) }}">
                                        {{ \App\Models\SupportTicket::CATEGORIES[$ticket->category] ?? $ticket->category }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $ticket->description }}</p>
                                <div class="flex items-center gap-3 mt-2 text-xs font-bold text-gray-400">
                                    <span>{{ $ticket->created_at->diffForHumans() }}</span>
                                    @if($ticket->assignedUser)
                                        <span>Assigned to: {{ $ticket->assignedUser->name }}</span>
                                    @else
                                        <span class="text-orange-500">Unassigned</span>
                                    @endif
                                    @if($ticket->resolved_at)
                                        <span class="text-green-500">Resolved {{ $ticket->resolved_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>

                    {{-- Expanded Details --}}
                    <div x-show="expanded" x-collapse class="border-t border-gray-100">
                        <div class="p-5 bg-gray-50/50">
                            <form action="{{ route('support-tickets.update', $ticket) }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
                                        <select name="status" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                                            @foreach(\App\Models\SupportTicket::STATUSES as $sKey => $sLabel)
                                                <option value="{{ $sKey }}" {{ $ticket->status === $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Priority</label>
                                        <select name="priority" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                                            @foreach(\App\Models\SupportTicket::PRIORITIES as $pKey => $pLabel)
                                                <option value="{{ $pKey }}" {{ $ticket->priority === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Assign To</label>
                                        <select name="assigned_to" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                                            <option value="">Unassigned</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ $ticket->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Notes</label>
                                    <textarea name="notes" rows="2" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="Add notes...">{{ $ticket->notes }}</textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    @if($ticket->status !== 'resolved')
                                        <button type="submit" form="resolve-{{ $ticket->id }}" class="bg-green-100 text-green-700 font-bold px-5 py-2 rounded-xl hover:bg-green-200 transition text-sm">
                                            ✅ Quick Resolve
                                        </button>
                                    @endif
                                    <button type="submit" class="bg-gray-900 text-white font-black px-5 py-2 rounded-xl hover:bg-gray-800 transition text-sm">Update Ticket</button>
                                </div>
                            </form>
                            @if($ticket->status !== 'resolved')
                                <form id="resolve-{{ $ticket->id }}" action="{{ route('support-tickets.resolve', $ticket) }}" method="POST" class="hidden">@csrf</form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <p class="text-4xl mb-3">🎫</p>
                    <p class="font-bold text-gray-500 text-lg">No tickets found</p>
                    <p class="text-sm text-gray-400 mt-1">Support tickets will appear here when the AI agent detects customer complaints.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
