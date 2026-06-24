<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan (Per-Item)</title>
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
        .sale-total td { font-weight: bold; }
        .grand-total td { font-weight: bold; font-size: 13px; border-top: 3px solid #000; background: #f0f0f0; }
        .footer { text-align: center; margin-top: 15px; font-size: 9px; }
        @media print {
            body { margin: 0; padding: 5px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right;margin-bottom:10px">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <div class="header">
        @if($settings->logo)
            <div style="text-align:center;margin-bottom:8px">
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" style="max-height:50px;max-width:100%;border-radius:8px;">
            </div>
        @endif
        <h2>{{ $settings->company_name ?? 'LAPORAN PENJUALAN DETAIL' }}</h2>
        <p>{{ $settings->address ?? '' }}</p>
        <p>Telp: {{ $settings->phone ?? '' }}</p>
        <hr>
        <p>Periode: {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="25">#</th>
                <th>Tanggal</th>
                <th>Nota</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Keterangan</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Diskon</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $salesCollection = $items->groupBy('nota');
                $rowNum = 1;
            @endphp
            @foreach($salesCollection as $nota => $notaItems)
                @foreach($notaItems as $item)
                <tr>
                    <td class="text-center">{{ $rowNum++ }}</td>
                    <td>{{ $item['date'] }}</td>
                    <td>{{ $item['nota'] }}</td>
                    <td>{{ $item['barcode'] }}</td>
                    <td>{{ $item['produk'] }}</td>
                    <td>{{ $item['keterangan'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['diskon'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @php
                    $summary = collect($saleSummary)->where('code', $nota)->first();
                @endphp
                @if($summary)
                <tr class="sale-total" style="border-top:2px solid #000;">
                    <td colspan="9" class="text-right">Diskon Nota</td>
                    <td class="text-right text-danger">- Rp {{ number_format($summary['discount_amount'], 0, ',', '.') }}</td>
                </tr>
                <tr class="sale-total">
                    <td colspan="9" class="text-right">Pajak 11%</td>
                    <td class="text-right">Rp {{ number_format($summary['tax_amount'], 0, ',', '.') }}</td>
                </tr>
                <tr class="sale-total">
                    <td colspan="9" class="text-right">TOTAL NOTA</td>
                    <td class="text-right">Rp {{ number_format($summary['total'], 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="grand-total">
                <td colspan="8">GRAND TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <hr>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>