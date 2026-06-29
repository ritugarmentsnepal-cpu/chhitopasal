<div class="space-y-6">
    <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-gray-900">Mockup Templates Library</h3>
                <p class="text-sm font-medium text-gray-500 mt-1">Upload blank templates (t-shirts, pouches) to be used in the Mockup Studio.</p>
            </div>
            <button x-data x-on:click="$dispatch('open-modal', 'add-mockup-template')" class="bg-mango text-gray-900 px-4 py-2 rounded-xl font-bold hover:shadow-md transition">
                + Upload Template
            </button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($mockupTemplates as $template)
                <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50 flex flex-col group relative">
                    <div class="aspect-square bg-white border border-gray-100 rounded-xl overflow-hidden mb-3 flex items-center justify-center relative">
                        <img src="{{ Storage::url($template->image_path) }}" alt="{{ $template->name }}" class="max-w-full max-h-full object-contain mix-blend-multiply">
                        <form action="{{ route('mockup_templates.destroy', $template) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Delete this template?')" class="bg-red-500 text-white p-1.5 rounded-lg hover:bg-red-600 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                    <div class="font-bold text-gray-900 truncate">{{ $template->name }}</div>
                    <div class="text-xs font-bold text-gray-500 uppercase mt-1">{{ str_replace('_', ' ', $template->product_type) }}</div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-500">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="font-bold">No mockup templates uploaded yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<x-modal name="add-mockup-template" :show="false" maxWidth="md">
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
