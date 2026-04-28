<form method="POST" action="{{ route('settings.store') }}">
    @csrf
    <input type="hidden" name="redirect_tab" value="automation">
    
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Inventory & Operations Automation
        </h3>
        
        <div class="space-y-8">
            <!-- Low Stock Threshold -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Global Low Stock Alert Threshold</label>
                <div class="flex items-center gap-2">
                    <input name="low_stock_threshold" type="number" min="0" value="{{ setting('low_stock_threshold', '10') }}" class="block w-32 rounded-xl border-gray-200 bg-gray-50 py-3 font-bold text-center" />
                    <span class="text-sm font-medium text-gray-500">units</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">Products whose inventory falls below this number will be flagged for restocking in the dashboard.</p>
            </div>
            
            <div class="border-t border-gray-100 pt-8">
                <label class="block text-sm font-bold text-gray-700 mb-2">Default Web Order Status</label>
                <select name="default_order_status" class="block w-full max-w-sm rounded-xl border-gray-200 bg-gray-50 py-3 font-bold">
                    <option value="pending" {{ setting('default_order_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending (Needs manual review)</option>
                    <option value="processing" {{ setting('default_order_status') == 'processing' ? 'selected' : '' }}>Processing (Ready for Pathao)</option>
                </select>
                <p class="text-xs text-gray-400 mt-2">Status applied automatically to new orders from the storefront.</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end sticky bottom-8">
        <button type="submit" class="bg-mango text-gray-900 font-black py-4 px-10 rounded-2xl shadow-sm hover:bg-yellow-400 transition-colors text-lg">
            Save Automation Settings
        </button>
    </div>
</form>
