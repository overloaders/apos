@extends('layouts.app')
@section('title', 'Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Produk</h4>
    <a href="{{ route('master.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Produk
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <input type="text" class="form-control form-control-sm" placeholder="Cari nama/Barcode..." style="width:250px;" id="searchInput">
            <select class="form-select form-select-sm" style="width:180px;" id="filterCategory">
                <option value="">Semua Kategori</option>
                @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select class="form-select form-select-sm" style="width:150px;">
                <option>Semua Status</option>
                <option>Aktif</option>
                <option>Nonaktif</option>
            </select>
        </div>

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
                        <th>Satuan</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products ?? [] as $product)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $product->barcode }}</code></td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <small class="text-muted">{{ $product->brand->name ?? '' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $product->category->name ?? '-' }}</span></td>
                            <td>{{ $product->unit->name ?? '-' }}</td>
                            <td>Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td>
                                @if($product->stock <= $product->minimum_stock)
                                    <span class="badge bg-danger">{{ $product->stock }}</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('master.products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('master.products.price-history', $product) }}" class="btn btn-sm btn-outline-info" title="Riwayat Harga">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                                <a href="{{ route('master.products.barcode', $product) }}" class="btn btn-sm btn-outline-info" title="Cetak Barcode" target="_blank">
                                    <i class="fas fa-barcode"></i>
                                </a>
                                <form action="{{ route('master.products.destroy', $product) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus produk ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-box fa-2x mb-2 d-block"></i>Belum ada data produk
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $products ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) }}
    </div>
</div>
@endsection
