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

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $category)
                    <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex items-center justify-between hover:shadow-lg transition-shadow">
                        <div>
                            <h3 class="font-black text-xl text-gray-900 mb-1">{{ $category->name }}</h3>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-md">{{ $category->products_count }} Products</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openEditModal({{ $category }})" class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center hover:bg-indigo-100 transition-colors active:scale-95">
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
                @endforeach
            </div>
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
            
            <div class="mb-8">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Category Name</label>
                <input id="name" name="name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" placeholder="e.g. Smart Home" required />
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
                    </div>

                    <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="closeEditModal()" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- AlpineJS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('categoryManager', () => ({
                editModalOpen: false,
                editingCategory: null,
                
                formData: {
                    name: '',
                },

                openEditModal(category) {
                    this.editingCategory = category;
                    this.formData.name = category.name;
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
