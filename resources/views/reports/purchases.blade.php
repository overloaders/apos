@extends('layouts.app')
@section('title', 'Laporan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Pembelian</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.purchases', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.purchases.print', request()->query()) }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print mr-1"></i> Cetak
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.purchases') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom ?? date('Y-m-01') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo ?? date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers ?? [] as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Diterima</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                    <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Dipesan</option>
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

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Pembelian</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($summary->total_amount ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Pesanan</div>
                <h4 class="fw-bold mb-0">{{ $summary->order_count ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Item Diterima</div>
                <h4 class="fw-bold mb-0">{{ $totalItemsReceived ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card info">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Qty Dipesan</div>
                <h4 class="fw-bold mb-0">{{ $totalQty ?? 0 }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Pembelian</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>No. Pesanan</th>
                        <th>Supplier</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th>Keterangan</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Diterima</th>
                        <th class="text-center">Diretur</th>
                        <th class="text-center">Net</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNum = 1; $grandQty = 0; $grandReceived = 0; $grandReturned = 0; $grandSubtotal = 0; @endphp
                    @forelse($purchases ?? [] as $purchase)
                        @php $poTotal = 0; @endphp
                        @foreach($purchase->items as $item)
                        @php
                            $netReceived = $item->received_quantity - ($item->returned_quantity ?? 0);
                            $effectiveQty = $netReceived ?: $item->quantity;
                            $effectiveSubtotal = $effectiveQty * $item->unit_price;
                            $grandQty += $item->quantity;
                            $grandReceived += $item->received_quantity;
                            $grandReturned += $item->returned_quantity ?? 0;
                            $grandSubtotal += $effectiveSubtotal;
                            $poTotal += $effectiveSubtotal;
                        @endphp
                        <tr>
                            <td>{{ $rowNum++ }}</td>
                            <td>{{ $purchase->order_date->format('d/m/Y') }}</td>
                            <td><code>{{ $purchase->code }}</code></td>
                            <td>{{ $purchase->supplier->name ?? '-' }}</td>
                            <td><code>{{ $item->product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->product->description ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">{{ $item->received_quantity }}</td>
                            <td class="text-center">{{ $item->returned_quantity ?? 0 }}@if($item->returned_quantity > 0)<i class="fas fa-undo text-danger ml-1"></i>@endif</td>
                            <td class="text-center fw-bold">{{ $netReceived }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td>{{ $item->discount_percent }}%</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($effectiveSubtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr style="background:#f8f9fa;border-top:2px solid #dee2e6;">
                            <td colspan="13" class="text-end fw-bold">TOTAL {{ $purchase->code }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($poTotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">
                                <i class="fas fa-chart-line fa-2x mb-2 d-block"></i>Tidak ada data pembelian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($purchases->count())
                <tfoot class="table-light">
                    <tr class="fw-bold" style="border-top:3px solid #000;background:#e9ecef;">
                        <td colspan="7" style="font-size:14px;">GRAND TOTAL</td>
                        <td class="text-center" style="font-size:14px;">{{ $grandQty }}</td>
                        <td class="text-center" style="font-size:14px;">{{ $grandReceived }}</td>
                        <td class="text-center" style="font-size:14px;">{{ $grandReturned }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-end" style="font-size:14px;">Rp {{ number_format($grandSubtotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $purchases->withQueryString()->links() }}
    </div>
</div>
@endsection