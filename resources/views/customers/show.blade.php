<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('customers.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                {{ __('Customer Profile') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Profile Card -->
                <div class="md:col-span-2 bg-gray-900 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative z-10 flex flex-col h-full">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-xs mb-2">Customer Identifier</span>
                        <h3 class="text-4xl font-black text-mango mb-1">{{ $customerData['phone'] }}</h3>
                        <p class="text-gray-300 font-medium text-sm">Active since {{ \Carbon\Carbon::parse($customerData['first_order_date'])->format('F Y') }}</p>
                        
                        <div class="mt-auto pt-6 flex gap-3 flex-wrap">
                            @foreach($customerData['names'] as $name)
                                <span class="bg-white/10 border border-white/20 px-3 py-1.5 rounded-lg text-sm font-bold shadow-sm">
                                    {{ $name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
                    <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    </div>
                    <h4 class="text-gray-500 font-bold text-sm">Total Orders</h4>
                    <p class="text-4xl font-black text-gray-900 mt-1">{{ $customerData['total_orders'] }}</p>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
                    <div class="w-14 h-14 bg-mango/20 text-mango rounded-2xl flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h4 class="text-gray-500 font-bold text-sm">Lifetime Value</h4>
                    <p class="text-3xl font-black text-gray-900 mt-1">Rs.{{ number_format($customerData['lifetime_value']) }}</p>
                </div>
            </div>

            <!-- Detailed History -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Known Addresses -->
                <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100">
                    <h4 class="font-black text-xl text-gray-900 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-wildOrchid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Delivery Locations
                    </h4>
                    <div class="space-y-4">
                        @foreach($customerData['addresses'] as $address)
                            <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $address }}</p>
                                    @php
                                        // Find city for this address from orders
                                        $city = collect($customerData['orders'])->where('address', $address)->first()->city ?? 'Unknown City';
                                    @endphp
                                    <p class="text-sm text-gray-500 font-medium">{{ $city }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order History -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-gray-100 bg-gray-50">
                        <h4 class="font-black text-xl text-gray-900">Order History</h4>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="p-4 pl-6 border-b border-gray-50">Order ID</th>
                                    <th class="p-4 border-b border-gray-50">Date</th>
                                    <th class="p-4 border-b border-gray-50">Items</th>
                                    <th class="p-4 border-b border-gray-50">Total</th>
                                    <th class="p-4 pr-6 border-b border-gray-50 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($customerData['orders'] as $order)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="p-4 pl-6 font-black text-gray-900">#{{ $order->id }}</td>
                                        <td class="p-4 text-sm font-medium text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                        <td class="p-4">
                                            <ul class="text-xs text-gray-500 font-medium space-y-1">
                                                @foreach($order->orderItems as $item)
                                                    <li>{{ $item->quantity }}x {{ $item->product->name ?? 'Unknown Product' }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="p-4 font-black text-gray-900">Rs.{{ number_format($order->total_amount) }}</td>
                                        <td class="p-4 pr-6 text-right">
                                            @php
                                                $colors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                    'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                    'delivered' => 'bg-green-100 text-green-800 border-green-200',
                                                    'failed' => 'bg-red-100 text-red-800 border-red-200',
                                                    'rejected' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                ];
                                                $color = $colors[$order->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wide border {{ $color }}">
                                                {{ $order->status }}
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
    </div>
</x-app-layout>
