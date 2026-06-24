@extends('layouts.app')
@section('title', 'Riwayat Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Riwayat Penjualan</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Cetak
        </button>
    </div>
</div>

<form method="GET" action="{{ route('pos.history.index') }}">
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Dari Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Sampai Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Cari</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="No. Nota / Member">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Metode Bayar</label>
                <select class="form-select form-select-sm" name="payment_method">
                    <option value="">Semua</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Kartu</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="ewallet" {{ request('payment_method') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                    <option value="gift_card" {{ request('payment_method') == 'gift_card' ? 'selected' : '' }}>Gift Card</option>
                    <option value="mixed" {{ request('payment_method') == 'mixed' ? 'selected' : '' }}>Campuran</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">Semua</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div class="col-md-2">
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
                        <th>No. Nota</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Member</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Bayar</th>
                        <th>Kembali</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales ?? [] as $tx)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $tx->code }}</code></td>
                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $tx->user->name ?? '-' }}</td>
                            <td>{{ $tx->member->name ?? '-' }}</td>
                            <td class="text-center">{{ $tx->items_count ?? $tx->items->count() }}</td>
                            <td class="fw-semibold">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($tx->amount_paid, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($tx->change_amount, 0, ',', '.') }}</td>
                            <td>
                                @switch($tx->payment_method)
                                    @case('cash')
                                        <span class="badge bg-success">Tunai</span>
                                        @break
                                    @case('card')
                                        <span class="badge bg-primary">Kartu</span>
                                        @break
                                    @case('transfer')
                                        <span class="badge bg-info">Transfer</span>
                                        @break
                                    @case('ewallet')
                                        <span class="badge bg-warning">E-Wallet</span>
                                        @break
                                    @case('gift_card')
                                        <span class="badge bg-info">Gift Card</span>
                                        @break
                                    @case('mixed')
                                        <span class="badge bg-secondary">Campuran</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $tx->payment_method }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($tx->status === 'completed')
                                    <span class="badge bg-success badge-status">Lunas</span>
                                @else
                                    <span class="badge bg-danger badge-status">Batal</span>
                                @endif
                            </td>
                            <td>
                                @if($tx->status === 'completed')
                                    <a href="{{ route('pos.sales-returns.create-from-sale', $tx) }}" class="btn btn-sm btn-outline-warning" title="Retur">
                                        <i class="fas fa-undo-alt"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-2 d-block"></i>Belum ada riwayat penjualan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $sales->withQueryString()->links() }}
    </div>
</div>
@endsection
