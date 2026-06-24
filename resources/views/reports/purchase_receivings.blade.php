@extends('layouts.app')
@section('title', 'Laporan Penerimaan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Penerimaan Barang</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.purchase-receivings', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.purchase-receivings.print', request()->query()) }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print mr-1"></i> Cetak
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.purchase-receivings') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <button class="btn btn-primary btn-sm">
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
                <div class="text-muted small mb-1">Total Transaksi</div>
                <h4 class="fw-bold mb-0">{{ $receivings->total() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Qty Diterima</div>
                <h4 class="fw-bold mb-0">{{ $grandQty }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card summary-card success">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Nilai</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Penerimaan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tgl Terima</th>
                        <th>No. Terima</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Penerima</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNum = 1; $tQty = 0; $tSub = 0; @endphp
                    @forelse($receivings ?? [] as $rcv)
                        @foreach($rcv->items as $item)
                        @php
                            $tQty += $item->quantity;
                            $tSub += $item->subtotal;
                        @endphp
                        <tr>
                            <td>{{ $rowNum++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($rcv->receiving_date)->format('d/m/Y') }}</td>
                            <td><code>{{ $rcv->code }}</code></td>
                            <td><code>{{ $rcv->purchaseOrder->code ?? '-' }}</code></td>
                            <td>{{ $rcv->purchaseOrder->supplier->name ?? '-' }}</td>
                            <td><code>{{ $item->product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $item->product->name ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td>{{ $rcv->user->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-check fa-2x mb-2 d-block"></i>Tidak ada data penerimaan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($receivings->count())
                <tfoot class="table-light">
                    <tr class="fw-bold" style="border-top:3px solid #000;background:#e9ecef;">
                        <td colspan="7" style="font-size:14px;">GRAND TOTAL</td>
                        <td class="text-center" style="font-size:14px;">{{ $tQty }}</td>
                        <td></td>
                        <td style="font-size:14px;">Rp {{ number_format($tSub, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $receivings->withQueryString()->links() }}
    </div>
</div>
@endsection
