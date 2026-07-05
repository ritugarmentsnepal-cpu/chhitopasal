    <!-- Pathao Tracking Detail Modal -->
    <div x-show="trackingModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="tracking-modal" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="trackingModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeTrackingModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="trackingModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">

          <!-- Header -->
          <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50 flex justify-between items-center">
            <div>
              <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                Order #<span x-text="trackingData?.order?.id"></span>
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">Last synced: <span x-text="trackingData?.status_updated_at || 'Never'" class="font-bold"></span></p>
            </div>
            <button type="button" @click="closeTrackingModal()" class="text-gray-400 hover:text-gray-900 transition"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
          </div>

          <!-- Loading state -->
          <div x-show="trackingLoading" class="p-12 text-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <p class="font-bold text-gray-500">Fetching live status from Pathao...</p>
          </div>

          <!-- Content -->
          <div x-show="!trackingLoading && trackingData" class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

            <!-- Status Timeline -->
            <div class="bg-gradient-to-r from-gray-50 to-indigo-50/30 p-5 rounded-2xl border border-gray-100">
              <div class="flex items-center justify-between mb-4">
                <h4 class="font-black text-gray-900 text-sm">Delivery Progress</h4>
                <span class="px-3 py-1 rounded-full text-xs font-bold" :class="getStatusBadgeClass(trackingData?.pathao?.status)" x-text="trackingData?.pathao?.status || 'Unknown'"></span>
              </div>
              <div class="flex items-center gap-0">
                <template x-for="(step, idx) in trackingSteps" :key="idx">
                  <div class="flex items-center" :class="idx < trackingSteps.length - 1 ? 'flex-1' : ''">
                    <div class="flex flex-col items-center">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300"
                         :class="getStepIndex() >= idx ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-gray-200 text-gray-500'">
                        <span x-text="idx + 1"></span>
                      </div>
                      <span class="text-[10px] font-bold mt-1.5 text-center leading-tight w-16" :class="getStepIndex() >= idx ? 'text-indigo-700' : 'text-gray-400'" x-text="step"></span>
                    </div>
                    <div x-show="idx < trackingSteps.length - 1" class="h-0.5 flex-1 mx-1 mt-[-16px] rounded-full transition-all duration-300" :class="getStepIndex() > idx ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Info Cards Row -->
            <div class="grid grid-cols-2 gap-4">
              <!-- Shipment Info -->
              <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                  Shipment Info
                </h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between"><span class="text-gray-500">Tracking ID</span><span class="font-bold text-gray-900 text-xs" x-text="trackingData?.order?.pathao_consignment_id"></span></div>
                  <div class="flex justify-between"><span class="text-gray-500">Shipped</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.shipped_date"></span></div>
                  <div class="flex justify-between"><span class="text-gray-500">Items</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.item_count"></span></div>
                  <div class="flex justify-between"><span class="text-gray-500">Weight</span><span class="font-bold text-gray-900" x-text="(trackingData?.order?.weight_kg || 0).toFixed(1) + ' kg'"></span></div>
                </div>
              </div>
              <!-- Customer Info -->
              <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  Customer
                </h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between"><span class="text-gray-500">Name</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.customer_name"></span></div>
                  <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.customer_phone"></span></div>
                  <div><span class="text-gray-500 text-xs">Address</span><div class="font-bold text-gray-900 text-xs mt-0.5" x-text="(trackingData?.order?.address || '') + (trackingData?.order?.city ? ', ' + trackingData.order.city : '')"></div></div>
                </div>
              </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider">Order Items</h4>
              </div>
              <table class="w-full text-sm">
                <thead><tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest"><th class="px-4 py-2 text-left">Product</th><th class="px-4 py-2 text-center">Qty</th><th class="px-4 py-2 text-right">Amount</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                  <template x-for="item in trackingData?.order?.items || []" :key="item.name">
                    <tr>
                      <td class="px-4 py-2.5 font-bold text-gray-900" x-text="item.name"></td>
                      <td class="px-4 py-2.5 text-center text-gray-600" x-text="item.quantity"></td>
                      <td class="px-4 py-2.5 text-right font-bold text-gray-900" x-text="'Rs. ' + Number(item.total).toLocaleString()"></td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <!-- Financial Summary -->
            <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-5 rounded-2xl text-white">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div>
                  <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Subtotal</p>
                  <p class="text-lg font-black mt-0.5" x-text="'Rs. ' + Number(trackingData?.order?.total_amount || 0).toLocaleString()"></p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Delivery</p>
                  <p class="text-lg font-black mt-0.5" x-text="'Rs. ' + Number(trackingData?.order?.delivery_charge || 0).toLocaleString()"></p>
                </div>
                <div>
                  <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Advance Paid</p>
                  <p class="text-lg font-black mt-0.5 text-green-400" x-text="'Rs. ' + Number(trackingData?.order?.paid_amount || 0).toLocaleString()"></p>
                </div>
                <div class="bg-white/10 rounded-xl p-2">
                  <p class="text-[10px] text-yellow-300 uppercase font-bold tracking-wider">COD Collect</p>
                  <p class="text-lg font-black mt-0.5 text-yellow-300" x-text="'Rs. ' + (Number(trackingData?.order?.total_amount || 0) - Number(trackingData?.order?.paid_amount || 0)).toLocaleString()"></p>
                </div>
              </div>
            </div>

            <!-- Delivery Rider Info -->
            <div x-show="trackingData?.pathao?.rider_name" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
              <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Delivery Rider
              </h4>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center border border-orange-100">
                  <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <div class="flex-1">
                  <div class="font-bold text-gray-900 text-sm" x-text="trackingData?.pathao?.rider_name"></div>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm text-gray-500" x-text="trackingData?.pathao?.rider_phone"></span>
                    <a x-show="trackingData?.pathao?.rider_phone" :href="'tel:' + trackingData?.pathao?.rider_phone" class="inline-flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2.5 py-1 rounded-lg border border-green-100 hover:bg-green-100 transition">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                      Call
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delivery Comments & Remarks -->
            <div x-show="trackingData?.pathao?.comments || trackingData?.pathao?.failed_reason" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                  Delivery Comments
                </h4>
              </div>
              <div class="p-4 space-y-3">
                <div x-show="trackingData?.pathao?.comments">
                  <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Pathao Remarks</div>
                  <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-sm text-blue-900 font-medium" x-text="trackingData?.pathao?.comments"></div>
                </div>
                <div x-show="trackingData?.pathao?.failed_reason">
                  <div class="text-[10px] text-red-400 uppercase font-bold tracking-wider mb-1">Failed Delivery Reason</div>
                  <div class="bg-red-50 border border-red-100 rounded-xl p-3 text-sm text-red-800 font-medium flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span x-text="trackingData?.pathao?.failed_reason"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer Actions -->
          <div x-show="!trackingLoading" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2 justify-between items-center">
            <div class="flex gap-2">
              <button @click="copyTrackingId()" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-50 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                Copy Tracking ID
              </button>
              <a x-show="trackingData?.order?.id" :href="`{{ url('orders') }}/${trackingData?.order?.id}/print-label?type=both`" target="_blank" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-50 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Print Label
              </a>
            </div>
            <div class="flex gap-2 items-center">
              <span x-show="trackingData?.status_updated_at" class="text-[10px] text-gray-400 font-medium" x-text="'Updated ' + (trackingData?.status_updated_at || '')"></span>
              <button @click="refreshTrackingStatus()" :disabled="refreshCooldown > 0 || trackingLoading" :class="refreshCooldown > 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'" class="px-4 py-2 text-white font-bold rounded-xl text-xs shadow-sm transition active:scale-95 flex items-center gap-1.5 disabled:opacity-70">
                <svg class="w-3.5 h-3.5" :class="trackingLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span x-text="refreshCooldown > 0 ? 'Wait ' + refreshCooldown + 's' : 'Sync Status'"></span>
              </button>
              <button @click="closeTrackingModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-300 transition">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>

