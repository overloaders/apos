@extends('layouts.app')
@section('title', 'Terima Pesanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Terima Pesanan: {{ $po->code }}</h4>
    <a href="{{ route('purchasing.receivings.create') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted">Supplier</small>
                <div class="fw-bold">{{ $po->supplier->name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Gudang Tujuan</small>
                <div class="fw-bold">{{ $po->warehouse->name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Tanggal Pesan</small>
                <div class="fw-bold">{{ $po->order_date->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('purchasing.receivings.store') }}" method="POST">
    @csrf
    <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
    <input type="hidden" name="warehouse_id" value="{{ $po->warehouse_id }}">

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0 fw-bold">Item Diterima</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Dipesan</th>
                            <th class="text-center">Sudah Diterima</th>
                            <th class="text-center">Sisa</th>
                            <th class="text-center">Diterima Sekarang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->items as $item)
                            @php
                                $received = $item->received_quantity ?? 0;
                                $remaining = $item->quantity - $received;
                            @endphp
                            <tr>
                                <td>
                                    {{ $item->product->name ?? '-' }}
                                    <input type="hidden" name="items[{{ $loop->index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id }}">
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ $received }}</td>
                                <td class="text-center fw-bold">{{ $remaining }}</td>
                                <td class="text-center" style="width:150px;">
                                    <input type="number" class="form-control form-control-sm text-center" name="items[{{ $loop->index }}][quantity]" value="{{ $remaining }}" min="0" max="{{ $remaining }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea class="form-control" name="notes" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('purchasing.receivings.create') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Simpan Penerimaan
        </button>
    </div>
</form>
@endsection
