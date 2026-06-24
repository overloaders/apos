@extends('layouts.app')
@section('title', 'Riwayat Harga Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Riwayat Harga - {{ $product->name }}</h4>
    <a href="{{ route('master.products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Tanggal</th>
                        <th>Harga Beli Lama</th>
                        <th>Harga Beli Baru</th>
                        <th>Harga Jual Lama</th>
                        <th>Harga Jual Baru</th>
                        <th>User</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            <td>Rp {{ number_format($history->old_cost_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($history->new_cost_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($history->old_selling_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($history->new_selling_price, 0, ',', '.') }}</td>
                            <td>{{ $history->user->name ?? '-' }}</td>
                            <td class="small">{{ $history->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-chart-line fa-2x mb-2 d-block"></i>Belum ada riwayat harga
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($histories->hasPages())
    <div class="card-footer">{{ $histories->links() }}</div>
    @endif
</div>
@endsection
