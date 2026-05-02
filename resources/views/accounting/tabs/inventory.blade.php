<div class="space-y-8" x-data="{
    adjustModal: false,
    selectedProductId: '',
    selectedProductName: '',
    currentStock: 0,
    adjustmentType: 'add',
    quantity: 1,
    reason: '',
    
    openAdjust(id, name, stock) {
        this.selectedProductId = id;
        this.selectedProductName = name;
        this.currentStock = stock;
        this.quantity = 1;
        this.reason = '';
        this.adjustmentType = 'add';
        this.$dispatch('open-modal', 'adjust-stock-modal');
    }
}">
    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-black text-gray-900">Inventory Management</h3>
    </div>

    <!-- Inventory Overview -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h4 class="font-bold text-gray-900">Current Stock Levels</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs tracking-wider font-bold uppercase">
                        <th class="px-6 py-4">Product ID</th>
                        <th class="px-6 py-4">Product Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4 text-center">Stock</th>
                        <th class="px-6 py-4 text-right">Value (Cost)</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data['products'] as $product)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-bold">#{{ $product->id }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden">
                                        @if($product->image_path)
                                            <img src="{{ Storage::url($product->image_path) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </div>
                                    {{ $product->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category ? $product->category->name : 'Uncategorized' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-black {{ $product->stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-900">
                                Rs. {{ number_format($product->cost_price * $product->stock, 2) }}
                                <div class="text-[10px] text-gray-400 font-medium leading-none mt-1">@ Rs. {{ $product->cost_price }}/unit</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openAdjust({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock }})" class="text-mango hover:text-yellow-600 font-bold text-sm">Adjust</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Logs -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h4 class="font-bold text-gray-900">Recent Inventory Movements</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs tracking-wider font-bold uppercase">
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Product ID</th>
                        <th class="px-6 py-4">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['inventory_logs'] as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $log->user ? $log->user->name : 'System' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2 py-1 rounded text-xs font-bold {{ str_contains($log->action, 'deduct') || str_contains($log->action, 'remove') ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700' }}">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">#{{ $log->model_id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if(isset($log->details['reason']))
                                    <span class="font-bold text-gray-700">{{ $log->details['reason'] }}</span> 
                                    (Qty: {{ $log->details['quantity'] ?? 'N/A' }})
                                @elseif(isset($log->details['new']['stock']))
                                    Stock changed: 
                                    {{ $log->details['old']['stock'] ?? 'N/A' }} 
                                    <span class="mx-1 text-gray-300">-></span> 
                                    <span class="font-bold text-gray-900">{{ $log->details['new']['stock'] }}</span>
                                @else
                                    <span class="italic text-gray-400">System update</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No recent inventory movements.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($data['inventory_logs']->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $data['inventory_logs']->links() }}
            </div>
        @endif
    </div>

    <!-- Adjust Stock Modal -->
    <x-modal name="adjust-stock-modal" :show="false" maxWidth="md">
        <div class="p-8">
            <h2 class="text-2xl font-black text-gray-900 mb-6">Adjust Stock</h2>
            <form action="{{ route('accounting.adjustStock') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" x-model="selectedProductId">
                
                <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="text-sm text-gray-500 font-bold mb-1">Product</div>
                    <div class="font-black text-gray-900" x-text="selectedProductName"></div>
                    <div class="text-xs text-gray-500 mt-1">Current Stock: <span class="font-bold text-gray-900" x-text="currentStock"></span></div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Adjustment Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="add" x-model="adjustmentType" class="text-mango focus:ring-mango"> 
                                <span class="font-bold text-sm text-green-600">+ Add Stock</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="deduct" x-model="adjustmentType" class="text-mango focus:ring-mango"> 
                                <span class="font-bold text-sm text-red-600">- Deduct Stock</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Quantity</label>
                        <input type="number" name="quantity" x-model="quantity" min="1" required class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango text-xl font-black text-center py-3">
                        <div class="mt-2 text-center text-xs text-gray-500">
                            New Stock will be: 
                            <span class="font-black" :class="adjustmentType === 'add' ? 'text-green-600' : 'text-red-600'" x-text="adjustmentType === 'add' ? parseInt(currentStock) + parseInt(quantity || 0) : parseInt(currentStock) - parseInt(quantity || 0)"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Reason</label>
                        <select name="reason" x-model="reason" required class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango">
                            <option value="">Select reason...</option>
                            <template x-if="adjustmentType === 'add'">
                                <optgroup label="Additions">
                                    <option value="Initial Stock">Initial Stock Entry</option>
                                    <option value="Found Missing Stock">Found Missing Stock</option>
                                    <option value="Customer Return (Manual)">Customer Return (Manual)</option>
                                    <option value="Other Addition">Other</option>
                                </optgroup>
                            </template>
                            <template x-if="adjustmentType === 'deduct'">
                                <optgroup label="Deductions">
                                    <option value="Damaged/Defective">Damaged/Defective</option>
                                    <option value="Lost/Stolen">Lost/Stolen</option>
                                    <option value="Used for Promotion">Used for Promotion/Marketing</option>
                                    <option value="Inventory Correction">Inventory Correction</option>
                                    <option value="Other Deduction">Other</option>
                                </optgroup>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Additional Notes (Optional)</label>
                        <input type="text" name="notes" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                    <button type="button" @click="$dispatch('close-modal', 'adjust-stock-modal')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-lg">Confirm Adjustment</button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
