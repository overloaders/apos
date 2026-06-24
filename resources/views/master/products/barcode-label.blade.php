<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; }
        .no-print { margin-bottom: 20px; }
        .no-print .btn { padding: 8px 20px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .no-print .btn:hover { background: #0b5ed7; }
        .labels { display: flex; flex-wrap: wrap; gap: 15px; }
        .label-item { width: 220px; border: 1px dashed #ccc; padding: 10px; text-align: center; page-break-inside: avoid; }
        .label-item .product-name { font-size: 11px; font-weight: bold; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .label-item .product-price { font-size: 12px; font-weight: bold; color: #d00; margin-top: 2px; }
        .label-item .product-sku { font-size: 9px; color: #666; margin-top: 2px; }
        .label-item svg { display: block; margin: 4px auto; max-width: 100%; }
        @media print {
            .no-print { display: none; }
            .label-item { border: none; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="javascript:window.print()" class="btn"><i class="fas fa-print"></i> Cetak</a>
        <a href="{{ route('master.products.index') }}" class="btn" style="background:#6c757d;">Kembali</a>
    </div>

    <div class="labels">
        @php $items = isset($products) ? $products : [$product]; @endphp
        @foreach($items as $p)
            <div class="label-item">
                <div class="product-name">{{ $p->name }}</div>
                {!! \App\Helpers\BarcodeHelper::generateBarcodeSVG($p->barcode ?? $p->code, 200, 60) !!}
                <div class="product-price">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</div>
                <div class="product-sku">{{ $p->barcode ?? $p->code }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
