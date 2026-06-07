<x-app-layout>
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

  {{-- ── PAGE HEADER ────────────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="text-2xl font-black text-gray-900 tracking-tight">Analytics</h2>
      <p class="text-gray-500 font-medium mt-1">Storefront funnel, ad performance & conversions.</p>
    </div>
    <form method="GET" action="{{ route('analytics.index') }}">
      <select name="date_filter" onchange="this.form.submit()"
        class="rounded-xl border-gray-200 text-sm font-bold bg-white focus:ring-2 focus:ring-mango shadow-sm py-2.5 px-4 cursor-pointer">
        @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'all_time' => 'All Time'] as $val => $label)
          <option value="{{ $val }}" {{ $dateFilter == $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </form>
  </div>

  {{-- ── TAB SWITCHER ────────────────────────────────────────────────────── --}}
  <div x-data="{ activeTab: 'storefront' }">

    <div class="flex gap-2 bg-white p-2 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100 w-fit">
      <button @click="activeTab = 'storefront'"
        :class="activeTab === 'storefront' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'"
        class="px-5 py-2.5 rounded-full font-bold text-sm transition-all">
        🏪 Storefront Analytics
      </button>
      <button @click="activeTab = 'ads'"
        :class="activeTab === 'ads' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'"
        class="px-5 py-2.5 rounded-full font-bold text-sm transition-all">
        📣 Meta Ads Performance
      </button>
    </div>

    {{-- ╔══════════════════════════════════════════════════╗ --}}
    {{-- ║     TAB 1: STOREFRONT ANALYTICS      ║ --}}
    {{-- ╚══════════════════════════════════════════════════╝ --}}
    <div x-show="activeTab === 'storefront'" x-transition class="space-y-8 mt-6">

      {{-- KPI TOP ROW --}}
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
          $kpis = [
            ['label' => 'Total Visitors',  'value' => number_format($totalVisitors),     'icon' => '👁️', 'color' => 'blue'],
            ['label' => 'Bounce Rate',   'value' => $bounceRate . '%',           'icon' => '💨', 'color' => 'red'],
            ['label' => 'Conversion Rate', 'value' => $conversionRate . '%',         'icon' => '🎯', 'color' => 'green'],
            ['label' => 'Revenue',     'value' => 'Rs. ' . number_format($totalRevenue), 'icon' => '💰', 'color' => 'yellow'],
          ];
          $colorMap = ['blue' => 'bg-blue-50 text-blue-700', 'red' => 'bg-red-50 text-red-700', 'green' => 'bg-green-50 text-green-700', 'yellow' => 'bg-amber-50 text-amber-700'];
        @endphp
        @foreach($kpis as $kpi)
        <div class="bg-white rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-xl">{{ $kpi['icon'] }}</span>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $kpi['label'] }}</span>
          </div>
          <div class="text-2xl font-black text-gray-900 ">{{ $kpi['value'] }}</div>
        </div>
        @endforeach
      </div>

      {{-- FUNNEL CHART + NUMBERS --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Funnel Chart --}}
        <div class="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
          <h3 class="text-lg font-black text-gray-900 mb-1">Customer Funnel</h3>
          <p class="text-xs text-gray-400 font-medium mb-5">How many visitors reach each stage</p>
          <div class="relative h-64">
            <canvas id="funnelChart"></canvas>
          </div>
        </div>

        {{-- Funnel Step Breakdown --}}
        <div class="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
          <h3 class="text-lg font-black text-gray-900 mb-1">Funnel Breakdown</h3>
          <p class="text-xs text-gray-400 font-medium mb-5">Step-by-step drop-off analysis</p>
          @php
            $steps = [
              ['label' => 'Total Visitors',    'count' => $totalVisitors,      'emoji' => '👁️', 'color' => '#6366f1'],
              ['label' => 'Explored Products',  'count' => $sessionsWithProductView, 'emoji' => '🔍', 'color' => '#8b5cf6'],
              ['label' => 'Added to Cart',    'count' => $sessionsWithAddToCart,  'emoji' => '🛒', 'color' => '#f59e0b'],
              ['label' => 'Initiated Checkout',  'count' => $sessionsWithCheckout,  'emoji' => '📋', 'color' => '#10b981'],
              ['label' => 'Completed Order',   'count' => $totalOrders,       'emoji' => '✅', 'color' => '#22c55e'],
            ];
          @endphp
          <div class="space-y-3">
            @foreach($steps as $i => $step)
            @php
              $pct = $totalVisitors > 0 ? round(($step['count'] / $totalVisitors) * 100) : 0;
              $dropOff = ($i > 0 && $steps[$i-1]['count'] > 0) ? round((1 - $step['count'] / $steps[$i-1]['count']) * 100) : null;
            @endphp
            <div>
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-bold text-gray-700">{{ $step['emoji'] }} {{ $step['label'] }}</span>
                <div class="flex items-center gap-2">
                  @if($dropOff !== null && $dropOff > 0)
                    <span class="text-xs font-bold text-red-400">-{{ $dropOff }}%</span>
                  @endif
                  <span class="text-sm font-black text-gray-900 ">{{ number_format($step['count']) }}</span>
                </div>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all duration-700"
                   style="width: {{ $pct }}%; background-color: {{ $step['color'] }}"></div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- TOP PRODUCTS TABLE --}}
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
          <h3 class="text-lg font-black text-gray-900 ">🔥 Top Performing Products</h3>
          <p class="text-xs text-gray-400 font-medium mt-1">Most viewed products and their cart conversion</p>
        </div>
        @if($topViewedProducts->isEmpty())
        <div class="p-10 text-center text-gray-400 font-bold">No product view data yet.</div>
        @else
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">#</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Product</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Views</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Add to Cart</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Price</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @foreach($topViewedProducts as $i => $event)
              <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 text-sm font-black text-gray-400">{{ $i + 1 }}</td>
                <td class="px-6 py-4">
                  <span class="font-bold text-gray-900 text-sm">
                    {{ optional($event->product)->name ?? 'Unknown Product' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-700 font-black text-sm">
                    {{ $event->view_count }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  @php $cartCount = $topCartProducts[$event->product_id] ?? 0; @endphp
                  <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl {{ $cartCount > 0 ? 'bg-amber-50 text-amber-700' : 'bg-gray-50 text-gray-400' }} font-black text-sm">
                    {{ $cartCount }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right text-sm font-bold text-gray-600">
                  Rs. {{ number_format(optional($event->product)->price ?? 0) }}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>

    {{-- ╔══════════════════════════════════════════════════╗ --}}
    {{-- ║     TAB 2: META ADS PERFORMANCE      ║ --}}
    {{-- ╚══════════════════════════════════════════════════╝ --}}
    <div x-show="activeTab === 'ads'" x-transition class="space-y-8 mt-6">

      {{-- ROAS CALCULATOR --}}
      <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-[24px] p-6 shadow-[0_10px_40px_rgb(79,70,229,0.3)]"
         x-data="{ adSpend: '', revenue: {{ $totalRevenue }}, orders: {{ $totalOrders }} }">
        <div class="flex items-center gap-3 mb-6">
          <span class="text-2xl">🧮</span>
          <div>
            <h3 class="text-lg font-black">ROAS & CPA Calculator</h3>
            <p class="text-sm text-blue-200 font-medium">Enter your ad spend to calculate your true return</p>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white/15 backdrop-blur rounded-2xl p-4">
            <label class="text-xs font-bold text-blue-200 uppercase tracking-wider block mb-2">Ad Spend (Rs.)</label>
            <input type="number" x-model.number="adSpend" placeholder="e.g. 5000"
              class="w-full bg-white/20 border border-white/30 rounded-xl text-white font-black text-xl px-3 py-2 placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-white/50 [appearance:textfield]">
          </div>
          <div class="bg-white/15 backdrop-blur rounded-2xl p-4">
            <div class="text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Revenue Generated</div>
            <div class="text-xl font-black">Rs. {{ number_format($totalRevenue) }}</div>
          </div>
          <div class="bg-white/15 backdrop-blur rounded-2xl p-4">
            <div class="text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">ROAS</div>
            <div class="text-xl font-black" x-text="adSpend > 0 ? (revenue / adSpend).toFixed(2) + 'x' : '—'"></div>
            <div class="text-xs text-blue-200 mt-1" x-show="adSpend > 0" x-text="revenue / adSpend >= 2 ? '✅ Profitable!' : '⚠️ Below 2x target'"></div>
          </div>
          <div class="bg-white/15 backdrop-blur rounded-2xl p-4">
            <div class="text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Cost Per Order (CPA)</div>
            <div class="text-xl font-black" x-text="(adSpend > 0 && orders > 0) ? 'Rs. ' + (adSpend / orders).toFixed(0) : '—'"></div>
          </div>
        </div>
      </div>

      {{-- CAMPAIGN PERFORMANCE TABLE --}}
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          </div>
          <div>
            <h3 class="text-lg font-black text-gray-900 ">Campaign Performance</h3>
            <p class="text-xs text-gray-400 font-medium">Tracked via UTM parameters in your Facebook Ad URLs</p>
          </div>
        </div>

        @if($campaigns->isEmpty())
        <div class="p-14 text-center">
          <div class="text-5xl mb-4">📡</div>
          <h4 class="text-lg font-black text-gray-900 ">No Ad Traffic Detected Yet</h4>
          <p class="text-gray-500 mt-2 max-w-sm mx-auto text-sm font-medium">Add UTM parameters to your Facebook Ad URLs to see performance here.<br><br>
          Example: <code class="bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs font-mono">chhitopasal.com?utm_source=facebook&amp;utm_campaign=summer_sale</code></p>
        </div>
        @else
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Campaign</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Source</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Visitors</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Add to Cart</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Orders</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Conv. %</th>
                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @foreach($campaigns as $campaign)
              <tr class="hover:bg-gray-50/50 transition-colors">
                <td class="px-6 py-4 font-black text-gray-900 text-sm">{{ $campaign->utm_campaign }}</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                    {{ $campaign->utm_source ?? 'unknown' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center font-medium text-gray-600">{{ number_format($campaign->total_clicks) }}</td>
                <td class="px-6 py-4 text-center font-medium text-gray-600">{{ number_format($campaign->add_to_carts) }}</td>
                <td class="px-6 py-4 text-center font-black text-gray-900 ">{{ number_format($campaign->orders) }}</td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black {{ $campaign->conversion_rate > 2 ? 'bg-green-100 text-green-800' : ($campaign->conversion_rate > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500') }}">
                    {{ $campaign->conversion_rate }}%
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-black text-green-600">Rs. {{ number_format($campaign->revenue) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>

      {{-- CATEGORY BREAKDOWN CHART --}}
      @if(!$categoryBreakdown->isEmpty())
      <div class="bg-white rounded-[24px] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
        <h3 class="text-lg font-black text-gray-900 mb-1">Category Interest (Add to Cart)</h3>
        <p class="text-xs text-gray-400 font-medium mb-5">Which categories are getting the most cart activity</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
          <div class="relative h-64">
            <canvas id="categoryChart"></canvas>
          </div>
          <div class="space-y-3">
            @php
              $total = $categoryBreakdown->sum('count');
              $chartColors = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#ec4899','#14b8a6','#f97316'];
            @endphp
            @foreach($categoryBreakdown as $i => $item)
            @php $pct = $total > 0 ? round(($item->count / $total) * 100) : 0; @endphp
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $chartColors[$i % count($chartColors)] }}"></div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between text-sm mb-1">
                  <span class="font-bold text-gray-700 truncate">{{ optional($item->category)->name ?? 'Unknown' }}</span>
                  <span class="font-black text-gray-900 ml-2">{{ $pct }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                  <div class="h-1.5 rounded-full" style="width: {{ $pct }}%; background-color: {{ $chartColors[$i % count($chartColors)] }}"></div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endif

    </div>{{-- end tab 2 --}}

  </div>{{-- end x-data tabs --}}
</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Funnel Chart
  const funnelCtx = document.getElementById('funnelChart');
  if (funnelCtx) {
    new Chart(funnelCtx, {
      type: 'bar',
      data: {
        labels: {!! json_encode($funnelData['labels']) !!},
        datasets: [{
          data: {!! json_encode($funnelData['data']) !!},
          backgroundColor: ['#6366f1','#8b5cf6','#f59e0b','#10b981','#22c55e'],
          borderRadius: 8,
          borderSkipped: false,
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: '#f3f4f6' }, ticks: { font: { weight: '700' } } },
          y: { grid: { display: false }, ticks: { font: { weight: '700' } } }
        }
      }
    });
  }

  // Category Pie Chart
  const catCtx = document.getElementById('categoryChart');
  if (catCtx) {
    new Chart(catCtx, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($categoryChartData['labels']) !!},
        datasets: [{
          data: {!! json_encode($categoryChartData['data']) !!},
          backgroundColor: ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#ec4899','#14b8a6','#f97316'],
          borderWidth: 0,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
        },
        cutout: '65%'
      }
    });
  }
});
</script>
</x-app-layout>
