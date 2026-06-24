@extends('layouts.app')
@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('master.products.index') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="d-inline mb-0 fw-bold">Detail Produk</h4>
    </div>
    <a href="{{ route('master.products.edit', $product) }}" class="btn btn-warning">
        <i class="fas fa-edit me-1"></i>Edit
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header fw-bold">Informasi Produk</div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Kode Produk</div>
                    <div class="col-sm-8 fw-semibold">{{ $product->code ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Barcode</div>
                    <div class="col-sm-8 fw-semibold">{{ $product->barcode ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nama Produk</div>
                    <div class="col-sm-8 fw-semibold">{{ $product->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Deskripsi</div>
                    <div class="col-sm-8">{{ $product->description ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Kategori</div>
                    <div class="col-sm-8">{{ $product->category->name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Merk</div>
                    <div class="col-sm-8">{{ $product->brand->name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Satuan</div>
                    <div class="col-sm-8">{{ $product->unit->name ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Status</div>
                    <div class="col-sm-8">
                        @if($product->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header fw-bold">Harga & Stok</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Harga Beli</div>
                    <div class="fs-5 fw-bold">Rp {{ number_format($product->cost_price ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Harga Jual</div>
                    <div class="fs-5 fw-bold text-primary">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Stok Minimum</div>
                    <div class="fw-semibold">{{ $product->min_stock ?? 0 }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Stok Maksimum</div>
                    <div class="fw-semibold">{{ $product->max_stock ?? 0 }}</div>
                </div>
            </div>
        </div>

        @if(isset($product->stocks) && count($product->stocks) > 0)
        <div class="card">
            <div class="card-header fw-bold">Stok per Gudang</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Gudang</th><th class="text-end">Stok</th></tr>
                    </thead>
                    <tbody>
                        @foreach($product->stocks as $stock)
                            <tr>
                                <td>{{ $stock->warehouse->name ?? '-' }}</td>
                                <td class="text-end fw-semibold">{{ $stock->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection