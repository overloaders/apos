@extends('layouts.app')
@section('title', 'Detail Penerimaan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Detail Penerimaan Barang</h4>
    <a href="{{ route('purchasing.receivings.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">No. Penerimaan</small>
                <div class="fw-bold"><code>{{ $receiving->code }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Tanggal</small>
                <div class="fw-bold">{{ $receiving->receiving_date?->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Supplier</small>
                <div class="fw-bold">{{ $receiving->supplier->name ?? $receiving->purchaseOrder->supplier->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Status</small>
                <div>
                    @if($receiving->status === 'accepted')
                        <span class="badge badge-success">Diterima</span>
                    @else
                        <span class="badge badge-warning">{{ $receiving->status }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Item Diterima</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receiving->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->quantity }} {{ $item->product->unit->symbol ?? '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Tidak ada item</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
