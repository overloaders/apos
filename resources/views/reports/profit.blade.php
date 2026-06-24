@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Laba Rugi</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.profit', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.profit.print', request()->query()) }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print me-1"></i> Cetak
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.profit') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Pendapatan</div>
                <h4 class="fw-bold mb-0 text-primary">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Harga Pokok Penjualan</div>
                <h4 class="fw-bold mb-0 text-warning">Rp {{ number_format($cogs ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Laba Kotor</div>
                <h4 class="fw-bold mb-0 text-success">Rp {{ number_format($grossProfit ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card" style="border-left: 4px solid {{ ($netProfit ?? 0) >= 0 ? '#198754' : '#dc3545' }};">
            <div class="card-body">
                <div class="text-muted small mb-1">Laba Bersih</div>
                <h4 class="fw-bold mb-0" style="color: {{ ($netProfit ?? 0) >= 0 ? '#198754' : '#dc3545' }};">
                    Rp {{ number_format($netProfit ?? 0, 0, ',', '.') }}
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Grafik Laba Rugi</h6>
    </div>
    <div class="card-body">
        <canvas id="profitChart" height="80"></canvas>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Rincian Pendapatan</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <td>Penjualan Produk</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($productSales ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Diskon Diberikan</td>
                            <td class="text-end text-danger">- Rp {{ number_format($totalDiscount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td>Total Pendapatan Bersih</td>
                            <td class="text-end">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Rincian Pengeluaran</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr>
                            <td>Harga Pokok Penjualan</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($cogs ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Pengeluaran Operasional</td>
                            <td class="text-end text-danger">Rp {{ number_format($operationalExpenses ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @if(($stockAdjustment ?? 0) != 0)
                        <tr>
                            <td>Penyesuaian Stok (Opname)</td>
                            <td class="text-end {{ ($stockAdjustment ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($stockAdjustment ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        <tr class="table-light fw-bold">
                            <td>Total Pengeluaran</td>
                            <td class="text-end">Rp {{ number_format(($cogs ?? 0) + ($totalExpenses ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('profitChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [
                {
                    label: 'Pendapatan',
                    data: {!! json_encode($revenueData ?? []) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'HPP',
                    data: {!! json_encode($cogsData ?? []) !!},
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Laba Bersih',
                    data: {!! json_encode($profitData ?? []) !!},
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } }
            }
        }
    });
</script>
@endsection