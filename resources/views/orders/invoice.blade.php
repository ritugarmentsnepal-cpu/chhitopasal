<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Invoice - {{ setting('order_invoice_prefix', 'ORD-') }}{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    @media print {
      body { background: white; }
      .no-print { display: none !important; }
      .print-area { box-shadow: none !important; border: none !important; padding: 0 !important; }
    }
  </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-900 flex flex-col items-center py-10 min-h-screen">
  
  <div class="mb-6 space-x-4 no-print w-full max-w-3xl flex justify-between">
    <a href="{{ route('accounting.index', ['tab' => 'pos']) }}" class="px-4 py-2 bg-white text-gray-700 rounded-xl font-bold border border-gray-200 hover:bg-gray-50 shadow-sm">&larr; Back to POS</a>
    <button onclick="window.print()" class="px-6 py-2 bg-gray-900 text-white rounded-xl font-bold shadow-md hover:bg-gray-800 transition-colors flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
      Print Invoice
    </button>
  </div>

  <!-- Invoice Container -->
  <div class="print-area bg-white p-10 rounded-2xl shadow-xl w-full max-w-3xl border border-gray-100">
    
    <!-- Header -->
    <div class="flex justify-between items-start border-b-2 border-gray-100 pb-8 mb-8">
      <div>
        <h1 class="text-4xl font-black text-gray-900 tracking-tight">INVOICE</h1>
        <p class="text-sm font-bold text-gray-500 mt-1">{{ setting('order_invoice_prefix', 'ORD-') }}{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
      </div>
      <div class="text-right">
        <div class="flex items-center gap-2 justify-end mb-2">
          <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
          </div>
          <h2 class="text-xl font-black tracking-tight text-gray-900">{{ setting('company_name', 'ChhitoPasal Pvt. Ltd.') }}</h2>
        </div>
        <p class="text-sm text-gray-500">{{ setting('billing_address', 'Kathmandu, Nepal') }}</p>
        <p class="text-sm text-gray-500">Phone: {{ setting('contact_phone', '9800000000') }}</p>
        @if(setting('vat_number'))
          <p class="text-sm text-gray-500">VAT/PAN: {{ setting('vat_number') }}</p>
        @endif
      </div>
    </div>

    <!-- Details -->
    <div class="flex justify-between mb-10">
      <div>
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Billed To</h3>
        <p class="font-bold text-gray-900 text-lg">{{ $order->customer_name }}</p>
        <p class="text-sm text-gray-600">{{ $order->address }}, {{ $order->city }}</p>
        <p class="text-sm text-gray-600">{{ $order->customer_phone }}</p>
      </div>
      <div class="text-right">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Invoice Details</h3>
        <p class="text-sm text-gray-600"><span class="font-bold">Date:</span> {{ $order->created_at->format('M d, Y h:i A') }}</p>
        <p class="text-sm text-gray-600"><span class="font-bold">Status:</span> {{ ucfirst($order->status) }}</p>
        <p class="text-sm text-gray-600"><span class="font-bold">Payment:</span> <span class="{{ $order->payment_status == 'paid' ? 'text-green-600' : 'text-red-600' }} font-black">{{ strtoupper($order->payment_status) }}</span></p>
      </div>
    </div>

    <!-- Custom Print Details -->
    @if($order->isCustomPrint())
    <div class="mb-10 p-4 bg-gray-50 rounded-xl border border-gray-200">
      <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Custom Print Specifications</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-gray-600"><span class="font-bold">Print Method:</span> {{ $order->print_method ? strtoupper(str_replace('_', ' ', $order->print_method)) : 'N/A' }}</p>
          <p class="text-sm text-gray-600"><span class="font-bold">Positions:</span> 
            @if($order->print_positions)
              {{ implode(', ', array_map(fn($p) => ucwords(str_replace('_', ' ', $p)), $order->print_positions)) }}
            @else
              N/A
            @endif
          </p>
        </div>
        <div>
          @if($order->design_file)
            <p class="text-sm text-gray-600 font-bold mb-1">Design Attached</p>
          @endif
          @if($order->design_notes)
            <p class="text-xs text-gray-500 italic">"{{ $order->design_notes }}"</p>
          @endif
        </div>
      </div>
    </div>
    @endif

    <!-- Items Table -->
    <table class="w-full text-left mb-8">
      <thead class="border-b-2 border-gray-900">
        <tr>
          <th class="py-3 font-black text-gray-900">Item Description</th>
          <th class="py-3 font-black text-gray-900 text-center">Qty</th>
          <th class="py-3 font-black text-gray-900 text-right">Rate</th>
          <th class="py-3 font-black text-gray-900 text-right">Amount</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($order->orderItems as $item)
        <tr>
          <td class="py-4 align-top">
            <div class="font-bold text-gray-800">{{ $item->product ? $item->product->name : 'Deleted Product' }}</div>
            @if(!empty($item->size_breakdown))
              <div class="mt-1 flex flex-wrap gap-1">
                @foreach($item->size_breakdown as $size => $qty)
                  <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md text-[10px] font-bold border border-gray-200">{{ $size }}: {{ $qty }}</span>
                @endforeach
              </div>
            @endif
          </td>
          <td class="py-4 align-top text-center text-gray-600">{{ $item->quantity }}</td>
          <td class="py-4 align-top text-right text-gray-600">Rs. {{ number_format($item->price_at_purchase, 2) }}</td>
          <td class="py-4 align-top text-right font-bold text-gray-900">Rs. {{ number_format($item->quantity * $item->price_at_purchase, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <!-- Totals -->
    <div class="flex justify-end">
      <div class="w-1/2">
        <div class="flex justify-between py-2 border-b border-gray-100">
          <span class="font-bold text-gray-500">Subtotal</span>
          <span class="font-bold text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</span>
        </div>
        <div class="flex justify-between py-2 border-b-2 border-gray-900">
          <span class="font-bold text-gray-500">Total</span>
          <span class="font-bold text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</span>
        </div>
        @if($order->advance_amount > 0)
        <div class="flex justify-between py-2 border-b border-gray-100">
          <span class="font-bold text-green-600">Advance Paid</span>
          <span class="font-bold text-green-600">- Rs. {{ number_format($order->advance_amount, 2) }}</span>
        </div>
        <div class="flex justify-between py-4 border-b-4 border-gray-900 bg-gray-50 px-2 rounded-b-lg">
          <span class="text-xl font-black text-gray-900">Balance Due</span>
          <span class="text-xl font-black text-gray-900">Rs. {{ number_format($order->total_amount - $order->advance_amount, 2) }}</span>
        </div>
        @else
        <div class="flex justify-between py-4 border-b-4 border-gray-900">
          <span class="text-xl font-black text-gray-900">Total</span>
          <span class="text-xl font-black text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</span>
        </div>
        @endif
      </div>
    </div>

    <!-- Footer -->
    <div class="mt-16 text-center text-sm text-gray-400 font-bold">
      <p>Thank you for your business!</p>
      {!! nl2br(e(setting('invoice_terms', '1. Goods once sold will not be returned.
2. Subject to Kathmandu jurisdiction.'))) !!}
    </div>
  </div>

</body>
</html>
