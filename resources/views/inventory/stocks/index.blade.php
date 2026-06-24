@extends('layouts.app')
@section('title', 'Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Stok</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Cetak
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Gudang</label>
                <select class="form-select form-select-sm" id="filterWarehouse">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses ?? [] as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Kategori</label>
                <select class="form-select form-select-sm" id="filterCategory">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Cari Produk</label>
                <input type="text" class="form-control form-control-sm" placeholder="Nama/Barcode..." id="searchInput">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Status Stok</label>
                <select class="form-select form-select-sm" id="filterStock">
                    <option value="">Semua</option>
                    <option value="low">Stok Menipis</option>
                    <option value="normal">Normal</option>
                    <option value="empty">Kosong</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Gudang</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Stok Minimum</th>
                        <th>Status</th>
                        <th>Rata-rata Harga</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks ?? [] as $stock)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $stock->product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $stock->product->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $stock->product->category->name ?? '-' }}</span></td>
                            <td>{{ $stock->warehouse->name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="fw-bold {{ $stock->quantity <= ($stock->product->min_stock ?? 0) ? 'text-danger' : 'text-success' }}">
                                    {{ $stock->quantity }}
                                </span>
                            </td>
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
                            <td>Rp {{ number_format($stock->average_cost ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('inventory.stocks.card', $stock->product) }}" class="btn btn-sm btn-outline-info" title="Kartu Stok">
                                    <i class="fas fa-archive"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Belum ada data stok
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $stocks ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20) }}
    </div>
</div>
@endsection
