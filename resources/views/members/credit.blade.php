@extends('layouts.app')
@section('title', 'Pembayaran Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Pembayaran Piutang Member</h4>
    <a href="{{ route('merchandise.members.show', $member) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">Member</small>
                <div class="fw-bold">{{ $member->name }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">No. Member</small>
                <div class="fw-bold"><code>{{ $member->code }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Limit Piutang</small>
                <div class="fw-bold">Rp {{ number_format($member->credit_limit, 0, ',', '.') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Sisa Piutang</small>
                <div class="fw-bold text-danger">Rp {{ number_format($member->outstanding_balance, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Catat Pembayaran Piutang</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('merchandise.members.credit.store', $member) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jumlah Pembayaran <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', $member->outstanding_balance) }}" min="0.01" max="{{ $member->outstanding_balance }}" step="0.01" required>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('payment_date') is-invalid @enderror" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('merchandise.members.show', $member) }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
