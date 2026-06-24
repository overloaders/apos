@extends('layouts.app')
@section('title', 'Buat Target Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Buat Target Penjualan</h4>
    <a href="{{ route('reports.sales-targets.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('reports.sales-targets.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">User (Opsional)</label>
                            <select class="form-select @error('user_id') is-invalid @enderror" name="user_id">
                                <option value="">Semua Kasir</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jumlah Target <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('target_amount') is-invalid @enderror" name="target_amount" value="{{ old('target_amount') }}" min="0" step="0.01" required>
                            @error('target_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                            <select class="form-select @error('period') is-invalid @enderror" name="period" required>
                                <option value="">Pilih Periode</option>
                                <option value="daily" {{ old('period') == 'daily' ? 'selected' : '' }}>Harian</option>
                                <option value="weekly" {{ old('period') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                <option value="monthly" {{ old('period') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                <option value="yearly" {{ old('period') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                            </select>
                            @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <i class="fas fa-save me-1"></i>Simpan Target
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
