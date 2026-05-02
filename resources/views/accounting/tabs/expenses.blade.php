<div class="space-y-8" x-data="{ expenseModal: false, categoryModal: false }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-2xl font-black text-gray-900">Expenses</h3>
        <div class="flex gap-2 sm:space-x-3">
            <button @click="categoryModal = true" class="bg-gray-200 text-gray-700 font-bold py-2.5 px-4 sm:px-5 rounded-xl shadow-sm hover:bg-gray-300 transition-colors text-sm sm:text-base whitespace-nowrap">
                + Category
            </button>
            <button @click="expenseModal = true" class="bg-gray-900 text-white font-bold py-2.5 px-4 sm:px-5 rounded-xl shadow-sm hover:bg-gray-800 transition-colors text-sm sm:text-base whitespace-nowrap">
                + Expense
            </button>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['expenses'] as $expense)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ $expense->category ? $expense->category->name : 'Uncategorized' }}</div>
                        @if($expense->attachment_path)
                            <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                View Receipt
                            </a>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">{{ $expense->description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="font-black text-gray-900">Rs. {{ number_format($expense->amount, 2) }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
      @if($data['expenses']->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $data['expenses']->appends(['tab' => 'expenses'])->links() }}
        </div>
      @endif
    </div>

    <!-- Record Expense Modal -->
    <div x-show="expenseModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="expenseModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    <h3 class="text-2xl font-black text-gray-900 mb-6">Record Expense</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Category</label>
                                <select name="expense_category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                                    @foreach($data['categories'] as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Payment Account</label>
                                <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                                    <option value="">Select Account...</option>
                                    @foreach($data['accounts'] as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} (Rs. {{ number_format($account->balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Amount (Rs.)</label>
                                <input type="number" name="amount" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Date</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Reference No</label>
                                <input type="text" name="reference_no" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Upload Receipt</label>
                            <input type="file" name="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-mango file:text-gray-900 hover:file:bg-yellow-400">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="expenseModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div x-show="categoryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 opacity-75" aria-hidden="true" @click="categoryModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('accounting.storeCategory') }}" method="POST" class="p-8">
                    @csrf
                    <h3 class="text-2xl font-black text-gray-900 mb-6">Add Expense Category</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Category Name</label>
                            <input type="text" name="name" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango focus:border-mango"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="categoryModal = false" class="px-5 py-2.5 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-sm">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
