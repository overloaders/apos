@extends('layouts.app')
@section('title', 'Kartu Stok - ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('inventory.stocks.index') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="d-inline mb-0 fw-bold">Kartu Stok</h4>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
                <small class="text-muted">
                    Barcode: <code>{{ $product->barcode ?? '-' }}</code> |
                    Kategori: {{ $product->category->name ?? '-' }} |
                    Satuan: {{ $product->unit->name ?? '-' }}
                </small>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('master.products.show', $product) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt me-1"></i>Detail Produk
                </a>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('inventory.stocks.card', $product) }}">
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Gudang</label>
                <select class="form-select form-select-sm" name="warehouse_id">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses ?? [] as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tanggal</th>
                        <th>No. Referensi</th>
                        <th>Tipe</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Saldo</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements ?? [] as $mv)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                            <td><code>{{ $mv->reference_number }}</code></td>
                            <td>
                                @if(in_array($mv->type, ['in']))
                                    <span class="badge bg-success badge-status">Masuk</span>
                                @else
                                    <span class="badge bg-danger badge-status">Keluar</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $mv->quantity }}</td>
                            <td class="text-center fw-bold">{{ $mv->balance }}</td>
                            <td>{{ $mv->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Belum ada pergerakan stok
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
