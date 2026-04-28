<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                {{ __('Purchases & Inventory Restock') }}
            </h2>
            <button x-data="" @click="$dispatch('open-modal', 'add-purchase')" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-5 rounded-xl transition-all active:scale-95 shadow-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                Record Purchase Bill
            </button>
        </div>
    </x-slot>

    <div class="py-12" x-data="purchaseManager()">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm">
                    <p class="font-bold text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                    <p class="font-bold text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="py-4 px-6 font-black text-xs text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="py-4 px-6 font-black text-xs text-gray-400 uppercase tracking-wider">Supplier</th>
                                <th class="py-4 px-6 font-black text-xs text-gray-400 uppercase tracking-wider">Ref No.</th>
                                <th class="py-4 px-6 font-black text-xs text-gray-400 uppercase tracking-wider">Total Amount</th>
                                <th class="py-4 px-6 font-black text-xs text-gray-400 uppercase tracking-wider">Items Restocked</th>
                                <th class="py-4 px-6 font-black text-xs text-gray-400 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($purchases as $purchase)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6 font-bold text-gray-900">{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</td>
                                    <td class="py-4 px-6 font-bold text-gray-700">{{ $purchase->supplier_name }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-500">{{ $purchase->reference_no ?? '-' }}</td>
                                    <td class="py-4 px-6 font-black text-mango">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                    <td class="py-4 px-6 font-medium text-sm text-gray-600">
                                        @foreach($purchase->items as $item)
                                            <div>{{ $item->quantity }}x {{ $item->product->name }}</div>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        @if(auth()->user()->role === 'admin')
                                            <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this purchase? This will reduce stock levels and may break inventory calculations.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm transition-colors">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400 font-medium">No purchase bills recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Purchase Modal -->
            <x-modal name="add-purchase" focusable maxWidth="4xl">
                <form method="POST" action="{{ route('purchases.store') }}" class="p-8">
                    @csrf
                    <h2 class="text-2xl font-black text-gray-900 mb-6">Record Purchase Bill & Restock</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Supplier Name</label>
                            <input type="text" name="supplier_name" placeholder="e.g. Vendor XYZ" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Reference No. (Bill No.)</label>
                            <input type="text" name="reference_no" placeholder="Optional" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                            <input type="text" name="notes" placeholder="Optional notes" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango transition-colors">
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 mb-6">
                        <h3 class="font-black text-gray-900 mb-4">Items Received</h3>
                        
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex flex-col md:flex-row gap-3 mb-3 items-end">
                                <div class="flex-grow">
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Product</label>
                                    <select :name="'items['+index+'][product_id]'" x-model="item.product_id" class="w-full rounded-xl border-gray-200 bg-white py-2" required>
                                        <option value="">Select Product...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (Current Stock: {{ $product->stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-full md:w-32">
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Quantity</label>
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" min="1" class="w-full rounded-xl border-gray-200 bg-white py-2" required>
                                </div>
                                <div class="w-full md:w-40">
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Unit Cost (Rs.)</label>
                                    <input type="number" step="0.01" :name="'items['+index+'][unit_cost]'" x-model="item.unit_cost" min="0" class="w-full rounded-xl border-gray-200 bg-white py-2" required>
                                </div>
                                <div class="w-full md:w-auto">
                                    <button type="button" @click="removeItem(index)" class="bg-red-100 hover:bg-red-200 text-red-600 font-bold py-2 px-4 rounded-xl transition text-sm mb-[2px]">Remove</button>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addItem" class="mt-2 text-wildOrchid font-bold text-sm hover:text-pink-700 transition-colors">+ Add Another Item</button>
                    </div>

                    <div class="flex justify-between items-center border-t border-gray-100 pt-6">
                        <div class="text-xl text-gray-900 font-black">
                            Total Bill: Rs. <span x-text="calculateTotal()"></span>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Save & Update Stock</button>
                        </div>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchaseManager', () => ({
                items: [
                    { product_id: '', quantity: 1, unit_cost: '' }
                ],

                addItem() {
                    this.items.push({ product_id: '', quantity: 1, unit_cost: '' });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                calculateTotal() {
                    let total = 0;
                    this.items.forEach(item => {
                        if (item.quantity && item.unit_cost) {
                            total += (parseFloat(item.quantity) * parseFloat(item.unit_cost));
                        }
                    });
                    return total.toFixed(2);
                }
            }))
        })
    </script>
</x-app-layout>
