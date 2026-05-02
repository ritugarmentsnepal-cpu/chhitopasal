<div class="space-y-8" x-data="{ 
    partyModal: false,
    editMode: false,
    formAction: '{{ route('accounting.storeParty') }}',
    party: { name: '', type: 'customer', phone: '', email: '', address: '', opening_balance: 0, current_balance: 0 },
    openCreate() {
        this.editMode = false;
        this.formAction = '{{ route('accounting.storeParty') }}';
        this.party = { name: '', type: 'customer', phone: '', email: '', address: '', opening_balance: 0, current_balance: 0 };
        this.partyModal = true;
    },
    editParty(data) {
        this.editMode = true;
        this.formAction = '{{ url('accounting/update-party') }}/' + data.id;
        this.party = { ...data };
        this.partyModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-2xl font-black text-gray-900">CRM & Parties</h3>
        <button @click="openCreate()" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm hover:bg-gray-800 transition-colors whitespace-nowrap">
            + Add New Party
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-red-50 rounded-2xl p-6 border border-red-100">
            <h4 class="text-sm font-bold text-red-600 uppercase tracking-wider mb-2">Total Payables</h4>
            <div class="text-3xl font-black text-red-700">Rs. {{ number_format($data['payables'] ?? 0, 2) }}</div>
            <div class="text-sm text-red-500 mt-1 font-bold">What you owe suppliers</div>
        </div>
        <div class="bg-green-50 rounded-2xl p-6 border border-green-100">
            <h4 class="text-sm font-bold text-green-600 uppercase tracking-wider mb-2">Total Receivables</h4>
            <div class="text-3xl font-black text-green-700">Rs. {{ number_format($data['receivables'] ?? 0, 2) }}</div>
            <div class="text-sm text-green-500 mt-1 font-bold">What customers owe you</div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Party Name</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Balance</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['parties'] as $party)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ $party->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $party->type === 'supplier' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ ucfirst($party->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">{{ $party->phone }}</div>
                        <div class="text-sm text-gray-500">{{ $party->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="font-black {{ ($party->current_balance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rs. {{ number_format(abs($party->current_balance ?? 0), 2) }} {{ ($party->current_balance ?? 0) >= 0 ? '(Dr)' : '(Cr)' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-4">
                            <a href="?tab=reports&report_type=ledger&party_id={{ $party->id }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Statement
                            </a>
                            <button @click="editParty({{ json_encode($party->toArray()) }})" class="text-sm font-bold text-mango hover:text-yellow-600 flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Edit
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>

    <!-- Add Party Modal -->
    <div x-show="partyModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="partyModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <form :action="formAction" method="POST" class="p-8">
                    @csrf
                    <h3 class="text-2xl font-black text-gray-900 mb-6" x-text="editMode ? 'Edit Party' : 'Add New Party'"></h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Party Name</label>
                                <input type="text" name="name" x-model="party.name" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Party Type</label>
                                <select name="type" x-model="party.type" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                                    <option value="customer">Customer</option>
                                    <option value="supplier">Supplier</option>
                                    <option value="pathao">Delivery/Logistics</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                                <input type="text" name="phone" x-model="party.phone" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" x-model="party.email" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Address</label>
                            <input type="text" name="address" x-model="party.address" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Opening Balance</label>
                                <input type="number" name="opening_balance" x-model.number="party.opening_balance" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Current Balance</label>
                                <input type="number" name="current_balance" x-model.number="party.current_balance" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="partyModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm" x-text="editMode ? 'Update Party' : 'Save Party'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
