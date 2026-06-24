<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok & Nilai Persediaan</title>
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
        .grand-total td { font-weight: bold; font-size: 13px; border-top: 3px solid #000; background: #f0f0f0; }
        .summary { display: flex; justify-content: space-between; margin: 5px 0; font-size: 10px; }
        .summary span { display: inline-block; }
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
        <h2>{{ $settings->company_name ?? 'LAPORAN STOK' }}</h2>
        <p>{{ $settings->address ?? '' }}</p>
        <p>Telp: {{ $settings->phone ?? '' }}</p>
        <hr>
        <p>Per Tanggal: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <span>Total Item: {{ $totalItems }}</span>
        <span>Nilai Stok: Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
        <span>Stok Menipis: {{ $lowStockCount }}</span>
        <span>Stok Kosong: {{ $emptyStockCount }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th>Gudang</th>
                <th>Stok</th>
                <th>Min</th>
                <th>Status</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks ?? [] as $stock)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $stock->product->barcode ?? '-' }}</td>
                    <td>{{ $stock->product->name ?? '-' }}</td>
                    <td>{{ $stock->product->description ?? '-' }}</td>
                    <td>{{ $stock->product->category->name ?? '-' }}</td>
                    <td>{{ $stock->warehouse->name ?? '-' }}</td>
                    <td class="text-center">{{ $stock->quantity }}</td>
                    <td class="text-center">{{ $stock->product->min_stock ?? 0 }}</td>
                    <td>
                        @if($stock->quantity == 0)
                            Kosong
                        @elseif($stock->quantity <= ($stock->product->min_stock ?? 0))
                            Menipis
                        @else
                            Normal
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($stock->quantity * ($stock->average_cost ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data stok</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="9">GRAND TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <hr>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>