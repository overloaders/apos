@extends('layouts.app')
@section('title', 'Detail Pesanan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Detail Pesanan Pembelian</h4>
    <a href="{{ route('purchasing.orders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
    @if($order->status === 'draft')
        <form action="{{ route('purchasing.orders.updateStatus', $order) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="status" value="ordered">
            <button class="btn btn-primary" data-confirm="Konfirmasi pesanan ini?">
                <i class="fas fa-check mr-1"></i>Pesan
            </button>
        </form>
        <form action="{{ route('purchasing.orders.updateStatus', $order) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <button class="btn btn-outline-danger" data-confirm="Batalkan pesanan ini?">
                <i class="fas fa-times mr-1"></i>Batalkan
            </button>
        </form>
    @endif
    @if(in_array($order->status, ['ordered', 'partial']))
        <a href="{{ route('purchasing.receivings.create_from_po', $order) }}" class="btn btn-success">
            <i class="fas fa-truck-loading mr-1"></i>Terima Barang
        </a>
    @endif
    @if($order->payment_status !== 'paid' && in_array($order->status, ['ordered', 'partial', 'received']))
        <a href="{{ route('purchasing.orders.payment', $order) }}" class="btn btn-outline-success">
            <i class="fas fa-money-bill mr-1"></i>Bayar
        </a>
    @endif
    @if($order->status === 'ordered')
        <form action="{{ route('purchasing.orders.updateStatus', $order) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <button class="btn btn-outline-danger" data-confirm="Batalkan pesanan ini?">
                <i class="fas fa-times mr-1"></i>Batalkan
            </button>
        </form>
    @endif
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">No. Pesanan</small>
                <div class="fw-bold"><code>{{ $order->code }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Tanggal</small>
                <div class="fw-bold">{{ $order->order_date->format('d/m/Y') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Supplier</small>
                <div class="fw-bold">{{ $order->supplier->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Status</small>
                <div>
                    @switch($order->status)
                        @case('draft') <span class="badge badge-secondary">Draft</span> @break
                        @case('ordered') <span class="badge badge-primary">Dipesan</span> @break
                        @case('partial') <span class="badge badge-warning">Diterima Sebagian</span> @break
                        @case('received') <span class="badge badge-success">Diterima</span> @break
                        @case('cancelled') <span class="badge badge-danger">Dibatalkan</span> @break
                    @endswitch
                </div>
            </div>
            @if($order->warehouse)
            <div class="col-md-3">
                <small class="text-muted">Gudang</small>
                <div class="fw-bold">{{ $order->warehouse->name }}</div>
            </div>
            @endif
            @if($order->expected_date)
            <div class="col-md-3">
                <small class="text-muted">Tgl. Diharapkan</small>
                <div class="fw-bold">{{ $order->expected_date->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($order->notes)
            <div class="col-12">
                <small class="text-muted">Catatan</small>
                <div>{{ $order->notes }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Item Pesanan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Diterima</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        @php $netReceived = $item->received_quantity - ($item->returned_quantity ?? 0); @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->quantity }} {{ $item->product->unit->symbol ?? '' }}</td>
                            <td>{{ $netReceived }} {{ $item->product->unit->symbol ?? '' }}
                                @if($item->returned_quantity > 0)
                                    <br><small class="text-danger">(retur {{ $item->returned_quantity }})</small>
                                @endif
                            </td>
                            <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-bold">Rp {{ number_format($netReceived * $item->unit_price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada item</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total</td>
                        <td>Rp {{ number_format($order->items->sum(fn($i) => (($i->received_quantity - ($i->returned_quantity ?? 0)) ?: $i->quantity) * $i->unit_price), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
