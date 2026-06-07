<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <a href="{{ route('orders.bulkBatches') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">Upload History</a>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
          <span class="text-sm font-bold text-gray-900">Batch Details</span>
        </div>
        <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight flex items-center gap-2">
          Batch: {{ substr($batchId, 0, 8) }}...
        </h2>
        <p class="text-sm font-bold text-gray-500 mt-1">Uploaded on {{ \Carbon\Carbon::parse($batchDate)->format('M d, Y \a\t g:i A') }} • {{ $batchOrders->count() }} orders</p>
      </div>
      <a href="{{ route('orders.bulkBatches') }}" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl shadow-sm hover:bg-gray-50 transition duration-150 active:scale-95 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        <span class="hidden sm:inline">Back to History</span>
      </a>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto max-h-[70vh]">
          <table class="w-full text-left border-collapse whitespace-nowrap text-sm">
            <thead class="sticky top-0 z-10">
              <tr class="bg-gray-50 text-[10px] font-black text-gray-500 uppercase tracking-widest shadow-sm">
                <th class="px-4 py-3 border-b border-gray-200 w-12 text-center bg-gray-50">#</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">Order ID</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">Customer Name</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">Phone</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">Address</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">City</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">Products</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50 text-right">Total Amount</th>
                <th class="px-4 py-3 border-b border-gray-200 bg-gray-50">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($batchOrders as $index => $order)
                <tr class="hover:bg-mango/5 transition-colors group">
                  <td class="px-4 py-3 text-center text-xs font-bold text-gray-400">
                    {{ $index + 1 }}
                  </td>
                  <td class="px-4 py-3">
                    <a href="{{ route('orders.index', ['search' => $order->id]) }}" class="font-black text-gray-900 hover:text-mango transition-colors">
                      #{{ $order->id }}
                    </a>
                  </td>
                  <td class="px-4 py-3 font-bold text-gray-800">
                    {{ $order->customer_name }}
                  </td>
                  <td class="px-4 py-3 text-gray-600 font-medium">
                    {{ $order->customer_phone }}
                  </td>
                  <td class="px-4 py-3 text-gray-600 truncate max-w-[200px]" title="{{ $order->address }}">
                    {{ $order->address }}
                  </td>
                  <td class="px-4 py-3 text-gray-600">
                    {{ $order->city ?: '-' }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex flex-col gap-1">
                      @foreach($order->orderItems as $item)
                        <div class="text-xs font-medium text-gray-700 bg-gray-50 rounded px-2 py-1 border border-gray-100 inline-block">
                          <span class="font-bold text-gray-900">{{ $item->quantity }}x</span> 
                          {{ $item->product ? $item->product->name : 'Unknown Product' }}
                          @if($item->size || $item->color)
                            <span class="text-gray-400">({{ trim($item->size . ' ' . $item->color) }})</span>
                          @endif
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right font-black text-gray-900">
                    Rs. {{ number_format($order->total_amount) }}
                  </td>
                  <td class="px-4 py-3">
                    @php
                      $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'delivered' => 'bg-green-100 text-green-800 border-green-200',
                        'failed' => 'bg-red-100 text-red-800 border-red-200',
                        'rejected' => 'bg-gray-100 text-gray-800 border-gray-200',
                        'return_delivered' => 'bg-orange-100 text-orange-800 border-orange-200',
                      ];
                      $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-black border {{ $colorClass }} uppercase tracking-wider">
                      {{ str_replace('_', ' ', $order->status) }}
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
