<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h3 class="text-2xl font-black text-gray-900">Invoices & Receivables</h3>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Invoice #</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($data['orders'] as $order)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-black text-gray-900">INV-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <div class="text-xs font-medium text-gray-500">{{ $order->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900">{{ $order->customer_name }}</div>
                        <div class="text-sm text-gray-500">{{ $order->customer_phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-black text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $order->status === 'delivered' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
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
        <div class="p-4 border-t border-gray-100">
            {{ $data['orders']->links() }}
        </div>
    </div>
</div>
