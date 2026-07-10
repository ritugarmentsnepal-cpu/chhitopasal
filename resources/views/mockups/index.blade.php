<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Mockup Studio</h2>
                <p class="text-sm font-medium text-gray-500 mt-0.5">AI-powered templates, mockups & print logos</p>
            </div>
            <div class="flex gap-3">
                <button x-data x-on:click="$dispatch('open-modal', 'template-generator')" class="bg-white border border-gray-200 text-gray-900 font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Generate Template
                </button>
                <button x-data x-on:click="$dispatch('open-modal', 'mockup-generator')" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black px-6 py-2.5 rounded-xl shadow-lg hover:shadow-xl hover:from-indigo-500 hover:to-purple-500 transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Generate Mockup
                </button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ tab: '{{ request('tab', 'mockups') }}' }">

        {{-- Stats Bar (PHASE-3.1: shared x-stat-card) --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <x-stat-card label="Mockups" :value="$mockups->total()" />
            <x-stat-card label="AI This Month" :value="$aiThisMonth->generations" color="text-indigo-600" :sub="'≈ $' . number_format($aiThisMonth->cost, 2)" title="AI generations this month (estimated cost)" />
            <x-stat-card label="Templates" :value="$templates->count()" color="text-amber-600" />
            <x-stat-card label="Logos Ready to Print" :value="$readyLogos->count()" color="text-emerald-600" />
            <x-stat-card label="Awaiting Confirmation" :value="$waitingLogos->count()" color="text-gray-500" />
        </div>

        {{-- Tabs --}}
        <div class="flex gap-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-2">
            <button @click="tab = 'mockups'" :class="tab === 'mockups' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-2.5 rounded-xl font-black text-sm transition">Mockups</button>
            <button @click="tab = 'templates'" :class="tab === 'templates' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-2.5 rounded-xl font-black text-sm transition">Templates</button>
            <button @click="tab = 'logos'" :class="tab === 'logos' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-2.5 rounded-xl font-black text-sm transition">
                Print Logos
                @if($readyLogos->count())
                    <span class="ml-1 bg-emerald-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $readyLogos->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'logolib'" :class="tab === 'logolib' ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:bg-gray-50'" class="flex-1 py-2.5 rounded-xl font-black text-sm transition">Logo Library</button>
        </div>

        {{-- ═══════════════ TAB: MOCKUPS ═══════════════ --}}
        <div x-show="tab === 'mockups'" class="space-y-6">
            {{-- Filter Bar --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px]">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search mockups..." class="w-full pl-10 rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <select name="source" class="rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5 min-w-[140px]">
                        <option value="">All Sources</option>
                        <option value="standalone" {{ request('source') === 'standalone' ? 'selected' : '' }}>Standalone</option>
                        <option value="order" {{ request('source') === 'order' ? 'selected' : '' }}>Linked to Order</option>
                    </select>
                    <select name="product_type" class="rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5 min-w-[140px]">
                        <option value="">All Types</option>
                        @foreach($productTypes as $type)
                            <option value="{{ $type }}" {{ request('product_type') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-gray-900 text-white font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-gray-800 transition active:scale-95">Filter</button>
                    @if(request()->anyFilled(['search', 'source', 'product_type']))
                        <a href="{{ route('mockups.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Gallery Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                @foreach($mockups as $mockup)
                    <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-indigo-200 transition-all duration-300 hover:-translate-y-0.5">
                        <div class="aspect-square bg-gray-50 overflow-hidden relative flex items-center justify-center p-3">
                            <img src="{{ '/storage/' . $mockup->image_path }}" alt="{{ $mockup->title }}" loading="lazy" class="max-w-full max-h-full object-contain rounded-lg">

                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-3">
                                <div class="flex gap-2">
                                    <a href="{{ '/storage/' . $mockup->image_path }}" target="_blank" class="bg-white/90 backdrop-blur-sm text-gray-900 p-2 rounded-lg hover:bg-white transition shadow-sm" title="View Full Size">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="{{ route('mockups.download', $mockup) }}" class="bg-white/90 backdrop-blur-sm text-gray-900 p-2 rounded-lg hover:bg-white transition shadow-sm" title="Download Mockup">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </a>
                                    @if($mockup->logo_path)
                                        <a href="{{ route('mockups.downloadLogo', $mockup) }}" class="bg-emerald-500/90 backdrop-blur-sm text-white p-2 rounded-lg hover:bg-emerald-600 transition shadow-sm" title="Download Logo for Print">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                        </a>
                                    @endif
                                    <button type="button" onclick="shareMockup({{ $mockup->id }})" class="bg-[#25D366]/90 backdrop-blur-sm text-white p-2 rounded-lg hover:bg-[#1ebe5b] transition shadow-sm" title="Share for Customer Approval">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </button>
                                    <form action="{{ route('mockups.destroy', $mockup) }}" method="POST" class="inline" onsubmit="return confirm('Delete this mockup?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="bg-red-500/90 backdrop-blur-sm text-white p-2 rounded-lg hover:bg-red-600 transition shadow-sm" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($mockup->approval_status)
                                <div class="absolute top-2 right-2">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full backdrop-blur-sm {{ $mockup->approval_status === 'approved' ? 'bg-emerald-500/90 text-white' : ($mockup->approval_status === 'changes_requested' ? 'bg-amber-500/90 text-white' : 'bg-gray-400/90 text-white') }}">
                                        {{ $mockup->approval_status === 'changes_requested' ? 'Changes Asked' : ucfirst($mockup->approval_status) }}
                                    </span>
                                </div>
                            @endif
                            @if($mockup->order_id)
                                <div class="absolute top-2 left-2">
                                    <a href="{{ route('orders.show', $mockup->order_id) }}" class="bg-purple-500/90 backdrop-blur-sm text-white text-[10px] font-black px-2 py-0.5 rounded-full hover:bg-purple-600 transition">
                                        Order #{{ $mockup->order_id }}
                                    </a>
                                </div>
                            @else
                                <div class="absolute top-2 left-2">
                                    <span class="bg-indigo-500/90 backdrop-blur-sm text-white text-[10px] font-black px-2 py-0.5 rounded-full">Standalone</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-3 border-t border-gray-50">
                            <h4 class="font-bold text-sm text-gray-900 truncate">{{ $mockup->title }}</h4>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-[10px] font-bold text-gray-400">{{ $mockup->created_at->format('M j, Y') }}</p>
                                @if($mockup->template)
                                    <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded-full">{{ ucwords(str_replace('_', ' ', $mockup->template->product_type)) }}</span>
                                @endif
                            </div>
                            @if(!empty($mockup->tags))
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($mockup->tags as $tag)
                                        <span class="text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($mockups->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-black text-gray-900">No mockups yet</h3>
                    <p class="text-sm font-medium text-gray-500 mt-1 max-w-md mx-auto">Generate a template first, then create mockups by uploading a customer's logo — the AI does the rest.</p>
                </div>
            @endif

            @if($mockups->hasPages())
                <div class="flex justify-center">
                    {{ $mockups->withQueryString()->links() }}
                </div>
            @endif
        </div>

        {{-- ═══════════════ TAB: TEMPLATES ═══════════════ --}}
        <div x-show="tab === 'templates'" x-cloak class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center justify-between">
                <p class="text-sm font-bold text-gray-500">Templates are reusable product scenes with a <span class="text-gray-900">"YOUR LOGO"</span> placeholder. Generate them with AI or upload manually.</p>
                <div class="flex gap-2 shrink-0">
                    @if(auth()->user()->role === 'admin')
                        <button x-data x-on:click="$dispatch('open-modal', 'add-template-manual')" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl font-bold text-sm hover:bg-gray-200 transition active:scale-95">
                            Upload Manually
                        </button>
                    @endif
                    <button x-data x-on:click="$dispatch('open-modal', 'template-generator')" class="bg-mango text-gray-900 px-4 py-2 rounded-xl font-bold text-sm hover:shadow-md transition active:scale-95">
                        + Generate with AI
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                @forelse($templates as $template)
                    <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-amber-200 transition-all duration-300">
                        <div class="aspect-square bg-gray-50 overflow-hidden relative flex items-center justify-center p-3">
                            <img src="{{ '/storage/' . $template->image_path }}" alt="{{ $template->name }}" loading="lazy" class="max-w-full max-h-full object-contain rounded-lg">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-3">
                                <div class="flex gap-2">
                                    <a href="{{ '/storage/' . $template->image_path }}" target="_blank" class="bg-white/90 backdrop-blur-sm text-gray-900 p-2 rounded-lg hover:bg-white transition shadow-sm" title="View Full Size">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                        <form action="{{ route('mockup_templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Delete this template?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="bg-red-500/90 backdrop-blur-sm text-white p-2 rounded-lg hover:bg-red-600 transition shadow-sm" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @if($template->is_ai_generated)
                                <span class="absolute top-2 left-2 bg-amber-500/90 backdrop-blur-sm text-white text-[10px] font-black px-2 py-0.5 rounded-full">AI</span>
                            @endif
                        </div>
                        <div class="p-3 border-t border-gray-50">
                            <h4 class="font-bold text-sm text-gray-900 truncate">{{ $template->name }}</h4>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                <span class="text-[9px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full uppercase">{{ str_replace('_', ' ', $template->product_type) }}</span>
                                @if($template->theme)
                                    <span class="text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ ucwords(str_replace('_', ' ', $template->theme)) }}</span>
                                @endif
                                @if($template->size)
                                    <span class="text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ ucfirst($template->size) }}</span>
                                @endif
                            </div>
                            @if($template->placements)
                                <p class="text-[10px] font-medium text-gray-400 mt-1.5 truncate" title="{{ $template->placements }}">{{ $template->placements }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                        <h3 class="text-lg font-black text-gray-900">No templates yet</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Generate your first template — upload a product photo and the AI turns it into a clean reusable mockup scene.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════ TAB: PRINT LOGOS ═══════════════ --}}
        <div x-show="tab === 'logos'" x-cloak class="space-y-6">
            {{-- Ready for print --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900">Ready for Print</h3>
                        <p class="text-xs font-medium text-gray-500">Logos from confirmed orders — download and send to printing</p>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($readyLogos as $m)
                        <div class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50/50 transition">
                            <div class="w-14 h-14 bg-gray-50 border border-gray-100 rounded-xl overflow-hidden flex items-center justify-center p-1.5 shrink-0">
                                <img src="{{ '/storage/' . $m->logo_path }}" loading="lazy" class="max-w-full max-h-full object-contain">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('orders.index', ['search' => $m->order_id]) }}" class="font-black text-sm text-gray-900 hover:text-indigo-600 transition">Order #{{ $m->order_id }}</a>
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $m->order->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">{{ str_replace('_', ' ', $m->order->status) }}</span>
                                </div>
                                <p class="text-xs font-medium text-gray-500 truncate">{{ $m->title }} · {{ $m->created_at->format('M j, Y') }}</p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <a href="{{ '/storage/' . $m->image_path }}" target="_blank" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-xl font-bold text-xs hover:bg-gray-200 transition">View Mockup</a>
                                <a href="{{ route('mockups.downloadLogo', $m) }}" class="bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold text-xs hover:bg-emerald-600 transition shadow-sm">Download Logo</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-gray-400 text-sm font-bold">No confirmed-order logos yet. When an order linked to a mockup gets confirmed, its logo appears here.</div>
                    @endforelse
                </div>
            </div>

            {{-- Awaiting confirmation --}}
            @if($waitingLogos->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden opacity-80">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-black text-gray-600">Awaiting Order Confirmation</h3>
                        <p class="text-xs font-medium text-gray-400">These logos become print-ready once their order is confirmed</p>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($waitingLogos as $m)
                            <div class="px-6 py-3 flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-50 border border-gray-100 rounded-xl overflow-hidden flex items-center justify-center p-1.5 shrink-0">
                                    <img src="{{ '/storage/' . $m->logo_path }}" loading="lazy" class="max-w-full max-h-full object-contain grayscale">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('orders.index', ['search' => $m->order_id]) }}" class="font-black text-sm text-gray-700">Order #{{ $m->order_id }}</a>
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">{{ str_replace('_', ' ', $m->order->status ?? 'unknown') }}</span>
                                    </div>
                                    <p class="text-xs font-medium text-gray-400 truncate">{{ $m->title }}</p>
                                </div>
                                <a href="{{ route('mockups.downloadLogo', $m) }}" class="text-gray-400 hover:text-gray-600 px-3 py-2 rounded-xl font-bold text-xs transition shrink-0">Download anyway</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══════════════ TAB: LOGO LIBRARY ═══════════════ --}}
        <div x-show="tab === 'logolib'" x-cloak class="space-y-6" x-data="{ logoSearch: '' }">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" x-model="logoSearch" placeholder="Search by logo name, customer or phone..." class="w-full pl-10 rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                </div>
                <button x-data x-on:click="$dispatch('open-modal', 'add-logo-modal')" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-800 transition active:scale-95 shrink-0">
                    + Add Logo
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @forelse($customerLogos as $logo)
                    <div x-show="logoSearch === '' || {{ json_encode(strtolower($logo->name . ' ' . $logo->customer_name . ' ' . $logo->customer_phone)) }}.includes(logoSearch.toLowerCase())"
                         x-data="{ editing: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group hover:border-indigo-200 hover:shadow-md transition">
                        <div class="aspect-square bg-gray-50 flex items-center justify-center p-3 relative">
                            <img src="{{ '/storage/' . $logo->file_path }}" alt="{{ $logo->name }}" loading="lazy" class="max-w-full max-h-full object-contain">
                            <div class="absolute top-1.5 right-1.5 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button @click="editing = !editing" class="bg-white/90 text-gray-700 p-1.5 rounded-lg shadow-sm hover:bg-white transition" title="Edit details">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form action="{{ route('customer_logos.destroy', $logo) }}" method="POST" onsubmit="return confirm('Remove this logo from the library?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-500/90 text-white p-1.5 rounded-lg shadow-sm hover:bg-red-600 transition" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="p-2.5 border-t border-gray-50" x-show="!editing">
                            <p class="font-bold text-xs text-gray-900 truncate">{{ $logo->name }}</p>
                            <p class="text-[10px] font-medium text-gray-400 truncate">
                                {{ $logo->customer_name ?: 'No customer' }}{{ $logo->customer_phone ? ' · ' . $logo->customer_phone : '' }}
                            </p>
                            <p class="text-[10px] font-bold text-indigo-400 mt-0.5">{{ $logo->mockups_count }} mockup{{ $logo->mockups_count !== 1 ? 's' : '' }}</p>
                        </div>
                        <form action="{{ route('customer_logos.update', $logo) }}" method="POST" class="p-2.5 border-t border-gray-50 space-y-1.5" x-show="editing" x-cloak>
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $logo->name }}" required placeholder="Logo name" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium py-1.5 px-2">
                            <input type="text" name="customer_name" value="{{ $logo->customer_name }}" placeholder="Customer name" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium py-1.5 px-2">
                            <input type="text" name="customer_phone" value="{{ $logo->customer_phone }}" placeholder="Customer phone" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs font-medium py-1.5 px-2">
                            <div class="flex gap-1.5">
                                <button type="submit" class="flex-1 bg-gray-900 text-white text-[10px] font-black py-1.5 rounded-lg hover:bg-gray-800 transition">Save</button>
                                <button type="button" @click="editing = false" class="flex-1 bg-gray-100 text-gray-500 text-[10px] font-black py-1.5 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                        <h3 class="text-lg font-black text-gray-900">No logos yet</h3>
                        <p class="text-sm font-medium text-gray-500 mt-1">Logos are added automatically when you generate mockups, or add one manually.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Add Logo Modal --}}
    <x-modal name="add-logo-modal" :show="false" maxWidth="md">
        <form method="POST" action="{{ route('customer_logos.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <h3 class="text-xl font-black text-gray-900 mb-6">Add Logo to Library</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Logo Name</label>
                    <input type="text" name="name" required placeholder="e.g. AAVA Jewellers" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Customer Name</label>
                        <input type="text" name="customer_name" placeholder="Optional" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Customer Phone</label>
                        <input type="text" name="customer_phone" placeholder="Optional" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Logo File (PNG best)</label>
                    <input type="file" name="logo" required accept="image/png,image/jpeg,image/webp" class="w-full text-sm font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700">
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                <button type="submit" class="bg-gray-900 text-white font-black px-8 py-2.5 rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95">Add Logo</button>
            </div>
        </form>
    </x-modal>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MODAL: AI TEMPLATE GENERATOR                             --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <x-modal name="template-generator" :show="false" maxWidth="4xl">
        <div class="p-6" x-data="templateGenerator()">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-xl font-black text-gray-900">Generate Mockup Template</h3>
                    <p class="text-sm font-medium text-gray-500">Pick or generate a background, add your product photo. <span class="text-amber-600 font-bold">~$0.04 per image.</span></p>
                </div>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 transition p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                {{-- Left: setup --}}
                <div class="space-y-4 max-h-[65vh] overflow-y-auto pr-1">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Template Name</label>
                            <input type="text" x-model="form.name" placeholder="e.g. White Polo" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Product</label>
                            <select x-model="form.product_type" @change="applyPlacementPreset()" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                                <option value="polo_tshirt">Polo T-Shirt</option>
                                <option value="tshirt">T-Shirt</option>
                                <option value="drawstring_pouch">Drawstring Pouch</option>
                                <option value="carry_bag">Carry Bag</option>
                                <option value="polymailer_bag">Polymailer Bag</option>
                                <option value="cap">Cap</option>
                                <option value="mug">Mug</option>
                                <option value="other">Other…</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="form.product_type === 'other'">
                        <input type="text" x-model="form.custom_product" placeholder="Custom product, e.g. canvas tote bag" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                    </div>

                    {{-- Step 1: background --}}
                    <div class="border border-indigo-100 bg-indigo-50/40 rounded-2xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-black text-gray-500 uppercase tracking-wider">1 · Background</label>
                            <div class="flex gap-1 bg-white border border-gray-100 rounded-lg p-0.5">
                                <button type="button" @click="bgMode = 'library'" :class="bgMode === 'library' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-400'" class="px-2.5 py-1 text-[10px] font-black rounded-md transition">Library</button>
                                <button type="button" @click="bgMode = 'new'" :class="bgMode === 'new' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-400'" class="px-2.5 py-1 text-[10px] font-black rounded-md transition">+ New</button>
                            </div>
                        </div>

                        <div x-show="bgMode === 'library'">
                            <div class="grid grid-cols-3 gap-2 max-h-44 overflow-y-auto p-1" x-show="backgrounds.length">
                                <template x-for="bg in backgrounds" :key="bg.id">
                                    <div @click="toggleBackground(bg.id)"
                                         :class="selectedBgIds.includes(bg.id) ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-300'"
                                         class="border-2 rounded-xl p-1 cursor-pointer transition bg-white relative group">
                                        <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                                            <img :src="bg.url" loading="lazy" class="w-full h-full object-cover">
                                        </div>
                                        <p class="text-[9px] font-bold text-gray-600 truncate mt-0.5" x-text="bg.name"></p>
                                        <div x-show="selectedBgIds.includes(bg.id)" class="absolute top-1 right-1 bg-indigo-500 text-white rounded-full p-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <button type="button" @click.stop="deleteBackground(bg)" class="absolute top-1 left-1 bg-red-500 text-white rounded-full w-4 h-4 leading-none text-[10px] font-black opacity-0 group-hover:opacity-100 transition">×</button>
                                    </div>
                                </template>
                            </div>
                            <p x-show="!backgrounds.length" class="text-[11px] font-bold text-gray-400 py-3 text-center">No saved backgrounds — <button type="button" @click="bgMode = 'new'" class="text-indigo-600 underline">generate one</button>, or leave empty to let the AI invent the scene.</p>
                            <p x-show="backgrounds.length" class="text-[10px] font-medium text-gray-400 mt-1">Select several for bulk generation — one template per background.</p>
                        </div>

                        <div x-show="bgMode === 'new'" x-cloak class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <select x-model="form.theme" class="w-full rounded-xl border-gray-200 bg-white text-xs font-medium py-2">
                                    <option value="studio">Studio (clean)</option>
                                    <option value="gradient">Soft Gradient</option>
                                    <option value="marble">Marble Surface</option>
                                    <option value="wood">Wooden Table</option>
                                    <option value="concrete">Concrete / Stone</option>
                                    <option value="linen">Linen Fabric</option>
                                    <option value="podium">Display Podium</option>
                                    <option value="lifestyle">Lifestyle Interior</option>
                                    <option value="outdoor">Outdoor</option>
                                </select>
                                <select x-model="form.lighting" class="w-full rounded-xl border-gray-200 bg-white text-xs font-medium py-2">
                                    <option value="soft">Soft Studio Light</option>
                                    <option value="warm">Warm Golden Light</option>
                                    <option value="dramatic">Dramatic Light</option>
                                    <option value="daylight">Natural Daylight</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <select x-model="form.size" class="w-full rounded-xl border-gray-200 bg-white text-xs font-medium py-2">
                                    <option value="square">Square (1:1)</option>
                                    <option value="portrait">Portrait (3:4)</option>
                                    <option value="landscape">Landscape (4:3)</option>
                                    <option value="wide">Wide (16:9)</option>
                                    <option value="story">Story (9:16)</option>
                                </select>
                                <input type="text" x-model="form.color_scheme" placeholder="Colours / mood (optional)" class="w-full rounded-xl border-gray-200 bg-white text-xs font-medium py-2">
                            </div>
                            <button type="button" @click="generateBackground()" :disabled="isGeneratingBg" class="w-full bg-indigo-600 text-white text-xs font-black py-2 rounded-xl hover:bg-indigo-700 transition active:scale-95 disabled:opacity-50">
                                <span x-show="!isGeneratingBg">Generate Background</span>
                                <span x-show="isGeneratingBg">Generating background…</span>
                            </button>
                        </div>
                    </div>

                    {{-- Step 2: product photo --}}
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">2 · Product Photo <span class="normal-case font-bold text-emerald-500">(recommended)</span></label>
                        <label class="block w-full cursor-pointer">
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-indigo-300 hover:bg-indigo-50/30 transition flex items-center justify-center gap-3 min-h-[56px]">
                                <template x-if="referencePreview">
                                    <img :src="referencePreview" class="h-12 rounded-lg object-contain">
                                </template>
                                <span class="text-xs font-bold text-gray-500" x-text="referencePreview ? 'Change photo' : 'Upload your product photo — it stays exactly as-is'"></span>
                            </div>
                            <input type="file" accept="image/png,image/jpeg,image/webp" @change="handleReference($event)" class="hidden">
                        </label>
                    </div>

                    {{-- Step 3: branding placeholder --}}
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">3 · Placeholder Size</label>
                        <div class="flex gap-1.5">
                            <template x-for="opt in [['small','Small'],['medium','Medium'],['large','Large'],['full','Full']]" :key="opt[0]">
                                <button type="button" @click="form.logo_coverage = opt[0]"
                                        :class="form.logo_coverage === opt[0] ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-gray-50 text-gray-600 border-gray-200'"
                                        class="flex-1 py-1.5 text-[11px] font-bold rounded-lg border transition active:scale-95" x-text="opt[1]"></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Logo Placements</label>
                        <input type="text" x-model="form.placements" placeholder="e.g. left chest pocket + large back print" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <template x-for="preset in placementPresets" :key="preset">
                                <button type="button" @click="form.placements = preset" class="text-[10px] font-bold text-gray-500 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-600 px-2 py-1 rounded-full transition" x-text="preset"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Advanced (collapsed) --}}
                    <details class="rounded-2xl border border-gray-100 p-3">
                        <summary class="text-xs font-black text-gray-400 uppercase tracking-wider cursor-pointer select-none">More Options</summary>
                        <div class="space-y-3 mt-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Presentation</label>
                                    <select x-model="form.presentation" class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium py-2">
                                        <option value="product_only">Product Only</option>
                                        <option value="ghost">Ghost Mannequin</option>
                                        <option value="model">On Model (faceless)</option>
                                        <option value="hanging">On Hanger</option>
                                        <option value="flat_lay">Flat Lay (top-down)</option>
                                        <option value="folded">Folded Stack</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Camera Angle</label>
                                    <select x-model="form.angle" class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium py-2">
                                        <option value="front">Front (straight-on)</option>
                                        <option value="three_quarter">3/4 Angle</option>
                                        <option value="high">Slightly Elevated</option>
                                        <option value="low">Low Hero Angle</option>
                                        <option value="closeup">Close-up</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Views in Image</label>
                                <div class="flex gap-1.5">
                                    <template x-for="opt in [['single','Single'],['front_back','Front + Back'],['grid','3-View Lineup']]" :key="opt[0]">
                                        <button type="button" @click="form.views = opt[0]"
                                                :class="form.views === opt[0] ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-gray-50 text-gray-600 border-gray-200'"
                                                class="flex-1 py-1.5 text-[11px] font-bold rounded-lg border transition active:scale-95" x-text="opt[1]"></button>
                                    </template>
                                </div>
                            </div>
                            <textarea x-model="form.style_notes" rows="2" placeholder="Extra style notes (optional)" class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium"></textarea>
                        </div>
                    </details>
                </div>

                {{-- Right: results --}}
                <div class="flex flex-col">
                    <div class="flex-1 bg-gray-50 border border-gray-200 rounded-2xl p-3 min-h-[320px] max-h-[58vh] overflow-y-auto">
                        <template x-if="!results.length">
                            <div class="h-full flex items-center justify-center text-center text-gray-400 min-h-[300px]">
                                <div>
                                    <svg class="w-12 h-12 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="text-sm font-bold">Generated templates appear here</p>
                                </div>
                            </div>
                        </template>
                        <div class="grid gap-3" :class="results.length > 1 ? 'grid-cols-2' : 'grid-cols-1'" x-show="results.length">
                            <template x-for="(r, idx) in results" :key="idx">
                                <div class="bg-white rounded-xl border p-2.5 relative" :class="r.discarded ? 'opacity-40 border-gray-200' : (r.status === 'error' ? 'border-red-200' : 'border-gray-200')">
                                    <p class="text-[10px] font-black text-gray-500 truncate mb-1.5" x-text="r.bgName"></p>
                                    <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                                        <template x-if="r.status === 'generating'">
                                            <svg class="animate-spin w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </template>
                                        <template x-if="r.status === 'done'">
                                            <img :src="r.url" class="max-w-full max-h-full object-contain">
                                        </template>
                                        <template x-if="r.status === 'error'">
                                            <p class="text-[11px] font-bold text-red-500 px-3 text-center" x-text="r.message"></p>
                                        </template>
                                    </div>
                                    <div class="flex gap-1.5 mt-2" x-show="r.status === 'done'">
                                        <a :href="r.url" target="_blank" class="flex-1 text-center bg-gray-100 text-gray-600 text-[11px] font-bold py-1.5 rounded-lg hover:bg-gray-200 transition">View</a>
                                        <button type="button" @click="r.discarded = !r.discarded" class="flex-1 text-[11px] font-bold py-1.5 rounded-lg transition" :class="r.discarded ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-red-50 text-red-500 hover:bg-red-100'" x-text="r.discarded ? 'Keep' : 'Discard'"></button>
                                        <button type="button" @click="retry(idx)" class="flex-1 bg-indigo-50 text-indigo-600 text-[11px] font-bold py-1.5 rounded-lg hover:bg-indigo-100 transition">Retry</button>
                                    </div>
                                    <div class="mt-2" x-show="r.status === 'error'">
                                        <button type="button" @click="retry(idx)" class="w-full bg-indigo-50 text-indigo-600 text-[11px] font-bold py-1.5 rounded-lg hover:bg-indigo-100 transition">Retry</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <p x-show="error" x-text="error" class="text-xs font-bold text-red-500 mt-2"></p>

                    <div class="flex gap-2 mt-4">
                        <button type="button" @click="generate()" :disabled="isGenerating" class="flex-1 bg-gray-900 text-white font-black py-2.5 rounded-xl hover:bg-gray-800 transition active:scale-95 disabled:opacity-50">
                            <span x-show="!isGenerating" x-text="'Generate ' + (selectedBgIds.length > 1 ? selectedBgIds.length + ' Templates' : 'Template')"></span>
                            <span x-show="isGenerating">Generating <span x-text="progressText"></span>…</span>
                        </button>
                        <button type="button" @click="saveAll()" x-show="results.some(r => r.status === 'done' && !r.discarded)" :disabled="isGenerating || isSavingTpl" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black py-2.5 rounded-xl shadow-lg hover:shadow-xl transition active:scale-95 disabled:opacity-50">
                            <span x-show="!isSavingTpl" x-text="'Save ' + results.filter(r => r.status === 'done' && !r.discarded).length + ' to Library'"></span>
                            <span x-show="isSavingTpl">Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MODAL: AI MOCKUP GENERATOR                               --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <x-modal name="mockup-generator" :show="false" maxWidth="6xl">
        <div class="p-6" x-data="mockupGenerator()">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-xl font-black text-gray-900">Generate Mockup</h3>
                    <p class="text-sm font-medium text-gray-500">Pick template(s), upload the customer's logo — the AI swaps the placeholder branding. <span class="text-amber-600 font-bold">~$0.04 per image.</span></p>
                </div>
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 transition p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <template x-if="wizard">
                <div class="mb-4 bg-indigo-50 border border-indigo-100 rounded-2xl px-4 py-3 flex items-center gap-3">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <p class="text-sm font-bold text-indigo-800">
                        Creating mockups for <span x-text="'Order #' + wizard.orderId"></span> — <span x-text="wizard.customer"></span>.
                        <span x-show="wizard.logoId" class="text-indigo-500 font-medium">Their logo is already selected from the library.</span>
                        <span class="text-indigo-400 font-medium">You'll return to the order after saving.</span>
                    </p>
                </div>
            </template>

            <div class="grid md:grid-cols-5 gap-6">
                {{-- Left: setup --}}
                <div class="md:col-span-2 space-y-4 max-h-[65vh] overflow-y-auto pr-1">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Mockup Title</label>
                        <input type="text" x-model="title" placeholder="e.g. ABC Company - Polo & Pouch" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider">Customer Logo</label>
                            <div class="flex gap-1 bg-gray-100 rounded-lg p-0.5">
                                <button type="button" @click="logoMode = 'upload'" :class="logoMode === 'upload' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-400'" class="px-2.5 py-1 text-[10px] font-black rounded-md transition">Upload</button>
                                <button type="button" @click="logoMode = 'library'" :class="logoMode === 'library' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-400'" class="px-2.5 py-1 text-[10px] font-black rounded-md transition">From Library</button>
                            </div>
                        </div>

                        <div x-show="logoMode === 'upload'">
                            <label class="block w-full cursor-pointer">
                                <div class="border-2 border-dashed border-gray-200 rounded-xl p-3 text-center hover:border-indigo-300 hover:bg-indigo-50/30 transition flex items-center justify-center gap-3 min-h-[64px]">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" class="h-14 rounded-lg object-contain">
                                    </template>
                                    <span class="text-xs font-bold text-gray-500" x-text="logoPreview ? 'Change logo' : 'Click to upload the customer logo (PNG best)'"></span>
                                </div>
                                <input type="file" accept="image/png,image/jpeg,image/webp" @change="handleLogo($event)" class="hidden">
                            </label>
                            <p class="text-[10px] font-medium text-gray-400 mt-1">Uploaded logos are saved to the Logo Library automatically.</p>
                        </div>

                        <div x-show="logoMode === 'library'" x-cloak>
                            <input type="text" x-model="logoFilter" placeholder="Search logos by name, customer, phone..." class="w-full rounded-xl border-gray-200 bg-gray-50 text-xs font-medium py-2 mb-2">
                            <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto p-1">
                                <template x-for="logo in filteredLogos" :key="logo.id">
                                    <div @click="pickLogo(logo)"
                                         :class="selectedLogoId === logo.id ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-300'"
                                         class="border-2 rounded-xl p-1 cursor-pointer transition bg-white relative" :title="logo.name + (logo.customer ? ' — ' + logo.customer : '')">
                                        <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                                            <img :src="logo.url" loading="lazy" class="max-w-full max-h-full object-contain">
                                        </div>
                                        <p class="text-[9px] font-bold text-gray-600 truncate mt-0.5" x-text="logo.name"></p>
                                        <div x-show="selectedLogoId === logo.id" class="absolute top-0.5 right-0.5 bg-indigo-500 text-white rounded-full p-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>
                                </template>
                                <p x-show="!filteredLogos.length" class="col-span-4 text-[11px] font-bold text-gray-300 py-4 text-center">No logos found</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider">Templates <span class="normal-case font-bold text-gray-300">(select one or more)</span></label>
                            <span class="text-[10px] font-black text-indigo-500" x-show="selectedTemplates.length" x-text="selectedTemplates.length + ' selected'"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 max-h-56 overflow-y-auto p-1">
                            @forelse($templates as $template)
                                <div @click="toggleTemplate({{ $template->id }})"
                                     :class="selectedTemplates.includes({{ $template->id }}) ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 hover:border-gray-300'"
                                     class="border-2 rounded-xl p-1.5 cursor-pointer transition bg-white relative">
                                    <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                                        <img src="{{ '/storage/' . $template->image_path }}" loading="lazy" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-700 truncate mt-1">{{ $template->name }}</p>
                                    <div x-show="selectedTemplates.includes({{ $template->id }})" class="absolute top-1 right-1 bg-indigo-500 text-white rounded-full p-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                            @empty
                                <p class="col-span-3 text-xs font-bold text-amber-600 py-4 text-center">⚠️ No templates yet — generate one first.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Logo Size on Product</label>
                        <div class="flex gap-2">
                            <template x-for="opt in [['small','Small'],['medium','Medium'],['large','Large']]" :key="opt[0]">
                                <button type="button" @click="logoSize = opt[0]"
                                        :class="logoSize === opt[0] ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-gray-50 text-gray-600 border-gray-200'"
                                        class="flex-1 py-2 text-xs font-bold rounded-xl border transition hover:scale-105 active:scale-95" x-text="opt[1]"></button>
                            </template>
                        </div>
                        <p class="text-[10px] font-medium text-gray-400 mt-1">Small = pocket/chest mark · Medium = standard print · Large = bold full print</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Link to Order <span class="normal-case font-bold text-gray-300">(optional)</span></label>
                        <input type="number" x-model="orderId" min="1" placeholder="Order ID, e.g. 1234" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                        <p class="text-[10px] font-medium text-gray-400 mt-1">Linked mockups appear on the order, and the logo lands in the Print Logos tab once the order is confirmed.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Extra Instructions <span class="normal-case font-bold text-gray-300">(optional)</span></label>
                        <textarea x-model="instructions" rows="2" placeholder="e.g. keep the logo small and subtle on the pocket" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium"></textarea>
                    </div>

                    <button type="button" @click="generate()" :disabled="isGenerating || !selectedTemplates.length || (!logoFile && !logoPath && !selectedLogoId)" class="w-full bg-gray-900 text-white font-black py-3 rounded-xl hover:bg-gray-800 transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                        <span x-show="!isGenerating" x-text="'Generate ' + (selectedTemplates.length > 1 ? selectedTemplates.length + ' Mockups' : 'Mockup')"></span>
                        <span x-show="isGenerating">Generating <span x-text="progressText"></span>…</span>
                    </button>
                    <p x-show="!isGenerating && (!selectedTemplates.length || (!logoFile && !logoPath && !selectedLogoId))" class="text-[11px] font-bold text-amber-600 text-center">
                        <span x-show="!logoFile && !logoPath && !selectedLogoId">Upload or pick a customer logo</span>
                        <span x-show="(!logoFile && !logoPath && !selectedLogoId) && !selectedTemplates.length"> and </span>
                        <span x-show="!selectedTemplates.length">select at least one template</span>
                        to generate.
                    </p>
                </div>

                {{-- Right: results --}}
                <div class="md:col-span-3 flex flex-col">
                    <div class="flex-1 bg-gray-50 border border-gray-200 rounded-2xl p-4 min-h-[380px] max-h-[58vh] overflow-y-auto">
                        <template x-if="!results.length">
                            <div class="h-full flex items-center justify-center text-center text-gray-400 min-h-[340px]">
                                <div>
                                    <svg class="w-12 h-12 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    <p class="text-sm font-bold">Generated mockups appear here</p>
                                </div>
                            </div>
                        </template>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="results.length">
                            <template x-for="(r, idx) in results" :key="idx">
                                <div class="bg-white rounded-xl border p-3 relative" :class="r.discarded ? 'opacity-40 border-gray-200' : (r.status === 'error' ? 'border-red-200' : 'border-gray-200')">
                                    <p class="text-[11px] font-black text-gray-500 truncate mb-2" x-text="r.templateName"></p>
                                    <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden flex items-center justify-center">
                                        <template x-if="r.status === 'generating'">
                                            <svg class="animate-spin w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </template>
                                        <template x-if="r.status === 'done'">
                                            <img :src="r.url" class="max-w-full max-h-full object-contain">
                                        </template>
                                        <template x-if="r.status === 'error'">
                                            <p class="text-[11px] font-bold text-red-500 px-3 text-center" x-text="r.message"></p>
                                        </template>
                                    </div>
                                    <div class="flex gap-1.5 mt-2" x-show="r.status === 'done'">
                                        <a :href="r.url" target="_blank" class="flex-1 text-center bg-gray-100 text-gray-600 text-[11px] font-bold py-1.5 rounded-lg hover:bg-gray-200 transition">View</a>
                                        <button type="button" @click="r.discarded = !r.discarded" class="flex-1 text-[11px] font-bold py-1.5 rounded-lg transition" :class="r.discarded ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-red-50 text-red-500 hover:bg-red-100'" x-text="r.discarded ? 'Keep' : 'Discard'"></button>
                                        <button type="button" @click="retry(idx)" class="flex-1 bg-indigo-50 text-indigo-600 text-[11px] font-bold py-1.5 rounded-lg hover:bg-indigo-100 transition">Retry</button>
                                    </div>
                                    <div class="mt-2" x-show="r.status === 'error'">
                                        <button type="button" @click="retry(idx)" class="w-full bg-indigo-50 text-indigo-600 text-[11px] font-bold py-1.5 rounded-lg hover:bg-indigo-100 transition">Retry</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <p x-show="error" x-text="error" class="text-xs font-bold text-red-500 mt-2"></p>

                    <div class="flex gap-2 mt-4" x-show="results.some(r => r.status === 'done' && !r.discarded)">
                        <button type="button" @click="saveAll()" :disabled="isSavingAll" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black py-3 rounded-xl shadow-lg hover:shadow-xl transition active:scale-95 disabled:opacity-50">
                            <span x-show="!isSavingAll" x-text="'Save ' + results.filter(r => r.status === 'done' && !r.discarded).length + ' to Library'"></span>
                            <span x-show="isSavingAll">Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    {{-- Manual template upload (admin fallback) --}}
    @if(auth()->user()->role === 'admin')
        <x-modal name="add-template-manual" :show="false" maxWidth="md">
            <form method="POST" action="{{ route('mockup_templates.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                <h3 class="text-xl font-black text-gray-900 mb-6">Upload Template Manually</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Template Name</label>
                        <input type="text" name="name" required placeholder="e.g. White T-Shirt Front" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Product Type</label>
                        <select name="product_type" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                            <option value="polo_tshirt">Polo T-Shirt</option>
                            <option value="tshirt">T-Shirt</option>
                            <option value="drawstring_pouch">Drawstring Pouch</option>
                            <option value="carry_bag">Carry Bag</option>
                            <option value="polymailer_bag">Polymailer Bag</option>
                            <option value="cap">Cap</option>
                            <option value="mug">Mug</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Template Image (PNG/JPG)</label>
                        <input type="file" name="image" required accept="image/*" class="w-full text-sm font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700">
                    </div>
                </div>
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-6 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition">Cancel</button>
                    <button type="submit" class="bg-gray-900 text-white font-black px-8 py-2.5 rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95">Upload</button>
                </div>
            </form>
        </x-modal>
    @endif

    @php
        // Blade's @json directive can't parse complex inline closures —
        // precompute the logo picker payload here.
        $logoPickerData = $customerLogos->map(fn ($l) => [
            'id' => $l->id,
            'name' => $l->name,
            'customer' => trim(($l->customer_name ?? '') . ' ' . ($l->customer_phone ?? '')),
            'url' => '/storage/' . $l->file_path,
        ])->values();

        $backgroundPickerData = $backgrounds->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'url' => '/storage/' . $b->image_path,
        ])->values();
    @endphp
    <script>
        document.addEventListener('alpine:init', () => {

            // ── Template Generator ─────────────────────────────
            Alpine.data('templateGenerator', () => ({
                form: {
                    name: '',
                    product_type: 'polo_tshirt',
                    custom_product: '',
                    size: 'square',
                    theme: 'studio',
                    presentation: 'product_only',
                    angle: 'front',
                    lighting: 'soft',
                    views: 'single',
                    color_scheme: '',
                    placements: '',
                    logo_coverage: 'large',
                    style_notes: '',
                },
                backgrounds: @json($backgroundPickerData),
                selectedBgIds: [],
                bgMode: 'library',
                isGeneratingBg: false,
                referenceFile: null,
                referencePreview: null,
                referencePath: null, // server-side path after first generation
                results: [],
                isGenerating: false,
                isSavingTpl: false,
                progressText: '',
                error: '',

                placementsByType: {
                    polo_tshirt: [
                        'left chest pocket logo + large back print, front & back views side by side',
                        'left chest pocket logo only',
                        'large back print only',
                    ],
                    tshirt: [
                        'left chest pocket logo + large back print, front & back views side by side',
                        'large front center print',
                        'left chest pocket logo only',
                    ],
                    drawstring_pouch: ['front center of the pouch'],
                    carry_bag: ['large front center of the bag'],
                    polymailer_bag: ['front center of the mailer bag'],
                    cap: ['front center panel of the cap'],
                    mug: ['front center of the mug'],
                    other: ['front center'],
                },

                get placementPresets() {
                    return this.placementsByType[this.form.product_type] || this.placementsByType.other;
                },

                applyPlacementPreset() {
                    this.form.placements = this.placementPresets[0] || '';
                },

                init() {
                    this.applyPlacementPreset();
                    if (!this.backgrounds.length) this.bgMode = 'new';
                },

                toggleBackground(id) {
                    const i = this.selectedBgIds.indexOf(id);
                    if (i > -1) this.selectedBgIds.splice(i, 1);
                    else this.selectedBgIds.push(id);
                },

                bgName(id) {
                    const bg = this.backgrounds.find(b => b.id === id);
                    return bg ? bg.name : 'AI scene';
                },

                generateBackground() {
                    this.error = '';
                    this.isGeneratingBg = true;

                    fetch('{{ route('mockup_backgrounds.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            theme: this.form.theme,
                            lighting: this.form.lighting,
                            color_scheme: this.form.color_scheme,
                            size: this.form.size,
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.backgrounds.unshift(data.background);
                            this.selectedBgIds.push(data.background.id);
                            this.bgMode = 'library';
                        } else {
                            this.error = data.message || 'Background generation failed.';
                        }
                    })
                    .catch(() => this.error = 'Network error — please try again.')
                    .finally(() => this.isGeneratingBg = false);
                },

                deleteBackground(bg) {
                    if (!confirm('Delete this background?')) return;
                    fetch('{{ url('/mockup-backgrounds') }}/' + bg.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.backgrounds = this.backgrounds.filter(b => b.id !== bg.id);
                            this.selectedBgIds = this.selectedBgIds.filter(id => id !== bg.id);
                        }
                    })
                    .catch(() => {});
                },

                handleReference(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.referenceFile = file;
                    this.referencePath = null; // new file supersedes previous server copy
                    const reader = new FileReader();
                    reader.onload = (e) => this.referencePreview = e.target.result;
                    reader.readAsDataURL(file);
                    event.target.value = '';
                },

                async generate() {
                    this.error = '';
                    this.isGenerating = true;

                    // One template per selected background; none selected =
                    // single template with an AI-invented scene.
                    const jobs = this.selectedBgIds.length ? this.selectedBgIds : [null];
                    this.results = jobs.map(id => ({
                        bgId: id,
                        bgName: id ? this.bgName(id) : 'AI-invented scene',
                        status: 'generating',
                        url: null, path: null, message: '', discarded: false,
                    }));

                    for (let i = 0; i < this.results.length; i++) {
                        this.progressText = (i + 1) + '/' + this.results.length;
                        await this.generateOne(this.results[i]);
                    }

                    this.isGenerating = false;
                    this.progressText = '';
                },

                async generateOne(r) {
                    r.status = 'generating';
                    const fd = new FormData();
                    Object.entries(this.form).forEach(([k, v]) => fd.append(k, v ?? ''));
                    if (r.bgId) fd.append('background_id', r.bgId);
                    if (this.referenceFile && !this.referencePath) {
                        fd.append('reference_image', this.referenceFile);
                    } else if (this.referencePath) {
                        fd.append('reference_path', this.referencePath);
                    }

                    try {
                        const resp = await fetch('{{ route('mockup_templates.generate') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: fd,
                        });
                        const data = await resp.json();
                        if (data.success) {
                            r.status = 'done';
                            r.url = data.url + '?t=' + Date.now();
                            r.path = data.path;
                            // reuse the uploaded reference for the rest of the batch
                            if (data.reference_path) this.referencePath = data.reference_path;
                        } else {
                            r.status = 'error';
                            r.message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Generation failed.';
                        }
                    } catch (e) {
                        r.status = 'error';
                        r.message = 'Network error — please retry.';
                    }
                },

                async retry(idx) {
                    if (this.isGenerating) return;
                    this.isGenerating = true;
                    await this.generateOne(this.results[idx]);
                    this.isGenerating = false;
                },

                async saveAll() {
                    const keepers = this.results.filter(r => r.status === 'done' && !r.discarded);
                    if (!keepers.length) return;
                    if (!this.form.name.trim()) { this.error = 'Please give the template a name.'; return; }
                    this.error = '';
                    this.isSavingTpl = true;

                    const baseName = this.form.name.trim();
                    let failed = 0;

                    for (const r of keepers) {
                        const name = keepers.length > 1 ? baseName + ' — ' + r.bgName : baseName;
                        try {
                            const resp = await fetch('{{ route('mockup_templates.saveGenerated') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    ...this.form,
                                    name: name,
                                    path: r.path,
                                    background_id: r.bgId,
                                    reference_path: this.referencePath,
                                }),
                            });
                            const data = await resp.json();
                            if (!data.success) failed++;
                        } catch (e) {
                            failed++;
                        }
                    }

                    if (failed) {
                        this.error = failed + ' template(s) failed to save — the rest were saved.';
                        this.isSavingTpl = false;
                    } else {
                        window.location = '{{ route('mockups.index') }}?tab=templates';
                    }
                },
            }));

            // ── Mockup Generator ───────────────────────────────
            Alpine.data('mockupGenerator', () => ({
                title: '',
                logoFile: null,
                logoPreview: null,
                logoPath: null, // server-side path after first upload
                logoMode: 'upload',
                logoFilter: '',
                selectedLogoId: null,
                customerLogoId: null, // library record id (returned by generate)
                libraryLogos: @json($logoPickerData),
                selectedTemplates: [],
                orderId: '',
                logoSize: 'medium',
                instructions: '',
                results: [],
                isGenerating: false,
                isSavingAll: false,
                progressText: '',
                error: '',
                templateNames: @json($templates->pluck('name', 'id')),
                wizard: @json($wizard),

                init() {
                    if (this.wizard) {
                        this.orderId = this.wizard.orderId;
                        this.title = this.wizard.title;
                        if (this.wizard.logoId && this.libraryLogos.some(l => l.id === this.wizard.logoId)) {
                            this.selectedLogoId = this.wizard.logoId;
                            this.logoMode = 'library';
                        }
                        this.$nextTick(() => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'mockup-generator' })));
                    }
                },

                get filteredLogos() {
                    const q = this.logoFilter.toLowerCase();
                    if (!q) return this.libraryLogos;
                    return this.libraryLogos.filter(l => (l.name + ' ' + l.customer).toLowerCase().includes(q));
                },

                pickLogo(logo) {
                    this.selectedLogoId = this.selectedLogoId === logo.id ? null : logo.id;
                    // library pick supersedes any uploaded file
                    this.logoFile = null;
                    this.logoPreview = null;
                    this.logoPath = null;
                },

                toggleTemplate(id) {
                    const i = this.selectedTemplates.indexOf(id);
                    if (i > -1) this.selectedTemplates.splice(i, 1);
                    else this.selectedTemplates.push(id);
                },

                handleLogo(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.logoFile = file;
                    this.logoPath = null; // new file supersedes previous server copy
                    this.selectedLogoId = null; // upload supersedes library pick
                    const reader = new FileReader();
                    reader.onload = (e) => this.logoPreview = e.target.result;
                    reader.readAsDataURL(file);
                    event.target.value = '';
                },

                async generate() {
                    this.error = '';
                    this.isGenerating = true;
                    this.results = this.selectedTemplates.map(id => ({
                        templateId: id,
                        templateName: this.templateNames[id] || ('Template #' + id),
                        status: 'generating',
                        url: null, path: null, message: '', discarded: false,
                    }));

                    for (let i = 0; i < this.results.length; i++) {
                        this.progressText = (i + 1) + '/' + this.results.length;
                        await this.generateOne(this.results[i]);
                    }

                    this.isGenerating = false;
                    this.progressText = '';
                },

                async generateOne(r) {
                    r.status = 'generating';
                    const fd = new FormData();
                    fd.append('template_id', r.templateId);
                    fd.append('logo_size', this.logoSize);
                    fd.append('instructions', this.instructions ?? '');
                    if (this.selectedLogoId) {
                        fd.append('customer_logo_id', this.selectedLogoId);
                    } else if (this.logoPath) {
                        fd.append('logo_path', this.logoPath);
                    } else if (this.logoFile) {
                        fd.append('logo', this.logoFile);
                    }

                    try {
                        const resp = await fetch('{{ route('mockups.generate') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: fd,
                        });
                        const data = await resp.json();
                        if (data.success) {
                            r.status = 'done';
                            r.url = data.url + '?t=' + Date.now();
                            r.path = data.path;
                            this.logoPath = data.logo_path; // reuse for subsequent templates / retries
                            this.customerLogoId = data.customer_logo_id;
                        } else {
                            r.status = 'error';
                            r.message = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Generation failed.';
                        }
                    } catch (e) {
                        r.status = 'error';
                        r.message = 'Network error — please retry.';
                    }
                },

                async retry(idx) {
                    if (this.isGenerating) return;
                    this.isGenerating = true;
                    await this.generateOne(this.results[idx]);
                    this.isGenerating = false;
                },

                async saveAll() {
                    const keepers = this.results.filter(r => r.status === 'done' && !r.discarded);
                    if (!keepers.length) return;
                    this.error = '';
                    this.isSavingAll = true;

                    const baseTitle = this.title.trim() || 'Untitled Mockup';

                    for (const r of keepers) {
                        const title = keepers.length > 1 ? baseTitle + ' — ' + r.templateName : baseTitle;
                        try {
                            const resp = await fetch('{{ route('mockups.saveGenerated') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    title: title,
                                    path: r.path,
                                    logo_path: this.logoPath,
                                    customer_logo_id: this.customerLogoId,
                                    template_id: r.templateId,
                                    order_id: this.orderId || null,
                                }),
                            });
                            const data = await resp.json();
                            if (!data.success) {
                                this.error = data.message || 'Failed to save "' + title + '".';
                                this.isSavingAll = false;
                                return;
                            }
                        } catch (e) {
                            this.error = 'Network error while saving.';
                            this.isSavingAll = false;
                            return;
                        }
                    }

                    // Wizard flow returns to the order; normal flow reloads the library
                    window.location = (this.wizard && this.wizard.returnTo) ? this.wizard.returnTo : '{{ route('mockups.index') }}';
                },
            }));
        });
    </script>

    @include('mockups.partials.share_script')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-6 right-6 z-50 bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-6 right-6 z-50 bg-red-500 text-white font-bold px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            {{ session('error') }}
        </div>
    @endif
</x-app-layout>
