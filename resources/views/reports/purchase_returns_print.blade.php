<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Retur Pembelian (Detail Barang)</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 9px; margin: 20px; color: #000; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; }
        hr { border-top: 1px dashed #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #000; padding: 2px 4px; text-align: left; font-size: 8px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .grand-total td { font-weight: bold; font-size: 12px; border-top: 3px solid #000; background: #f0f0f0; }
        .summary { display: flex; justify-content: space-between; margin: 5px 0; font-size: 9px; }
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
        <h2>{{ $settings->company_name ?? 'LAPORAN RETUR PEMBELIAN' }}</h2>
        <p>{{ $settings->address ?? '' }}</p>
        <p>Telp: {{ $settings->phone ?? '' }}</p>
        <hr>
        <p>Periode: {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <span>Total Retur: {{ $returns->count() }}</span>
        <span>Total Qty Diretur: {{ $grandQty }}</span>
        <span>Total Nilai: Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
    </div>

    @php $tQty = 0; $tSub = 0; @endphp
    @foreach($returns ?? [] as $ret)
    @php $retTotal = 0; @endphp
    <table>
        <caption style="caption-side:top;text-align:left;font-weight:bold;font-size:10px;padding:4px 0;">
            No. Retur: {{ $ret->code }} &mdash; {{ $ret->supplier->name ?? '-' }}
            &mdash; PO: {{ $ret->purchaseOrder->code ?? '-' }}
            &mdash; {{ $ret->return_date->format('d/m/Y') }}
        </caption>
        <thead>
            <tr>
                <th>#</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ret->items as $item)
            @php
                $sub = $item->quantity * $item->unit_price;
                $tQty += $item->quantity;
                $tSub += $sub;
                $retTotal += $sub;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->product->barcode ?? '-' }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                <td>{{ $item->reason ?? '-' }}</td>
            </tr>
            @endforeach
            <tr class="grand-total">
                <td colspan="6" class="text-right">TOTAL RETUR</td>
                <td class="text-right">Rp {{ number_format($retTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <table>
        <tr class="grand-total" style="font-size:14px;">
            <td colspan="6">GRAND TOTAL</td>
            <td class="text-right">Rp {{ number_format($tSub, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="6" class="text-right">Total Qty Diretur</td>
            <td class="text-right">{{ $tQty }}</td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
