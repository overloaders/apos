@extends('layouts.app')
@section('title', 'Pilih Pesanan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Pilih Pesanan Pembelian</h4>
    <a href="{{ route('purchasing.receivings.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><code>{{ $order->code }}</code></td>
                            <td>{{ $order->order_date->format('d/m/Y') }}</td>
                            <td>{{ $order->supplier->name ?? '-' }}</td>
                            <td>
                                @switch($order->status)
                                    @case('ordered') <span class="badge badge-primary">Dipesan</span> @break
                                    @case('partial') <span class="badge badge-warning">Diterima Sebagian</span> @break
                                    @default <span class="badge badge-secondary">{{ $order->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('purchasing.receivings.create_from_po', $order) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-truck-loading mr-1"></i>Terima
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pesanan yang siap diterima</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
