<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Klasifikasi Pergerakan Stok (ABC)</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 10px; margin: 20px; color: #000; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; }
        hr { border-top: 1px dashed #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; font-size: 9px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { display: flex; justify-content: space-between; margin: 5px 0; font-size: 10px; }
        .summary span { display: inline-block; }
        .footer { text-align: center; margin-top: 15px; font-size: 9px; }
        .not-moving { background: #f8d7da; }
        .slow-moving { background: #fff3cd; }
        .moving { background: #cfe2ff; }
        .fast-moving { background: #d1e7dd; }
        @media print {
            body { margin: 0; padding: 5px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo)
            <div style="text-align:center;margin-bottom:8px">
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" style="max-height:50px;max-width:100%;border-radius:8px;">
            </div>
        @endif
        <h2>{{ $settings->company_name ?? 'Perusahaan' }}</h2>
        <p><strong>Laporan Klasifikasi Pergerakan Stok (ABC)</strong></p>
        <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    </div>
    <hr>

    @php
        $totalNotMoving = $products->filter(fn($p) => ($soldQty[$p->id] ?? 0) == 0)->count();
        $totalSlow = $products->filter(function($p) use ($soldQty, $q1) {
            $sold = $soldQty[$p->id] ?? 0;
            return $sold > 0 && $sold < $q1;
        })->count();
        $totalMoving = $products->filter(function($p) use ($soldQty, $q1, $q3) {
            $sold = $soldQty[$p->id] ?? 0;
            return $sold >= $q1 && $sold < $q3;
        })->count();
        $totalFast = $products->filter(function($p) use ($soldQty, $q3) {
            $sold = $soldQty[$p->id] ?? 0;
            return $sold >= $q3 && $sold > 0;
        })->count();
    @endphp

    <table style="margin-bottom:8px;">
        <tr>
            <td style="padding:3px 6px; text-align:center; background:#f8d7da;"><strong>Not Moving:</strong> {{ $totalNotMoving }}</td>
            <td style="padding:3px 6px; text-align:center; background:#fff3cd;"><strong>Slow Moving:</strong> {{ $totalSlow }}</td>
            <td style="padding:3px 6px; text-align:center; background:#cfe2ff;"><strong>Moving:</strong> {{ $totalMoving }}</td>
            <td style="padding:3px 6px; text-align:center; background:#d1e7dd;"><strong>Fast Moving:</strong> {{ $totalFast }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="30">#</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th width="60" class="text-center">Stok</th>
                <th width="60" class="text-center">Terjual</th>
                <th width="70" class="text-center">Rata/Bln</th>
                <th>Klasifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                @php
                    $sold = $soldQty[$product->id] ?? 0;
                    $avgMonthly = $periodMonths > 0 ? round($sold / $periodMonths, 1) : 0;
                    $totalStock = $product->stocks->sum('quantity');
                    if ($sold == 0) {
                        $class = 'Not Moving';
                        $rowClass = 'not-moving';
                    } elseif ($sold < $q1) {
                        $class = 'Slow Moving';
                        $rowClass = 'slow-moving';
                    } elseif ($sold < $q3) {
                        $class = 'Moving';
                        $rowClass = 'moving';
                    } else {
                        $class = 'Fast Moving';
                        $rowClass = 'fast-moving';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $product->barcode ?? '-' }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td class="text-center">{{ $totalStock }}</td>
                    <td class="text-center">{{ $sold }}</td>
                    <td class="text-center">{{ $avgMonthly }}</td>
                    <td><strong>{{ $class }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:15px; font-size:9px;">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li><strong>Not Moving</strong>: Produk dengan penjualan 0 selama periode</li>
            <li><strong>Slow Moving</strong>: Penjualan di bawah Q1 ({{ $q1 }})</li>
            <li><strong>Moving</strong>: Penjualan antara Q1 dan Q3 ({{ $q1 }} - {{ $q3 }})</li>
            <li><strong>Fast Moving</strong>: Penjualan di atas Q3 ({{ $q3 }})</li>
        </ul>
    </div>

    <div class="footer">
        <hr>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
