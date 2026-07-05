    <!-- Payment Modal -->
    <div x-show="paymentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="paymentModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closePaymentModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="paymentModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
          <template x-if="paymentOrder">
            <form :action="`{{ url('orders') }}/${paymentOrder?.id}/payment`" method="POST">
              @csrf
              
              <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-xl font-black text-gray-900">Record Payment #<span x-text="paymentOrder?.id"></span></h3>
                <button type="button" @click="closePaymentModal()" class="text-gray-400 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
              </div>

              <div class="p-6 space-y-6">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-center flex justify-between">
                  <div class="text-sm text-gray-500 font-bold">Total: Rs.<span x-text="paymentOrder?.total_amount"></span></div>
                  <div class="text-sm text-gray-500 font-bold text-green-600">Paid: Rs.<span x-text="paymentOrder?.paid_amount || 0"></span></div>
                </div>

                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Payment Method</label>
                  <select name="payment_method" x-model="paymentMethod" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors text-sm" required>
                    <option value="cod">Cash on Delivery (Pathao)</option>
                    <option value="paid">Fully Paid (Advance)</option>
                    <option value="partial">Partially Paid (Advance)</option>
                  </select>
                </div>

                <div x-show="paymentMethod === 'paid' || paymentMethod === 'partial'" x-transition>
                  <div class="mb-4">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Amount to Pay</label>
                    <input name="amount" x-model="paymentAmount" type="number" step="0.01" min="0" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" :required="paymentMethod !== 'cod'">
                  </div>

                  <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Deposit To Account</label>
                    <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" :required="paymentMethod !== 'cod'">
                      <option value="">Select Account...</option>
                      @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} (Rs. {{ number_format($account->balance, 2) }})</option>
                      @endforeach
                    </select>
                  </div>
                  
                  <div class="mt-4">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Notes (Optional)</label>
                    <input name="notes" type="text" placeholder="e.g. Fonepay, Bank Transfer" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors">
                  </div>
                </div>
              </div>

              <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" @click="closePaymentModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white font-bold rounded-xl shadow-lg hover:bg-green-700 transition active:scale-95">Save Payment</button>
              </div>
            </form>
          </template>
        </div>
      </div>
    </div>

