@extends('layouts.app')
@section('title', 'Penerimaan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Penerimaan Barang</h4>
    <a href="{{ route('purchasing.receivings.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Penerimaan
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari nomor penerimaan..." style="width:220px;">
            <select class="form-select form-select-sm" style="width:150px;">
                <option>Semua Status</option>
                <option>Diterima</option>
                <option>Sebagian</option>
            </select>
            <input type="date" class="form-control form-control-sm" style="width:160px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Penerimaan</th>
                        <th>Tanggal</th>
                        <th>No. Pesanan</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivings ?? [] as $recv)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $recv->code }}</code></td>
                            <td>{{ $recv->receiving_date?->format('d/m/Y') }}</td>
                            <td>{{ $recv->purchaseOrder->code ?? '-' }}</td>
                            <td>{{ $recv->supplier->name ?? $recv->purchaseOrder->supplier->name ?? '-' }}</td>
                            <td class="fw-semibold">Rp {{ number_format($recv->items->sum('subtotal'), 0, ',', '.') }}</td>
                            <td>
                                @if($recv->status === 'accepted')
                                    <span class="badge bg-success badge-status">Diterima</span>
                                @elseif($recv->status === 'draft')
                                    <span class="badge bg-secondary badge-status">Draft</span>
                                @else
                                    <span class="badge bg-warning badge-status">{{ $recv->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('purchasing.receivings.show', $recv) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-check fa-2x mb-2 d-block"></i>Belum ada data penerimaan barang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $receivings ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) }}
    </div>
</div>
@endsection
