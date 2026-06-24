@extends('layouts.app')
@section('title', 'Mutasi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Mutasi Stok</h4>
    <a href="{{ route('inventory.mutations.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Mutasi
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari produk..." style="width:220px;">
            <select class="form-select form-select-sm" style="width:150px;">
                <option>Semua Tipe</option>
                <option>Masuk</option>
                <option>Keluar</option>
                <option>Transfer</option>
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
                        <th>No. Mutasi</th>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Tipe</th>
                        <th>Gudang Asal</th>
                        <th>Gudang Tujuan</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations ?? [] as $mutation)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $mutation->reference_number }}</code></td>
                            <td>{{ $mutation->created_at->format('d/m/Y H:i') }}</td>
                            <td class="fw-semibold">{{ $mutation->product->name ?? '-' }}</td>
                            <td>
                                @if($mutation->type === 'in')
                                    <span class="badge bg-success badge-status">Masuk</span>
                                @elseif($mutation->type === 'out')
                                    <span class="badge bg-danger badge-status">Keluar</span>
                                @else
                                    <span class="badge bg-primary badge-status">Transfer</span>
                                @endif
                            </td>
                            <td>{{ $mutation->warehouse->name ?? '-' }}</td>
                            <td>{{ $mutation->warehouseDestination->name ?? '-' }}</td>
                            <td class="fw-semibold">{{ $mutation->quantity }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($mutation->notes ?? '-', 30) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-exchange-alt fa-2x mb-2 d-block"></i>Belum ada data mutasi stok
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $mutations ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) }}
    </div>
</div>
@endsection
