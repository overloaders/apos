@extends('layouts.app')
@section('title', 'Laporan Retur Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Laporan Retur Pembelian</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.purchase-returns', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        <a href="{{ route('reports.purchase-returns.print', request()->query()) }}" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-print mr-1"></i> Cetak
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.purchase-returns') }}" class="row g-3 align-items-end">
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
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="No. Retur..." value="{{ request('search') }}">
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
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card danger">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Retur</div>
                <h4 class="fw-bold mb-0">{{ $returnCount ?? $returns->total() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card warning">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Qty Diretur</div>
                <h4 class="fw-bold mb-0">{{ $grandQty }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card summary-card primary">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Nilai Retur</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Detail Retur</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tgl Retur</th>
                        <th>No. Retur</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Barcode</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Alasan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNum = 1; $tQty = 0; $tSub = 0; @endphp
                    @forelse($returns ?? [] as $ret)
                        @foreach($ret->items as $item)
                        @php
                            $sub = $item->quantity * $item->unit_price;
                            $tQty += $item->quantity;
                            $tSub += $sub;
                        @endphp
                        <tr>
                            <td>{{ $rowNum++ }}</td>
                            <td>{{ $ret->return_date->format('d/m/Y') }}</td>
                            <td><code>{{ $ret->code }}</code></td>
                            <td><code>{{ $ret->purchaseOrder->code ?? '-' }}</code></td>
                            <td>{{ $ret->supplier->name ?? '-' }}</td>
                            <td><code>{{ $item->product->barcode ?? '-' }}</code></td>
                            <td class="fw-semibold">{{ $item->product->name ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                            <td><span class="badge bg-light text-dark">{{ $item->reason ?? '-' }}</span></td>
                            <td>{{ $ret->user->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="fas fa-undo-alt fa-2x mb-2 d-block"></i>Tidak ada data retur
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($returns->count())
                <tfoot class="table-light">
                    <tr class="fw-bold" style="border-top:3px solid #000;background:#e9ecef;">
                        <td colspan="7" style="font-size:14px;">GRAND TOTAL</td>
                        <td class="text-center" style="font-size:14px;">{{ $tQty }}</td>
                        <td></td>
                        <td style="font-size:14px;">Rp {{ number_format($tSub, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $returns->withQueryString()->links() }}
    </div>
</div>
@endsection
