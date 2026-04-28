<form method="POST" action="{{ route('settings.store') }}">
    @csrf
    <input type="hidden" name="redirect_tab" value="erp">
    
    <!-- Legal Profile -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Legal Company Profile
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Legal Company Name</label>
                <input name="company_name" type="text" value="{{ setting('company_name', 'ChhitoPasal Pvt. Ltd.') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">VAT / PAN Number</label>
                <input name="vat_number" type="text" value="{{ setting('vat_number') }}" placeholder="e.g. 609876543" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Company Phone</label>
                <input name="company_phone" type="text" value="{{ setting('company_phone') }}" placeholder="e.g. 9800000000" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Company Email</label>
                <input name="company_email" type="email" value="{{ setting('company_email') }}" placeholder="e.g. info@chhitopasal.com" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-2">Billing Address</label>
                <textarea name="billing_address" rows="2" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-medium">{{ setting('billing_address', 'Kathmandu, Nepal') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Financial Defaults -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Financial Defaults
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Base Currency Symbol</label>
                <input name="currency_symbol" type="text" value="{{ setting('currency_symbol', 'Rs.') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Global VAT / Tax Rate (%)</label>
                <input name="tax_rate" type="number" step="0.01" value="{{ setting('tax_rate', '13') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                <p class="text-xs text-gray-400 mt-1">Leave 0 if not applicable.</p>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Default Cash Account (POS Sales)</label>
                <select name="default_cash_account_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold">
                    <option value="">-- Select Account --</option>
                    @if(isset($accounts))
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ setting('default_cash_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ $acc->type }})</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Default Bank Account (Pathao Settlements)</label>
                <select name="default_bank_account_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold">
                    <option value="">-- Select Account --</option>
                    @if(isset($accounts))
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ setting('default_bank_account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ $acc->type }})</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    <!-- Invoice Formatting -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 mb-8">
        <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
            Invoice Formatting
        </h3>
        
        <div class="grid grid-cols-1 gap-8">
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Order Invoice Prefix</label>
                    <input name="order_invoice_prefix" type="text" value="{{ setting('order_invoice_prefix', 'ORD-') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">POS Invoice Prefix</label>
                    <input name="pos_invoice_prefix" type="text" value="{{ setting('pos_invoice_prefix', 'POS-') }}" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-bold" />
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Invoice Terms & Conditions (Footer)</label>
                <textarea name="invoice_terms" rows="3" class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 font-medium">{{ setting('invoice_terms', '1. Goods once sold will not be returned.
2. Subject to Kathmandu jurisdiction.') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end sticky bottom-8">
        <button type="submit" class="bg-mango text-gray-900 font-black py-4 px-10 rounded-2xl shadow-sm hover:bg-yellow-400 transition-colors text-lg">
            Save ERP Settings
        </button>
    </div>
</form>
