@extends('layouts.app')
@section('title', 'Terbitkan Gift Card')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Terbitkan Gift Card</h4>
    <a href="{{ route('settings.gift-cards.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.gift-cards.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Gift Card <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('code') is-invalid @enderror" name="code" id="code" value="{{ old('code', 'GC-' . strtoupper(uniqid())) }}" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Saldo Awal <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('initial_balance') is-invalid @enderror" name="initial_balance" value="{{ old('initial_balance') }}" min="0" step="0.01" required>
                            @error('initial_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Kadaluarsa</label>
                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" name="expires_at" value="{{ old('expires_at') }}">
                            @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Terbitkan Gift Card
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function generateCode() {
        document.getElementById('code').value = 'GC-' + Math.random().toString(36).substring(2, 8).toUpperCase();
    }
</script>
@endsection
