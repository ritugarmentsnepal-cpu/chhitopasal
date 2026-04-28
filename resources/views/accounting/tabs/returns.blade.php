{{-- Sale Returns Tab --}}
<div x-data="saleReturnApp()" class="space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Sale Returns</h2>
            <p class="text-gray-500 font-medium mt-1">Process cancelled / returned orders and deduct from Pathao receivables.</p>
        </div>
        @if(isset($data['pathao_clearing']))
        <div class="bg-white border border-gray-200 rounded-2xl px-6 py-4 text-right shadow-sm">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pathao Clearing Balance</p>
            <p class="text-2xl font-black text-gray-900">Rs. {{ number_format($data['pathao_clearing']->balance ?? 0) }}</p>
        </div>
        @endif
    </div>

    {{-- Search & Process Return --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-6 py-5 text-white">
            <h3 class="font-black text-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-mango" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Process New Sale Return
            </h3>
            <p class="text-gray-300 text-sm mt-1">Enter Order # or Pathao Consignment ID to find and process the return.</p>
        </div>

        <div class="p-6">
            {{-- Search Bar --}}
            <div class="flex gap-3 mb-6">
                <input type="text" x-model="searchQuery" @keydown.enter="searchOrder()" placeholder="Order # or Consignment ID (e.g. 15 or DC280426...)"
                       class="flex-1 bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-bold text-lg py-3">
                <button @click="searchOrder()" :disabled="searching" class="bg-gray-900 text-white font-bold px-6 py-3 rounded-xl hover:bg-gray-800 transition active:scale-95 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="!searching" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span x-show="searching" class="animate-spin">⏳</span>
                    Find Order
                </button>
            </div>

            {{-- Error --}}
            <div x-show="searchError" x-transition class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 font-bold text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <span x-text="searchError"></span>
            </div>

            {{-- Found Order Details --}}
            <div x-show="foundOrder" x-transition class="border-2 border-mango/30 rounded-2xl overflow-hidden bg-mango/5">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="text-xl font-black text-gray-900">Order #<span x-text="foundOrder?.id"></span></h4>
                            <p class="text-gray-500 font-medium text-sm" x-text="foundOrder?.created_at"></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase"
                              :class="{
                                  'bg-red-100 text-red-700': ['failed','rejected','return_delivered'].includes(foundOrder?.status),
                                  'bg-yellow-100 text-yellow-700': foundOrder?.status === 'pending',
                                  'bg-blue-100 text-blue-700': foundOrder?.status === 'shipped',
                                  'bg-green-100 text-green-700': foundOrder?.status === 'delivered',
                                  'bg-gray-100 text-gray-700': !foundOrder?.status
                              }"
                              x-text="foundOrder?.status"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 text-sm">
                        <div class="bg-white rounded-xl p-3 border border-gray-100">
                            <p class="text-gray-400 font-bold text-xs uppercase">Customer</p>
                            <p class="font-black text-gray-900" x-text="foundOrder?.customer_name"></p>
                            <p class="text-gray-500" x-text="foundOrder?.customer_phone"></p>
                        </div>
                        <div class="bg-white rounded-xl p-3 border border-gray-100">
                            <p class="text-gray-400 font-bold text-xs uppercase">Total Amount</p>
                            <p class="font-black text-gray-900 text-lg">Rs. <span x-text="foundOrder?.total_amount?.toLocaleString()"></span></p>
                        </div>
                        <div class="bg-white rounded-xl p-3 border border-gray-100">
                            <p class="text-gray-400 font-bold text-xs uppercase">Consignment</p>
                            <p class="font-black text-gray-900" x-text="foundOrder?.pathao_consignment_id || 'N/A'"></p>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden mb-4">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left px-4 py-2 font-bold text-gray-500">Item</th>
                                    <th class="text-center px-4 py-2 font-bold text-gray-500">Qty</th>
                                    <th class="text-right px-4 py-2 font-bold text-gray-500">Price</th>
                                    <th class="text-right px-4 py-2 font-bold text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in foundOrder?.items" :key="item.product_name">
                                    <tr class="border-t border-gray-50">
                                        <td class="px-4 py-2 font-bold text-gray-900" x-text="item.product_name"></td>
                                        <td class="px-4 py-2 text-center" x-text="item.quantity"></td>
                                        <td class="px-4 py-2 text-right" x-text="'Rs. ' + item.price?.toLocaleString()"></td>
                                        <td class="px-4 py-2 text-right font-bold" x-text="'Rs. ' + item.total?.toLocaleString()"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Already Returned Warning --}}
                    <div x-show="foundOrder?.already_returned" class="bg-red-50 border border-red-200 p-4 rounded-xl mb-4 text-red-700 font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        This order has already been processed as a sale return. Cannot process again.
                    </div>

                    {{-- Process Return Form --}}
                    <div x-show="!foundOrder?.already_returned">
                        <form method="POST" action="{{ route('accounting.saleReturn') }}">
                            @csrf
                            <input type="hidden" name="order_id" :value="foundOrder?.id">

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Return Reason *</label>
                                    <input type="text" name="reason" required placeholder="e.g. Customer refused delivery, Wrong address, Damaged in transit..."
                                           class="w-full bg-white border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3">
                                </div>

                                <label class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-3 cursor-pointer hover:bg-gray-50 transition">
                                    <input type="checkbox" name="restore_stock" value="1" checked class="rounded border-gray-300 text-mango focus:ring-mango">
                                    <span class="font-bold text-gray-900 text-sm">Restore stock to inventory</span>
                                </label>

                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-xl transition active:scale-95 shadow-lg shadow-red-600/20 flex items-center justify-center gap-2 text-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                    Process Sale Return — Deduct Rs. <span x-text="foundOrder?.total_amount?.toLocaleString()"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- History of Returned Orders --}}
    @if(isset($data['returned_orders']) && $data['returned_orders']->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="font-black text-lg text-gray-900">Return History ({{ $data['returned_orders']->count() }} orders)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-3 font-bold text-gray-500">Order #</th>
                        <th class="text-left px-4 py-3 font-bold text-gray-500">Customer</th>
                        <th class="text-left px-4 py-3 font-bold text-gray-500">Consignment</th>
                        <th class="text-right px-4 py-3 font-bold text-gray-500">Amount</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500">Status</th>
                        <th class="text-left px-4 py-3 font-bold text-gray-500">Date</th>
                        <th class="text-center px-4 py-3 font-bold text-gray-500">Accounted?</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['returned_orders'] as $order)
                    @php
                        $isAccounted = \App\Models\Transaction::where('reference_type', 'SaleReturn')->where('reference_id', $order->id)->exists();
                    @endphp
                    <tr class="border-t border-gray-50 hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-black text-gray-900">#{{ $order->id }}</td>
                        <td class="px-4 py-3 font-bold text-gray-700">{{ $order->customer_name }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $order->pathao_consignment_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-black text-gray-900">Rs. {{ number_format($order->total_amount) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-black uppercase bg-red-100 text-red-700">{{ $order->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($isAccounted)
                                <span class="inline-flex items-center gap-1 text-green-600 font-bold text-xs">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    Done
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-orange-500 font-bold text-xs">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                                    Pending
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
function saleReturnApp() {
    return {
        searchQuery: '',
        searching: false,
        searchError: '',
        foundOrder: null,

        async searchOrder() {
            if (!this.searchQuery.trim()) {
                this.searchError = 'Please enter an order number or consignment ID.';
                return;
            }
            this.searching = true;
            this.searchError = '';
            this.foundOrder = null;

            try {
                const response = await fetch(`{{ route('accounting.findOrder') }}?q=${encodeURIComponent(this.searchQuery.trim())}`);
                const data = await response.json();

                if (data.success) {
                    this.foundOrder = data.order;
                } else {
                    this.searchError = data.message;
                }
            } catch (e) {
                this.searchError = 'Network error. Please try again.';
            } finally {
                this.searching = false;
            }
        }
    }
}
</script>
