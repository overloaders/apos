@extends('layouts.app')
@section('title', 'Tambah Mutasi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Tambah Mutasi Stok</h4>
    <a href="{{ route('inventory.mutations.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('inventory.mutations.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Produk <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="product_id" class="form-select select2-product @error('product_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->barcode ?? $product->code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="openScanner(scanMutationProduct)" title="Scan Barcode">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                    </div>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gudang Asal <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Gudang Asal --</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gudang Tujuan <span class="text-danger">*</span></label>
                    <select name="warehouse_destination_id" class="form-select @error('warehouse_destination_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Gudang Tujuan --</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_destination_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_destination_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan Mutasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function scanMutationProduct(product) {
    var select = document.querySelector('select[name="product_id"]');
    if (select) {
        $(select).val(product.id).trigger('change');
    }
}

$('.select2-product').select2({
    theme: 'bootstrap4',
    width: '100%'
});
</script>
@endsection