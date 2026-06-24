@extends('layouts.app')
@section('title', 'Buat Retur Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Buat Retur Pembelian</h4>
    <a href="{{ route('purchasing.returns.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Pilih Pesanan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                        <tr>
                            <td><code class="fw-semibold">{{ $order->code }}</code></td>
                            <td>{{ $order->order_date->format('d/m/Y') }}</td>
                            <td>{{ $order->supplier->name ?? '-' }}</td>
                            <td class="fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>
                                @switch($order->status)
                                    @case('partial') <span class="badge bg-warning badge-status">Sebagian</span> @break
                                    @case('received') <span class="badge bg-success badge-status">Diterima</span> @break
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('purchasing.returns.create_from_po', $order) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-undo mr-1"></i>Retur
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Tidak ada pesanan dengan penerimaan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
