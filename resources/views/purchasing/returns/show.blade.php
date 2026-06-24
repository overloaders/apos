@extends('layouts.app')
@section('title', 'Detail Retur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Detail Retur</h4>
    <a href="{{ route('purchasing.returns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">No. Retur</small>
                <div class="fw-bold"><code>{{ $return->code }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">No. PO</small>
                <div class="fw-bold"><code>{{ $return->purchaseOrder->code ?? '-' }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Supplier</small>
                <div class="fw-bold">{{ $return->supplier->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Tanggal Retur</small>
                <div class="fw-bold">{{ $return->return_date->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Gudang</small>
                <div class="fw-bold">{{ $return->warehouse->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Status</small>
                <div>
                    @switch($return->status)
                        @case('draft') <span class="badge bg-secondary badge-status">Draft</span> @break
                        @case('completed') <span class="badge bg-success badge-status">Selesai</span> @break
                        @case('cancelled') <span class="badge bg-danger badge-status">Dibatalkan</span> @break
                    @endswitch
                </div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Kasir</small>
                <div class="fw-bold">{{ $return->user->name ?? '-' }}</div>
            </div>
            @if($return->notes)
            <div class="col-12">
                <small class="text-muted">Catatan</small>
                <div>{{ $return->notes }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Item Retur</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Barcode</th>
                        <th>Produk</th>
                        <th class="text-center">Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($return->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $item->product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $item->product->name ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-bold">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $item->reason ?? '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada item</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total Retur</td>
                        <td>Rp {{ number_format($return->total, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
