@extends('layouts.app')
@section('title', 'Pembayaran PO')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Pembayaran Purchase Order</h4>
    <a href="{{ route('purchasing.orders.show', $order) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">No. Pesanan</small>
                <div class="fw-bold"><code>{{ $order->code }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Supplier</small>
                <div class="fw-bold">{{ $order->supplier->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Total PO</small>
                <div class="fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Status Pembayaran</small>
                <div>
                    @if($order->payment_status === 'paid')
                        <span class="badge bg-success">Lunas</span>
                    @elseif($order->payment_status === 'partial')
                        <span class="badge bg-warning">Partial</span>
                    @else
                        <span class="badge bg-danger">Belum Dibayar</span>
                    @endif
                </div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Sudah Dibayar</small>
                <div class="fw-bold">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Sisa Tagihan</small>
                <div class="fw-bold text-danger">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Catat Pembayaran</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('purchasing.orders.recordPayment', $order) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jumlah Pembayaran <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', $order->remaining_amount) }}" min="0.01" max="{{ $order->remaining_amount }}" step="0.01" required>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('payment_date') is-invalid @enderror" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('purchasing.orders.show', $order) }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
