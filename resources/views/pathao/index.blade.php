<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-mango" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Pathao Manager
            </h2>
            <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'settlement-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-gray-800 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Record COD Settlement
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen" x-data="{ activeTab: 'dashboard' }">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Tabs Navigation -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex overflow-x-auto no-scrollbar mb-6">
                <button @click="activeTab = 'dashboard'" :class="{'bg-mango text-gray-900 shadow-sm': activeTab === 'dashboard', 'text-gray-500 hover:bg-gray-50 hover:text-gray-900': activeTab !== 'dashboard'}" class="px-6 py-2.5 rounded-xl font-bold whitespace-nowrap transition-all flex-1 text-center">
                    Overview
                </button>
                <button @click="activeTab = 'deliveries'" :class="{'bg-mango text-gray-900 shadow-sm': activeTab === 'deliveries', 'text-gray-500 hover:bg-gray-50 hover:text-gray-900': activeTab !== 'deliveries'}" class="px-6 py-2.5 rounded-xl font-bold whitespace-nowrap transition-all flex-1 text-center">
                    Deliveries Tracker
                </button>
                <button @click="activeTab = 'ledger'" :class="{'bg-mango text-gray-900 shadow-sm': activeTab === 'ledger', 'text-gray-500 hover:bg-gray-50 hover:text-gray-900': activeTab !== 'ledger'}" class="px-6 py-2.5 rounded-xl font-bold whitespace-nowrap transition-all flex-1 text-center">
                    Financial Ledger
                </button>
            </div>

            <!-- Dashboard Tab -->
            <div x-show="activeTab === 'dashboard'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Metric Card -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-110 transition-transform duration-500 opacity-50"></div>
                        <div class="relative z-10">
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">In Transit (Shipped)</p>
                            <h3 class="text-4xl font-black text-gray-900">{{ $inTransitCount }} <span class="text-lg text-gray-400 font-medium">orders</span></h3>
                        </div>
                    </div>
                    
                    <!-- Metric Card -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-green-50 rounded-full group-hover:scale-110 transition-transform duration-500 opacity-50"></div>
                        <div class="relative z-10">
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Delivered (Pending COD)</p>
                            <h3 class="text-4xl font-black text-gray-900">{{ $deliveredCount }} <span class="text-lg text-gray-400 font-medium">orders</span></h3>
                        </div>
                    </div>

                    <!-- Metric Card -->
                    <div class="bg-gray-900 rounded-3xl p-6 shadow-lg border border-gray-800 relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-mango/10 rounded-full group-hover:scale-110 transition-transform duration-500 opacity-50"></div>
                        <div class="relative z-10">
                            <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Pathao Receivables</p>
                            <h3 class="text-4xl font-black text-mango">Rs.{{ number_format($pendingFromPathao) }}</h3>
                            <p class="text-xs text-gray-500 mt-2">Amount owed by Pathao for delivered COD orders.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-lg font-black text-gray-900 mb-4">Location Finder</h3>
                    <p class="text-sm text-gray-500 mb-6">Use this tool to find valid Pathao Cities, Zones, and Areas for your shipping forms.</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" x-data="locationFinder()">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">City</label>
                            <select x-model="cityId" @change="fetchZones()" class="w-full rounded-xl border-gray-200 focus:ring-mango">
                                <option value="">Select City</option>
                                <template x-for="city in cities" :key="city.city_id">
                                    <option :value="city.city_id" x-text="city.city_name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Zone</label>
                            <select x-model="zoneId" @change="fetchAreas()" class="w-full rounded-xl border-gray-200 focus:ring-mango" :disabled="!zones.length">
                                <option value="">Select Zone</option>
                                <template x-for="zone in zones" :key="zone.zone_id">
                                    <option :value="zone.zone_id" x-text="zone.zone_name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Area</label>
                            <select x-model="areaId" class="w-full rounded-xl border-gray-200 focus:ring-mango" :disabled="!areas.length">
                                <option value="">Select Area</option>
                                <template x-for="area in areas" :key="area.area_id">
                                    <option :value="area.area_id" x-text="area.area_name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deliveries Tab -->
            <div x-show="activeTab === 'deliveries'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-bold">
                                <th class="p-4 border-b border-gray-100">Consignment ID</th>
                                <th class="p-4 border-b border-gray-100">Order ID & Date</th>
                                <th class="p-4 border-b border-gray-100">Customer</th>
                                <th class="p-4 border-b border-gray-100">Status</th>
                                <th class="p-4 border-b border-gray-100 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($deliveries as $order)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-black text-gray-900">{{ $order->pathao_consignment_id }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">#{{ $order->id }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->updated_at->format('M d, Y') }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">{{ $order->customer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->city }}</div>
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                            {{ $order->status === 'delivered' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $order->status === 'return_delivered' ? 'bg-orange-100 text-orange-700' : '' }}
                                        ">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('orders.printLabel', $order) }}" target="_blank" class="text-mango hover:text-orange-500 font-bold text-sm flex justify-end items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Print Label
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-gray-400">
                                        <p class="font-bold">No Pathao consignments found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($deliveries->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50">
                            {{ $deliveries->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ledger Tab -->
            <div x-show="activeTab === 'ledger'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="font-black text-gray-900 text-lg">Pathao Financial Ledger</h3>
                            <p class="text-sm text-gray-500">Track all COD liabilities and bank settlements.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-500 uppercase">Current Balance Owed</p>
                            <p class="text-2xl font-black text-mango">Rs.{{ number_format($pendingFromPathao) }}</p>
                        </div>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white text-gray-400 text-xs uppercase tracking-wider font-bold border-b border-gray-100">
                                <th class="p-4">Date</th>
                                <th class="p-4">Reference & Notes</th>
                                <th class="p-4 text-right">Debit (Owed)</th>
                                <th class="p-4 text-right">Credit (Paid)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ledger as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}</td>
                                    <td class="p-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $tx->reference_type }} {{ $tx->reference_id ? '#'.$tx->reference_id : '' }}</div>
                                        <div class="text-xs text-gray-500">{{ $tx->notes }}</div>
                                    </td>
                                    <td class="p-4 text-right text-sm font-bold text-gray-900">
                                        @if($tx->type === 'in') Rs.{{ number_format($tx->amount) }} @else - @endif
                                    </td>
                                    <td class="p-4 text-right text-sm font-bold text-green-600">
                                        @if($tx->type === 'out') Rs.{{ number_format($tx->amount) }} @else - @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-gray-400">
                                        <p class="font-bold">No financial records found for Pathao.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($ledger->hasPages())
                        <div class="p-4 border-t border-gray-100 bg-gray-50">
                            {{ $ledger->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Settlement Modal -->
        <x-modal name="settlement-modal" focusable>
            <div class="p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-2xl font-black text-gray-900">Record Pathao Settlement</h2>
                    <p class="text-sm text-gray-500 mt-1">Record the COD bulk amount deposited by Pathao.</p>
                </div>
                
                <form method="POST" action="{{ route('pathao.settlement') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Settlement Amount (Rs.)</label>
                        <input type="number" name="amount" step="0.01" min="1" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 text-lg font-black focus:ring-mango" required placeholder="e.g. 50000">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Deposit To</label>
                        <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango" required>
                            <option value="">Select Bank Account...</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Reference ID (Optional)</label>
                            <input type="text" name="reference" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango" placeholder="Bank Txn ID">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Notes</label>
                        <input type="text" name="notes" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango" placeholder="e.g. Bulk settlement for 12 orders">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95">Save Settlement</button>
                    </div>
                </form>
            </div>
        </x-modal>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('locationFinder', () => ({
                cities: [],
                zones: [],
                areas: [],
                cityId: '',
                zoneId: '',
                areaId: '',

                async init() {
                    const res = await fetch('{{ url("api/pathao/cities") }}');
                    this.cities = await res.json();
                },
                async fetchZones() {
                    this.zones = [];
                    this.areas = [];
                    this.zoneId = '';
                    this.areaId = '';
                    if(!this.cityId) return;
                    const res = await fetch('{{ url("api/pathao/zones") }}/' + this.cityId);
                    this.zones = await res.json();
                },
                async fetchAreas() {
                    this.areas = [];
                    this.areaId = '';
                    if(!this.zoneId) return;
                    const res = await fetch('{{ url("api/pathao/areas") }}/' + this.zoneId);
                    this.areas = await res.json();
                }
            }));
        });
    </script>
</x-app-layout>
