<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi (Pendapatan - HPP - Biaya)</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 11px; margin: 20px; color: #000; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; }
        hr { border-top: 1px dashed #000; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #000; padding: 5px 8px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc3545; }
        .grand-total td { font-weight: bold; font-size: 13px; border-top: 3px solid #000; background: #f0f0f0; }
        .summary-cards { display: flex; justify-content: space-between; margin: 10px 0; gap: 8px; }
        .card-item { border: 1px solid #000; padding: 8px; text-align: center; flex: 1; }
        .card-item .label { font-size: 9px; }
        .card-item .value { font-size: 14px; font-weight: bold; margin-top: 3px; }
        .result { border-top: 3px double #000; font-weight: bold; font-size: 14px; }
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
        <h2>{{ $settings->company_name ?? 'LAPORAN LABA RUGI' }}</h2>
        <p>{{ $settings->address ?? '' }}</p>
        <p>Telp: {{ $settings->phone ?? '' }}</p>
        <hr>
        <p>Periode: {{ Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
    </div>

    <div class="summary-cards">
        <div class="card-item">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="card-item">
            <div class="label">Harga Pokok Penjualan</div>
            <div class="value">Rp {{ number_format($totalCOGS ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="card-item">
            <div class="label">Laba Kotor</div>
            <div class="value">Rp {{ number_format($grossProfit ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="card-item">
            <div class="label">Laba Bersih</div>
            <div class="value">Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="card-item">
            <div class="label">Margin</div>
            <div class="value">{{ number_format($margin ?? 0, 2) }}%</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="2" style="background:#e8f4fd;">RINCIAN PENDAPATAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Penjualan Produk</td>
                <td class="text-right">Rp {{ number_format($productSales ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Diskon Diberikan</td>
                <td class="text-right text-danger">- Rp {{ number_format($totalDiscount ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td>Total Pendapatan Bersih</td>
                <td class="text-right">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="2" style="background:#fff3cd;">RINCIAN PENGELUARAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Harga Pokok Penjualan (HPP)</td>
                <td class="text-right">Rp {{ number_format($totalCOGS ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pengeluaran Operasional</td>
                <td class="text-right">Rp {{ number_format($totalExpenses ?? 0, 0, ',', '.') }}</td>
            </tr>
            @if(($stockAdjustment ?? 0) != 0)
            <tr>
                <td>Penyesuaian Stok (Opname)</td>
                <td class="text-right">Rp {{ number_format($stockAdjustment ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>Total Pengeluaran</td>
                <td class="text-right">Rp {{ number_format(($totalCOGS ?? 0) + ($totalExpenses ?? 0), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tr class="result">
            <td style="font-size:15px;">LABA BERSIH</td>
            <td class="text-right" style="font-size:15px;">Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Margin Laba</td>
            <td class="text-right">{{ number_format($margin ?? 0, 2) }}%</td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        <p>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>