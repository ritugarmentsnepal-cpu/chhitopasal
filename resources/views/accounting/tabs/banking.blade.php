<div class="space-y-8" x-data="{ paymentInModal: false, paymentOutModal: false }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-2xl font-black text-gray-900">Banking & Ledger</h3>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <button @click="paymentInModal = true" class="bg-green-100 text-green-700 font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-green-200 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                Pay In
            </button>
            <button @click="paymentOutModal = true" class="bg-red-100 text-red-700 font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-red-200 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                Pay Out
            </button>
            <form action="{{ route('accounting.syncPathao') }}" method="POST">
                @csrf
                <button type="submit" class="bg-mango text-gray-900 font-black py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-yellow-400 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync Pathao
                </button>
            </form>
        </div>
    </div>

    <!-- Account Balances -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @foreach($data['accounts'] as $account)
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">{{ $account->name }}</h4>
                <div class="text-3xl font-black text-gray-900">Rs. {{ number_format($account->balance, 2) }}</div>
            </div>
            <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Reference / Notes</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['transactions'] as $transaction)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ $transaction->account->name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">{{ $transaction->reference_type ? $transaction->reference_type . ' #' . $transaction->reference_id : 'Manual Entry' }}</div>
                        <div class="text-sm text-gray-500">{{ $transaction->notes }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="font-black {{ $transaction->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'in' ? '+' : '-' }} Rs. {{ number_format($transaction->amount, 2) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>

    <!-- Payment In Modal -->
    <div x-show="paymentInModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="paymentInModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('accounting.storeTransaction') }}" method="POST" class="p-8">
                    @csrf
                    <input type="hidden" name="type" value="in">
                    <h3 class="text-2xl font-black text-green-600 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                        Receive Payment
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Amount (Rs.)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-green-500 focus:border-green-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Receive Into Account</label>
                            <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-green-500 focus:border-green-500" required>
                                @foreach($data['accounts'] as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} (Bal: Rs. {{ number_format($account->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">From Party (Optional)</label>
                            <select name="party_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-green-500 focus:border-green-500">
                                <option value="">None / Walk-in</option>
                                @foreach(App\Models\Party::all() as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }} ({{ ucfirst($party->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Notes / Description</label>
                            <input type="text" name="notes" placeholder="e.g. Sales Collection" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="paymentInModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Save Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment Out Modal -->
    <div x-show="paymentOutModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="paymentOutModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('accounting.storeTransaction') }}" method="POST" class="p-8">
                    @csrf
                    <input type="hidden" name="type" value="out">
                    <h3 class="text-2xl font-black text-red-600 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                        Send Payment
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Amount (Rs.)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pay From Account</label>
                            <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-red-500 focus:border-red-500" required>
                                @foreach($data['accounts'] as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} (Bal: Rs. {{ number_format($account->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">To Party (Optional)</label>
                            <select name="party_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-red-500 focus:border-red-500">
                                <option value="">None / General</option>
                                @foreach(App\Models\Party::all() as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }} ({{ ucfirst($party->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Notes / Description</label>
                            <input type="text" name="notes" placeholder="e.g. Utility Bill Payment" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="paymentOutModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
