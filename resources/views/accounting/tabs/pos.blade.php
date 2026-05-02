<div class="space-y-8" x-data="posInvoice()">
    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-black text-gray-900">Point of Sale (Invoice Builder)</h3>
    </div>

    <!-- Main Form -->
    <form method="POST" action="{{ route('orders.pos') }}" class="bg-white p-10 rounded-2xl shadow-xl w-full border border-gray-100">
        @csrf
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-gray-100 pb-8 mb-8">
            <div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">NEW INVOICE</h1>
                <p class="text-sm font-bold text-gray-500 mt-1">Cash Sale</p>
            </div>
            <div class="text-right">
                <div class="flex items-center gap-2 justify-end mb-2">
                    <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-gray-900">Mission <span class="text-mango">Control</span></h2>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="grid grid-cols-2 gap-8 mb-10">
            <div class="space-y-4">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Bill To</h3>
                
                <!-- Payment Method & Party Selection -->
                <div class="flex gap-4">
                    <div class="w-1/3">
                        <select name="payment_method" x-model="paymentMethod" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango font-bold text-gray-800">
                            <option value="cash">Cash Sale</option>
                            <option value="credit">Credit Sale</option>
                        </select>
                    </div>
                    <div class="w-2/3" x-show="paymentMethod === 'credit'" x-cloak>
                        <select name="party_id" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango font-bold text-gray-800" :required="paymentMethod === 'credit'">
                            <option value="">Select Party Name...</option>
                            @foreach($data['parties'] as $party)
                                <option value="{{ $party->id }}">{{ $party->name }} ({{ ucfirst($party->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <input type="text" name="customer_name" placeholder="Customer Name (e.g. Walk-in Customer)" class="w-full text-lg font-bold rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango" required>
                </div>
                <div>
                    <input type="text" name="customer_phone" placeholder="Phone Number" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango" required>
                </div>
            </div>
            <div class="space-y-4 text-right flex flex-col items-end">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Details</h3>
                <div class="text-sm text-gray-600">Date: {{ now()->format('M d, Y') }}</div>
                <div class="text-sm text-gray-600 font-bold px-3 py-1 rounded-full" 
                     :class="paymentMethod === 'cash' ? 'text-green-600 bg-green-50' : 'text-orange-600 bg-orange-50'"
                     x-text="paymentMethod === 'cash' ? 'Status: PAID (Cash)' : 'Status: CREDIT / PARTIAL'">
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-8">
            <table class="w-full text-left">
                <thead class="border-b-2 border-gray-900">
                    <tr>
                        <th class="py-3 font-black text-gray-900 w-1/2">Item Description</th>
                        <th class="py-3 font-black text-gray-900 text-center w-32">Qty</th>
                        <th class="py-3 font-black text-gray-900 text-right">Rate</th>
                        <th class="py-3 font-black text-gray-900 text-right">Amount</th>
                        <th class="py-3 text-center w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(item, index) in items" :key="index">
                        <tr class="group">
                            <td class="py-4">
                                <select x-model="item.product_id" :name="'items['+index+'][product_id]'" @change="updatePrice(index)" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango font-bold text-gray-800" required>
                                    <option value="">Select product...</option>
                                    @foreach($data['products'] as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-4 px-2">
                                <input type="number" x-model.number="item.quantity" :name="'items['+index+'][quantity]'" min="1" class="w-full text-center rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango" required>
                            </td>
                            <td class="py-4 px-2">
                                <div class="flex items-center justify-end">
                                    <span class="text-gray-500 mr-2 text-sm">Rs.</span>
                                    <input type="number" x-model.number="item.price" :name="'items['+index+'][price]'" step="0.01" min="0" class="w-24 text-right rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango py-1" required>
                                </div>
                            </td>
                            <td class="py-4 px-2">
                                <div class="flex items-center justify-end">
                                    <span class="text-gray-500 mr-2 text-sm font-bold">Rs.</span>
                                    <input type="number" :value="(item.price * item.quantity).toFixed(2)" @input="item.price = $event.target.value / item.quantity" step="0.01" min="0" class="w-28 text-right font-bold rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango py-1">
                                </div>
                            </td>
                            <td class="py-4 text-center">
                                <button type="button" @click="removeItem(index)" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <button type="button" @click="addItem()" class="mt-4 text-sm font-bold text-mango hover:text-yellow-600 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Line Item
            </button>
        </div>

        <!-- Totals & Payment -->
        <div class="flex justify-end mb-8">
            <!-- Totals Section -->
            <div class="w-1/2">
                <div class="flex justify-between py-2 border-b border-gray-100 items-center">
                    <span class="font-bold text-gray-500">Subtotal</span>
                    <span class="font-bold text-gray-900">Rs. <span x-text="subtotal.toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100 items-center">
                    <span class="font-bold text-gray-500">Delivery Charge</span>
                    <div class="flex items-center w-32 justify-end">
                        <span class="text-gray-500 mr-2">Rs.</span>
                        <input type="number" x-model.number="deliveryCharge" name="delivery_charge" class="w-24 text-right rounded-xl border-gray-200 bg-gray-50 focus:ring-mango focus:border-mango py-1" min="0" step="0.01">
                    </div>
                </div>
                <div class="flex justify-between py-4 border-b-4 border-gray-900">
                    <span class="text-xl font-black text-gray-900">Total</span>
                    <span class="text-xl font-black text-gray-900">Rs. <span x-text="total.toFixed(2)"></span></span>
                </div>

                <!-- Amount Paid and Due Balance -->
                <div class="flex justify-between py-3 border-b border-gray-100 items-center bg-gray-50 px-4 rounded-xl mt-4">
                    <span class="font-bold text-gray-700">Amount Paid</span>
                    <div class="flex items-center w-32 justify-end">
                        <span class="text-gray-700 mr-2 font-bold">Rs.</span>
                        <input type="number" name="paid_amount" x-model.number="paidAmount" step="0.01" min="0" class="w-28 text-right font-bold rounded-xl border-gray-200 bg-white focus:ring-mango focus:border-mango py-1">
                    </div>
                </div>
                <div class="flex justify-between py-3 px-4 mt-2 rounded-xl items-center" :class="paymentMethod === 'credit' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'">
                    <span class="font-bold" x-text="paymentMethod === 'credit' ? 'Due Balance' : 'Change Due (if any)'"></span>
                    <span class="font-black text-lg">Rs. <span x-text="Math.max(0, total - (parseFloat(paidAmount) || 0)).toFixed(2)"></span></span>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" class="bg-mango hover:bg-yellow-400 text-gray-900 font-black py-4 px-8 rounded-xl transition-all shadow-sm text-lg flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Issue Bill & Record Payment
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posInvoice', () => ({
        items: [{ product_id: '', quantity: 1, price: 0 }],
        deliveryCharge: 0,
        paymentMethod: 'cash',
        paidAmount: 0,
        
        init() {
            this.$watch('paymentMethod', value => {
                if (value === 'cash') {
                    this.paidAmount = this.total;
                } else {
                    this.paidAmount = 0;
                }
            });
            this.$watch('total', value => {
                if (this.paymentMethod === 'cash') {
                    this.paidAmount = value;
                }
            });
        },
        addItem() {
            this.items.push({ product_id: '', quantity: 1, price: 0 });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        
        updatePrice(index) {
            const select = event.target;
            const option = select.options[select.selectedIndex];
            if(option && option.dataset.price) {
                this.items[index].price = parseFloat(option.dataset.price);
            } else {
                this.items[index].price = 0;
            }
        },
        
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        
        get total() {
            return this.subtotal + (parseFloat(this.deliveryCharge) || 0);
        }
    }));
});
</script>
