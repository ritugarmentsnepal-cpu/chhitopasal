<x-app-layout>
  <x-slot name="header">
    <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
      {{ __('Customer Analytics') }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h3 class="text-xl font-black text-gray-900 ">Unique Customers</h3>
            <p class="text-gray-500 text-sm mt-1">Automatically grouped by phone number from all orders.</p>
          </div>
          <div class="bg-mango/20 text-mango font-black px-4 py-2 rounded-xl text-sm border border-mango/30">
            Top Spenders First
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white">
                <th class="p-4 border-b border-gray-100 pl-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Customer</th>
                <th class="p-4 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">Contact</th>
                <th class="p-4 border-b border-gray-100 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Orders</th>
                <th class="p-4 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">Lifetime Value</th>
                <th class="p-4 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">Last Active</th>
                <th class="p-4 border-b border-gray-100 text-right pr-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Profile</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($customers as $customer)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                  <td class="p-4 pl-6 align-middle">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-mango/10 text-mango rounded-xl flex items-center justify-center font-black text-sm border border-mango/20">
                        {{ strtoupper(substr($customer->latest_name, 0, 1)) }}
                      </div>
                      <div class="font-black text-gray-900 ">{{ $customer->latest_name }}</div>
                    </div>
                  </td>
                  <td class="p-4 align-middle font-bold text-gray-600">
                    {{ $customer->customer_phone }}
                  </td>
                  <td class="p-4 align-middle text-center">
                    <span class="bg-gray-100 text-gray-700 font-black px-3 py-1 rounded-lg text-sm border border-gray-200">
                      {{ $customer->total_orders }}
                    </span>
                  </td>
                  <td class="p-4 align-middle font-black text-mango text-lg">
                    Rs.{{ number_format($customer->lifetime_value) }}
                  </td>
                  <td class="p-4 align-middle text-sm text-gray-500 font-medium">
                    {{ \Carbon\Carbon::parse($customer->last_order_date)->diffForHumans() }}
                  </td>
                  <td class="p-4 pr-6 align-middle text-right">
                    <a href="{{ route('customers.show', $customer->customer_phone) }}" class="inline-flex items-center gap-1 text-mango font-bold hover:text-gray-900 transition-colors bg-mango/5 hover:bg-gray-100 px-4 py-2 rounded-lg">
                      View Details
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="p-12 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <p class="font-bold text-lg">No customers found.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        @if($customers->hasPages())
          <div class="p-4 border-t border-gray-100 rounded-b-[24px]">
            {{ $customers->links() }}
          </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
