@extends('layouts.app')
@section('title', 'Gift Card / Voucher')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Gift Card / Voucher</h4>
    <a href="{{ route('settings.gift-cards.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Terbitkan Gift Card
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form action="{{ route('settings.gift-cards.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode..." style="width:200px;" value="{{ request('search') }}">
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Saldo Awal</th>
                        <th>Saldo Saat Ini</th>
                        <th>Kadaluarsa</th>
                        <th>Status</th>
                        <th>Diterbitkan Oleh</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($giftCards as $giftCard)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $giftCard->code }}</code></td>
                            <td>Rp {{ number_format($giftCard->initial_balance, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($giftCard->current_balance, 0, ',', '.') }}</td>
                            <td>{{ $giftCard->expires_at ? $giftCard->expires_at->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($giftCard->is_active && !$giftCard->hasExpired())
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $giftCard->issuer->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('settings.gift-cards.edit', $giftCard) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="topUp({{ $giftCard->id }}, '{{ $giftCard->code }}')" title="Top Up">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                                <form action="{{ route('settings.gift-cards.destroy', $giftCard) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Hapus gift card ini?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-gift-card fa-2x mb-2 d-block"></i>Belum ada gift card
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($giftCards->hasPages())
        <div class="card-footer">
            {{ $giftCards->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="topUpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="topUpForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Top Up Gift Card</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Menambah saldo untuk <strong id="topUpCode"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah Top Up <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" id="topUpAmount" min="0.01" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Top Up</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function topUp(id, code) {
        document.getElementById('topUpCode').textContent = code;
        document.getElementById('topUpForm').action = '{{ url("settings/gift-cards") }}/' + id + '/top-up';
        document.getElementById('topUpAmount').value = '';
        $('#topUpModal').modal('show');
    }
</script>
@endsection
