{{-- Shipping Label — Clean sectioned design inspired by reference --}}
<div style="width:100%; height:100%; font-family:Arial,'Helvetica Neue',sans-serif; background:#fff; display:flex; flex-direction:column; overflow:hidden; border:2px solid #000;">

    {{-- Header: Company + Order --}}
    <div style="padding:2.5mm 3mm; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #000;">
        <div>
            <div style="font-size:13px; font-weight:900; letter-spacing:0.5px; line-height:1;">{{ strtoupper(setting('company_name', 'CHHITO PASAL')) }}</div>
            <div style="font-size:7px; color:#444; margin-top:0.5mm;">{{ setting('billing_address', 'Kathmandu, Nepal') }}</div>
            <div style="font-size:7px; color:#444;">Ph: {{ setting('company_phone', '9800000000') }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:7px; color:#666; text-transform:uppercase; font-weight:700;">Order</div>
            <div style="font-size:16px; font-weight:900; line-height:1;">#{{ $order->id }}</div>
            <div style="font-size:7px; color:#444;">{{ $order->created_at->format('d M Y') }}</div>
        </div>
    </div>

    {{-- Package Details --}}
    <div style="padding:2mm 3mm; border-bottom:1px solid #ccc; font-size:8px; display:flex; gap:3mm;">
        <div style="font-weight:900; font-size:8px; text-transform:uppercase; white-space:nowrap;">Package<br>Details:</div>
        <div style="flex:1;">
            <div><span style="font-weight:700;">Items:</span> {{ $order->orderItems->sum('quantity') }} pcs — {{ $order->orderItems->map(fn($i) => $i->quantity.'× '.($i->product->name ?? 'Item'))->join(', ') }}</div>
            <div style="margin-top:0.5mm;"><span style="font-weight:700;">Weight:</span> {{ number_format($order->orderItems->sum(fn($i) => ($i->product->weight_grams ?? 500) * $i->quantity) / 1000, 1) }} Kg &nbsp; | &nbsp; <span style="font-weight:700;">Tracking:</span> {{ $order->pathao_consignment_id ?? 'Pending' }}</div>
        </div>
    </div>

    {{-- FROM --}}
    <div style="padding:2mm 3mm; border-bottom:2px solid #000;">
        <div style="font-size:11px; font-weight:900; margin-bottom:1mm;">FROM:</div>
        <div style="font-size:9px; font-weight:700;">{{ setting('company_name', 'Chhito Pasal') }}</div>
        <div style="font-size:8px; color:#333;">{{ setting('billing_address', 'Kathmandu, Nepal') }} | Ph: {{ setting('company_phone', '9800000000') }}</div>
    </div>

    {{-- TO --}}
    <div style="padding:2.5mm 3mm; flex:1;">
        <div style="font-size:13px; font-weight:900; margin-bottom:1.5mm;">TO:</div>
        <div style="font-size:14px; font-weight:900; line-height:1.2;">{{ $order->customer_name }}</div>
        <div style="font-size:11px; font-weight:700; margin-top:1.5mm;">{{ $order->address }}{{ $order->city ? ', '.$order->city : '' }}</div>
        <div style="font-size:11px; font-weight:700; margin-top:1mm;">Ph: {{ $order->customer_phone }}</div>
        @if($order->remarks)
            <div style="font-size:10px; font-weight:700; margin-top:1.5mm; border-top:1px dashed #ccc; padding-top:1mm;">Notes: {{ $order->remarks }}</div>
        @endif
    </div>

    {{-- Barcode --}}
    <div style="padding:1.5mm 3mm; text-align:center; border-top:2px solid #000;">
        <svg class="barcode" data-value="{{ $order->pathao_consignment_id ?? 'ORD-'.$order->id }}" style="width:95%; height:15mm;"></svg>
        <div style="font-size:10px; font-weight:700; letter-spacing:2px;">{{ $order->pathao_consignment_id ?? 'ORD-'.$order->id }}</div>
    </div>

    {{-- Footer: COD --}}
    <div style="padding:2mm 3mm; text-align:center; border-top:2px solid #000; background:#000; color:#fff;">
        <div style="font-size:14px; font-weight:900; letter-spacing:1px;">{{ ($order->total_amount - ($order->paid_amount ?? 0)) > 0 ? 'COD: Rs.'.number_format($order->total_amount - ($order->paid_amount ?? 0)) : 'PREPAID' }}</div>
    </div>

</div>
