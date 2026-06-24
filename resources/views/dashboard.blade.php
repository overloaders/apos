@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Dashboard</h4>
    <span class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Penjualan Hari Ini</div>
                        <h4 class="fw-bold mb-0">Rp {{ number_format($todaySalesTotal ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-shopping-cart text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Transaksi Hari Ini</div>
                        <h4 class="fw-bold mb-0">{{ $todayTransactionCount ?? 0 }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-receipt text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Stok Menipis</div>
                        <h4 class="fw-bold mb-0">{{ $lowStockCount ?? 0 }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Member</div>
                        <h4 class="fw-bold mb-0">{{ $totalMembers ?? 0 }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-id-card text-info fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(($lowStockProducts ?? collect())->count() > 0)
    @include('partials.low-stock-alert')
@endif

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Grafik Penjualan Minggu Ini</h6>
                <span class="text-muted small">Bulan {{ now()->translatedFormat('F') }}: <strong>Rp {{ number_format($monthlySales ?? 0, 0, ',', '.') }}</strong></span>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Transaksi Terakhir</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentSales as $tx)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold small">{{ $tx->code }}</div>
                                <div class="text-muted small">{{ $tx->sale_date->format('d M') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold small">Rp {{ number_format($tx->total, 0, ',', '.') }}</div>
                                <span class="badge bg-success badge-status">Lunas</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                            Belum ada transaksi hari ini
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(($lowStockProducts ?? collect())->count() > 0)
<script>
    Swal.fire({
        icon: 'warning',
        title: 'Stok Menipis',
        html: 'Terdapat <strong>{{ $lowStockProducts->count() }}</strong> produk dengan stok menipis.<br>Segera lakukan pengadaan barang.',
        confirmButtonColor: '#ffc107',
        confirmButtonText: 'OK, Saya Tahu'
    });
</script>
@endif
@php
    $chartLabels = [];
    $chartDataValues = [];
    $start = \Carbon\Carbon::now()->startOfWeek();
    for ($i = 0; $i < 7; $i++) {
        $date = $start->copy()->addDays($i);
        $chartLabels[] = $date->translatedFormat('D');
        $key = $date->toDateString();
        $chartDataValues[] = ($salesChartData[$key] ?? false) ? (float) $salesChartData[$key]->total : 0;
    }
@endphp
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode($chartDataValues) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd'
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
