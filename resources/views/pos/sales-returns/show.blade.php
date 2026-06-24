@extends('layouts.app')
@section('title', 'Detail Retur Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('pos.sales-returns.index') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="d-inline mb-0 fw-bold">Detail Retur Penjualan</h4>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="140">No. Retur</td>
                        <td class="fw-semibold"><code>{{ $saleReturn->return_number }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Nota</td>
                        <td class="fw-semibold"><code>{{ $saleReturn->sale->code ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Retur</td>
                        <td>{{ $saleReturn->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="140">Kasir</td>
                        <td>{{ $saleReturn->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Member</td>
                        <td>{{ $saleReturn->sale->member->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Refund</td>
                        <td class="fw-bold text-danger">Rp {{ number_format($saleReturn->total_refund, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @if($saleReturn->reason)
        <div class="mt-3">
            <strong>Alasan:</strong>
            <p class="mb-0">{{ $saleReturn->reason }}</p>
        </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header fw-bold">Item yang Dikembalikan</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Produk</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saleReturn->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                <small class="text-muted">{{ $item->product->barcode ?? '' }}</small>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total Refund</td>
                        <td class="text-end text-danger">Rp {{ number_format($saleReturn->total_refund, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
