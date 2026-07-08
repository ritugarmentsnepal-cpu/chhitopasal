    <div class="space-y-6 mt-6">

      {{-- KPI CARDS --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-xl">👁️</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Sessions</span>
          </div>
          <div class="text-2xl font-black text-gray-900 ">{{ number_format($data['totalSessions']) }}</div>
        </div>
        <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-xl">✅</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Converted Sessions</span>
          </div>
          <div class="text-2xl font-black text-emerald-600">{{ number_format($data['convertedSessions']) }}</div>
        </div>
        <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-xl">🎯</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Conversion Rate</span>
          </div>
          <div class="text-2xl font-black text-gray-900 ">{{ $data['conversionRate'] }}%</div>
        </div>
      </div>

      {{-- FILTERS --}}
      <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 ">
        <form method="GET" action="{{ route('activity-log.index') }}" class="flex flex-wrap items-end gap-3">
          <input type="hidden" name="tab" value="customer">

          <div class="flex-1 min-w-[130px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">From</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
          </div>

          <div class="flex-1 min-w-[130px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">To</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
          </div>

          <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">UTM Source</label>
            <select name="utm_source" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
              <option value="">All Sources</option>
              @foreach($data['utmSources'] as $source)
                <option value="{{ $source }}" {{ request('utm_source') == $source ? 'selected' : '' }}>{{ $source }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex-1 min-w-[140px]">
            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Conversion</label>
            <select name="converted" class="w-full rounded-xl border-gray-200  text-sm font-bold focus:ring-2 focus:ring-mango py-2.5 px-3">
              <option value="">All Sessions</option>
              <option value="1" {{ request('converted') === '1' ? 'selected' : '' }}>Converted Only</option>
            </select>
          </div>

          <div class="flex gap-2">
            <button type="submit" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:opacity-90 transition-opacity shadow-lg shadow-gray-900/20">
              Filter
            </button>
            <a href="{{ route('activity-log.index', ['tab' => 'customer']) }}" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-gray-200 transition-colors">
              Clear
            </a>
          </div>
        </form>
      </div>

      {{-- SESSION LIST --}}
      <div class="space-y-4" x-data="{ expandedSession: null }">
        @forelse($data['sessions'] as $session)
        @php
          $eventCounts = $session->events->groupBy('event_type')->map->count();
          $hasOrder = $session->orders->isNotEmpty();
          $eventTypes = [
            'page_view' => ['emoji' => '📄', 'label' => 'Page Views', 'color' => 'bg-gray-100 text-gray-600 '],
            'view_product' => ['emoji' => '🔍', 'label' => 'Product Views', 'color' => 'bg-blue-50 text-blue-700 '],
            'add_to_cart' => ['emoji' => '🛒', 'label' => 'Add to Cart', 'color' => 'bg-amber-50 text-amber-700 '],
            'initiate_checkout' => ['emoji' => '📋', 'label' => 'Checkout', 'color' => 'bg-emerald-50 text-emerald-700 '],
          ];
        @endphp
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden {{ $hasOrder ? 'ring-2 ring-emerald-200 ' : '' }}">
          <div class="px-6 py-4 cursor-pointer hover:bg-gray-50/50 transition-colors"
             @click="expandedSession = expandedSession === '{{ $session->session_id }}' ? null : '{{ $session->session_id }}'">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                {{-- Journey indicator --}}
                <div class="flex items-center gap-1">
                  @foreach($eventTypes as $type => $info)
                    @if(isset($eventCounts[$type]))
                      <div class="w-3 h-3 rounded-full {{ $type === 'add_to_cart' ? 'bg-amber-400' : ($type === 'view_product' ? 'bg-blue-400' : ($type === 'initiate_checkout' ? 'bg-emerald-400' : 'bg-gray-300')) }}" title="{{ $info['label'] }}: {{ $eventCounts[$type] }}"></div>
                    @else
                      <div class="w-3 h-3 rounded-full bg-gray-100 " title="{{ $info['label'] }}: 0"></div>
                    @endif
                  @endforeach
                  @if($hasOrder)
                    <div class="w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200" title="Ordered!"></div>
                  @else
                    <div class="w-3 h-3 rounded-full bg-gray-100 " title="No Order"></div>
                  @endif
                </div>

                <div>
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 text-sm">{{ $session->ip_address ?? 'Visitor' }} · {{ $session->created_at->format('M d, h:i A') }}</span>
                    @if($hasOrder)
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 ">
                        ✅ CONVERTED
                      </span>
                    @endif
                    @if($session->utm_source)
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600 ">
                        {{ $session->utm_source }}
                      </span>
                    @endif
                  </div>
                  <div class="text-xs text-gray-400 mt-0.5">
                    {{ $session->created_at->format('M d, Y h:i A') }} · {{ $session->events->count() }} events
                    @if($session->landing_page_url)
                      · 🔗 {{ parse_url($session->landing_page_url, PHP_URL_PATH) ?: '/' }}
                    @endif
                  </div>
                </div>
              </div>

              {{-- Event count pills --}}
              <div class="hidden md:flex items-center gap-2">
                @foreach($eventTypes as $type => $info)
                  @if(isset($eventCounts[$type]))
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold {{ $info['color'] }}">
                    {{ $info['emoji'] }} {{ $eventCounts[$type] }}
                  </span>
                  @endif
                @endforeach
                @if($hasOrder)
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 ">
                    💰 Rs. {{ number_format($session->orders->sum('total_amount')) }}
                  </span>
                @endif
              </div>

              <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="expandedSession === '{{ $session->session_id }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>

          {{-- Expanded: Event Timeline --}}
          <div x-show="expandedSession === '{{ $session->session_id }}'" x-collapse x-cloak>
            <div class="px-6 pb-5 pt-2 border-t border-gray-100 ">
              <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Event Timeline</div>
              <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($session->events->sortBy('created_at') as $event)
                @php $evInfo = $eventTypes[$event->event_type] ?? ['emoji' => '📌', 'label' => $event->event_type, 'color' => 'bg-gray-100 text-gray-600']; @endphp
                <div class="flex items-center gap-3 text-sm">
                  <span class="text-xs text-gray-400 w-16 shrink-0">{{ $event->created_at->format('H:i:s') }}</span>
                  <span class="text-base">{{ $evInfo['emoji'] }}</span>
                  <span class="font-medium text-gray-700 ">{{ $evInfo['label'] }}</span>
                  @if($event->product)
                    <span class="text-xs text-gray-500">— {{ $event->product->name }}</span>
                  @endif
                  @if($event->url)
                    <span class="text-xs text-gray-400 truncate max-w-[200px]">{{ parse_url($event->url, PHP_URL_PATH) }}</span>
                  @endif
                </div>
                @endforeach

                {{-- Show orders if converted --}}
                @foreach($session->orders as $order)
                <div class="flex items-center gap-3 text-sm mt-2 pt-2 border-t border-gray-100 ">
                  <span class="text-xs text-gray-400 w-16 shrink-0">{{ $order->created_at->format('H:i:s') }}</span>
                  <span class="text-base">🎉</span>
                  <span class="font-bold text-emerald-600">Order #{{ $order->id }}</span>
                  <span class="text-xs text-gray-500">— {{ $order->customer_name }} · Rs. {{ number_format($order->total_amount) }}</span>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : ($order->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                    {{ ucfirst($order->status) }}
                  </span>
                </div>
                @endforeach
              </div>

              {{-- Session metadata --}}
              <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-3 text-xs text-gray-400">
                @if($session->ip_address)
                  <span>🌐 {{ $session->ip_address }}</span>
                @endif
                @if($session->utm_campaign)
                  <span>📣 Campaign: {{ $session->utm_campaign }}</span>
                @endif
                @if($session->utm_medium)
                  <span>📡 Medium: {{ $session->utm_medium }}</span>
                @endif
                @if($session->fbclid)
                  <span>📘 Facebook Click ID</span>
                @endif
              </div>
            </div>
          </div>
        </div>
        @empty
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 p-14 text-center">
          <div class="text-5xl mb-4">🛍️</div>
          <h4 class="text-lg font-black text-gray-900 ">No Visitor Sessions Found</h4>
          <p class="text-gray-500 mt-2 text-sm font-medium">Customer sessions will appear here once visitors browse the storefront.</p>
        </div>
        @endforelse

        @if($data['sessions']->hasPages())
        <div class="mt-4">
          {{ $data['sessions']->links() }}
        </div>
        @endif
      </div>
    </div>
