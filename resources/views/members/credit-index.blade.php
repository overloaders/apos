@extends('layouts.app')
@section('title', 'Piutang Member')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Piutang Member</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Member</th>
                        <th>Nama</th>
                        <th>Limit Piutang</th>
                        <th>Piutang Berjalan</th>
                        <th>Sisa Limit</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $member->code }}</code></td>
                            <td class="fw-semibold">{{ $member->name }}</td>
                            <td>Rp {{ number_format($member->credit_limit, 0, ',', '.') }}</td>
                            <td class="text-danger fw-semibold">Rp {{ number_format($member->outstanding_balance, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($member->remaining_credit, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('merchandise.members.credit', $member) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-money-bill mr-1"></i>Bayar
                                </a>
                                <a href="{{ route('merchandise.members.credit.history', $member) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-credit-card fa-2x mb-2 d-block"></i>Tidak ada piutang member
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($members->hasPages())
    <div class="card-footer">{{ $members->links() }}</div>
    @endif
</div>
@endsection
