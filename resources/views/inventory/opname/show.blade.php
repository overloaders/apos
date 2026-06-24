<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Stok Opname - {{ $opname->code }}</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 10px; margin: 20px; color: #000; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; }
        hr { border-top: 1px dashed #000; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; font-size: 9px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .grand-total td { font-weight: bold; border-top: 3px solid #000; background: #f0f0f0; font-size: 12px; }
        .info-table { width: auto; margin: 5px 0; }
        .info-table td { border: none; padding: 2px 10px 2px 0; }
        .info-table td:first-child { font-weight: bold; }
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
        <h2>STOK OPNAME</h2>
        <hr>
    </div>

    <table class="info-table">
        <tr><td>No. Opname</td><td>: {{ $opname->code }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $opname->opname_date->format('d/m/Y') }}</td></tr>
        <tr><td>Gudang</td><td>: {{ $opname->warehouse->name ?? '-' }}</td></tr>
        <tr><td>Status</td><td>: {{ $opname->status === 'approved' ? 'Disetujui' : ($opname->status === 'draft' ? 'Draft' : 'Ditolak') }}</td></tr>
        <tr><td>Dibuat Oleh</td><td>: {{ $opname->user->name ?? '-' }}</td></tr>
        @if($opname->approved_by)
        <tr><td>Disetujui Oleh</td><td>: {{ $opname->approver->name ?? '-' }}</td></tr>
        @endif
        @if($opname->notes)
        <tr><td>Keterangan</td><td>: {{ $opname->notes }}</td></tr>
        @endif
    </table>

    @php
        $grandSysVal = 0; $grandActVal = 0; $grandDiffVal = 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Harga Satuan</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik</th>
                <th>Selisih</th>
                <th>Nilai Sistem</th>
                <th>Nilai Fisik</th>
                <th>Nilai Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($opname->items as $i => $item)
            @php
                $grandSysVal += $item->system_value;
                $grandActVal += $item->actual_value;
                $grandDiffVal += $item->difference_value;
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->product->barcode ?? '-' }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->system_stock }}</td>
                <td class="text-center">{{ $item->actual_stock }}</td>
                <td class="text-center {{ $item->difference > 0 ? 'text-success' : ($item->difference < 0 ? 'text-danger' : '') }}">
                    {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                </td>
                <td class="text-right">Rp {{ number_format($item->system_value, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->actual_value, 0, ',', '.') }}</td>
                <td class="text-right {{ $item->difference_value > 0 ? 'text-success' : ($item->difference_value < 0 ? 'text-danger' : '') }}">
                    Rp {{ number_format($item->difference_value, 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="4">GRAND TOTAL</td>
                <td class="text-center">{{ $opname->items->sum('system_stock') }}</td>
                <td class="text-center">{{ $opname->items->sum('actual_stock') }}</td>
                <td class="text-center {{ $opname->items->sum('difference') > 0 ? 'text-success' : ($opname->items->sum('difference') < 0 ? 'text-danger' : '') }}">
                    {{ $opname->items->sum('difference') > 0 ? '+' : '' }}{{ $opname->items->sum('difference') }}
                </td>
                <td class="text-right">Rp {{ number_format($grandSysVal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandActVal, 0, ',', '.') }}</td>
                <td class="text-right {{ $grandDiffVal > 0 ? 'text-success' : ($grandDiffVal < 0 ? 'text-danger' : '') }}">
                    Rp {{ number_format($grandDiffVal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <hr>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>