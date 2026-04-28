<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                {{ __('Products Manager') }}
            </h2>
            <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-product-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden sm:inline">Add Product</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen" x-data="productManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-lg">
                    <ul class="list-disc pl-5 font-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-4">
                @foreach($products as $product)
                    <div class="bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] overflow-hidden flex flex-col border border-gray-100 hover:shadow-lg transition-shadow group">
                        <div class="aspect-[4/5] bg-gray-50 relative overflow-hidden">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-2.5 py-1 rounded-lg shadow-sm text-xs font-black text-gray-900 border border-gray-100 flex gap-2 items-center">
                                @if($product->video_path)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-wildOrchid" viewBox="0 0 20 20" fill="currentColor"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" /></svg>
                                @endif
                                @if($product->additional_images && count($product->additional_images) > 0)
                                    <span class="text-gray-500 font-bold">+{{ count($product->additional_images) }}</span>
                                @endif
                                <span>{{ $product->stock }} in stock</span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col relative">
                            <span class="absolute top-0 right-5 -mt-3 bg-wildOrchid text-white text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md shadow-sm">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                            <h4 class="font-black text-lg mb-1 leading-tight text-gray-900 mt-1">{{ $product->name }}</h4>
                            <p class="text-wildOrchid font-bold text-lg mb-3">Rs.{{ number_format($product->price) }}</p>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2 flex-1">{{ $product->description }}</p>
                            
                            <div class="mt-auto pt-4 border-t border-gray-100 flex justify-between">
                                <button type="button" @click="openEditModal({{ $product }})" class="text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 text-sm font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 text-sm font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Add Product Modal -->
        <x-modal name="add-product-modal" focusable>
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-8 max-h-[90vh] overflow-y-auto">
                @csrf
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-gray-900">Add New Product</h2>
                    <p class="text-sm text-gray-500 mt-1">Enter details for the new inventory item.</p>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Product Name</label>
                    <input name="name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" required />
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea name="description" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" rows="3" required></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Category</label>
                    <select name="category_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required>
                        <option value="" disabled selected>-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Selling Price (Rs.)</label>
                        <input name="price" type="number" step="0.01" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Cost Price (Rs.)</label>
                        <input name="cost_price" type="number" step="0.01" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" placeholder="0" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Weight (g)</label>
                        <input name="weight_grams" type="number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Stock</label>
                        <input name="stock" type="number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
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
                </div>

                <!-- Media Uploads -->
                <div class="space-y-4 mb-8">
                    <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">Media Assets</h3>
                    
                    <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Primary Thumbnail Image (Required)</label>
                        <p class="text-xs text-gray-500 mb-3">This is the main image shown on the grid.</p>
                        <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" required accept="image/*">
                    </div>

                    <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Additional Gallery Images (Optional)</label>
                        <p class="text-xs text-gray-500 mb-3">Select multiple files at once.</p>
                        <input type="file" name="additional_images[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="image/*">
                    </div>

                    <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Product Video (Optional)</label>
                        <p class="text-xs text-gray-500 mb-3">Max 10MB (mp4, webm).</p>
                        <input type="file" name="video" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="video/mp4,video/webm,video/quicktime">
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                    <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Save Product</button>
                </div>
            </form>
        </x-modal>

        <!-- Edit Product Modal -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    
                    <form :action="`{{ url('products') }}/${editingProduct?.id}`" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="px-8 py-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="text-xl font-black text-gray-900">Edit Product</h3>
                            <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>

                        <div class="p-8 max-h-[70vh] overflow-y-auto">
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Product Name</label>
                                <input name="name" x-model="formData.name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" required />
                            </div>

                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                                <textarea name="description" x-model="formData.description" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" rows="3" required></textarea>
                            </div>

                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Category</label>
                                <select name="category_id" x-model="formData.category_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Selling Price (Rs.)</label>
                                    <input name="price" x-model="formData.price" type="number" step="0.01" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Cost Price (Rs.)</label>
                                    <input name="cost_price" x-model="formData.cost_price" type="number" step="0.01" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Weight (g)</label>
                                    <input name="weight_grams" x-model="formData.weight_grams" type="number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Stock</label>
                                    <input name="stock" x-model="formData.stock" type="number" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
                                </div>
                            </div>

                            <div class="mb-5 border border-gray-200 rounded-2xl p-5 bg-white">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-bold text-gray-900">Product Bundles</h3>
                                    <button type="button" @click="formData.bundles.push({qty: '', price: ''})" class="text-xs bg-mango text-gray-900 font-bold px-3 py-1.5 rounded-lg hover:bg-[#ffdf8c] shadow-sm">+ Add Bundle</button>
                                </div>
                                
                                <template x-for="(bundle, index) in formData.bundles" :key="index">
                                    <div class="flex gap-4 mb-3 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Bundle Quantity</label>
                                            <input type="number" :name="`bundles[${index}][qty]`" x-model="bundle.qty" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-2 text-sm" placeholder="e.g. 3" required>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Total Bundle Price (Rs.)</label>
                                            <input type="number" step="0.01" :name="`bundles[${index}][price]`" x-model="bundle.price" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-2 text-sm" placeholder="e.g. 2500" required>
                                        </div>
                                        <button type="button" @click="formData.bundles.splice(index, 1)" class="bg-red-50 text-red-500 hover:bg-red-100 p-2.5 rounded-xl mb-[1px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </template>
                                <p x-show="formData.bundles.length === 0" class="text-xs text-gray-400 font-medium">No bundles configured.</p>
                            </div>

                            <!-- Media Uploads Edit -->
                            <div class="space-y-4">
                                <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">Update Media</h3>
                                <p class="text-xs text-gray-500 mb-3">Uploading new files will replace the existing ones. Leave blank to keep existing files.</p>
                                
                                <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Replace Thumbnail Image</label>
                                    <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="image/*">
                                </div>

                                <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Replace Additional Gallery Images</label>
                                    <input type="file" name="additional_images[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="image/*">
                                </div>

                                <div class="p-4 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Replace Product Video</label>
                                    <input type="file" name="video" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors file:cursor-pointer" accept="video/mp4,video/webm,video/quicktime">
                                </div>
                            </div>
                        </div>

                        <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="closeEditModal()" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                            <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- AlpineJS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('productManager', () => ({
                editModalOpen: false,
                editingProduct: null,
                
                formData: {
                    name: '',
                    description: '',
                    category_id: '',
                    price: '',
                    cost_price: '',
                    weight_grams: '',
                    stock: '',
                    bundles: []
                },

                openEditModal(product) {
                    this.editingProduct = product;
                    this.formData.name = product.name;
                    this.formData.description = product.description;
                    this.formData.category_id = product.category_id;
                    this.formData.price = product.price;
                    this.formData.cost_price = product.cost_price;
                    this.formData.weight_grams = product.weight_grams;
                    this.formData.stock = product.stock;
                    this.formData.bundles = product.bundles || [];
                    
                    this.editModalOpen = true;
                },

                closeEditModal() {
                    this.editModalOpen = false;
                    setTimeout(() => { this.editingProduct = null; }, 300);
                }
            }));
        });
    </script>
</x-app-layout>
