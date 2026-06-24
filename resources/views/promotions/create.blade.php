@extends('layouts.app')
@section('title', isset($promotion) ? 'Edit Promosi' : 'Tambah Promosi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ isset($promotion) ? 'Edit Promosi' : 'Tambah Promosi' }}</h4>
    <a href="{{ route('merchandise.promotions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($promotion) ? route('merchandise.promotions.update', $promotion) : route('merchandise.promotions.store') }}" method="POST">
            @csrf
            @isset($promotion) @method('PUT') @endisset
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Promosi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $promotion->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tipe <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                            <option value="discount_percent" {{ (old('type', $promotion->type ?? '') == 'discount_percent') ? 'selected' : '' }}>Diskon Persentase</option>
                            <option value="discount_amount" {{ (old('type', $promotion->type ?? '') == 'discount_amount') ? 'selected' : '' }}>Diskon Nominal</option>
                        </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Nilai <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('value') is-invalid @enderror" name="value" value="{{ old('value', $promotion->value ?? '') }}" min="0" required>
                    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date', isset($promotion) ? $promotion->start_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Berakhir <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date', isset($promotion) ? $promotion->end_date->format('Y-m-d') : date('Y-m-d', strtotime('+7 days'))) }}" required>
                    @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" name="is_active">
                        <option value="1" {{ old('is_active', $promotion->is_active ?? 1) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $promotion->is_active ?? 1) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Produk</label>
                    <div class="input-group mb-1">
                        <select class="form-select select2-product-multiple" name="product_ids[]" multiple style="width:100%;">
                            @foreach($products as $p)
                                @php
                                    $selectedIds = old('product_ids', isset($promotion) ? $promotion->products->pluck('product_id')->toArray() : []);
                                @endphp
                                <option value="{{ $p->id }}" {{ in_array($p->id, $selectedIds) ? 'selected' : '' }}>
                                    {{ $p->barcode }} - {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="openScanner(scanPromotionProduct)" title="Scan Barcode">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Ketik untuk mencari produk, atau scan barcode untuk menambah produk</small>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description', $promotion->notes ?? '') }}</textarea>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('merchandise.promotions.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>{{ isset($promotion) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function scanPromotionProduct(product) {
    var select = $('.select2-product-multiple');
    var existing = select.val() || [];
    if (existing.indexOf(String(product.id)) === -1) {
        existing.push(String(product.id));
        select.val(existing).trigger('change');
    }
}

$('.select2-product-multiple').select2({
    theme: 'bootstrap4',
    width: '100%',
    placeholder: 'Cari dan pilih produk...'
});
</script>
@endsection
