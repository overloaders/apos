@extends('layouts.app')
@section('title', 'Riwayat Pembayaran Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Riwayat Pembayaran Piutang - {{ $member->name }}</h4>
    <a href="{{ route('merchandise.members.show', $member) }}" class="btn btn-outline-secondary">
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
                        <th>Jumlah</th>
                        <th>Petugas</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="fw-semibold text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td>{{ $payment->user->name ?? '-' }}</td>
                            <td class="small">{{ $payment->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-2 d-block"></i>Belum ada pembayaran piutang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payments->hasPages())
    <div class="card-footer">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
