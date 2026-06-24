@extends('layouts.app')
@section('title', isset($product) ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ isset($product) ? 'Edit Produk' : 'Tambah Produk' }}</h4>
    <a href="{{ route('master.products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($product) ? route('master.products.update', $product) : route('master.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($product)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Barcode <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}" required>
                        <button class="btn btn-outline-secondary" type="button" id="generateBarcode">
                            <i class="fas fa-qrcode"></i> Generate
                        </button>
                        @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Merek</label>
                    <select class="form-select" name="brand_id">
                        <option value="">Pilih Merek</option>
                        @foreach($brands ?? [] as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Satuan <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit_id') is-invalid @enderror" name="unit_id" required>
                        <option value="">Pilih Satuan</option>
                        @foreach($units ?? [] as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Harga Beli <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('cost_price') is-invalid @enderror" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0" required>
                        @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Harga Jual <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('selling_price') is-invalid @enderror" name="selling_price" value="{{ old('selling_price', $product->selling_price ?? '') }}" min="0" required>
                        @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Harga Member</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="member_price" value="{{ old('member_price', $product->member_price ?? '') }}" min="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Stok Minimum</label>
                    <input type="number" class="form-control @error('min_stock') is-invalid @enderror" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 5) }}" min="0">
                    @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Gambar</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    @if(!empty($product->image))
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Gambar Produk" style="max-height:120px;border-radius:8px;">
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" name="is_active">
                        <option value="1" {{ old('is_active', $product->is_active ?? 1) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $product->is_active ?? 1) ? '' : 'selected' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('master.products.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('generateBarcode').addEventListener('click', function() {
        var barcode = 'BRG' + Date.now().toString().slice(-10);
        document.querySelector('input[name="barcode"]').value = barcode;
    });
</script>
@endsection
