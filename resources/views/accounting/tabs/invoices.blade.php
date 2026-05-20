<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-black text-gray-900 dark:text-white">Invoices & Receivables</h3>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Invoice #</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Customer</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Payment</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['orders'] as $order)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-black text-gray-900 dark:text-white">INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <div class="text-xs font-medium text-gray-500">{{ $order->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">{{ $order->customer_name }}</div>
                        <div class="text-sm text-gray-500">{{ $order->customer_phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-black text-gray-900 dark:text-white">Rs. {{ number_format($order->total_amount, 2) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md {{ $order->status === 'delivered' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="text-mango hover:text-yellow-600 font-bold text-sm">Print PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
        <div class="p-4 border-t border-gray-100">
            {{ $data['orders']->links() }}
        </div>
    </div>
</div>
