    <!-- Full Edit Order Modal -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
          
          <form :action="`{{ url('orders') }}/${selectedEditOrder?.id}/full-update`" method="POST" @submit="validateEditForm">
            @csrf
            @method('PUT')
            
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
              <h3 class="text-xl font-black text-gray-900">Edit Order #<span x-text="selectedEditOrder?.id"></span></h3>
              <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>

            <div class="p-6 space-y-6 max-h-[65vh] overflow-y-auto">
              <!-- Customer Details -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Customer Name</label>
                  <input name="customer_name" x-model="editFormData.customer_name" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
                </div>
                <div>
                  <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
                  <input name="customer_phone" x-model="editFormData.customer_phone" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
                </div>
              </div>

              <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                <h4 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                  Delivery Details
                </h4>
                <div class="mb-3">
                  <div>
                    <label class="block text-xs font-bold text-blue-800 mb-1">Street Address</label>
                    <input name="address" x-model="editFormData.address" type="text" class="w-full rounded-xl border-blue-200 bg-white py-2 focus:ring-blue-500" required>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-bold text-blue-800 mb-1">Pathao City</label>
                    <select name="pathao_city_id" x-model="editFormData.pathao_city_id" @change="fetchEditZones()" class="w-full rounded-xl border-blue-200 bg-white py-2 text-sm focus:ring-blue-500">
                      <option value="">Select City</option>
                      <template x-for="city in cities" :key="city.city_id">
                        <option :value="city.city_id" x-text="city.city_name"></option>
                      </template>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-blue-800 mb-1">Pathao Zone</label>
                    <select name="pathao_zone_id" x-model="editFormData.pathao_zone_id" class="w-full rounded-xl border-blue-200 bg-white py-2 text-sm focus:ring-blue-500" :disabled="!editZones.length">
                      <option value="">Select Zone</option>
                      <template x-for="zone in editZones" :key="zone.zone_id">
                        <option :value="zone.zone_id" x-text="zone.zone_name"></option>
                      </template>
                    </select>
                  </div>
                </div>
              </div>
              
              @if(auth()->user()->role === 'admin')
              <div class="bg-red-50 p-4 rounded-2xl border border-red-100">
                <label class="block text-sm font-bold text-red-900 mb-1">Admin Only: Override Status</label>
                <select name="status" x-model="editFormData.status" class="w-full rounded-xl border-red-200 bg-white py-2 focus:ring-red-500 font-bold text-red-700">
                  <option value="pending">Pending</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="shipped">Shipped</option>
                  <option value="delivered">Delivered</option>
                  <option value="return_delivered">Returned</option>
                  <option value="failed">Failed</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>
              @endif

              <!-- Order Items -->
              <div>
                <h4 class="font-bold text-gray-900 mb-3 border-b border-gray-100 pb-2">Order Items & Pricing</h4>
                <template x-if="selectedEditOrder">
                  <div class="space-y-3">
                    <template x-for="(item, index) in editFormData.items" :key="index">
                      <div class="flex items-start gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100 relative">
                        <div class="flex-1">
                          <label class="block text-xs font-bold text-gray-500 mb-1">Product</label>
                          <select :name="`items[${index}][_selection]`" x-model="item.selection" @change="onProductChange(index)" class="w-full rounded-lg border-gray-200 py-1.5 px-2 font-bold focus:ring-mango text-sm" required>
                            <option value="" disabled>Select Product</option>
                            @foreach($products as $product)
                              <option value="{{ $product->id }}:1">{{ $product->name }}</option>
                              @if($product->bundles)
                                @foreach($product->bundles as $bundle)
                                  <option value="{{ $product->id }}:{{ $bundle['qty'] }}">{{ $product->name }} ({{ $bundle['qty'] }}-Pack — Rs.{{ number_format($bundle['price']) }})</option>
                                @endforeach
                              @endif
                            @endforeach
                          </select>
                          <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                          <input type="hidden" :name="`items[${index}][id]`" :value="item.id" x-show="item.id">
                          <input type="hidden" :name="`items[${index}][color]`" :value="item.color || ''">
                          <input type="hidden" :name="`items[${index}][size]`" :value="item.size || ''">
                          <!-- Variant badges -->
                          <div x-show="item.color || item.size" class="flex gap-1.5 mt-1.5 flex-wrap">
                            <span x-show="item.color" class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-purple-100" x-text="item.color"></span>
                            <span x-show="item.size" class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-blue-100" x-text="'Size: ' + item.size"></span>
                          </div>
                        </div>
                        <div class="w-20">
                          <label class="block text-xs font-bold text-gray-500 mb-1">Qty</label>
                          <input :name="`items[${index}][quantity]`" x-model="item.quantity" @input="onQuantityChange(index)" type="number" min="1" class="w-full rounded-lg border-gray-200 py-1.5 px-2 text-center font-bold focus:ring-mango text-sm" required>
                        </div>
                        <div class="w-28">
                          <label class="block text-xs font-bold text-gray-500 mb-1">Total (Rs.)</label>
                          <input :name="`items[${index}][total_price]`" x-model="item.total_price" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-200 py-1.5 px-2 text-center font-bold focus:ring-mango text-sm" required>
                        </div>
                        <button type="button" @click="removeProduct(index)" class="mt-5 p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition" title="Remove Product">
                          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                      </div>
                    </template>
                    <button type="button" @click="addProduct()" class="w-full mt-3 py-2 border-2 border-dashed border-gray-200 text-gray-500 font-bold rounded-xl hover:border-mango hover:text-mango transition flex items-center justify-center gap-2">
                      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      Add Product
                    </button>
                  </div>
                </template>
                </div>
                
                <!-- Delivery Charge & Total Override -->
                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                  <div class="flex items-center justify-between gap-4">
                    <div class="w-1/2">
                      <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Delivery Charge (Rs.)</label>
                      <input name="delivery_charge" x-model.number="editFormData.delivery_charge" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors text-gray-900">
                      <p class="text-xs text-gray-500 mt-1">Can be modified or waived.</p>
                    </div>
                    <div class="w-1/2 text-right">
                      <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Grand Total</label>
                      <div class="text-2xl font-black text-gray-900">Rs.<span x-text="calculateEditGrandTotal()"></span></div>
                    </div>
                  </div>
                </div>
                
                <div class="mt-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                  <label class="block text-sm font-bold text-yellow-800 mb-1">Internal Remarks</label>
                  <textarea name="remarks" x-model="editFormData.remarks" rows="2" class="w-full rounded-xl border-yellow-300 bg-white py-2 focus:ring-yellow-500 placeholder-yellow-400 text-sm" placeholder="Add any special notes or remarks here..."></textarea>
                </div>
              </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
              <button type="button" @click="closeEditModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
              
              <template x-if="selectedEditOrder?.status === 'pending'">
                <button type="submit" name="confirm_order" value="1" class="px-5 py-2.5 bg-mango text-gray-900 font-bold rounded-xl shadow-lg hover:bg-[#FFC033] transition active:scale-95" @click="isConfirming = true">Save & Confirm Order</button>
              </template>
              
              <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95" x-text="selectedEditOrder?.status === 'pending' ? 'Save Draft' : 'Save Changes'" @click="isConfirming = false"></button>
            </div>
          </form>
        </div>
      </div>
    </div>


