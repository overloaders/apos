@extends('layouts.app')
@section('title', 'Laporan Laba per Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Laba per Produk</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.product-margin', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </a>
        <a href="{{ route('reports.product-margin', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.product-margin') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Kategori</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter mr-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Margin Produk</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga Modal</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Harga Member</th>
                        <th class="text-end">Margin (Rp)</th>
                        <th class="text-center">Margin (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $marginAmount = ($product->selling_price ?? 0) - ($product->cost_price ?? 0);
                            $marginPercent = $product->cost_price > 0 ? round(($marginAmount / $product->cost_price) * 100, 2) : 0;
                            if ($marginPercent >= 30) {
                                $badgeClass = 'success';
                                $textClass = 'text-success';
                            } elseif ($marginPercent >= 15) {
                                $badgeClass = 'warning text-dark';
                                $textClass = 'text-warning';
                            } else {
                                $badgeClass = 'danger';
                                $textClass = 'text-danger';
                            }
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $product->code }}</code></td>
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $product->category->name ?? '-' }}</span></td>
                            <td class="text-end">Rp {{ number_format($product->cost_price ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($product->member_price ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold {{ $textClass }}">Rp {{ number_format($marginAmount, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $badgeClass }} badge-status">{{ $marginPercent }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-box fa-2x mb-2 d-block"></i>Tidak ada data produk
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
