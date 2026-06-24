@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Penjualan</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.sales', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.sales.print', request()->query()) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print mr-1"></i>Cetak Laporan
        </a>
    </div>
</div>

<form method="GET" action="{{ route('reports.sales') }}">
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Kasir</label>
                <select class="form-select form-select-sm" name="cashier_id">
                    <option value="">Semua Kasir</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Kategori</label>
                <select class="form-select form-select-sm" name="category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Metode Bayar</label>
                <select class="form-select form-select-sm" name="payment_method">
                    <option value="">Semua</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Kartu</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="ewallet" {{ request('payment_method') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i>Tampilkan
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Penjualan</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Transaksi</div>
                <h4 class="fw-bold mb-0">{{ $totalTransactions ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Diskon</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalDiscount ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card info">
            <div class="card-body">
                <div class="text-muted small mb-1">Rata-rata per Transaksi</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($avgTransaction ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Grafik Penjualan Harian</h6>
    </div>
    <div class="card-body">
        <canvas id="salesReportChart" height="80"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Penjualan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                        <th>Diskon Item</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNum = 1; @endphp
                    @forelse($salesData ?? [] as $sale)
                        @foreach($sale->items as $item)
                        <tr>
                            <td class="text-center">{{ $rowNum++ }}</td>
                            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td><a href="{{ route('reports.sales.detail', $sale) }}" target="_blank"><code>{{ $sale->code }}</code></a></td>
                            <td>{{ $item->product->barcode ?? '-' }}</td>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->product->description ?? '-' }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="table-light" style="border-top:2px solid #dee2e6;">
                            <td colspan="8" class="text-end fw-bold">Subtotal Nota</td>
                            <td class="text-right"></td>
                            <td class="fw-bold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="9" class="text-end">Diskon Nota</td>
                            <td class="text-right text-danger">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="9" class="text-end">Pajak 11%</td>
                            <td class="text-right">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-light" style="border-bottom:2px solid #dee2e6;">
                            <td colspan="9" class="text-end fw-bold">TOTAL NOTA</td>
                            <td class="text-right fw-bold text-primary">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>Tidak ada data penjualan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold" style="border-top:3px solid #000;background:#e9ecef;">
                        <td colspan="6" style="font-size:14px;">GRAND TOTAL</td>
                        <td style="font-size:14px;"></td>
                        <td class="text-center" style="font-size:14px;">{{ $salesData->sum(fn($s) => $s->items->sum('quantity')) }}</td>
                        <td class="text-danger" style="font-size:14px;">Rp {{ number_format($totalDiscount ?? 0, 0, ',', '.') }}</td>
                        <td class="text-primary" style="font-size:14px;">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('salesReportChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode($chartData ?? []) !!},
                backgroundColor: 'rgba(13,110,253,0.7)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } }
            }
        }
    });
</script>
@endsection