@extends('layouts.app')
@section('title', 'Profil Perusahaan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Profil Perusahaan</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name', $company->company_name ?? '') }}" required>
                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. Telepon</label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $company->phone ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $company->email ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="url" class="form-control" name="website" value="{{ old('website', $company->website ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control" name="address" rows="2">{{ old('address', $company->address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kota</label>
                            <input type="text" class="form-control" name="city" value="{{ old('city', $company->city ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Provinsi</label>
                            <input type="text" class="form-control" name="province" value="{{ old('province', $company->province ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kode Pos</label>
                            <input type="text" class="form-control" name="postal_code" value="{{ old('postal_code', $company->postal_code ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NPWP</label>
                            <input type="text" class="form-control" name="npwp" value="{{ old('npwp', $company->npwp ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">No. Telp/Fax</label>
                            <input type="text" class="form-control" name="fax" value="{{ old('fax', $company->fax ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pajak (%)</label>
                            <input type="number" class="form-control" name="tax_rate" value="{{ old('tax_rate', $company->tax_rate ?? 11) }}" min="0" max="100">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Kata-kata di Struk</label>
                            <input type="text" class="form-control" name="receipt_message" value="{{ old('receipt_message', $company->receipt_message ?? 'Terima kasih telah berbelanja!') }}" placeholder="Terima kasih telah berbelanja!">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Logo Perusahaan</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            @if(!empty($company->logo))
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" style="max-height:80px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Header Struk</label>
                            <textarea class="form-control" name="receipt_header" rows="3" placeholder="Teks yang muncul di bagian atas struk (bisa pakai HTML sederhana)">{{ old('receipt_header', $company->receipt_header ?? '') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Footer Struk</label>
                            <textarea class="form-control" name="receipt_footer" rows="3" placeholder="Teks yang muncul di bagian bawah struk">{{ old('receipt_footer', $company->receipt_footer ?? '') }}</textarea>
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
                    <label class="text-muted small">Versi Aplikasi</label>
                    <div class="fw-semibold">v1.0.0</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Terakhir Diperbarui</label>
                    <div class="fw-semibold">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
                <hr>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Logo yang diunggah akan ditampilkan di struk cetak. Format yang didukung: JPG, PNG, SVG. Maksimal 2MB.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
