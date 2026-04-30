<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Print — {{ $orders->count() }} Orders</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }

        @page {
            size: 80mm 100mm;
            margin: 0;
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            .no-print { display: none !important; }
            .print-page {
                width: 80mm !important;
                height: 100mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .print-page:last-child {
                page-break-after: auto;
            }
        }

        /* Screen preview */
        body { background: #cbd5e1; font-family: 'Segoe UI', Arial, sans-serif; }
        .preview-toolbar {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .preview-toolbar h2 { font-size: 18px; font-weight: 900; color: #111; }
        .preview-toolbar .btn-group { display: flex; gap: 8px; }
        .preview-toolbar button {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13px;
            border: none;
            cursor: pointer;
        }
        .btn-close { background: white; color: #555; border: 1px solid #ddd !important; }
        .btn-print { background: #111; color: white; }

        .preview-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 16px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }
        .print-page {
            width: 80mm;
            height: 100mm;
            background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            overflow: hidden;
            flex-shrink: 0;
        }
        .order-divider {
            width: 80mm;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
            color: #aaa;
            padding: 6px 0;
            border-top: 2px dashed #bbb;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <div class="preview-toolbar no-print">
        <h2>Thermal Print — {{ $orders->count() }} Orders</h2>
        <div class="btn-group">
            <button class="btn-close" onclick="window.close()">Close</button>
            <button class="btn-print" onclick="window.print()">🖨 Print All</button>
        </div>
    </div>

    <div class="preview-container">
        @foreach($orders as $order)
            <div class="print-page">
                @include('orders.partials.label_80x100', ['order' => $order])
            </div>
            <div class="print-page">
                @include('orders.partials.invoice_80x100', ['order' => $order])
            </div>
            @if(!$loop->last)
                <div class="order-divider no-print">— Order #{{ $order->id }} done —</div>
            @endif
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.barcode').forEach(function(svg) {
                const value = svg.getAttribute('data-value');
                if (value) {
                    JsBarcode(svg, value, {
                        format: "CODE128", width: 1.5, height: 30,
                        displayValue: false, margin: 0
                    });
                }
            });
            setTimeout(() => window.print(), 600);
        });
    </script>
</body>
</html>
