<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-xl text-gray-800 leading-tight flex items-center gap-2">
                <a href="{{ route('accounting.index', ['tab' => 'banking']) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                Account Statement
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Account Info Card -->
            <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                        {{ $account->getTypeIcon() }}
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-gray-900">{{ $account->name }}</h3>
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 uppercase tracking-wider">
                                {{ $account->type }}
                            </span>
                            @if($account->bank_name)
                                <span class="text-sm font-bold text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $account->bank_name }}
                                </span>
                            @endif
                            @if($account->branch)
                                <span class="text-sm font-bold text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $account->branch }}
                                </span>
                            @endif
                            @if($account->account_number)
                                <span class="text-sm font-bold text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    {{ $account->account_number }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-left md:text-right w-full md:w-auto">
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-1">Current Balance</p>
                    <p class="text-4xl font-black text-gray-900">Rs. {{ number_format($account->balance, 2) }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <form action="{{ route('accounting.statement', $account->id) }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 px-3 text-sm font-bold">
                    </div>
                    
                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-bold text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango py-2 px-3 text-sm font-bold">
                    </div>

                    <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                        <button type="submit" class="w-full sm:w-auto bg-gray-900 text-white font-bold py-2 px-6 rounded-xl hover:bg-gray-800 transition">
                            Filter
                        </button>
                        <a href="{{ route('accounting.exportStatement', [$account->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="w-full sm:w-auto bg-green-100 text-green-800 hover:bg-green-200 font-bold py-2 px-4 rounded-xl transition flex items-center justify-center gap-2 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Opening Balance</p>
                    <p class="text-xl font-black text-gray-900">Rs. {{ number_format($openingBalance, 2) }}</p>
                </div>
                <div class="bg-green-50 rounded-2xl p-4 border border-green-100">
                    <p class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Total In (+)</p>
                    <p class="text-xl font-black text-green-700">Rs. {{ number_format($totalIn, 2) }}</p>
                </div>
                <div class="bg-red-50 rounded-2xl p-4 border border-red-100">
                    <p class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">Total Out (-)</p>
                    <p class="text-xl font-black text-red-700">Rs. {{ number_format($totalOut, 2) }}</p>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs tracking-wider font-bold uppercase">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Reference</th>
                                <th class="px-6 py-4">Notes / Party</th>
                                <th class="px-6 py-4 text-right">Debit (In)</th>
                                <th class="px-6 py-4 text-right">Credit (Out)</th>
                                <th class="px-6 py-4 text-right bg-gray-100">Running Bal.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <!-- Opening Balance Row -->
                            <tr class="bg-gray-50/50">
                                <td colspan="5" class="px-6 py-4 text-sm font-bold text-gray-500 text-right">
                                    Opening Balance as of {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-gray-900 bg-gray-50">
                                    {{ number_format($openingBalance, 2) }}
                                </td>
                            </tr>

                            <!-- Transactions -->
                            @forelse($transactionsWithBalance as $tx)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-500 font-bold whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($tx->date)->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        {{ $tx->reference_type ? $tx->reference_type . ' #' . $tx->reference_id : 'Manual' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $tx->notes ?: '-' }}</div>
                                        @if($tx->party)
                                            <div class="text-xs font-bold text-gray-500 mt-0.5">{{ $tx->party->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-green-600 whitespace-nowrap">
                                        {{ $tx->type === 'in' ? number_format($tx->amount, 2) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-red-600 whitespace-nowrap">
                                        {{ $tx->type === 'out' ? number_format($tx->amount, 2) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-gray-900 bg-gray-50 whitespace-nowrap">
                                        {{ number_format($tx->running_balance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No transactions found in this date range.</td>
                                </tr>
                            @endforelse

                            <!-- Closing Balance Row -->
                            <tr class="bg-gray-50/80 border-t-2 border-gray-200">
                                <td colspan="5" class="px-6 py-4 text-sm font-black text-gray-900 text-right">
                                    Closing Balance as of {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-right text-base font-black text-gray-900 bg-gray-100">
                                    Rs. {{ number_format($closingBalance, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
