{{-- Custom Print Order Creation Modal --}}
<x-modal name="add-custom-print-modal" :show="false" maxWidth="3xl">
  <form method="POST" action="{{ route('orders.storeCustomPrint') }}" enctype="multipart/form-data" class="p-6" x-data="{
    sizeBreakdown: { S: 0, M: 0, L: 0, XL: 0, '2XL': 0, '3XL': 0 },
    customSizes: '',
    selectedPositions: [],
    positionLabels: { front: 'Front', back: 'Back', left_sleeve: 'Left Sleeve', right_sleeve: 'Right Sleeve', pocket: 'Pocket' },
    totalQty() {
      let sum = Object.values(this.sizeBreakdown).reduce((a, b) => parseInt(a || 0) + parseInt(b || 0), 0);
      if (this.customSizes) {
        this.customSizes.split(',').forEach(p => {
          const parts = p.trim().split(':');
          if (parts.length === 2) sum += parseInt(parts[1]) || 0;
        });
      }
      return sum;
    }
  }">
    @csrf

    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-3">
      <span class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
      </span>
      New Custom Print Order
    </h3>

    <div class="space-y-6">
      {{-- Customer Info --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Customer Name *</label>
          <input type="text" name="customer_name" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Phone *</label>
          <input type="text" name="customer_phone" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Address *</label>
          <input type="text" name="address" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
        </div>
        <div>
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">City</label>
          <input type="text" name="city" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
        </div>
      </div>

      {{-- Product & Print Config --}}
      <div class="border-t border-gray-100 pt-6">
        <h4 class="font-black text-gray-900 mb-4">Print Configuration</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Blank T-Shirt (Product) *</label>
            <select name="product_id" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
              <option value="">Select product...</option>
              @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Print Method *</label>
            <select name="print_method" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
              <option value="dtf">DTF (Direct to Film)</option>
              <option value="screen_print">Screen Print</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Print Positions *</label>
          <div class="flex flex-wrap gap-3">
            <template x-for="(label, key) in positionLabels" :key="key">
              <label class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200 cursor-pointer hover:bg-purple-50 hover:border-purple-200 transition">
                <input type="checkbox" name="print_positions[]" :value="key" x-model="selectedPositions" class="rounded text-purple-600 focus:ring-purple-500">
                <span class="text-sm font-bold text-gray-700" x-text="label"></span>
              </label>
            </template>
          </div>
        </div>
      </div>

      {{-- Size Breakdown --}}
      <div class="border-t border-gray-100 pt-6">
        <h4 class="font-black text-gray-900 mb-4">Size Breakdown</h4>
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
          @foreach(['S', 'M', 'L', 'XL', '2XL', '3XL'] as $size)
            <div class="text-center">
              <label class="block text-xs font-black text-gray-400 uppercase mb-1">{{ $size }}</label>
              <input type="number" name="size_breakdown[{{ $size }}]" min="0" value="0"
                x-model.number="sizeBreakdown['{{ $size }}']"
                class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-bold text-center focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
            </div>
          @endforeach
        </div>
        <div class="mt-3">
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Custom Sizes <span class="normal-case text-gray-300">(format: 4XL:5, 5XL:3)</span></label>
          <input type="text" name="custom_sizes" x-model="customSizes" placeholder="e.g. 4XL:5, 5XL:3" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
        </div>
        <div class="mt-2 flex items-center gap-2">
          <span class="text-sm font-bold text-gray-500">Total Quantity:</span>
          <span class="text-lg font-black text-purple-600" x-text="totalQty()">0</span>
        </div>
        <input type="hidden" name="total_quantity" :value="totalQty() || 1">
      </div>

      {{-- Design Upload --}}
      <div class="border-t border-gray-100 pt-6">
        <h4 class="font-black text-gray-900 mb-4">Design & Notes</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Design Files</label>
            
            <div x-show="selectedPositions.length === 0" class="text-sm text-gray-400 italic py-2">
              Select print positions above to upload files.
            </div>

            <div class="space-y-3">
              <template x-for="pos in selectedPositions" :key="pos">
                <div class="bg-white border border-gray-200 p-3 rounded-xl flex items-center justify-between gap-3 shadow-sm">
                  <div class="shrink-0 font-bold text-sm text-gray-700 w-24" x-text="positionLabels[pos]"></div>
                  <input type="file" :name="`design_files[${pos}]`" accept="*/*"
                    class="w-full text-xs font-medium file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                </div>
              </template>
            </div>
            
            <p class="text-[10px] text-gray-400 mt-2">Any file type accepted. Max 20MB per file.</p>
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Estimated Delivery Date</label>
            <input type="date" name="estimated_delivery_date" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Design Notes / Instructions</label>
          <textarea name="design_notes" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10" placeholder="Print placement details, color requirements, special instructions..."></textarea>
        </div>
      </div>

      {{-- Pricing --}}
      <div class="border-t border-gray-100 pt-6">
        <h4 class="font-black text-gray-900 mb-4">Pricing</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Total Amount (Rs.) *</label>
            <input type="number" name="total_amount" step="0.01" min="0" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-bold focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Advance Received (Rs.)</label>
            <input type="number" name="advance_amount" step="0.01" min="0" value="0" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-bold focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5">
          </div>
          <div>
            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1">Remarks</label>
            <input type="text" name="remarks" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium focus:border-purple-500 focus:ring focus:ring-purple-500/10 py-2.5" placeholder="Internal notes...">
          </div>
        </div>
      </div>
    </div>

    <div class="mt-8 flex justify-end gap-3">
      <button type="button" x-on:click="$dispatch('close')" class="px-6 py-2.5 rounded-xl font-bold text-gray-500 hover:bg-gray-100 transition">Cancel</button>
      <button type="submit" class="bg-purple-600 text-white font-black px-8 py-2.5 rounded-xl shadow-lg hover:bg-purple-700 transition active:scale-95">
        Create Print Order
      </button>
    </div>
  </form>
</x-modal>
