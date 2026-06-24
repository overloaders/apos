@extends('layouts.app')
@section('title', 'Kasir & Shift')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Kasir & Shift</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#shiftModal">
        <i class="fas fa-plus me-1"></i>Buka Shift Baru
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card summary-card primary">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Shift Aktif</div>
                <h3 class="fw-bold mb-0">{{ $activeShifts ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card success">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Total Penjualan Shift Ini</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($shiftSales ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card warning">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Tunai di Kasir</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($cashInDrawer ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card info">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Total Transaksi</div>
                <h3 class="fw-bold mb-0">{{ $shiftTransactions ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card summary-card secondary">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Shift Tertutup</div>
                <h3 class="fw-bold mb-0">{{ $closedShifts->count() ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card success">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Total Penjualan Tertutup</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($closedSales ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card warning">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Saldo Akhir Tertutup</div>
                <h4 class="fw-bold mb-0">Rp {{ number_format($closedCash ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card info">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Transaksi Tertutup</div>
                <h3 class="fw-bold mb-0">{{ $closedTransactions ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Riwayat Shift</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Shift</th>
                        <th>Kasir</th>
                        <th>Kasir Register</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Saldo Awal</th>
                        <th>Saldo Akhir</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts ?? [] as $shift)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $shift->code ?? 'SHIFT-' . str_pad($shift->id, 6, '0', STR_PAD_LEFT) }}</code></td>
                            <td>{{ $shift->user->name ?? '-' }}</td>
                            <td>{{ $shift->cashRegister->name ?? '-' }}</td>
                            <td>{{ $shift->opened_at ? $shift->opened_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</td>
                            <td>{{ $shift->closing_cash ? 'Rp ' . number_format($shift->closing_cash, 0, ',', '.') : '-' }}</td>
                            <td>
                                @if($shift->status === 'open')
                                    <span class="badge bg-success badge-status">Buka</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Tutup</span>
                                @endif
                            </td>
                            <td>
                                @if($shift->status === 'open' && ($shift->user_id === Auth::id() || Auth::user()->hasPermission('settings.manage')))
                                    <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#closeShiftModal-{{ $shift->id }}">
                                        <i class="fas fa-times-circle mr-1"></i>Tutup Shift
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-clock fa-2x mb-2 d-block"></i>Belum ada data shift
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="shiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pos.shifts.open') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Buka Shift Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kasir</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Register Kas</label>
                        <select class="form-select" name="cash_register_id" required>
                            <option value="">Pilih Register</option>
                            @foreach($registers ?? [] as $reg)
                                <option value="{{ $reg->id }}">{{ $reg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Saldo Awal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="opening_cash" value="0" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buka Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($shifts as $shift)
@if($shift->status === 'open' && ($shift->user_id === Auth::id() || Auth::user()->hasPermission('settings.manage')))
<div class="modal fade" id="closeShiftModal-{{ $shift->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pos.shifts.close', $shift) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tutup Shift</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Saldo Akhir <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="closing_cash" value="0" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Tutup Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach
@endsection
