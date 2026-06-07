<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
                {{ __('Flash Sales Management') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-900 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden border border-gray-100 mb-6">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-gray-900">Manage Flash Sale Items</h3>
                    <form method="GET" action="{{ route('flash-sales.index') }}" class="flex gap-2">
                        <select name="status" class="rounded-xl border-gray-200 bg-white shadow-sm focus:border-gray-900 py-2 text-sm" onchange="this.form.submit()">
                            <option value="">All Products</option>
                            <option value="flash_sale" {{ request('status') == 'flash_sale' ? 'selected' : '' }}>Flash Sales Only</option>
                        </select>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="rounded-xl border-gray-200 bg-white shadow-sm focus:border-gray-900 py-2 text-sm">
                        <button type="submit" class="bg-gray-900 text-white font-bold py-2 px-4 rounded-xl shadow-sm hover:bg-gray-800 transition">Filter</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-bold border-b border-gray-100">
                                <th class="px-6 py-4">Product</th>
                                <th class="px-6 py-4">Regular Price</th>
                                <th class="px-6 py-4">Flash Sale Price</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($displayItems as $product)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        @if($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" class="w-12 h-12 rounded-lg object-cover bg-gray-100 border border-gray-200">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-gray-900 flex items-center gap-2">
                                                {{ $product->name }}
                                                @if(isset($product->is_bundle) && $product->is_bundle)
                                                    <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-md font-bold">Bundle</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $product->category->name ?? 'Uncategorized' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">Rs.{{ number_format($product->regular_price ?? $product->price) }}</td>
                                <td class="px-6 py-4">
                                    <form id="flash-sale-form-{{ $product->id }}-{{ $product->is_bundle ? $product->bundle_index : 'main' }}" method="POST" action="{{ route('flash-sales.update', $product->id) }}">
                                        @csrf
                                        @if(isset($product->is_bundle) && $product->is_bundle)
                                            <input type="hidden" name="bundle_index" value="{{ $product->bundle_index }}">
                                        @endif
                                        <input type="number" name="flash_sale_price" value="{{ $product->flash_sale_price }}" step="0.01" class="w-32 rounded-xl border-gray-200 bg-white shadow-sm focus:border-gray-900 py-1.5 text-sm" placeholder="e.g. 999">
                                </td>
                                <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="hidden" name="is_flash_sale" value="0">
                                            <input type="checkbox" name="is_flash_sale" value="1" class="sr-only peer" {{ $product->is_flash_sale ? 'checked' : '' }} onchange="document.getElementById('flash-sale-form-{{ $product->id }}-{{ $product->is_bundle ? $product->bundle_index : 'main' }}').submit()">
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-mango/30 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-mango"></div>
                                        </label>
                                </td>
                                <td class="px-6 py-4 text-right">
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">Save</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
