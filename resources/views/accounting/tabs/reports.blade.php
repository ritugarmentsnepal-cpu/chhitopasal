<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-black text-gray-900">Financial Reports</h3>
    </div>

    <!-- Report Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
        <form action="{{ route('accounting.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="reports">
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Report Type</label>
                <select name="report_type" onchange="this.form.submit()" class="bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 pl-3 pr-8 text-sm font-bold">
                    <option value="pl" {{ $data['report_type'] === 'pl' ? 'selected' : '' }}>Profit & Loss (P&L)</option>
                    <option value="ledger" {{ $data['report_type'] === 'ledger' ? 'selected' : '' }}>Transaction Ledger</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $data['start_date'] }}" class="bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 px-3 text-sm font-bold">
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $data['end_date'] }}" class="bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 px-3 text-sm font-bold">
            </div>

            @if($data['report_type'] === 'ledger')
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Filter by Party</label>
                    <select name="party_id" class="bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 pl-3 pr-8 text-sm">
                        <option value="">All Parties</option>
                        @foreach($data['parties'] as $party)
                            <option value="{{ $party->id }}" {{ (isset($data['selected_party']) && $data['selected_party'] == $party->id) ? 'selected' : '' }}>{{ $party->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Filter by Account</label>
                    <select name="account_id" class="bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 pl-3 pr-8 text-sm">
                        <option value="">All Accounts</option>
                        @foreach($data['accounts'] as $acc)
                            <option value="{{ $acc->id }}" {{ (isset($data['selected_account']) && $data['selected_account'] == $acc->id) ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex gap-2">
                <button type="submit" class="bg-gray-900 text-white font-bold py-2 px-6 rounded-xl hover:bg-gray-800 transition">
                    Generate
                </button>
                <a href="{{ route('accounting.exportReport', request()->query()) }}" class="bg-green-100 text-green-800 hover:bg-green-200 font-bold py-2 px-4 rounded-xl transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export CSV
                </a>
            </div>
        </form>
    </div>

    @if($data['report_type'] === 'pl')
        <!-- P&L Statement -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-black text-xl text-gray-900">Income Statement (P&L)</h4>
                <span class="text-sm font-bold text-gray-500">{{ \Carbon\Carbon::parse($data['start_date'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($data['end_date'])->format('M d, Y') }}</span>
            </div>
            <div class="p-8 max-w-3xl mx-auto">
                <div class="space-y-4 text-lg">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="font-bold text-gray-700">Total Revenue (Sales)</span>
                        <span class="font-black text-gray-900">Rs. {{ number_format($data['pl_revenue'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100 text-red-500">
                        <span class="font-bold">Less: Cost of Goods Sold (COGS)</span>
                        <span class="font-black">(Rs. {{ number_format($data['pl_cogs'], 2) }})</span>
                    </div>
                    <div class="flex justify-between items-center py-4 bg-gray-50 rounded-xl px-4 mt-2 mb-6">
                        <span class="font-black text-gray-900">Gross Profit</span>
                        <span class="font-black text-xl text-mango">Rs. {{ number_format($data['pl_gross'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-gray-100 text-red-500">
                        <span class="font-bold">Less: Operating Expenses</span>
                        <span class="font-black">(Rs. {{ number_format($data['pl_expenses'], 2) }})</span>
                    </div>
                    <div class="flex justify-between items-center py-6 mt-6 border-t-4 border-gray-900">
                        <span class="font-black text-2xl text-gray-900">Net Profit / (Loss)</span>
                        <span class="font-black text-3xl {{ $data['pl_net'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                            Rs. {{ number_format($data['pl_net'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Ledger Statement -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-black text-xl text-gray-900">Transaction Ledger</h4>
                <span class="text-sm font-bold text-gray-500">{{ \Carbon\Carbon::parse($data['start_date'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($data['end_date'])->format('M d, Y') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs tracking-wider font-bold uppercase">
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Account</th>
                            <th class="px-6 py-4">Party</th>
                            <th class="px-6 py-4">Notes</th>
                            <th class="px-6 py-4 text-right">Debit (In)</th>
                            <th class="px-6 py-4 text-right">Credit (Out)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $totalIn = 0;
                            $totalOut = 0;
                        @endphp
                        @forelse($data['ledger_transactions'] as $tx)
                            @php
                                if ($tx->type === 'in') $totalIn += $tx->amount;
                                else $totalOut += $tx->amount;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 text-sm text-gray-500 font-bold">{{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}</td>
                                <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ $tx->account->name }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $tx->party ? $tx->party->name : '-' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $tx->notes ?: $tx->reference_type . ' #' . $tx->reference_id }}</td>
                                <td class="px-6 py-3 text-right text-sm font-black text-green-600">
                                    {{ $tx->type === 'in' ? number_format($tx->amount, 2) : '-' }}
                                </td>
                                <td class="px-6 py-3 text-right text-sm font-black text-red-600">
                                    {{ $tx->type === 'out' ? number_format($tx->amount, 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No transactions found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right font-black text-gray-900">Totals:</td>
                            <td class="px-6 py-4 text-right font-black text-green-600">Rs. {{ number_format($totalIn, 2) }}</td>
                            <td class="px-6 py-4 text-right font-black text-red-600">Rs. {{ number_format($totalOut, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</div>
