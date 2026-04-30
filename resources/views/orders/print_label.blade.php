<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label & Invoice #{{ $order->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }

        @page {
            size: 80mm 100mm;
            margin: 0;
        }

        @media print {
            html, body { 
                background: white !important; 
                margin: 0 !important; 
                padding: 0 !important;
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
            gap: 20px;
        }
        .print-page {
            width: 80mm;
            height: 100mm;
            background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            overflow: hidden;
            flex-shrink: 0;
        }
        .page-label {
            font-size: 11px;
            font-weight: 800;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <div class="preview-toolbar no-print">
        <h2>Order #{{ $order->id }}</h2>
        <div class="btn-group">
            <button class="btn-close" onclick="window.close()">Close</button>
            <button class="btn-print" onclick="window.print()">🖨 Print</button>
        </div>
    </div>

    <div class="preview-container">
        <div class="page-label no-print">Shipping Label</div>
        <div class="print-page">
            @include('orders.partials.label_80x100', ['order' => $order])
        </div>

        <div class="page-label no-print">Invoice</div>
        <div class="print-page">
            @include('orders.partials.invoice_80x100', ['order' => $order])
        </div>
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
