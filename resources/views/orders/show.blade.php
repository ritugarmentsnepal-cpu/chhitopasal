@php
    $due = max(0, (float) $order->total_amount + (float) $order->delivery_charge - (float) $order->paid_amount);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('orders.index', ['status' => $order->status]) }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:shadow-md transition" title="Back to Orders">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Order #{{ $order->id }}</h2>
                        <x-status-badge :status="$order->status" />
                        @if($order->isCustomPrint())
                            <span class="text-xs font-black uppercase px-3 py-1 rounded-full bg-purple-100 text-purple-700">Custom Print</span>
                        @endif
                    </div>
                    <p class="text-sm font-medium text-gray-500 mt-0.5">
                        {{ $order->created_at->format('M j, Y g:i A') }}
                        · Source: <span class="font-bold">{{ ucfirst($order->source ?? 'manual') }}</span>
                        @if($order->payment_status)
                            · Payment: <span class="font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">{{ ucfirst($order->payment_status) }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="bg-white border border-gray-200 text-gray-900 font-bold px-4 py-2.5 rounded-xl shadow-sm hover:shadow-md transition text-sm">Invoice</a>
                <a href="{{ route('orders.printLabel', ['order' => $order, 'type' => 'both']) }}" target="_blank" class="bg-gray-900 text-white font-bold px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-800 transition text-sm">Print Label</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-6 items-start">

            {{-- ═══════════ LEFT: items, custom print, timeline ═══════════ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Items --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-black text-gray-900">Items</h3>
                        <span class="text-xs font-bold text-gray-400">{{ $order->orderItems->count() }} line{{ $order->orderItems->count() !== 1 ? 's' : '' }}</span>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-wider border-b border-gray-50">
                                <th class="px-6 py-3">Product</th>
                                <th class="px-3 py-3">Variant</th>
                                <th class="px-3 py-3 text-center">Qty</th>
                                <th class="px-3 py-3 text-right">Price</th>
                                <th class="px-6 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td class="px-6 py-3">
                                        <div class="font-bold text-gray-900">{{ $item->product->name ?? 'Deleted product' }}</div>
                                        @if($item->custom_notes)
                                            <div class="text-xs text-gray-400 font-medium mt-0.5">{{ $item->custom_notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-gray-500 font-medium">
                                        {{ collect([$item->color, $item->size])->filter()->implode(' / ') ?: '—' }}
                                        @if(!empty($item->size_breakdown))
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                @foreach($item->size_breakdown as $sz => $q){{ $sz }}×{{ $q }} @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-gray-900">{{ $item->quantity }}</td>
                                    <td class="px-3 py-3 text-right font-medium text-gray-500">Rs. {{ number_format($item->price_at_purchase) }}</td>
                                    <td class="px-6 py-3 text-right font-bold text-gray-900">Rs. {{ number_format($item->price_at_purchase * $item->quantity) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-100 text-sm">
                            <tr>
                                <td colspan="4" class="px-6 py-2 text-right font-bold text-gray-400">Delivery</td>
                                <td class="px-6 py-2 text-right font-bold text-gray-500">Rs. {{ number_format($order->delivery_charge) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 py-2 text-right font-black text-gray-900">Total</td>
                                <td class="px-6 py-2 text-right font-black text-gray-900">Rs. {{ number_format($order->total_amount + $order->delivery_charge) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-6 pb-4 py-2 text-right font-bold text-gray-400">Paid / Due</td>
                                <td class="px-6 pb-4 py-2 text-right font-bold">
                                    <span class="text-emerald-600">Rs. {{ number_format($order->paid_amount) }}</span>
                                    <span class="text-gray-300 mx-1">/</span>
                                    <span class="{{ $due > 0 ? 'text-red-500' : 'text-gray-400' }}">Rs. {{ number_format($due) }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Custom Print pipeline --}}
                @if($order->isCustomPrint())
                    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-purple-50/40">
                            <h3 class="font-black text-gray-900">Custom Print Production</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            {{-- Production stepper --}}
                            <div class="flex items-center gap-1">
                                @foreach(\App\Models\Order::productionStatuses() as $i => $step)
                                    @php
                                        $currentIdx = array_search($order->production_status, \App\Models\Order::productionStatuses());
                                        $done = $currentIdx !== false && $i <= $currentIdx;
                                    @endphp
                                    <div class="flex-1">
                                        <div class="h-1.5 rounded-full {{ $done ? 'bg-purple-500' : 'bg-gray-100' }}"></div>
                                        <p class="text-[9px] font-black uppercase mt-1.5 {{ $done ? 'text-purple-600' : 'text-gray-300' }}">{{ ucwords(str_replace('_', ' ', $step)) }}</p>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Advance status form --}}
                            <form method="POST" action="{{ route('orders.updateProductionStatus', $order) }}" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <div class="flex-1 min-w-[180px]">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Set Production Status</label>
                                    <select name="production_status" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2">
                                        @foreach(\App\Models\Order::productionStatuses() as $step)
                                            <option value="{{ $step }}" {{ $order->production_status === $step ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $step)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-[2] min-w-[200px]">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Note (optional)</label>
                                    <input type="text" name="production_notes" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2" placeholder="e.g. sent to embroidery">
                                </div>
                                <button type="submit" class="bg-purple-600 text-white font-bold text-sm px-5 py-2 rounded-xl hover:bg-purple-500 transition active:scale-95">Update</button>
                            </form>

                            @if($order->production_notes)
                                <p class="text-xs font-medium text-gray-500 bg-gray-50 rounded-xl px-4 py-2.5">{{ $order->production_notes }}</p>
                            @endif

                            {{-- Design files & mockups --}}
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2">Design Files</h4>
                                    <div class="grid grid-cols-3 gap-2">
                                        @forelse($order->design_files ?? [] as $position => $file)
                                            <a href="{{ '/storage/' . $file }}" target="_blank" class="aspect-square bg-gray-50 border border-gray-100 rounded-xl overflow-hidden flex items-center justify-center p-1.5 hover:border-purple-300 transition relative group">
                                                <img src="{{ '/storage/' . $file }}" loading="lazy" class="max-w-full max-h-full object-contain">
                                                <span class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[9px] font-bold text-center py-0.5 opacity-0 group-hover:opacity-100 transition">{{ $position }}</span>
                                            </a>
                                        @empty
                                            <p class="col-span-3 text-xs font-bold text-gray-300 py-4 text-center bg-gray-50 rounded-xl">No design files</p>
                                        @endforelse
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Mockups</h4>
                                        <a href="{{ route('mockups.index', ['order' => $order->id, 'open' => 'generator']) }}" class="text-[10px] font-black text-white bg-gradient-to-r from-indigo-600 to-purple-600 px-2.5 py-1 rounded-lg hover:from-indigo-500 hover:to-purple-500 transition shadow-sm">⚡ Generate Mockup</a>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        @forelse($order->libraryMockups as $mockup)
                                            <div>
                                                <a href="{{ '/storage/' . $mockup->image_path }}" target="_blank" class="aspect-square bg-gray-50 border border-gray-100 rounded-xl overflow-hidden flex items-center justify-center p-1.5 hover:border-indigo-300 transition relative block">
                                                    <img src="{{ '/storage/' . $mockup->image_path }}" loading="lazy" class="max-w-full max-h-full object-contain">
                                                    @if($mockup->approval_status)
                                                        <span class="absolute top-1 right-1 text-[8px] font-black uppercase px-1.5 py-0.5 rounded-full {{ $mockup->approval_status === 'approved' ? 'bg-emerald-500 text-white' : ($mockup->approval_status === 'changes_requested' ? 'bg-amber-500 text-white' : 'bg-gray-400 text-white') }}">
                                                            {{ $mockup->approval_status === 'changes_requested' ? 'Changes' : ucfirst($mockup->approval_status) }}
                                                        </span>
                                                    @endif
                                                </a>
                                                <button type="button" onclick="shareMockup({{ $mockup->id }})" class="w-full mt-1 bg-[#25D366]/10 text-[#128C4B] text-[10px] font-black py-1 rounded-lg hover:bg-[#25D366]/20 transition flex items-center justify-center gap-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                                    Approval
                                                </button>
                                            </div>
                                        @empty
                                            <a href="{{ route('mockups.index', ['order' => $order->id, 'open' => 'generator']) }}" class="col-span-3 text-xs font-bold text-indigo-500 py-4 text-center bg-indigo-50/50 border border-dashed border-indigo-200 rounded-xl hover:bg-indigo-50 transition block">
                                                No mockups yet — Generate one now →
                                            </a>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            @if($order->print_method || $order->design_notes)
                                <div class="text-xs font-medium text-gray-500 space-y-1">
                                    @if($order->print_method)<p><span class="font-black text-gray-400 uppercase text-[10px]">Method:</span> {{ ucfirst($order->print_method) }}</p>@endif
                                    @if($order->design_notes)<p><span class="font-black text-gray-400 uppercase text-[10px]">Design notes:</span> {{ $order->design_notes }}</p>@endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Timeline --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-black text-gray-900">Timeline</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-0 relative">
                            <div class="absolute left-[7px] top-2 bottom-2 w-px bg-gray-100"></div>
                            @forelse($timeline as $log)
                                <div class="flex gap-4 relative pb-5 last:pb-0">
                                    <div class="w-4 h-4 rounded-full shrink-0 mt-0.5 border-2 border-white shadow {{ str_contains($log->action, 'payment') ? 'bg-emerald-400' : (str_contains($log->action, 'status') || $log->action === 'updated' ? 'bg-indigo-400' : 'bg-gray-300') }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-baseline justify-between gap-2 flex-wrap">
                                            <p class="text-sm font-bold text-gray-900">{{ ucwords(str_replace('_', ' ', $log->action)) }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 shrink-0" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }} · {{ $log->user->name ?? 'System' }}</p>
                                        </div>
                                        @if(is_array($log->details) && count($log->details))
                                            <div class="text-xs font-medium text-gray-500 mt-0.5 space-y-0.5">
                                                @if($log->action === 'updated' && isset($log->details['new']))
                                                    @foreach($log->details['new'] as $field => $newVal)
                                                        @if(!in_array($field, ['updated_at']))
                                                            <p><span class="text-gray-400">{{ str_replace('_', ' ', $field) }}:</span>
                                                                <span class="line-through text-gray-300">{{ \Illuminate\Support\Str::limit(is_scalar($log->details['old'][$field] ?? null) ? (string)($log->details['old'][$field] ?? '—') : json_encode($log->details['old'][$field] ?? '—'), 40) }}</span>
                                                                → <span class="text-gray-700 font-bold">{{ \Illuminate\Support\Str::limit(is_scalar($newVal) ? (string)$newVal : json_encode($newVal), 40) }}</span></p>
                                                        @endif
                                                    @endforeach
                                                @elseif($log->action !== 'created')
                                                    @foreach($log->details as $k => $v)
                                                        @if(is_scalar($v))
                                                            <p><span class="text-gray-400">{{ str_replace('_', ' ', $k) }}:</span> {{ \Illuminate\Support\Str::limit((string) $v, 80) }}</p>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm font-bold text-gray-300 text-center py-4">No activity recorded</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════ RIGHT: customer, status, payment, shipping ═══════════ --}}
            <div class="space-y-6">

                {{-- Customer --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-black text-gray-900">Customer</h3>
                        @if($order->customer_phone)
                            <a href="{{ route('customers.show', $order->customer_phone) }}" class="text-[10px] font-black text-indigo-500 hover:text-indigo-700 transition uppercase">History →</a>
                        @endif
                    </div>
                    <div class="space-y-2 text-sm">
                        <p class="font-bold text-gray-900">{{ $order->customer_name }}</p>
                        <p class="font-medium text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            {{ $order->customer_phone }}
                            <a href="https://wa.me/977{{ ltrim(preg_replace('/\D/', '', $order->customer_phone), '0') }}" target="_blank" class="text-emerald-500 hover:text-emerald-600 font-black text-[10px] uppercase">WhatsApp</a>
                        </p>
                        <p class="font-medium text-gray-500">{{ $order->address }}{{ $order->city ? ', ' . $order->city : '' }}</p>
                    </div>
                </div>

                {{-- Status --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-black text-gray-900 mb-4">Order Status</h3>
                    <form method="POST" action="{{ route('orders.status', $order) }}" class="flex gap-2">
                        @csrf
                        <select name="status" class="flex-1 rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                            @foreach(['pending', 'confirmed', 'shipped', 'delivered', 'failed', 'rejected', 'return_delivered'] as $s)
                                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-gray-900 text-white font-bold text-sm px-4 py-2.5 rounded-xl hover:bg-gray-800 transition active:scale-95">Set</button>
                    </form>
                    @if($order->status === 'return_delivered' && !$order->return_verified_at)
                        <p class="text-xs font-bold text-orange-500 mt-3">⚠️ Return not yet verified — verify from the orders list to restock items.</p>
                    @endif
                    @if($order->remarks)
                        <p class="text-xs font-medium text-gray-500 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 mt-4">📝 {{ $order->remarks }}</p>
                    @endif
                </div>

                {{-- Payment --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6" x-data="{ method: 'paid' }">
                    <h3 class="font-black text-gray-900 mb-4">Record Payment</h3>
                    <form method="POST" action="{{ route('orders.payment', $order) }}" class="space-y-3">
                        @csrf
                        <select name="payment_method" x-model="method" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                            <option value="paid">Full / Partial Payment</option>
                            <option value="partial">Advance</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                        <template x-if="method !== 'cod'">
                            <div class="space-y-3">
                                <input type="number" name="amount" step="0.01" min="0" value="{{ $due }}" placeholder="Amount" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                                <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2.5">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} (Rs. {{ number_format($account->balance) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </template>
                        <button type="submit" class="w-full bg-emerald-500 text-white font-bold text-sm py-2.5 rounded-xl hover:bg-emerald-600 transition active:scale-95">Save Payment</button>
                    </form>

                    @if($order->transactions->isNotEmpty())
                        <div class="mt-5 pt-4 border-t border-gray-50 space-y-2">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Transactions</h4>
                            @foreach($order->transactions as $txn)
                                <div class="flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-bold text-gray-700">{{ $txn->account->name ?? '—' }} <span class="text-gray-300">·</span> {{ \Illuminate\Support\Carbon::parse($txn->date)->format('M j') }}</p>
                                        @if($txn->notes)<p class="text-gray-400 font-medium">{{ \Illuminate\Support\Str::limit($txn->notes, 40) }}</p>@endif
                                    </div>
                                    <span class="font-black {{ $txn->type === 'in' ? 'text-emerald-600' : 'text-red-500' }}">{{ $txn->type === 'in' ? '+' : '-' }}Rs. {{ number_format($txn->amount) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Shipping / Pathao --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-black text-gray-900 mb-4">Shipping</h3>
                    @if($order->pathao_consignment_id)
                        <div class="space-y-2 text-sm">
                            <p class="font-medium text-gray-500">Consignment: <span class="font-black text-gray-900">{{ $order->pathao_consignment_id }}</span></p>
                            <p class="font-medium text-gray-500">Pathao status: <span class="font-bold text-cyan-600">{{ $order->pathao_status ?? 'Unknown' }}</span></p>
                            @if($order->pathao_status_updated_at)
                                <p class="text-[10px] font-bold text-gray-400">Updated {{ \Illuminate\Support\Carbon::parse($order->pathao_status_updated_at)->diffForHumans() }}</p>
                            @endif
                            @if($order->shipped_at)
                                <p class="font-medium text-gray-500">Shipped: <span class="font-bold">{{ $order->shipped_at->format('M j, Y') }}</span></p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('orders.syncPathaoStatus', $order) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full bg-cyan-500 text-white font-bold text-sm py-2.5 rounded-xl hover:bg-cyan-600 transition active:scale-95">Sync Pathao Status</button>
                        </form>
                    @else
                        <p class="text-sm font-medium text-gray-400">Not shipped via Pathao yet.</p>
                        @if(in_array($order->status, ['pending', 'confirmed']))
                            <a href="{{ route('orders.index', ['status' => $order->status, 'search' => $order->id]) }}" class="mt-3 block text-center w-full bg-gray-100 text-gray-700 font-bold text-sm py-2.5 rounded-xl hover:bg-gray-200 transition">Ship from Orders List</a>
                        @endif
                    @endif

                    @if($riderComments->isNotEmpty())
                        <div class="mt-5 pt-4 border-t border-gray-50 space-y-2">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Rider Comments</h4>
                            @foreach($riderComments->take(4) as $comment)
                                <div class="text-xs bg-gray-50 rounded-xl px-3 py-2">
                                    <p class="font-medium text-gray-600">{{ \Illuminate\Support\Str::limit($comment->rider_comment, 90) }}</p>
                                    <p class="text-[10px] font-bold {{ $comment->status === 'unread' ? 'text-red-400' : 'text-gray-300' }} mt-0.5">{{ ucfirst($comment->status) }} · {{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    @include('mockups.partials.share_script')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-6 right-6 z-50 bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl shadow-2xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="fixed bottom-6 right-6 z-50 bg-red-500 text-white font-bold px-6 py-3 rounded-xl shadow-2xl">{{ session('error') }}</div>
    @endif
</x-app-layout>
