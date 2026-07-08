    <x-modal name="settlement-modal" focusable>
      <div class="p-8">
        <div class="mb-6 border-b border-gray-100 pb-4">
          <h2 class="text-2xl font-black text-gray-900 ">Record Pathao Settlement</h2>
          <p class="text-sm text-gray-500 mt-1">Record the COD bulk amount deposited by Pathao.</p>
        </div>
        
        <form method="POST" action="{{ route('pathao.settlement') }}" class="space-y-5">
          @csrf
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Net Deposited Amount (Rs.)</label>
              <input type="number" name="amount" step="0.01" min="0" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 text-lg font-black transition-colors" required placeholder="e.g. 45000">
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Delivery Charges Taken (Rs.)</label>
              <input type="number" name="delivery_charge" step="0.01" min="0" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 text-lg font-black text-red-600 transition-colors" placeholder="e.g. 5000">
            </div>
          </div>

          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Deposit To</label>
            <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
              <option value="">Select Bank Account...</option>
              @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Date</label>
              <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
            </div>
            <div>
              <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Reference ID (Optional)</label>
              <input type="text" name="reference" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" placeholder="Bank Txn ID">
            </div>
          </div>

          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Notes</label>
            <input type="text" name="notes" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" placeholder="e.g. Bulk settlement for 12 orders">
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
            <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 transition active:scale-95">Save Settlement</button>
          </div>
        </form>
      </div>
    </x-modal>
