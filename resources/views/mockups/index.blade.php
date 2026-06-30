<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Mockup Studio</h2>
                <p class="text-sm font-medium text-gray-500 mt-0.5">Generate, browse & manage product mockups</p>
            </div>
            <button x-data x-on:click="$dispatch('open-modal', 'mockup-generator')" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black px-6 py-2.5 rounded-xl shadow-lg hover:shadow-xl hover:from-indigo-500 hover:to-purple-500 transition-all active:scale-95 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Mockup
            </button>
        </div>
    </x-slot>

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- Stats Bar --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Total Mockups</div>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ $mockups->total() + $orderMockups->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Standalone</div>
                <div class="text-2xl font-black text-indigo-600 mt-1">{{ $mockups->total() }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">From Orders</div>
                <div class="text-2xl font-black text-purple-600 mt-1">{{ $orderMockups->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Templates</div>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ $templates->count() }}</div>
            </div>
        </div>

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
                    <option value="order" {{ request('source') === 'order' ? 'selected' : '' }}>From Orders</option>
                </select>
                <select name="product_type" class="rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5 min-w-[140px]">
                    <option value="">All Types</option>
                    <option value="tshirt" {{ request('product_type') === 'tshirt' ? 'selected' : '' }}>T-Shirt</option>
                    <option value="hoodie" {{ request('product_type') === 'hoodie' ? 'selected' : '' }}>Hoodie</option>
                    <option value="pouch" {{ request('product_type') === 'pouch' ? 'selected' : '' }}>Pouch</option>
                    <option value="other" {{ request('product_type') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                <button type="submit" class="bg-gray-900 text-white font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-gray-800 transition active:scale-95">Filter</button>
                @if(request()->anyFilled(['search', 'source', 'product_type']))
                    <a href="{{ route('mockups.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition">Clear</a>
                @endif
            </form>
        </div>

        {{-- Mockup Gallery Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
            {{-- Library Mockups (from database) --}}
            @foreach($mockups as $mockup)
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-indigo-200 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="aspect-square bg-gray-50 overflow-hidden relative flex items-center justify-center p-3">
                        <img src="{{ Storage::url($mockup->image_path) }}" alt="{{ $mockup->title }}" class="max-w-full max-h-full object-contain rounded-lg">
                        
                        {{-- Overlay Actions --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-3">
                            <div class="flex gap-2">
                                <a href="{{ Storage::url($mockup->image_path) }}" target="_blank" class="bg-white/90 backdrop-blur-sm text-gray-900 p-2 rounded-lg hover:bg-white transition shadow-sm" title="View Full Size">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('mockups.download', $mockup) }}" class="bg-white/90 backdrop-blur-sm text-gray-900 p-2 rounded-lg hover:bg-white transition shadow-sm" title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </a>
                                <form action="{{ route('mockups.destroy', $mockup) }}" method="POST" class="inline" onsubmit="return confirm('Delete this mockup?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-500/90 backdrop-blur-sm text-white p-2 rounded-lg hover:bg-red-600 transition shadow-sm" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Source Badge --}}
                        @if($mockup->order_id)
                            <div class="absolute top-2 left-2">
                                <a href="{{ route('orders.index', ['search' => $mockup->order_id]) }}" class="bg-purple-500/90 backdrop-blur-sm text-white text-[10px] font-black px-2 py-0.5 rounded-full hover:bg-purple-600 transition">
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
                                <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded-full">{{ ucfirst($mockup->template->product_type) }}</span>
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

            {{-- Order-inline Mockups (not yet in library) --}}
            @foreach($orderMockups as $om)
                <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-purple-200 transition-all duration-300 hover:-translate-y-0.5">
                    <div class="aspect-square bg-gray-50 overflow-hidden relative flex items-center justify-center p-3">
                        <img src="{{ Storage::url($om->image_path) }}" alt="{{ $om->title }}" class="max-w-full max-h-full object-contain rounded-lg">
                        
                        {{-- Overlay Actions --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center p-3">
                            <div class="flex gap-2">
                                <a href="{{ Storage::url($om->image_path) }}" target="_blank" class="bg-white/90 backdrop-blur-sm text-gray-900 p-2 rounded-lg hover:bg-white transition shadow-sm" title="View Full Size">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Source Badge --}}
                        <div class="absolute top-2 left-2">
                            <a href="{{ route('orders.index', ['search' => $om->order_id]) }}" class="bg-purple-500/90 backdrop-blur-sm text-white text-[10px] font-black px-2 py-0.5 rounded-full hover:bg-purple-600 transition">
                                Order #{{ $om->order_id }}
                            </a>
                        </div>
                    </div>
                    <div class="p-3 border-t border-gray-50">
                        <h4 class="font-bold text-sm text-gray-900 truncate">{{ $om->title }}</h4>
                        <p class="text-[10px] font-bold text-gray-400 mt-1">{{ $om->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Empty State --}}
        @if($mockups->isEmpty() && $orderMockups->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-gray-900">No mockups yet</h3>
                <p class="text-sm font-medium text-gray-500 mt-1 max-w-md mx-auto">Create your first product mockup by clicking the "Create Mockup" button above. You can overlay designs onto blank templates.</p>
                <button x-data x-on:click="$dispatch('open-modal', 'mockup-generator')" class="mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold px-6 py-2.5 rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-95">
                    Create Your First Mockup
                </button>
            </div>
        @endif

        {{-- Pagination --}}
        @if($mockups->hasPages())
            <div class="flex justify-center">
                {{ $mockups->withQueryString()->links() }}
            </div>
        @endif

        {{-- Templates Section (Collapsible) --}}
        <div x-data="{ showTemplates: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <button @click="showTemplates = !showTemplates" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div class="text-left">
                        <h3 class="font-black text-gray-900">Base Templates</h3>
                        <p class="text-xs font-medium text-gray-500">{{ $templates->count() }} template{{ $templates->count() !== 1 ? 's' : '' }} available — blank products used as canvas backgrounds</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="showTemplates ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="showTemplates" x-collapse>
                <div class="border-t border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm font-bold text-gray-500">These templates are used as the base layer in the mockup generator.</p>
                        <button x-on:click="$dispatch('open-modal', 'add-template-from-library')" class="bg-mango text-gray-900 px-4 py-2 rounded-xl font-bold text-sm hover:shadow-md transition active:scale-95">
                            + Upload Template
                        </button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @forelse($templates as $template)
                            <div class="group border border-gray-200 rounded-xl p-3 bg-gray-50 flex flex-col relative">
                                <div class="aspect-square bg-white border border-gray-100 rounded-lg overflow-hidden mb-2 flex items-center justify-center">
                                    <img src="{{ Storage::url($template->image_path) }}" alt="{{ $template->name }}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="font-bold text-xs text-gray-900 truncate">{{ $template->name }}</div>
                                <div class="text-[10px] font-bold text-gray-500 uppercase">{{ str_replace('_', ' ', $template->product_type) }}</div>
                                <form action="{{ route('mockup_templates.destroy', $template) }}" method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this template?')" class="bg-red-500 text-white p-1 rounded-lg hover:bg-red-600 shadow-sm">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 text-gray-400">
                                <p class="font-bold text-sm">No templates yet. Upload blank product images to get started.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MOCKUP GENERATOR MODAL (Fabric.js Canvas Studio)      --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <x-modal name="mockup-generator" :show="false" maxWidth="6xl">
        <div class="p-6 bg-gray-50 flex flex-col h-[90vh]" x-data="mockupLibraryStudio()">
            
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6 shrink-0">
                <div>
                    <h3 class="text-2xl font-black text-gray-900">Create Mockup</h3>
                    <p class="text-sm font-medium text-gray-500">Select a template, add your designs, and save to library.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 rounded-xl font-bold text-gray-500 bg-white border border-gray-200 hover:bg-gray-50 transition shadow-sm">Cancel</button>
                    <button type="button" @click="saveMockup()" :disabled="isSaving || !selectedTemplateId" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black px-6 py-2.5 rounded-xl shadow-lg hover:shadow-xl transition active:scale-95 disabled:opacity-50 flex items-center gap-2">
                        <span x-show="!isSaving">Save to Library</span>
                        <span x-show="isSaving" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>

            {{-- Main Workspace --}}
            <div class="flex gap-6 flex-1 min-h-0">
                {{-- Sidebar: Tools & Assets --}}
                <div class="w-80 flex flex-col gap-4 shrink-0 overflow-y-auto pr-2 custom-scrollbar">
                    
                    {{-- Mockup Title --}}
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Mockup Title</label>
                        <input type="text" x-model="mockupTitle" placeholder="e.g. White Tee - Butterfly Design" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Step 1: Select Template --}}
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                        <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs flex items-center justify-center font-black">1</span>
                            Base Template
                        </h4>
                        <select x-model="selectedTemplateId" @change="loadTemplate()" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium">
                            <option value="">-- Select Template --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-url="{{ Storage::url($template->image_path) }}" data-type="{{ $template->product_type }}">
                                    {{ $template->name }} ({{ ucfirst($template->product_type) }})
                                </option>
                            @endforeach
                        </select>
                        @if($templates->isEmpty())
                            <p class="text-xs text-amber-600 font-bold mt-2">⚠️ No templates. Upload one from the Templates section below.</p>
                        @endif
                    </div>

                    {{-- Step 2: Upload Designs --}}
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                        <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs flex items-center justify-center font-black">2</span>
                            Design Images
                        </h4>
                        <p class="text-xs text-gray-500 mb-3">Upload design images to overlay onto the template.</p>
                        
                        <label class="block w-full cursor-pointer">
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-indigo-300 hover:bg-indigo-50/30 transition">
                                <svg class="w-6 h-6 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <span class="text-xs font-bold text-gray-500">Click to upload designs</span>
                            </div>
                            <input type="file" accept="image/*" multiple @change="handleFileUpload($event)" class="hidden">
                        </label>

                        {{-- Uploaded design thumbnails --}}
                        <div class="grid grid-cols-3 gap-2 mt-3" x-show="uploadedDesigns.length > 0">
                            <template x-for="(design, idx) in uploadedDesigns" :key="idx">
                                <div @click="addDesignToCanvas(design.url)" class="aspect-square bg-gray-100 rounded-lg border-2 border-transparent hover:border-indigo-400 cursor-pointer transition flex items-center justify-center p-1 relative group overflow-hidden">
                                    <img :src="design.url" class="max-w-full max-h-full object-contain">
                                    <button @click.stop="removeDesign(idx)" class="absolute top-0.5 right-0.5 bg-red-500 text-white p-0.5 rounded opacity-0 group-hover:opacity-100 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Canvas Controls --}}
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm" x-show="canvas">
                        <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                            Canvas Controls
                        </h4>
                        <div class="space-y-2">
                            <button type="button" @click="bringToFront()" class="w-full py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"></path></svg>
                                Bring to Front
                            </button>
                            <button type="button" @click="sendToBack()" class="w-full py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"></path></svg>
                                Send to Back
                            </button>
                            <button type="button" @click="deleteSelected()" class="w-full py-2 bg-red-50 text-red-600 font-bold text-xs rounded-lg hover:bg-red-100 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Delete Selected
                            </button>
                            <button type="button" @click="clearCanvas()" class="w-full py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Reset Canvas
                            </button>
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                        <h4 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            Tags
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="tag in availableTags" :key="tag">
                                <button type="button" @click="toggleTag(tag)" :class="selectedTags.includes(tag) ? 'bg-indigo-100 text-indigo-700 border-indigo-300' : 'bg-gray-50 text-gray-600 border-gray-200'" class="px-3 py-1 text-xs font-bold rounded-full border transition hover:scale-105 active:scale-95" x-text="tag"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Canvas Area --}}
                <div class="flex-1 bg-white rounded-2xl border border-gray-200 shadow-inner overflow-hidden flex items-center justify-center relative" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+CjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0iI2ZmZiIvPgo8cmVjdCB3aWR0aD0iMTAiIGhlaWdodD0iMTAiIGZpbGw9IiNmM2Y0ZjYiLz4KPHJlY3QgeD0iMTAiIHk9IjEwIiB3aWR0aD0iMTAiIGhlaWdodD0iMTAiIGZpbGw9IiNmM2Y0ZjYiLz4KPC9zdmc+');">
                    <div class="relative w-full h-full flex items-center justify-center" id="library-canvas-wrapper">
                        <canvas id="library-mockup-canvas"></canvas>
                    </div>
                    
                    <div x-show="!selectedTemplateId" class="absolute inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm z-10 pointer-events-none">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <p class="font-black text-gray-600 text-lg">Select a Base Template</p>
                            <p class="text-sm font-medium text-gray-400 mt-1">Choose a template from the sidebar to start designing</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>

    {{-- Upload Template Modal (accessible from library page) --}}
    <x-modal name="add-template-from-library" :show="false" maxWidth="md">
        <form method="POST" action="{{ route('mockup_templates.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <h3 class="text-xl font-black text-gray-900 mb-6">Upload Template</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Template Name</label>
                    <input type="text" name="name" required placeholder="e.g. White T-Shirt Front" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                </div>
                
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Product Type</label>
                    <select name="product_type" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                        <option value="tshirt">T-Shirt</option>
                        <option value="hoodie">Hoodie</option>
                        <option value="pouch">Drawstring Pouch</option>
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
                <button type="submit" class="bg-gray-900 text-white font-black px-8 py-2.5 rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95">
                    Upload Template
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Fabric.js & Mockup Studio Script --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mockupLibraryStudio', () => ({
                canvas: null,
                selectedTemplateId: '',
                selectedProductType: '',
                mockupTitle: '',
                isSaving: false,
                uploadedDesigns: [],
                selectedTags: [],
                availableTags: ['T-Shirt', 'Hoodie', 'Pouch', 'Front', 'Back', 'Logo', 'Full Print', 'Custom'],

                init() {
                    window.addEventListener('open-modal', (e) => {
                        if (e.detail === 'mockup-generator') {
                            setTimeout(() => this.initCanvas(), 150);
                        }
                    });
                },

                initCanvas() {
                    if (this.canvas) return;

                    const wrapper = document.getElementById('library-canvas-wrapper');
                    if (!wrapper) return;
                    
                    const width = wrapper.clientWidth - 40;
                    const height = wrapper.clientHeight - 40;

                    this.canvas = new fabric.Canvas('library-mockup-canvas', {
                        width: Math.max(width, 400),
                        height: Math.max(height, 400),
                        preserveObjectStacking: true
                    });
                },

                loadTemplate() {
                    if (!this.selectedTemplateId || !this.canvas) return;

                    const select = this.$el.querySelector('select[x-model="selectedTemplateId"]');
                    const option = select.options[select.selectedIndex];
                    const url = option.dataset.url;
                    this.selectedProductType = option.dataset.type || '';

                    // Auto-add product type tag
                    if (this.selectedProductType && !this.selectedTags.includes(this.selectedProductType.charAt(0).toUpperCase() + this.selectedProductType.slice(1))) {
                        const typeTag = this.selectedProductType.charAt(0).toUpperCase() + this.selectedProductType.slice(1);
                        if (this.availableTags.includes(typeTag) && !this.selectedTags.includes(typeTag)) {
                            this.selectedTags.push(typeTag);
                        }
                    }

                    this.canvas.clear();

                    fabric.Image.fromURL(url, (img) => {
                        const scale = Math.min(
                            this.canvas.width / img.width,
                            this.canvas.height / img.height
                        );

                        img.set({
                            originX: 'center',
                            originY: 'center',
                            left: this.canvas.width / 2,
                            top: this.canvas.height / 2,
                            scaleX: scale * 0.9,
                            scaleY: scale * 0.9,
                            selectable: false,
                            evented: false,
                            crossOrigin: 'anonymous'
                        });

                        this.canvas.setBackgroundImage(img, this.canvas.renderAll.bind(this.canvas));
                    }, { crossOrigin: 'anonymous' });
                },

                handleFileUpload(event) {
                    const files = event.target.files;
                    Array.from(files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.uploadedDesigns.push({
                                url: e.target.result,
                                name: file.name
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                    // Reset file input
                    event.target.value = '';
                },

                removeDesign(index) {
                    this.uploadedDesigns.splice(index, 1);
                },

                addDesignToCanvas(url) {
                    if (!this.canvas || !this.selectedTemplateId) return;

                    fabric.Image.fromURL(url, (img) => {
                        const scale = Math.min(
                            (this.canvas.width * 0.3) / img.width,
                            (this.canvas.height * 0.3) / img.height
                        );

                        img.set({
                            originX: 'center',
                            originY: 'center',
                            left: this.canvas.width / 2,
                            top: this.canvas.height / 2,
                            scaleX: scale,
                            scaleY: scale,
                            cornerColor: '#6366F1',
                            cornerStrokeColor: '#312E81',
                            borderColor: '#312E81',
                            cornerSize: 12,
                            transparentCorners: false,
                            crossOrigin: 'anonymous'
                        });

                        this.canvas.add(img);
                        this.canvas.setActiveObject(img);
                        this.canvas.renderAll();
                    }, { crossOrigin: 'anonymous' });
                },

                deleteSelected() {
                    if (!this.canvas) return;
                    const activeObjects = this.canvas.getActiveObjects();
                    if (activeObjects.length) {
                        this.canvas.discardActiveObject();
                        activeObjects.forEach((obj) => this.canvas.remove(obj));
                    }
                },

                bringToFront() {
                    if (!this.canvas) return;
                    const obj = this.canvas.getActiveObject();
                    if (obj) {
                        obj.bringToFront();
                        this.canvas.renderAll();
                    }
                },

                sendToBack() {
                    if (!this.canvas) return;
                    const obj = this.canvas.getActiveObject();
                    if (obj) {
                        obj.sendToBack();
                        this.canvas.renderAll();
                    }
                },

                clearCanvas() {
                    if (!this.canvas) return;
                    this.canvas.clear();
                    this.selectedTemplateId = '';
                },

                toggleTag(tag) {
                    const idx = this.selectedTags.indexOf(tag);
                    if (idx > -1) {
                        this.selectedTags.splice(idx, 1);
                    } else {
                        this.selectedTags.push(tag);
                    }
                },

                saveMockup() {
                    if (!this.canvas || !this.selectedTemplateId) return;

                    const title = this.mockupTitle.trim() || 'Untitled Mockup';

                    // Deselect active objects
                    this.canvas.discardActiveObject();
                    this.canvas.renderAll();

                    this.isSaving = true;

                    const base64Image = this.canvas.toDataURL({
                        format: 'png',
                        quality: 1,
                        multiplier: 2
                    });

                    fetch('{{ route("mockups.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            image: base64Image,
                            title: title,
                            template_id: this.selectedTemplateId,
                            tags: this.selectedTags
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to save mockup.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('An error occurred while saving.');
                    })
                    .finally(() => {
                        this.isSaving = false;
                    });
                }
            }));
        });
    </script>

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
