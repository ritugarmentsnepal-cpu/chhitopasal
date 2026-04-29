<div class="space-y-8" x-data="{
    purchaseModal: false,
    paymentModal: false,
    selectedPurchaseId: null,
    selectedPurchaseAmount: 0,
    items: [{ product_id: '', quantity: 1, unit_cost: 0 }],
    
    addItem() {
        this.items.push({ product_id: '', quantity: 1, unit_cost: 0 });
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },
    updateCost(index, event) {
        const select = event.target;
        const option = select.options[select.selectedIndex];
        if(option && option.dataset.cost) {
            this.items[index].unit_cost = parseFloat(option.dataset.cost);
        }
    },
    get subtotal() {
        return this.items.reduce((sum, item) => sum + (item.unit_cost * item.quantity), 0);
    },
    openPayment(id, amount) {
        this.selectedPurchaseId = id;
        this.selectedPurchaseAmount = amount;
        this.paymentModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-2xl font-black text-gray-900">Purchase Bills</h3>
        <button @click="purchaseModal = true" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm hover:bg-gray-800 transition-colors whitespace-nowrap">
            + Record Purchase
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Payment Status</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['purchases'] as $purchase)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ $purchase->party ? $purchase->party->name : $purchase->supplier_name }}</div>
                        @if($purchase->attachment_path)
                            <a href="{{ Storage::url($purchase->attachment_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                View Bill
                            </a>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div x-data="{ editingAmount: false, newAmount: {{ $purchase->total_amount }} }">
                            <div x-show="!editingAmount" class="flex items-center gap-2">
                                <span class="font-black text-gray-900">Rs. {{ number_format($purchase->total_amount, 2) }}</span>
                                <button @click="editingAmount = true" class="text-gray-400 hover:text-mango">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                            <form x-show="editingAmount" action="{{ route('purchases.updateAmount', $purchase) }}" method="POST" class="flex items-center gap-2" x-cloak>
                                @csrf @method('PATCH')
                                <input type="number" step="0.01" name="total_amount" x-model="newAmount" class="w-24 rounded border-gray-200 py-1 px-2 text-sm font-bold">
                                <button type="submit" class="text-green-500 hover:text-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></button>
                                <button type="button" @click="editingAmount = false; newAmount = {{ $purchase->total_amount }}" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </form>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $purchase->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($purchase->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        @if($purchase->payment_status !== 'paid')
                            <button @click="openPayment({{ $purchase->id }}, {{ $purchase->total_amount }})" class="text-mango hover:text-yellow-600 font-bold text-sm">Make Payment</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>

    <!-- Record Purchase Modal -->
    <div x-show="purchaseModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="purchaseModal" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="purchaseModal = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="purchaseModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form action="{{ route('purchases.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-black text-gray-900">Record Purchase Bill</h3>
                        <button type="button" @click="purchaseModal = false" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Supplier Party</label>
                            <select name="party_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                                <option value="">Select a supplier...</option>
                                @foreach($data['parties'] as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Bill/Invoice Number</label>
                            <input type="text" name="reference_no" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Upload Bill Image/PDF</label>
                            <input type="file" name="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-mango file:text-gray-900 hover:file:bg-yellow-400">
                        </div>
                    </div>

                    <!-- Dynamic Items -->
                    <div class="mb-6 border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-4 font-bold text-sm text-gray-900">Product</th>
                                    <th class="py-3 px-4 font-bold text-sm text-gray-900 w-32">Qty</th>
                                    <th class="py-3 px-4 font-bold text-sm text-gray-900 w-40">Unit Cost</th>
                                    <th class="py-3 px-4 font-bold text-sm text-gray-900 text-right w-40">Total</th>
                                    <th class="py-3 px-4 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="p-2">
                                            <select x-model="item.product_id" :name="'items['+index+'][product_id]'" @change="updateCost(index, $event)" class="w-full rounded-lg border-gray-200 bg-gray-50 text-sm" required>
                                                <option value="">Select...</option>
                                                @foreach(App\Models\Product::all() as $product)
                                                    <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" x-model.number="item.quantity" :name="'items['+index+'][quantity]'" min="1" class="w-full rounded-lg border-gray-200 bg-gray-50 text-center text-sm" required>
                                        </td>
                                        <td class="p-2">
                                            <input type="number" x-model.number="item.unit_cost" :name="'items['+index+'][unit_cost]'" step="0.01" class="w-full rounded-lg border-gray-200 bg-gray-50 text-right text-sm" required>
                                        </td>
                                        <td class="p-2 text-right font-bold text-gray-900">
                                            Rs. <span x-text="(item.quantity * item.unit_cost).toFixed(2)"></span>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div class="bg-gray-50 p-4 flex justify-between items-center border-t border-gray-200">
                            <button type="button" @click="addItem()" class="text-sm font-bold text-mango hover:text-yellow-600 flex items-center gap-1">
                                + Add Row
                            </button>
                            <div class="font-black text-lg text-gray-900">
                                Total: Rs. <span x-text="subtotal.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="purchaseModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Record Purchase & Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Make Payment Modal -->
    <div x-show="paymentModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="paymentModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('accounting.payPurchase') }}" method="POST" class="p-8">
                    @csrf
                    <input type="hidden" name="purchase_id" x-model="selectedPurchaseId">
                    <h3 class="text-2xl font-black text-gray-900 mb-6">Make Payment</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Payment Amount (Rs.)</label>
                            <input type="number" name="amount" x-model="selectedPurchaseAmount" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pay From Account</label>
                            <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                                @foreach(App\Models\Account::all() as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} (Bal: {{ $account->balance }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Payment Notes</label>
                            <input type="text" name="notes" placeholder="e.g. Cheque No 123456" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="paymentModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
