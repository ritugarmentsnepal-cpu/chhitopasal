<div class="space-y-8">
    <h3 class="text-2xl font-black text-gray-900">Financial Overview</h3>

    <!-- Top Line Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Realized Revenue -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:border-mango transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-mango/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Realized Revenue</p>
                <h3 class="text-3xl font-black text-gray-900 mb-2">Rs. {{ number_format($data['revenue'], 2) }}</h3>
            </div>
            <p class="text-xs font-bold text-gray-400">Total from Delivered Orders</p>
        </div>

        <!-- Net Profit -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:border-green-400 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-green-400/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Net Profit</p>
                <h3 class="text-3xl font-black {{ $data['netProfit'] >= 0 ? 'text-green-500' : 'text-red-500' }} mb-2">
                    Rs. {{ number_format($data['netProfit'], 2) }}
                </h3>
            </div>
            <p class="text-xs font-bold text-gray-400">Revenue - COGS - Expenses</p>
        </div>

        <!-- Cost of Goods Sold -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:border-orange-400 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-400/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Cost of Goods Sold</p>
                <h3 class="text-3xl font-black text-orange-500 mb-2">Rs. {{ number_format($data['cogs'], 2) }}</h3>
            </div>
            <p class="text-xs font-bold text-gray-400">Base cost of delivered items</p>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:border-red-400 transition-colors">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-400/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Expenses</p>
                <h3 class="text-3xl font-black text-red-500 mb-2">Rs. {{ number_format($data['expenses'], 2) }}</h3>
            </div>
            <p class="text-xs font-bold text-gray-400">Operational costs & overhead</p>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Pending Revenue -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Pending Receivables</p>
                <h3 class="text-2xl font-black text-blue-500">Rs. {{ number_format($data['pendingRevenue'], 2) }}</h3>
                <p class="text-xs font-bold text-gray-400 mt-1">Locked in Pending/Shipped orders</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-2xl">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Bank Balance -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Bank Balance</p>
                <h3 class="text-2xl font-black text-purple-500">Rs. {{ number_format($data['totalBank'], 2) }}</h3>
                <p class="text-xs font-bold text-gray-400 mt-1">Available in bank accounts</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-2xl">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8" x-data>
        <h4 class="text-xl font-black text-gray-900 mb-4">Quick Actions</h4>
        <div class="flex flex-wrap gap-4">
            <button @click="$dispatch('open-modal', 'quick-expense-modal')" class="bg-white border border-gray-200 px-6 py-4 rounded-2xl font-bold text-gray-700 shadow-sm hover:border-mango hover:text-mango transition-colors flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                Record Expense
            </button>
            <button @click="$dispatch('open-modal', 'quick-transaction-modal')" class="bg-white border border-gray-200 px-6 py-4 rounded-2xl font-bold text-gray-700 shadow-sm hover:border-blue-500 hover:text-blue-500 transition-colors flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
                Manual Transaction
            </button>
        </div>

        <!-- Modals -->
        <x-modal name="quick-expense-modal" :show="false" maxWidth="2xl">
            <div class="p-8">
                <h2 class="text-2xl font-black text-gray-900 mb-6">Record Quick Expense</h2>
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Category</label>
                                <select name="expense_category_id" required class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango">
                                    <option value="">Select Category...</option>
                                    @foreach($data['categories'] as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Amount (Rs.)</label>
                                <input type="number" step="0.01" name="amount" required class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango" placeholder="0.00">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Reference No</label>
                                <input type="text" name="reference_no" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango" placeholder="Bill or Receipt #">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="2" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango" placeholder="Expense details..."></textarea>
                        </div>
                        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                            <button type="button" @click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                            <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-lg">Save Expense</button>
                        </div>
                    </div>
                </form>
            </div>
        </x-modal>

        <x-modal name="quick-transaction-modal" :show="false" maxWidth="2xl">
            <div class="p-8">
                <h2 class="text-2xl font-black text-gray-900 mb-6">Manual Transaction</h2>
                <form action="{{ route('accounting.storeTransaction') }}" method="POST">
                    @csrf
                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Account</label>
                                <select name="account_id" required class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango">
                                    <option value="">Select Account...</option>
                                    @foreach($data['accounts'] as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Rs. {{ number_format($acc->balance) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Type</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="type" value="in" required class="text-mango focus:ring-mango"> <span class="font-bold text-sm">Money In</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="type" value="out" required class="text-mango focus:ring-mango"> <span class="font-bold text-sm">Money Out</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Amount (Rs.)</label>
                                <input type="number" step="0.01" name="amount" required class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Party (Optional)</label>
                                <select name="party_id" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango">
                                    <option value="">None</option>
                                    @foreach($data['parties'] as $party)
                                        <option value="{{ $party->id }}">{{ $party->name }} ({{ ucfirst($party->type) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notes / Description</label>
                            <input type="text" name="notes" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango" placeholder="What was this for?">
                        </div>
                        <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                            <button type="button" @click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                            <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 transition shadow-lg">Save Transaction</button>
                        </div>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</div>
