<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian (Per-PO & Detail Item)</title>
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
        <h2>{{ $settings->company_name ?? 'LAPORAN PEMBELIAN' }}</h2>
        <p>{{ $settings->address ?? '' }}</p>
        <p>Telp: {{ $settings->phone ?? '' }}</p>
        <hr>
        <p>Periode: {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <span>Total Pembelian: Rp {{ number_format($summary->total_amount ?? 0, 0, ',', '.') }}</span>
        <span>Total Pesanan: {{ $summary->order_count ?? 0 }}</span>
        <span>Item Diterima: {{ $totalItemsReceived ?? 0 }}</span>
        <span>Total Qty Dipesan: {{ $totalQty ?? 0 }}</span>
    </div>

    @php $grandQty = 0; $grandReceived = 0; $grandReturned = 0; $grandSubtotal = 0; @endphp
    @foreach($purchases ?? [] as $purchase)
    @php $purchaseTotal = 0; @endphp
    <table>
        <caption style="caption-side:top;text-align:left;font-weight:bold;font-size:10px;padding:4px 0;">
            Nota: {{ $purchase->code }} &mdash; {{ $purchase->supplier->name ?? '-' }}
            &mdash; {{ $purchase->order_date->format('d/m/Y') }}
            &mdash; Status:
            @if($purchase->status === 'received') Diterima
            @elseif($purchase->status === 'partial') Sebagian
            @elseif($purchase->status === 'ordered') Dipesan
            @else {{ $purchase->status }}
            @endif
        </caption>
        <thead>
            <tr>
                <th>#</th>
                <th>Barcode</th>
                <th>Nama Produk</th>
                <th>Keterangan</th>
                <th>Qty</th>
                <th>Diterima</th>
                <th>Diretur</th>
                <th>Net</th>
                <th>Harga</th>
                <th>Diskon</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
            @php
                $netReceived = $item->received_quantity - ($item->returned_quantity ?? 0);
                $effectiveQty = $netReceived ?: $item->quantity;
                $effectiveSubtotal = $effectiveQty * $item->unit_price;
                $grandQty += $item->quantity;
                $grandReceived += $item->received_quantity;
                $grandReturned += $item->returned_quantity ?? 0;
                $grandSubtotal += $effectiveSubtotal;
                $purchaseTotal += $effectiveSubtotal;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->product->barcode ?? '-' }}</td>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $item->product->description ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ $item->received_quantity }}</td>
                <td class="text-center">{{ $item->returned_quantity ?? 0 }}</td>
                <td class="text-center">{{ $netReceived }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->discount_percent }}%</td>
                <td class="text-right">Rp {{ number_format($effectiveSubtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="grand-total">
                <td colspan="10" class="text-right">TOTAL PESANAN</td>
                <td class="text-right">Rp {{ number_format($purchaseTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <table>
        <tr class="grand-total" style="font-size:14px;">
            <td colspan="10">GRAND TOTAL</td>
            <td class="text-right">Rp {{ number_format($grandSubtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="10" class="text-right">Total Qty Dipesan</td>
            <td class="text-right">{{ $grandQty }}</td>
        </tr>
        <tr>
            <td colspan="10" class="text-right">Total Qty Diterima</td>
            <td class="text-right">{{ $grandReceived }}</td>
        </tr>
        <tr>
            <td colspan="10" class="text-right">Total Qty Diretur</td>
            <td class="text-right">{{ $grandReturned }}</td>
        </tr>
        <tr>
            <td colspan="10" class="text-right">Total Qty Net</td>
            <td class="text-right">{{ $grandReceived - $grandReturned }}</td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>