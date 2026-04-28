{{-- Invoice — Clean sectioned design matching label style --}}
<div style="width:100%; height:100%; font-family:Arial,'Helvetica Neue',sans-serif; background:#fff; display:flex; flex-direction:column; overflow:hidden; border:2px solid #000;">

    {{-- Header --}}
    <div style="padding:2.5mm 3mm; text-align:center; border-bottom:2px solid #000;">
        <div style="font-size:14px; font-weight:900; letter-spacing:1px;">TAX INVOICE</div>
        <div style="font-size:9px; font-weight:700; margin-top:0.5mm;">{{ strtoupper(setting('company_name', 'CHHITO PASAL')) }}</div>
        <div style="font-size:7px; color:#444;">{{ setting('billing_address', 'Kathmandu, Nepal') }} | Ph: {{ setting('company_phone', '9800000000') }}{{ setting('company_email') ? ' | '.setting('company_email') : '' }}</div>
        @if(setting('vat_number'))
        <div style="font-size:9px; font-weight:900; margin-top:0.5mm;">PAN/VAT: {{ setting('vat_number') }}</div>
        @endif
    </div>

    {{-- Invoice Meta --}}
    <div style="padding:1.5mm 3mm; font-size:9px; font-weight:900; display:flex; justify-content:space-between; border-bottom:1px solid #ccc;">
        <span>INV-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
        <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
    </div>

    {{-- Bill To --}}
    <div style="padding:2mm 3mm; border-bottom:2px solid #000;">
        <div style="font-size:10px; font-weight:900;">BILL TO:</div>
        <div style="font-size:11px; font-weight:900; margin-top:0.5mm;">{{ $order->customer_name }}</div>
        <div style="font-size:8px; color:#333; margin-top:0.5mm;">{{ $order->customer_phone }} | {{ $order->address }}{{ $order->city ? ', '.$order->city : '' }}</div>
    </div>

    {{-- Items Table --}}
    <div style="flex:1; min-height:0; overflow:hidden; border-bottom:2px solid #000;">
        {{-- Table Header --}}
        <div style="display:flex; padding:1.5mm 3mm; border-bottom:1px solid #000; font-size:7px; font-weight:900; text-transform:uppercase; background:#f5f5f5;">
            <span style="flex:2;">Item</span>
            <span style="width:10mm; text-align:center;">Qty</span>
            <span style="width:16mm; text-align:right;">Rate</span>
            <span style="width:18mm; text-align:right;">Amount</span>
        </div>
        {{-- Rows --}}
        @foreach($order->orderItems as $item)
        <div style="display:flex; padding:1.5mm 3mm; border-bottom:0.5px solid #ddd; font-size:9px;">
            <span style="flex:2; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $item->product->name ?? 'Product' }}</span>
            <span style="width:10mm; text-align:center;">{{ $item->quantity }}</span>
            <span style="width:16mm; text-align:right; color:#444;">{{ number_format($item->price_at_purchase) }}</span>
            <span style="width:18mm; text-align:right; font-weight:900;">{{ number_format($item->quantity * $item->price_at_purchase) }}</span>
        </div>
        @endforeach
    </div>

    {{-- Totals --}}
    <div style="padding:1.5mm 3mm; border-bottom:1px solid #ccc;">
        <div style="display:flex; justify-content:space-between; font-size:9px; font-weight:700;">
            <span>Subtotal</span>
            <span>Rs. {{ number_format($order->orderItems->sum(fn($i) => $i->quantity * $i->price_at_purchase)) }}</span>
        </div>
        @if(($order->delivery_charge ?? 0) > 0)
        <div style="display:flex; justify-content:space-between; font-size:8px; color:#444; margin-top:0.3mm;">
            <span>Delivery</span>
            <span>Rs. {{ number_format($order->delivery_charge) }}</span>
        </div>
        @endif
        @if(($order->paid_amount ?? 0) > 0)
        <div style="display:flex; justify-content:space-between; font-size:8px; color:#444; margin-top:0.3mm;">
            <span>Advance Paid</span>
            <span>- Rs. {{ number_format($order->paid_amount) }}</span>
        </div>
        @endif
    </div>

    {{-- Grand Total --}}
    <div style="padding:2mm 3mm; display:flex; justify-content:space-between; align-items:center; background:#000; color:#fff;">
        <span style="font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:1px;">Total Due</span>
        <span style="font-size:16px; font-weight:900;">Rs. {{ number_format($order->total_amount - ($order->paid_amount ?? 0)) }}</span>
    </div>

    {{-- Footer --}}
    <div style="padding:1.5mm 3mm; text-align:center; font-size:7px; color:#888;">
        Thank you for your purchase! | Goods once sold will not be returned.
    </div>

</div>
