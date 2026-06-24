@extends('layouts.app')
@section('title', 'Edit Gift Card')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Edit Gift Card</h4>
    <a href="{{ route('settings.gift-cards.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.gift-cards.update', $giftCard) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Gift Card <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $giftCard->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Saldo Awal <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('initial_balance') is-invalid @enderror" name="initial_balance" value="{{ old('initial_balance', $giftCard->initial_balance) }}" min="0" step="0.01" required>
                            @error('initial_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Kadaluarsa</label>
                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" name="expires_at" value="{{ old('expires_at', $giftCard->expires_at ? $giftCard->expires_at->format('Y-m-d') : '') }}">
                            @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                                <option value="1" {{ old('is_active', $giftCard->is_active) ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active', $giftCard->is_active) ? '' : 'selected' }}>Nonaktif</option>
                            </select>
                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3">{{ old('notes', $giftCard->notes) }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Informasi</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Saldo Saat Ini</label>
                    <div class="fw-bold h5">Rp {{ number_format($giftCard->current_balance, 0, ',', '.') }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Diterbitkan Oleh</label>
                    <div class="fw-semibold">{{ $giftCard->issuer->name ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Diterbitkan Pada</label>
                    <div class="fw-semibold">{{ $giftCard->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
