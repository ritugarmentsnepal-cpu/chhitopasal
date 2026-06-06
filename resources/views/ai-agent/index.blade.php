<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight flex items-center gap-3">
                    <span class="text-2xl">🤖</span> {{ __('AI Agent') }}
                    @if($aiEnabled)
                        <span class="bg-green-100 text-green-700 font-bold text-xs px-3 py-1 rounded-full">ACTIVE</span>
                    @else
                        <span class="bg-gray-100 text-gray-500 font-bold text-xs px-3 py-1 rounded-full">INACTIVE</span>
                    @endif
                </h2>
                <p class="text-sm font-bold text-gray-500 mt-1">Train, monitor, and manage your AI-powered Facebook Messenger agent.</p>
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
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 font-bold px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="flex gap-1 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100 mb-6">
            @php
                $tabs = [
                    'dashboard' => ['icon' => '📊', 'label' => 'Dashboard'],
                    'knowledge' => ['icon' => '📚', 'label' => 'Knowledge Base'],
                    'products' => ['icon' => '🏷️', 'label' => 'Product Training'],
                    'training' => ['icon' => '💬', 'label' => 'Conversation Training'],
                    'settings' => ['icon' => '⚙️', 'label' => 'Settings'],
                ];
            @endphp
            @foreach($tabs as $key => $tabInfo)
                <a href="{{ route('ai-agent.index', ['tab' => $key]) }}"
                   class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $tab === $key ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span>{{ $tabInfo['icon'] }}</span>
                    {{ $tabInfo['label'] }}
                </a>
            @endforeach
        </div>

        {{-- ======================= DASHBOARD TAB ======================= --}}
        @if($tab === 'dashboard')
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
        @endif

        {{-- ======================= KNOWLEDGE BASE TAB ======================= --}}
        @if($tab === 'knowledge')
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div class="flex gap-2">
                    <a href="{{ route('ai-agent.index', ['tab' => 'knowledge']) }}" class="px-3 py-1.5 rounded-lg text-sm font-bold {{ !$categoryFilter ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">All</a>
                    @foreach($categories as $catKey => $catLabel)
                        <a href="{{ route('ai-agent.index', ['tab' => 'knowledge', 'category' => $catKey]) }}" class="px-3 py-1.5 rounded-lg text-sm font-bold {{ $categoryFilter === $catKey ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">{{ $catLabel }}</a>
                    @endforeach
                </div>
                <button onclick="document.getElementById('knowledge-modal').classList.remove('hidden')" class="bg-gray-900 text-white font-black px-5 py-2.5 rounded-xl hover:bg-gray-800 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Entry
                </button>
            </div>

            <div class="grid gap-4">
                @forelse($knowledgeEntries as $entry)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-start justify-between gap-4 {{ !$entry->is_active ? 'opacity-50' : '' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] font-bold uppercase tracking-wider">{{ $categories[$entry->category] ?? $entry->category }}</span>
                                @if(!$entry->is_active)
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-bold">DISABLED</span>
                                @endif
                            </div>
                            <h4 class="font-black text-gray-900">{{ $entry->title }}</h4>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-3">{{ $entry->content }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button onclick="toggleKnowledge({{ $entry->id }})" class="p-2 rounded-lg {{ $entry->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }} transition" title="{{ $entry->is_active ? 'Disable' : 'Enable' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $entry->is_active ? 'M5 13l4 4L19 7' : 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' }}"></path></svg>
                            </button>
                            <button onclick="editKnowledge({{ json_encode($entry) }})" class="p-2 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <form action="{{ route('ai-agent.deleteKnowledge', $entry->id) }}" method="POST" onsubmit="return confirm('Delete this entry?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                        <p class="text-4xl mb-3">📚</p>
                        <p class="font-bold text-gray-500">No knowledge entries yet. Add FAQs, policies, and product info to train the AI.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Add/Edit Knowledge Modal --}}
        <div id="knowledge-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4">
                <form action="{{ route('ai-agent.storeKnowledge') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="knowledge-id" value="">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-black text-xl text-gray-900" id="knowledge-modal-title">Add Knowledge Entry</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Category</label>
                            <select name="category" id="knowledge-category" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" required>
                                @foreach($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}">{{ $catLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Title</label>
                            <input type="text" name="title" id="knowledge-title" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="e.g., Return Policy" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Content</label>
                            <textarea name="content" id="knowledge-content" rows="6" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="Enter the knowledge content that the AI will use..." required></textarea>
                        </div>
                    </div>
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('knowledge-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                        <button type="submit" class="bg-gray-900 text-white font-black px-6 py-2.5 rounded-xl hover:bg-gray-800 transition">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function toggleKnowledge(id) {
                fetch(`/ai-agent/knowledge/${id}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                    .then(r => r.json()).then(() => window.location.reload());
            }
            function editKnowledge(entry) {
                document.getElementById('knowledge-id').value = entry.id;
                document.getElementById('knowledge-category').value = entry.category;
                document.getElementById('knowledge-title').value = entry.title;
                document.getElementById('knowledge-content').value = entry.content;
                document.getElementById('knowledge-modal-title').textContent = 'Edit Knowledge Entry';
                document.getElementById('knowledge-modal').classList.remove('hidden');
            }
        </script>
        @endif

        {{-- ======================= PRODUCT TRAINING TAB ======================= --}}
        @if($tab === 'products')
        <div class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-black text-lg text-gray-900">Product Catalog (AI View)</h3>
                        <p class="text-sm font-bold text-gray-500">This is what the AI "knows" about your products. All data is auto-injected from your product database.</p>
                    </div>
                    <span class="bg-blue-100 text-blue-700 font-black text-xs px-3 py-1 rounded-full">{{ $products->count() }} Products</span>
                </div>
            </div>

            @foreach($products as $product)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start gap-4">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-xl object-cover shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-black text-gray-900">{{ $product->name }}</h4>
                                @if($product->category)
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold">{{ $product->category->name }}</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-3 text-sm font-bold mt-1">
                                <span class="text-green-600">Rs. {{ number_format($product->price) }}</span>
                                <span class="{{ $product->stock > 0 ? 'text-blue-600' : 'text-red-600' }}">Stock: {{ $product->stock }}</span>
                                @if($product->cost_price)
                                    <span class="text-gray-400">Cost: Rs. {{ number_format($product->cost_price) }}</span>
                                @endif
                            </div>
                            @if(!empty($product->color_options) && is_array($product->color_options))
                                <div class="flex items-center gap-1 mt-2">
                                    <span class="text-xs font-bold text-gray-500">Colors:</span>
                                    @foreach($product->color_options as $color)
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-bold text-gray-700">{{ $color }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($product->size_options) && is_array($product->size_options))
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="text-xs font-bold text-gray-500">Sizes:</span>
                                    @foreach($product->size_options as $size)
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-bold text-gray-700">{{ $size }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($product->bundles) && is_array($product->bundles))
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="text-xs font-bold text-gray-500">Bundles:</span>
                                    @foreach($product->bundles as $bundle)
                                        <span class="px-2 py-0.5 bg-mango/20 text-gray-900 rounded text-xs font-bold">{{ $bundle['qty'] }}-Pack: Rs. {{ number_format($bundle['price']) }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($product->description)
                                <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ strip_tags($product->description) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- ======================= CONVERSATION TRAINING TAB ======================= --}}
        @if($tab === 'training')
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
        @endif

        {{-- ======================= SETTINGS TAB ======================= --}}
        @if($tab === 'settings')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('settings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect_url" value="{{ route('ai-agent.index', ['tab' => 'settings']) }}">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-black text-xl text-gray-900">AI Agent Settings</h3>
                    <p class="text-sm font-bold text-gray-500">Configure your AI agent behavior</p>
                </div>
                <div class="p-6 space-y-6">
                    {{-- Master Toggle --}}
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-4 border border-gray-200">
                        <div>
                            <h4 class="font-black text-gray-900">AI Agent Status</h4>
                            <p class="text-sm text-gray-500 font-bold">Enable or disable the AI agent globally</p>
                        </div>
                        <div x-data="{ enabled: {{ $settings['ai_agent_enabled'] ? 'true' : 'false' }} }" class="flex items-center">
                            <input type="hidden" name="ai_agent_enabled" :value="enabled ? '1' : '0'">
                            <button type="button" @click="enabled = !enabled"
                                :class="enabled ? 'bg-gray-900' : 'bg-gray-300'"
                                class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                <span :class="enabled ? 'translate-x-8' : 'translate-x-1'"
                                    class="inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out shadow-sm">
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Model --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">AI Model</label>
                        <select name="ai_agent_model" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                            <option value="google/gemini-2.5-flash" {{ $settings['ai_agent_model'] === 'google/gemini-2.5-flash' ? 'selected' : '' }}>Google Gemini 2.5 Flash (Recommended)</option>
                            <option value="google/gemini-2.5-pro" {{ $settings['ai_agent_model'] === 'google/gemini-2.5-pro' ? 'selected' : '' }}>Google Gemini 2.5 Pro</option>
                            <option value="anthropic/claude-sonnet-4.6" {{ $settings['ai_agent_model'] === 'anthropic/claude-sonnet-4.6' ? 'selected' : '' }}>Claude Sonnet 4.6</option>
                            <option value="openai/gpt-4o" {{ $settings['ai_agent_model'] === 'openai/gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Uses the OpenRouter API key from Settings → Integrations</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        {{-- Max Messages --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Max AI Messages per Thread</label>
                            <input type="number" name="ai_agent_max_messages" value="{{ $settings['ai_agent_max_messages'] }}" min="1" max="100" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                            <p class="text-xs text-gray-400 mt-1">AI stops replying after this many messages in a single thread</p>
                        </div>

                        {{-- Response Delay --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Response Delay (seconds)</label>
                            <input type="number" name="ai_agent_response_delay" value="{{ $settings['ai_agent_response_delay'] }}" min="0" max="30" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                            <p class="text-xs text-gray-400 mt-1">Simulates human typing time (0 = instant reply)</p>
                        </div>

                        {{-- Working Hours Start --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Working Hours Start (Nepal Time)</label>
                            <select name="ai_agent_working_hours_start" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                                @for($h = 0; $h < 24; $h++)
                                    <option value="{{ $h }}" {{ (int)$settings['ai_agent_working_hours_start'] === $h ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Working Hours End --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Working Hours End (Nepal Time)</label>
                            <select name="ai_agent_working_hours_end" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900">
                                @for($h = 0; $h < 24; $h++)
                                    <option value="{{ $h }}" {{ (int)$settings['ai_agent_working_hours_end'] === $h ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Custom Greeting --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Custom Greeting (Optional)</label>
                        <textarea name="ai_agent_greeting" rows="3" class="w-full border-gray-200 rounded-xl text-sm focus:ring-gray-900 focus:border-gray-900" placeholder="Custom greeting message the AI should use...">{{ $settings['ai_agent_greeting'] }}</textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="bg-gray-900 text-white font-black px-8 py-2.5 rounded-xl hover:bg-gray-800 transition">Save Settings</button>
                </div>
            </form>
        </div>

        {{-- AI Real-Time Daemon Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-[24px] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 mt-8">
            <h3 class="text-xl font-black text-gray-900 mb-2">Real-Time AI Processing</h3>
            <p class="text-gray-500 font-medium mb-6">If your AI model takes too long to reply and hits the server timeout, start the background daemon to process messages instantly without timing out.</p>
            
            <form method="POST" action="{{ route('ai-agent.startDaemon') }}">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white font-black py-3 px-8 rounded-xl shadow-[0_8px_20px_rgb(79,70,229,0.3)] hover:bg-indigo-700 active:scale-95 transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Start Background Queue Daemon
                </button>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
