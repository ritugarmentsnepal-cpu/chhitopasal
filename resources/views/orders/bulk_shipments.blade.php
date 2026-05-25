<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                    {{ __('Bulk Shipments History') }}
                </h2>
                <p class="text-sm font-bold text-gray-500 mt-1">Review all past bulk shipment batches and print labels.</p>
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

            @if (session('error'))
                <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Batches Table -->
            <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="p-4 border-b border-gray-100 rounded-tl-3xl w-16">#</th>
                                <th class="p-4 border-b border-gray-100">Batch ID</th>
                                <th class="p-4 border-b border-gray-100">Date Shipped</th>
                                <th class="p-4 border-b border-gray-100">Orders Shipped</th>
                                <th class="p-4 border-b border-gray-100">Total Amount</th>
                                <th class="p-4 border-b border-gray-100 rounded-tr-3xl">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($batches as $index => $batch)
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="p-4 align-middle">
                                        <span class="text-xs font-black text-gray-400">{{ $batches->firstItem() + $index }}</span>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div class="font-bold text-gray-900 font-mono text-xs">{{ $batch->bulk_ship_batch_id }}</div>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <div class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($batch->shipped_at)->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500 font-medium">at {{ \Carbon\Carbon::parse($batch->shipped_at)->format('g:i A') }}</div>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1 rounded-lg text-sm font-black">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                            {{ $batch->order_count }}
                                        </span>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <span class="text-sm text-gray-900 font-bold">Rs. {{ number_format($batch->total_amount) }}</span>
                                    </td>
                                    <td class="p-4 align-middle">
                                        <a href="{{ route('orders.bulkShipmentPrint', ['batchId' => $batch->bulk_ship_batch_id]) }}" target="_blank" class="inline-flex items-center gap-2 bg-gray-900 text-white font-bold py-2 px-4 rounded-xl shadow-sm hover:bg-gray-800 transition duration-150 active:scale-95 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                            Print A4 Labels
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-12 text-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                        <p class="font-bold text-lg">No bulk shipments found.</p>
                                        <p class="text-sm mt-1">Bulk shipments you create will appear here.</p>
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
