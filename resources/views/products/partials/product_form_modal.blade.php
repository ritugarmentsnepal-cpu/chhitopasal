    <x-modal name="add-product-modal" focusable>
      <form id="add-product-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-8 max-h-[90vh] overflow-y-auto" x-data="{ submitting: false }" @submit="if(submitting) { $event.preventDefault(); return; } submitting = true;">
        @csrf
        <div class="mb-8">
          <h2 class="text-2xl font-black text-gray-900 ">Add New Product</h2>
          <p class="text-sm text-gray-500 mt-1">Enter details for the new inventory item.</p>
        </div>
        
        <div class="mb-5 relative">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider">Product Name</label>
          </div>
          <input name="name" id="add-name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required placeholder="Enter product name" />
        </div>



        <div class="mb-5">
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Category</label>
          <select name="category_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
            <option value="" disabled selected>-- Select Category --</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-5">
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Selling Price (Rs.)</label>
            <input name="price" type="number" step="0.01" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required />
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Cost Price (Rs.)</label>
            <input name="cost_price" type="number" step="0.01" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" placeholder="0" />
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Weight (g)</label>
            <input name="weight_grams" type="number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required />
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Stock</label>
            <input name="stock" type="number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required />
          </div>
        </div>

        <div x-data="{ bundles: [] }" class="mb-5 border border-gray-200 rounded-2xl p-5 bg-white">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-900">Product Bundles (Optional)</h3>
            <button type="button" @click="bundles.push({qty: '', price: ''})" class="text-xs bg-mango text-gray-900 font-bold px-3 py-1.5 rounded-lg hover:bg-[#ffdf8c] shadow-sm">+ Add Bundle</button>
          </div>
          
          <template x-for="(bundle, index) in bundles" :key="index">
            <div class="flex gap-4 mb-3 items-end">
              <div class="flex-1">
                <label class="block text-xs font-bold text-gray-500 mb-1">Bundle Quantity</label>
                <input type="number" :name="`bundles[${index}][qty]`" x-model="bundle.qty" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-2 text-sm" placeholder="e.g. 3" required>
              </div>
              <div class="flex-1">
                <label class="block text-xs font-bold text-gray-500 mb-1">Total Bundle Price (Rs.)</label>
                <input type="number" step="0.01" :name="`bundles[${index}][price]`" x-model="bundle.price" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-2 text-sm" placeholder="e.g. 2500" required>
              </div>
              <button type="button" @click="bundles.splice(index, 1)" class="bg-red-50 text-red-500 hover:bg-red-100 p-2.5 rounded-xl mb-[1px]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
              </button>
            </div>
          </template>
          <p x-show="bundles.length === 0" class="text-xs text-gray-400 font-medium">No bundles configured. Customers will only be able to buy single pieces.</p>
          
          <!-- Bundle Only Toggle -->
          <div x-show="bundles.length > 0" x-cloak class="mt-4 pt-4 border-t border-gray-100">
            <label class="flex items-center gap-3 cursor-pointer group">
              <input type="hidden" name="bundle_only" value="0">
              <input type="checkbox" name="bundle_only" value="1" class="w-5 h-5 rounded-lg border-gray-300 text-amber-500 focus:ring-amber-500/30 cursor-pointer">
              <div>
                <span class="font-bold text-gray-900 text-sm group-hover:text-amber-600 transition-colors">Bundle Only Product</span>
                <p class="text-xs text-gray-400">Each bundle will be listed as a separate product card on the storefront. Single unit purchase will be disabled.</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Color & Size Variants (Optional) -->
        <div class="mb-5 border border-gray-200 rounded-2xl p-5 bg-white">
          <h3 class="font-bold text-gray-900 mb-1">Colour & Size Variants <span class="text-xs text-gray-400 font-medium">(Optional)</span></h3>
          <p class="text-xs text-gray-400 mb-4">Enter comma-separated values. Leave blank if no variants.</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-1">Colour Options</label>
              <input name="color_options" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-2.5 text-sm font-medium transition-colors" placeholder="e.g. Red, Blue, Black" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-1">Size Options</label>
              <input name="size_options" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-2.5 text-sm font-medium transition-colors" placeholder="e.g. S, M, L, XL" />
            </div>
          </div>
        </div>


        <!-- Media Uploads -->
        <div class="space-y-4 mb-8">
          <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">Media Assets</h3>
          
          <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50 relative">
            <div class="flex justify-between items-center mb-1">
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider">Primary Thumbnail Image (Required)</label>
              <button type="button" @click="generateAIThumbnails('add')" class="text-xs bg-purple-50 text-purple-600 hover:bg-purple-100 font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-colors shadow-sm" :disabled="generatingThumbnails && generatingThumbnailMode === 'add'">
                <span x-show="generatingThumbnails && generatingThumbnailMode === 'add'" class="animate-spin inline-block w-3 h-3 border-2 border-purple-600 border-t-transparent rounded-full"></span>
                <span x-show="!(generatingThumbnails && generatingThumbnailMode === 'add')">✨</span>
                <span x-text="(generatingThumbnails && generatingThumbnailMode === 'add') ? 'Generating...' : 'Enhance with AI'"></span>
              </button>
            </div>
            <p class="text-xs text-gray-500 mb-3">This is the main image shown on the grid. Select a raw image, then click Enhance with AI.</p>
            
            <input type="hidden" name="ai_thumbnail_url" id="ai_image_url_add">
            <div id="add_thumbnail_preview_container" style="display:none;" class="mb-3 relative inline-block">
              <img id="add_thumbnail_preview" src="" class="h-24 w-24 object-cover rounded-xl shadow-sm border border-gray-200">
              <button type="button" onclick="document.getElementById('ai_image_url_add').value=''; document.getElementById('add_thumbnail_preview_container').style.display='none';" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold hover:bg-red-600">&times;</button>
            </div>

            <input type="file" name="image" id="add-image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="image/*">
          </div>

          <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Additional Gallery Images (Optional)</label>
            <p class="text-xs text-gray-500 mb-3">Select multiple files at once.</p>
            <input type="file" name="additional_images[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="image/*">
          </div>

          <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Product Video (Optional)</label>
            <p class="text-xs text-gray-500 mb-3">Max 10MB (mp4, webm).</p>
            <input type="file" name="video" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="video/mp4,video/webm,video/quicktime">
          </div>
        </div>

        <div class="mb-5 relative mt-4">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider">Description</label>
            <button type="button" @click="generateDetails('add')" class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-colors shadow-sm" :disabled="generatingAI && generatingMode === 'add'">
              <span x-show="generatingAI && generatingMode === 'add'" class="animate-spin inline-block w-3 h-3 border-2 border-indigo-600 border-t-transparent rounded-full"></span>
              <span x-show="!(generatingAI && generatingMode === 'add')">✨</span>
              <span x-text="generatingAI && generatingMode === 'add' ? 'Generating...' : 'AI Generate Description'"></span>
            </button>
          </div>
          <textarea name="description" id="add-desc" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" rows="4" required></textarea>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
          <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
          <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-800 active:scale-95'" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] transition"><span x-show="!submitting">Save Product</span><span x-show="submitting">Saving...</span></button>
        </div>
      </form>
    </x-modal>
