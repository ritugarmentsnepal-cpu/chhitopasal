<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                {{ __('Categories Manager') }}
            </h2>
            <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-category-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden sm:inline">Add Category</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen" x-data="categoryManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $category)
                    <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="font-black text-xl text-gray-900 mb-1">{{ $category->name }}</h3>
                                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-md">{{ $category->products_count }} Products</span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openEditModal({{ $category->id }})" class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center hover:bg-indigo-100 transition-colors active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Are you sure you want to delete this category? Products in this category will be marked as uncategorized.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-100 transition-colors active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Variant Badges -->
                        <div class="flex flex-wrap gap-2">
                            @if($category->has_color_variants)
                                <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-purple-100">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>
                                    Colors: {{ $category->color_options ? implode(', ', $category->color_options) : 'None' }}
                                </span>
                            @endif
                            @if($category->has_size_variants)
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-blue-100">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                    Sizes: {{ $category->size_options ? implode(', ', $category->size_options) : 'None' }}
                                </span>
                            @endif
                            @if(!$category->has_color_variants && !$category->has_size_variants)
                                <span class="text-xs text-gray-400 font-medium">No variants</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    <!-- Add Category Modal -->
    <x-modal name="add-category-modal" focusable>
        <form method="POST" action="{{ route('categories.store') }}" class="p-8">
            @csrf
            <div class="mb-8">
                <h2 class="text-2xl font-black text-gray-900">Add New Category</h2>
                <p class="text-sm text-gray-500 mt-1">Create a new product classification.</p>
            </div>
            
            <div class="mb-6">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Category Name</label>
                <input id="name" name="name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" placeholder="e.g. Clothing" required />
            </div>

            <!-- Variant Toggles -->
            <div class="mb-6 bg-gray-50 rounded-2xl p-5 border border-gray-100 space-y-5" x-data="{ showColors: false, showSizes: false }">
                <h4 class="font-black text-gray-900 text-sm uppercase tracking-wider">Variant Options</h4>
                
                <!-- Color Toggle -->
                <div>
                    <label class="flex items-center justify-between cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>
                            </div>
                            <span class="font-bold text-gray-700 group-hover:text-gray-900 transition">Color Variants</span>
                        </div>
                        <div class="relative">
                            <input type="hidden" name="has_color_variants" value="0">
                            <input type="checkbox" name="has_color_variants" value="1" x-model="showColors" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                        </div>
                    </label>
                    <div x-show="showColors" x-transition class="mt-3 ml-11">
                        <input type="text" name="color_options" placeholder="e.g. Red, Blue, Black, White" class="w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-purple-400 focus:ring focus:ring-purple-100 py-2.5 text-sm font-medium">
                        <p class="text-xs text-gray-400 mt-1">Comma-separated list of colors</p>
                    </div>
                </div>

                <!-- Size Toggle -->
                <div>
                    <label class="flex items-center justify-between cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            </div>
                            <span class="font-bold text-gray-700 group-hover:text-gray-900 transition">Size Variants</span>
                        </div>
                        <div class="relative">
                            <input type="hidden" name="has_size_variants" value="0">
                            <input type="checkbox" name="has_size_variants" value="1" x-model="showSizes" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                        </div>
                    </label>
                    <div x-show="showSizes" x-transition class="mt-3 ml-11">
                        <input type="text" name="size_options" placeholder="e.g. S, M, L, XL, XXL" class="w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 py-2.5 text-sm font-medium">
                        <p class="text-xs text-gray-400 mt-1">Comma-separated list of sizes</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Save Category</button>
            </div>
        </form>
    </x-modal>

    <!-- Edit Category Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
                
                <form :action="`{{ url('categories') }}/${editingCategory?.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-xl font-black text-gray-900">Edit Category</h3>
                        <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>

                    <div class="p-8">
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Category Name</label>
                            <input name="name" x-model="formData.name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
                        </div>

                        <!-- Variant Toggles (Edit) -->
                        <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 space-y-5">
                            <h4 class="font-black text-gray-900 text-sm uppercase tracking-wider">Variant Options</h4>
                            
                            <!-- Color Toggle -->
                            <div>
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>
                                        </div>
                                        <span class="font-bold text-gray-700 group-hover:text-gray-900 transition">Color Variants</span>
                                    </div>
                                    <div class="relative">
                                        <input type="hidden" name="has_color_variants" value="0">
                                        <input type="checkbox" name="has_color_variants" value="1" x-model="formData.has_color_variants" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                    </div>
                                </label>
                                <div x-show="formData.has_color_variants" x-transition class="mt-3 ml-11">
                                    <input type="text" name="color_options" x-model="formData.color_options_text" placeholder="e.g. Red, Blue, Black, White" class="w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-purple-400 focus:ring focus:ring-purple-100 py-2.5 text-sm font-medium">
                                    <p class="text-xs text-gray-400 mt-1">Comma-separated list of colors</p>
                                </div>
                            </div>

                            <!-- Size Toggle -->
                            <div>
                                <label class="flex items-center justify-between cursor-pointer group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                        </div>
                                        <span class="font-bold text-gray-700 group-hover:text-gray-900 transition">Size Variants</span>
                                    </div>
                                    <div class="relative">
                                        <input type="hidden" name="has_size_variants" value="0">
                                        <input type="checkbox" name="has_size_variants" value="1" x-model="formData.has_size_variants" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </div>
                                </label>
                                <div x-show="formData.has_size_variants" x-transition class="mt-3 ml-11">
                                    <input type="text" name="size_options" x-model="formData.size_options_text" placeholder="e.g. S, M, L, XL, XXL" class="w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 py-2.5 text-sm font-medium">
                                    <p class="text-xs text-gray-400 mt-1">Comma-separated list of sizes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="closeEditModal()" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    </div> <!-- Close categoryManager scope -->

    <!-- AlpineJS Logic -->
    <script>
        const __categories = @json($categories, JSON_HEX_TAG | JSON_HEX_APOS);
        document.addEventListener('alpine:init', () => {
            Alpine.data('categoryManager', () => ({
                editModalOpen: false,
                editingCategory: null,
                
                formData: {
                    name: '',
                    has_color_variants: false,
                    has_size_variants: false,
                    color_options_text: '',
                    size_options_text: '',
                },

                openEditModal(categoryId) {
                    const category = __categories.find(c => c.id === categoryId);
                    if (!category) return;
                    this.editingCategory = category;
                    this.formData.name = category.name;
                    this.formData.has_color_variants = !!category.has_color_variants;
                    this.formData.has_size_variants = !!category.has_size_variants;
                    this.formData.color_options_text = (category.color_options || []).join(', ');
                    this.formData.size_options_text = (category.size_options || []).join(', ');
                    this.editModalOpen = true;
                },

                closeEditModal() {
                    this.editModalOpen = false;
                    setTimeout(() => { this.editingCategory = null; }, 300);
                }
            }));
        });
    </script>
</x-app-layout>
