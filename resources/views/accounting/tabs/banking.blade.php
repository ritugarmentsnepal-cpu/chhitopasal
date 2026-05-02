<div class="space-y-8" x-data="{ 
    paymentInModal: false, 
    paymentOutModal: false,
    addAccountModal: false,
    editAccountModal: false,
    transferModal: false,
    editAccountData: { id: '', name: '', type: '', account_number: '', bank_name: '', branch: '' }
}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-2xl font-black text-gray-900">Banking & Ledger</h3>
        <div class="flex flex-wrap gap-2 sm:gap-3">
            <button @click="addAccountModal = true" class="bg-gray-900 text-white font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-gray-800 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Account
            </button>
            <button @click="transferModal = true" class="bg-blue-100 text-blue-700 font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-blue-200 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Transfer
            </button>
            <button @click="paymentInModal = true" class="bg-green-100 text-green-700 font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-green-200 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path></svg>
                Pay In
            </button>
            <button @click="paymentOutModal = true" class="bg-red-100 text-red-700 font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-red-200 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path></svg>
                Pay Out
            </button>
            {{-- FIN-MED-03: Reconciliation check button --}}
            <form method="POST" action="{{ route('accounting.reconcile') }}" class="inline">
                @csrf
                <button type="submit" class="bg-amber-100 text-amber-700 font-bold py-2 sm:py-2.5 px-3 sm:px-5 rounded-xl shadow-sm hover:bg-amber-200 transition-colors flex items-center gap-1.5 sm:gap-2 text-sm sm:text-base" title="Check for ledger discrepancies">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Reconcile
                </button>
            </form>
        </div>
    </div>

    <!-- Account Balances -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @foreach($data['accounts'] as $account)
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative group hover:shadow-md transition-shadow">
            <a href="{{ route('accounting.statement', $account->id) }}" class="block">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl" title="{{ ucfirst($account->type) }}">{{ $account->getTypeIcon() }}</span>
                        <div>
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">{{ $account->name }}</h4>
                            @if($account->bank_name)
                                <p class="text-xs text-gray-400 font-medium">{{ $account->bank_name }} @if($account->account_number) • {{ substr($account->account_number, -4) }} @endif</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-3xl font-black text-gray-900 mt-2">Rs. {{ number_format($account->balance, 2) }}</div>
            </a>
            
            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-10 py-1" style="display: none;">
                        <a href="{{ route('accounting.statement', $account->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-bold">View Statement</a>
                        
                        <button @click="open = false; editAccountData = { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }}', type: '{{ $account->type }}', account_number: '{{ addslashes($account->account_number) }}', bank_name: '{{ addslashes($account->bank_name) }}', branch: '{{ addslashes($account->branch) }}' }; editAccountModal = true" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 font-bold">Edit Account</button>
                        
                        @if(!$account->isProtected() && $account->balance == 0)
                            <form action="{{ route('accounting.destroyAccount', $account->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-bold">Delete Account</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h4 class="font-black text-lg text-gray-900">Recent Transactions</h4>
        </div>
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
                            <div class="text-xs text-gray-500">{{ ucfirst($transaction->account->type) }}</div>
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
        @if($data['transactions']->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $data['transactions']->appends(['tab' => 'banking'])->links() }}
            </div>
        @endif
    </div>

    <!-- Modals -->

    <!-- Transfer Modal -->
    <div x-show="transferModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="transferModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('accounting.transferFunds') }}" method="POST" class="p-8">
                    @csrf
                    <h3 class="text-2xl font-black text-blue-600 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        Transfer Funds
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">From Account</label>
                            <select name="from_account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select source account...</option>
                                @foreach($data['accounts'] as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} (Bal: Rs. {{ number_format($account->balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">To Account</label>
                            <select name="to_account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Select destination account...</option>
                                @foreach($data['accounts'] as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Amount (Rs.)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Notes / Description</label>
                            <input type="text" name="notes" placeholder="e.g. Cash deposit to bank" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="transferModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Account Modal -->
    <div x-show="addAccountModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="addAccountModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('accounting.storeAccount') }}" method="POST" class="p-8" x-data="{ accountType: 'cash' }">
                    @csrf
                    <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Add New Account
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Account Name</label>
                            <input type="text" name="name" placeholder="e.g. Nabil Bank Checking" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Account Type</label>
                            <select name="type" x-model="accountType" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Account</option>
                                <option value="mobile_wallet">Mobile Wallet (eSewa, Khalti)</option>
                                <option value="clearing">Clearing / Virtual</option>
                            </select>
                        </div>
                        
                        <div x-show="accountType === 'bank' || accountType === 'mobile_wallet'" class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Bank / Provider Name</label>
                                <input type="text" name="bank_name" placeholder="e.g. Nabil Bank, eSewa" class="w-full rounded-lg border-gray-300 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Account / Wallet Number</label>
                                <input type="text" name="account_number" placeholder="e.g. 0123456789" class="w-full rounded-lg border-gray-300 py-2 text-sm">
                            </div>
                            <div x-show="accountType === 'bank'">
                                <label class="block text-xs font-bold text-gray-600 mb-1">Branch</label>
                                <input type="text" name="branch" placeholder="e.g. New Baneshwor" class="w-full rounded-lg border-gray-300 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Opening Balance (Rs.)</label>
                            <input type="number" name="opening_balance" step="0.01" min="0" value="0.00" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="addAccountModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Save Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div x-show="editAccountModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="editAccountModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form :action="'{{ route('accounting.updateAccount', 'ACCOUNT_ID') }}'.replace('ACCOUNT_ID', editAccountData.id)" method="POST" class="p-8">
                    @csrf
                    @method('PUT')
                    <h3 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit Account
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Account Name</label>
                            <input type="text" name="name" x-model="editAccountData.name" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Account Type</label>
                            <select name="type" x-model="editAccountData.type" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Account</option>
                                <option value="mobile_wallet">Mobile Wallet (eSewa, Khalti)</option>
                                <option value="clearing">Clearing / Virtual</option>
                            </select>
                        </div>
                        
                        <div x-show="editAccountData.type === 'bank' || editAccountData.type === 'mobile_wallet'" class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Bank / Provider Name</label>
                                <input type="text" name="bank_name" x-model="editAccountData.bank_name" class="w-full rounded-lg border-gray-300 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Account / Wallet Number</label>
                                <input type="text" name="account_number" x-model="editAccountData.account_number" class="w-full rounded-lg border-gray-300 py-2 text-sm">
                            </div>
                            <div x-show="editAccountData.type === 'bank'">
                                <label class="block text-xs font-bold text-gray-600 mb-1">Branch</label>
                                <input type="text" name="branch" x-model="editAccountData.branch" class="w-full rounded-lg border-gray-300 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="editAccountModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment In/Out Modals -->
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
