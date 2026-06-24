<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle text-warning mr-1"></i>Stok Menipis</h6>
        <span class="badge bg-warning text-dark">{{ $lowStockProducts->count() }} produk</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Stok Minimum</th>
                        <th>Gudang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts ?? [] as $stock)
                        <tr>
                            <td class="fw-semibold">{{ $stock->product->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $stock->product->category->name ?? '-' }}</span></td>
                            <td class="text-center fw-bold text-danger">{{ $stock->quantity }}</td>
                            <td class="text-center">{{ $stock->product->min_stock ?? 0 }}</td>
                            <td>{{ $stock->warehouse->name ?? '-' }}</td>
                            <td>
                                @if($stock->quantity == 0)
                                    <span class="badge bg-danger badge-status">Kosong</span>
                                @else
                                    <span class="badge bg-warning text-dark badge-status">Menipis</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>Semua stok dalam kondisi normal
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
