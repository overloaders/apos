@extends('layouts.app')
@section('title', 'Detail Penjualan - ' . $sale->code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Detail Penjualan</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.sales', ['date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-bold">Info Transaksi</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td style="width:120px">No. Nota</td><td><code class="fw-bold">{{ $sale->code }}</code></td></tr>
                    <tr><td>Tanggal</td><td>{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
                    <tr><td>Kasir</td><td>{{ $sale->user->name ?? '-' }}</td></tr>
                    <tr><td>Member</td><td>{{ $sale->member->name ?? '-' }}</td></tr>
                    <tr><td>Status</td><td><span class="badge bg-success">Lunas</span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-bold">Ringkasan Pembayaran</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><td style="width:120px">Subtotal</td><td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td></tr>
                    <tr><td>Diskon</td><td class="text-right text-danger">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td></tr>
                    <tr><td>Pajak 11%</td><td class="text-right">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td></tr>
                    <tr class="fw-bold"><td>TOTAL</td><td class="text-right text-primary" style="font-size:18px">Rp {{ number_format($sale->total, 0, ',', '.') }}</td></tr>
                    <tr><td>Bayar</td><td class="text-right">Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td></tr>
                    <tr><td>Kembali</td><td class="text-right">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td></tr>
                    <tr><td>Metode</td><td class="text-right">{{ $sale->payment_method }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Item yang Dibeli</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Barcode</th>
                        <th>Produk</th>
                        <th>Keterangan</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Diskon</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $item->product->barcode ?? '-' }}</code></td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->product->description ?? '-' }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                        <td class="fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    <button class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print mr-1"></i>Cetak Detail
    </button>
</div>
@endsection