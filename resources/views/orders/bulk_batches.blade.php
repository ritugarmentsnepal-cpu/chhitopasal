<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                    {{ __('Bulk Upload History') }}
                </h2>
                <p class="text-sm font-bold text-gray-500 mt-1">Review all past bulk order uploads.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl shadow-sm hover:bg-gray-50 transition duration-150 active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span class="hidden sm:inline">Back to Orders</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Batches Table -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="p-4 border-b border-gray-100 rounded-tl-3xl w-16">#</th>
                                <th class="p-4 border-b border-gray-100">Batch Date</th>
                                <th class="p-4 border-b border-gray-100">Orders Created</th>
                                <th class="p-4 border-b border-gray-100">Total Amount</th>
                                <th class="p-4 border-b border-gray-100">Order IDs</th>
                                <th class="p-4 border-b border-gray-100 rounded-tr-3xl">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($batches as $index => $batch)
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="p-4 align-top">
                                        <span class="text-xs font-black text-gray-400">{{ $batches->firstItem() + $index }}</span>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($batch->created_at)->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500 font-medium">at {{ \Carbon\Carbon::parse($batch->created_at)->format('g:i A') }}</div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1 rounded-lg text-sm font-black">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                            {{ $batch->order_count }}
                                        </span>
                                    </td>
                                    <td class="p-4 align-top">
                                        <span class="text-sm text-gray-900 font-bold">Rs. {{ number_format($batch->total_amount) }}</span>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto pr-2">
                                            @foreach(explode(',', $batch->order_ids) as $orderId)
                                                <a href="{{ route('orders.index', ['status' => 'pending', 'search' => trim($orderId)]) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-gray-100 text-gray-700 hover:bg-mango/20 hover:text-gray-900 border border-gray-200 hover:border-mango/40 transition-all duration-150">
                                                    #{{ trim($orderId) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="p-4 align-top text-right">
                                        <a href="{{ route('orders.bulkBatchShow', $batch->bulk_batch_id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            View Spreadsheet
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <p class="font-bold text-lg">No bulk upload batches found.</p>
                                        <p class="text-sm mt-1">Bulk uploads you create will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                @if($batches->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-[24px]">
                        {{ $batches->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
