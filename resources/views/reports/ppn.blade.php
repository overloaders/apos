@extends('layouts.app')
@section('title', 'Laporan PPN')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan PPN (Pajak Pertambahan Nilai)</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.ppn', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </a>
        <a href="{{ route('reports.ppn', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.ppn') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Bulan</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter mr-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Penjualan (Dasar Pengenaan Pajak)</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalSubtotal ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Total PPN</div>
                <h4 class="fw-bold mb-0 text-warning">Rp {{ number_format($totalTax ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Transaksi</div>
                <h4 class="fw-bold mb-0">{{ $totalTransactions ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Transaksi</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tanggal</th>
                        <th>No Faktur</th>
                        <th>Member</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">PPN</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandSubtotal = 0; $grandTax = 0; $grandTotal = 0; @endphp
                    @forelse($sales as $sale)
                        @php
                            $grandSubtotal += $sale->subtotal;
                            $grandTax += $sale->tax_amount;
                            $grandTotal += $sale->total;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td><code>{{ $sale->code }}</code></td>
                            <td>{{ $sale->member->name ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-receipt fa-2x mb-2 d-block"></i>Tidak ada data transaksi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($sales->count())
                <tfoot class="table-light">
                    <tr class="fw-bold" style="border-top:3px solid #000;background:#e9ecef;">
                        <td colspan="4" style="font-size:14px;">GRAND TOTAL</td>
                        <td class="text-end" style="font-size:14px;">Rp {{ number_format($grandSubtotal, 0, ',', '.') }}</td>
                        <td class="text-end" style="font-size:14px;">Rp {{ number_format($grandTax, 0, ',', '.') }}</td>
                        <td class="text-end" style="font-size:14px;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
