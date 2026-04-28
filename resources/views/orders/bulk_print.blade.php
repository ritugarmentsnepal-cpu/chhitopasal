<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Print — A4 Layout ({{ $orders->count() }} Orders)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }

        @page {
            size: 297mm 210mm;
            margin: 0;
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            .no-print { display: none !important; }
            .a4-page {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .a4-page:last-child {
                page-break-after: auto;
            }
        }

        /* Screen preview */
        body { background: #cbd5e1; font-family: 'Segoe UI', Arial, sans-serif; }
        .preview-toolbar {
            max-width: 1100px;
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

        .pages-container {
            margin: 0 auto;
            padding: 0 16px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        .a4-page {
            width: 297mm;
            height: 210mm;
            background: white;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            overflow: hidden;
            padding: 5mm;
            display: flex;
            flex-direction: column;
        }

        .label-row {
            flex: 1;
            display: flex;
            align-items: stretch;
            gap: 0;
        }
        .label-row:first-child {
            border-bottom: 1px dashed #999;
        }

        .cell {
            flex: 1;
            overflow: hidden;
            min-width: 0;
        }
    </style>
</head>
<body>

    <div class="preview-toolbar no-print">
        <h2>A4 Bulk Print — {{ $orders->count() }} Orders (3 per page)</h2>
        <div class="btn-group">
            <button class="btn-close" onclick="window.close()">Close</button>
            <button class="btn-print" onclick="window.print()">🖨 Print All</button>
        </div>
    </div>

    <div class="pages-container">
        @foreach($orders->chunk(3) as $chunk)
        <div class="a4-page">
            {{-- Row 1: Shipping Labels --}}
            <div class="label-row">
                @foreach($chunk as $order)
                <div class="cell">
                    @include('orders.partials.label_80x100', ['order' => $order])
                </div>
                @endforeach
                @for($i = $chunk->count(); $i < 3; $i++)
                <div class="cell"></div>
                @endfor
            </div>

            {{-- Row 2: Invoices (matching order) --}}
            <div class="label-row">
                @foreach($chunk as $order)
                <div class="cell">
                    @include('orders.partials.invoice_80x100', ['order' => $order])
                </div>
                @endforeach
                @for($i = $chunk->count(); $i < 3; $i++)
                <div class="cell"></div>
                @endfor
            </div>
        </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.barcode').forEach(function(svg) {
                const value = svg.getAttribute('data-value');
                if (value) {
                    JsBarcode(svg, value, {
                        format: "CODE128", width: 1.2, height: 28,
                        displayValue: false, margin: 0
                    });
                }
            });
            setTimeout(() => window.print(), 600);
        });
    </script>
</body>
</html>
