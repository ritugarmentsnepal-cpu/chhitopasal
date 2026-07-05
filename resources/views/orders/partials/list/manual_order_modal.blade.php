    <!-- Manual Single Order Modal -->
    <!-- Just basic placeholder to not break the UI flow -->
    <x-modal name="add-order-modal" focusable>
      <div class="p-8">
        <h2 class="text-2xl font-black text-gray-900">Add Manual Order</h2>
        <p class="text-sm text-gray-500 mt-1">Use the bulk uploader for multiple orders.</p>
        <form method="POST" action="{{ route('orders.store') }}" class="mt-6 space-y-4">
          @csrf
          <!-- Normal form fields... omitted for brevity as bulk upload is preferred -->
          <input type="text" name="customer_name" placeholder="Name" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
          <input type="text" name="customer_phone" placeholder="Phone" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
          <input type="text" name="address" placeholder="Address" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
          <input type="text" name="city" placeholder="City (Optional)" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors">
          <select name="product_id" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
            @foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach
          </select>
          <input type="number" name="quantity" value="1" min="1" class="w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required>
          
          <div class="flex justify-end gap-2 pt-4">
            <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition px-4 py-2">Cancel</button>
            <button type="submit" class="bg-gray-900 text-white font-bold rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition px-4 py-2">Save</button>
          </div>
        </form>
      </div>
    </x-modal>

