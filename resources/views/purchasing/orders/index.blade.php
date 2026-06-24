@extends('layouts.app')
@section('title', 'Pesanan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Pesanan Pembelian</h4>
    <a href="{{ route('purchasing.orders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Buat Pesanan
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari nomor pesanan..." style="width:220px;">
            <select class="form-select form-select-sm" style="width:150px;">
                <option>Semua Status</option>
                <option>Draft</option>
                <option>Dipesan</option>
                <option>Diterima Sebagian</option>
                <option>Diterima</option>
                <option>Dibatalkan</option>
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
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Sisa</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders ?? [] as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $order->code }}</code></td>
                            <td>{{ $order->order_date->format('d/m/Y') }}</td>
                            <td>{{ $order->supplier->name ?? '-' }}</td>
                            <td class="fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>
                                @switch($order->status)
                                    @case('draft')
                                        <span class="badge bg-secondary badge-status">Draft</span>
                                        @break
                                    @case('ordered')
                                        <span class="badge bg-primary badge-status">Dipesan</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-warning badge-status">Diterima Sebagian</span>
                                        @break
                                    @case('received')
                                        <span class="badge bg-success badge-status">Diterima</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger badge-status">Dibatalkan</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if($order->payment_status === 'paid')
                                    <span class="badge bg-success badge-status">Lunas</span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="badge bg-warning badge-status">Partial</span>
                                @else
                                    <span class="badge bg-danger badge-status">Belum</span>
                                @endif
                            </td>
                            <td class="fw-semibold {{ $order->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <a href="{{ route('purchasing.orders.show', $order) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($order->payment_status !== 'paid' && in_array($order->status, ['ordered', 'partial', 'received']))
                                    <a href="{{ route('purchasing.orders.payment', $order) }}" class="btn btn-sm btn-success" title="Bayar">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                @endif
                                <form action="{{ route('purchasing.orders.destroy', $order) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus pesanan ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>Belum ada pesanan pembelian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $purchaseOrders ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) }}
    </div>
</div>
@endsection
