    <!-- Return Verification Modal -->
    <div x-show="returnModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="return-modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="returnModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeReturnModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="returnModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
          <template x-if="returnOrder">
            <div>
              <!-- Header -->
              <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50 flex justify-between items-start">
                <div>
                  <h3 class="text-xl font-black text-gray-900 flex items-center gap-2" id="return-modal-title">
                    <span class="w-8 h-8 bg-orange-100 rounded-xl flex items-center justify-center">
                      <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </span>
                    Verify Return
                  </h3>
                  <p class="text-sm text-gray-500 mt-1 ml-10">
                    Order <span class="font-black text-gray-700">#<span x-text="String(returnOrder?.id).padStart(5, '0')"></span></span>
                    &bull; <span x-text="returnOrder?.customer_name"></span>
                    &bull; <span class="font-bold text-orange-600">Rs.<span x-text="Number(returnOrder?.total_amount).toLocaleString()"></span></span>
                  </p>
                </div>
                <button type="button" @click="closeReturnModal()" class="text-gray-400 hover:text-gray-900 transition p-1">
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>

              <!-- Body -->
              <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto">

                <!-- Quick Fill Buttons -->
                <div class="flex items-center gap-2">
                  <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Quick Fill:</span>
                  <button type="button" @click="setAllGood()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    All Good
                  </button>
                  <button type="button" @click="setAllDamaged()" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition active:scale-95">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    All Damaged
                  </button>
                </div>

                <!-- Item Inspection Cards -->
                <div class="space-y-3">
                  <template x-for="(item, idx) in returnItems" :key="idx">
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 hover:border-gray-200 transition">
                      <div class="flex items-start justify-between mb-3">
                        <div>
                          <p class="font-bold text-gray-900 text-sm" x-text="item.product_name"></p>
                          <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-gray-500">Ordered: <span class="font-bold text-gray-700" x-text="item.original_qty"></span> pcs</span>
                            <span class="text-xs text-gray-400">&bull;</span>
                            <span class="text-xs text-gray-500">Rs.<span x-text="Number(item.unit_price).toLocaleString()"></span>/pc</span>
                            <template x-if="item.color || item.size">
                              <span class="text-xs text-gray-400">
                                &bull; <span x-show="item.color" x-text="item.color"></span><span x-show="item.color && item.size"> / </span><span x-show="item.size" x-text="item.size"></span>
                              </span>
                            </template>
                          </div>
                        </div>
                        <div class="text-right">
                          <template x-if="(parseInt(item.good_qty)||0) + (parseInt(item.damaged_qty)||0) < item.original_qty">
                            <span class="text-[10px] font-bold text-yellow-600 bg-yellow-50 px-2 py-0.5 rounded-full border border-yellow-200">
                              <span x-text="item.original_qty - (parseInt(item.good_qty)||0) - (parseInt(item.damaged_qty)||0)"></span> missing
                            </span>
                          </template>
                        </div>
                      </div>

                      <div class="grid grid-cols-2 gap-3">
                        <!-- Good Qty -->
                        <div>
                          <label class="flex items-center gap-1.5 text-[10px] font-black text-green-600 uppercase tracking-wider mb-1.5">
                            <span class="w-4 h-4 bg-green-100 rounded flex items-center justify-center">
                              <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </span>
                            Good (Restock)
                          </label>
                          <input type="number" x-model.number="item.good_qty" @input="clampReturnQty(item, 'good_qty')" min="0" :max="item.original_qty" class="w-full rounded-xl border-green-200 bg-green-50/50 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500/10 py-2.5 text-center font-bold text-green-700 text-sm transition-colors">
                        </div>
                        <!-- Damaged Qty -->
                        <div>
                          <label class="flex items-center gap-1.5 text-[10px] font-black text-red-600 uppercase tracking-wider mb-1.5">
                            <span class="w-4 h-4 bg-red-100 rounded flex items-center justify-center">
                              <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </span>
                            Damaged (Write-off)
                          </label>
                          <input type="number" x-model.number="item.damaged_qty" @input="clampReturnQty(item, 'damaged_qty')" min="0" :max="item.original_qty" class="w-full rounded-xl border-red-200 bg-red-50/50 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500/10 py-2.5 text-center font-bold text-red-700 text-sm transition-colors">
                        </div>
                      </div>

                      <!-- Validation error -->
                      <template x-if="(parseInt(item.good_qty)||0) + (parseInt(item.damaged_qty)||0) > item.original_qty">
                        <p class="mt-2 text-xs font-bold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg border border-red-200">
                          ⚠️ Total exceeds ordered quantity (<span x-text="item.original_qty"></span>)
                        </p>
                      </template>
                    </div>
                  </template>
                </div>

                <!-- Return Notes -->
                <div>
                  <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Return Notes</label>
                  <textarea x-model="returnNotes" rows="3" placeholder="Describe the condition of returned items, packaging, any remarks..." class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 px-4 font-medium text-sm transition-colors resize-none"></textarea>
                </div>

                <!-- Summary -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-5 text-white">
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Return Summary</p>
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white/10 rounded-xl p-3 text-center">
                      <p class="text-2xl font-black text-green-400" x-text="returnSummary.totalGood"></p>
                      <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Restock</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-3 text-center">
                      <p class="text-2xl font-black text-red-400" x-text="returnSummary.totalDamaged"></p>
                      <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Damaged</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-3 text-center">
                      <p class="text-2xl font-black text-yellow-400" x-text="returnSummary.totalNotReturned"></p>
                      <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Missing</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-3 text-center">
                      <p class="text-lg font-black text-white">Rs.<span x-text="Number(returnSummary.refundAmount).toLocaleString()"></span></p>
                      <p class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">Full Reversal</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                <p class="text-xs text-gray-400 font-medium">Stock will be updated for good items only</p>
                <div class="flex gap-3">
                  <button type="button" @click="closeReturnModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                  <button type="button" @click="submitReturn()" :disabled="returnSubmitting || returnHasErrors" class="px-5 py-2.5 bg-orange-600 text-white font-bold rounded-xl shadow-lg hover:bg-orange-700 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg x-show="returnSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span x-text="returnSubmitting ? 'Verifying...' : 'Confirm Return'"></span>
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
