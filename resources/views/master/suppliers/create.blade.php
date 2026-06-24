@extends('layouts.app')
@section('title', isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ isset($supplier) ? 'Edit Supplier' : 'Tambah Supplier' }}</h4>
    <a href="{{ route('master.suppliers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('master.suppliers.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $supplier->id ?? '' }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $supplier->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Perusahaan</label>
                    <input type="text" class="form-control" name="company" value="{{ old('company', $supplier->company ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kontak Person</label>
                    <input type="text" class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Telepon</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $supplier->email ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fax</label>
                    <input type="text" class="form-control" name="fax" value="{{ old('fax', $supplier->fax ?? '') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea class="form-control" name="address" rows="2">{{ old('address', $supplier->address ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kota</label>
                    <input type="text" class="form-control" name="city" value="{{ old('city', $supplier->city ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode Pos</label>
                    <input type="text" class="form-control" name="postal_code" value="{{ old('postal_code', $supplier->postal_code ?? '') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('master.suppliers.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
