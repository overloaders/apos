@extends('layouts.app')
@section('title', 'Laporan Pergerakan Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Pergerakan Stok</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.moving-stock', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.moving-stock.print', request()->query()) }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print mr-1"></i> Cetak
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.moving-stock') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Kategori</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama/Barcode..." value="{{ request('search') }}">
            </div>
            <div class="col-12">
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-filter mr-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

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

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card danger">
            <div class="card-body">
                <div class="text-muted small mb-1">Not Moving</div>
                <h4 class="fw-bold mb-0 text-danger">{{ $totalNotMoving }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Slow Moving</div>
                <h4 class="fw-bold mb-0 text-warning">{{ $totalSlow }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Moving</div>
                <h4 class="fw-bold mb-0 text-primary">{{ $totalMoving }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Fast Moving</div>
                <h4 class="fw-bold mb-0 text-success">{{ $totalFast }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Produk</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Terjual</th>
                        <th class="text-center">Rata-rata/Bulan</th>
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
                                $badge = 'bg-danger';
                            } elseif ($sold < $q1) {
                                $class = 'Slow Moving';
                                $badge = 'bg-warning text-dark';
                            } elseif ($sold < $q3) {
                                $class = 'Moving';
                                $badge = 'bg-primary';
                            } else {
                                $class = 'Fast Moving';
                                $badge = 'bg-success';
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $product->category->name ?? '-' }}</span></td>
                            <td class="text-center fw-bold">{{ $totalStock }}</td>
                            <td class="text-center fw-bold">{{ $sold }}</td>
                            <td class="text-center">{{ $avgMonthly }}</td>
                            <td><span class="badge {{ $badge }} badge-status">{{ $class }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Tidak ada data produk
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection
