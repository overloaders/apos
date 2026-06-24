@extends('layouts.app')
@section('title', 'Laporan Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Stok</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.stocks', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.stocks.print', request()->query()) }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print mr-1"></i> Cetak
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.stocks') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Gudang</label>
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses ?? [] as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Kategori</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Status Stok</label>
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="empty" {{ request('stock_status') == 'empty' ? 'selected' : '' }}>Kosong</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Menipis</option>
                    <option value="normal" {{ request('stock_status') == 'normal' ? 'selected' : '' }}>Normal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama/Barcode..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter mr-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Item</div>
                <h4 class="fw-bold mb-0">{{ $stocks->total() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Nilai Stok</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalValue ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Stok Menipis</div>
                <h4 class="fw-bold mb-0 text-danger">{{ $lowStockCount ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card info">
            <div class="card-body">
                <div class="text-muted small mb-1">Stok Kosong</div>
                <h4 class="fw-bold mb-0 text-danger">{{ $emptyStockCount ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Stok</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Keterangan</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Minimum</th>
                        <th>Status</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks ?? [] as $stock)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $stock->product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $stock->product->name ?? '-' }}</td>
                            <td>{{ $stock->product->description ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $stock->product->category->name ?? '-' }}</span></td>
                            <td>{{ $stock->warehouse->name ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ $stock->quantity }}</td>
                            <td class="text-center">{{ $stock->product->min_stock ?? 0 }}</td>
                            <td>
                                @if($stock->quantity == 0)
                                    <span class="badge bg-danger badge-status">Kosong</span>
                                @elseif($stock->quantity <= ($stock->product->min_stock ?? 0))
                                    <span class="badge bg-warning badge-status">Menipis</span>
                                @else
                                    <span class="badge bg-success badge-status">Normal</span>
                                @endif
                            </td>
                            <td class="fw-semibold">Rp {{ number_format($stock->quantity * ($stock->average_cost ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Tidak ada data stok
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $stocks->withQueryString()->links() }}
    </div>
</div>
@endsection
