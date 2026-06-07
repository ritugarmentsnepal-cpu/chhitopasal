<x-app-layout>
  <x-slot name="header">
    <div class="flex justify-between items-center">
      <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
        {{ __('Expenses') }}
      </h2>
      <button x-data="" @click="$dispatch('open-modal', 'add-expense')" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-5 rounded-xl transition-all active:scale-95 shadow-[0_8px_20px_rgb(17,24,39,0.2)] flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Record Expense
      </button>
    </div>
  </x-slot>

  <div class="py-6" x-data="expenseManager()">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      
      @if(session('success'))
        <div class="mb-6 bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <p class="font-bold">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <p class="font-bold">{{ session('error') }}</p>
        </div>
      @endif

      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white">
                <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Category</th>
                <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Description</th>
                <th class="py-4 px-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50/50 transition-colors">
                  <td class="py-4 px-6 font-bold text-gray-900">{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</td>
                  <td class="py-4 px-6 font-bold text-gray-700">
                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md bg-gray-100 text-gray-700">{{ $expense->category }}</span>
                  </td>
                  <td class="py-4 px-6 font-black text-mango">Rs. {{ number_format($expense->amount, 2) }}</td>
                  <td class="py-4 px-6 font-medium text-gray-500">{{ $expense->description ?? '-' }}</td>
                  <td class="py-4 px-6 text-right">
                    @if(auth()->user()->role === 'admin')
                      <button @click="openEditModal({{ $expense }})" class="text-wildOrchid hover:text-gray-900 font-bold text-sm transition-colors mr-3">Edit</button>
                      <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this expense?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm transition-colors">Delete</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="py-12 text-center text-gray-400 font-medium">No expenses recorded yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add Expense Modal -->
      <x-modal name="add-expense" focusable>
        <form method="POST" action="{{ route('expenses.store') }}" class="p-8">
          @csrf
          <h2 class="text-2xl font-black text-gray-900 mb-6">Record Expense</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Date</label>
              <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Category</label>
              <input type="text" name="category" placeholder="e.g. Rent, Utilities, Courier" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Amount (Rs.)</label>
              <input type="number" step="0.01" name="amount" placeholder="0.00" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Description (Optional)</label>
              <textarea name="description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors"></textarea>
            </div>
          </div>

          <div class="mt-8 flex justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-[0_8px_20px_rgb(17,24,39,0.2)] active:scale-95">Save Expense</button>
          </div>
        </form>
      </x-modal>

      <!-- Edit Expense Modal -->
      <x-modal name="edit-expense" focusable x-show="editModalOpen" @close="editModalOpen = false">
        <form :action="`{{ url('expenses') }}/${editingExpense?.id}`" method="POST" class="p-8">
          @csrf @method('PUT')
          <h2 class="text-2xl font-black text-gray-900 mb-6">Edit Expense</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Date</label>
              <input type="date" name="date" x-model="formData.date" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Category ID</label>
              <input type="number" name="expense_category_id" x-model="formData.expense_category_id" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Amount (Rs.)</label>
              <input type="number" step="0.01" name="amount" x-model="formData.amount" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Description</label>
              <textarea name="description" x-model="formData.description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm py-3 focus:border-gray-900 focus:ring focus:ring-gray-900/10 font-medium transition-colors"></textarea>
            </div>
          </div>

          <div class="mt-8 flex justify-end gap-3">
            <button type="button" @click="$dispatch('close')" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-[0_8px_20px_rgb(17,24,39,0.2)] active:scale-95">Update Expense</button>
          </div>
        </form>
      </x-modal>
    </div>
  </div>

  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('expenseManager', () => ({
        editModalOpen: false,
        editingExpense: null,
        formData: {
          category: '',
          amount: '',
          date: '',
          description: ''
        },

        openEditModal(expense) {
          this.editingExpense = expense;
          this.formData.category = expense.category;
          this.formData.amount = expense.amount;
          this.formData.date = expense.date;
          this.formData.description = expense.description;
          this.editModalOpen = true;
          this.$dispatch('open-modal', 'edit-expense');
        }
      }))
    })
  </script>
</x-app-layout>
