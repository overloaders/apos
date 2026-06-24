@extends('layouts.app')
@section('title', 'Buat Request Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Buat Request Pembelian</h4>
    <a href="{{ route('purchasing.requests.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<form action="{{ route('purchasing.requests.store') }}" method="POST">
    @csrf

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold">No. Request</label>
            <input type="text" class="form-control" value="PR-{{ date('Ymd') . '-' . strtoupper(uniqid()) }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Diminta Oleh</label>
            <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Item Yang Diminta</h6>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                    <i class="fas fa-plus me-1"></i>Tambah Item
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openScanner(scanRequestProduct)">
                    <i class="fas fa-camera me-1"></i>Scan
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th width="30">#</th>
                        <th>Produk</th>
                        <th width="120">Qty</th>
                        <th>Catatan</th>
                        <th width="50">Aksi</th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr id="itemRow0">
                        <td>1</td>
                        <td>
                            <select class="form-select form-select-sm select2-product" name="items[0][product_id]" required>
                                <option value="">Pilih Produk</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} - {{ $prod->barcode }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm" name="items[0][quantity]" value="1" min="0.01" step="0.01"></td>
                        <td><input type="text" class="form-control form-control-sm" name="items[0][notes]" placeholder="Catatan item"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Catatan</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3">{{ old('notes') }}</textarea>
            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i>Simpan Request
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    let itemIndex = 1;
    function initSelect2(el) {
        $(el).select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Cari produk...'
        });
    }
    function addItem() {
        const row = document.getElementById('itemRow0').cloneNode(true);
        row.id = 'itemRow' + itemIndex;
        row.querySelector('td:first-child').textContent = itemIndex + 1;
        row.querySelectorAll('select, input').forEach(el => {
            el.name = el.name.replace('[0]', '[' + itemIndex + ']');
            if (el.type !== 'hidden') el.value = el.type === 'number' ? '1' : '';
        });
        document.getElementById('itemsBody').appendChild(row);
        initSelect2(row.querySelector('.select2-product'));
        itemIndex++;
    }
    function removeItem(btn) {
        if (document.querySelectorAll('#itemsBody tr').length > 1) {
            const row = btn.closest('tr');
            const sel = row.querySelector('.select2-product');
            if (sel && $(sel).data('select2')) $(sel).select2('destroy');
            row.remove();
        }
    }
    function scanRequestProduct(product) {
        var existingRow = null;
        document.querySelectorAll('#itemsBody tr').forEach(function(row) {
            var sel = row.querySelector('.select2-product');
            if (sel && !sel.value) existingRow = row;
        });
        if (!existingRow) {
            addItem();
            existingRow = document.querySelector('#itemsBody tr:last-child');
        }
        var select = existingRow.querySelector('.select2-product');
        if (select) {
            $(select).val(product.id).trigger('change');
        }
    }
    $(document).ready(function() {
        $('.select2-product').each(function() { initSelect2(this); });
    });
</script>
@endsection
