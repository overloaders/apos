@extends('layouts.app')
@section('title', 'Buat Stok Opname')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Buat Stok Opname</h4>
    <a href="{{ route('inventory.opname.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<form method="POST" action="{{ route('inventory.opname.store') }}" id="formOpname">
    @csrf
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gudang <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" id="warehouse_id" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="1">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Daftar Produk</h6>
            <div>
                <button type="button" class="btn btn-primary btn-sm" id="btnTambahProduk">
                    <i class="fas fa-plus me-1"></i>Tambah Produk
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openScanner(scanOpnameProduct)">
                    <i class="fas fa-camera me-1"></i>Scan
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableItems">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Produk</th>
                            <th>Stok Sistem</th>
                            <th>Stok Fisik</th>
                            <th>Selisih</th>
                            <th width="50">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        <tr class="empty-row">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Belum ada produk. Klik "Tambah Produk" untuk memulai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Simpan Stok Opname
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
let productIndex = 0;
const products = {!! $products ?? '[]' !!};

document.getElementById('btnTambahProduk').addEventListener('click', function() {
    addProductRow();
});

function scanOpnameProduct(product) {
    var match = products.find(function(p) { return String(p.id) === String(product.id); });
    if (match) {
        addProductRow(match.id);
        var warehouseId = document.getElementById('warehouse_id').value;
        if (warehouseId) {
            var lastRow = document.querySelector('#itemsContainer tr:last-child');
            if (lastRow) {
                fetchSystemStock(match.id, warehouseId).then(function(data) {
                    lastRow.querySelector('.system-stock').value = data.stock;
                    calcDifference(lastRow);
                });
            }
        }
    }
}

document.getElementById('warehouse_id').addEventListener('change', function() {
    loadSystemStocks();
});

function addProductRow(productId = '', systemStock = 0, physicalCount = '') {
    const container = document.getElementById('itemsContainer');
    const emptyRow = container.querySelector('.empty-row');
    if (emptyRow) emptyRow.remove();

    const idx = productIndex++;
    const tr = document.createElement('tr');
    tr.id = `row-${idx}`;
    tr.innerHTML = `
        <td class="text-center row-num">${idx + 1}</td>
        <td>
            <select name="items[${idx}][product_id]" class="form-select form-select-sm product-select select2-product" required>
                <option value="">-- Pilih Produk --</option>
                ${products.map(p => `<option value="${p.id}" ${String(p.id) === String(productId) ? 'selected' : ''}>${p.name} (${p.barcode || p.code})</option>`).join('')}
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm system-stock" value="${systemStock}" readonly style="background:#f0f0f0;">
        </td>
        <td>
            <input type="number" min="0" step="0.01" name="items[${idx}][physical_count]" class="form-control form-control-sm physical-count" value="${physicalCount}" required placeholder="0">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm difference" value="0" readonly style="background:#f0f0f0;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${idx})">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;

    tr.querySelector('.physical-count').addEventListener('input', function() {
        calcDifference(tr);
    });

    container.appendChild(tr);

    const $select = $(tr).find('.product-select');
    $select.select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Cari produk...' });

    renumberRows();
}

function removeRow(idx) {
    const row = document.getElementById(`row-${idx}`);
    if (row) {
        $(row).find('.select2-product').select2('destroy');
        row.remove();
        renumberRows();
        if (document.querySelectorAll('#itemsContainer tr:not(.empty-row)').length === 0) {
            document.getElementById('itemsContainer').innerHTML = `
                <tr class="empty-row">
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-boxes fa-2x mb-2 d-block"></i>Belum ada produk. Klik "Tambah Produk" untuk memulai.
                    </td>
                </tr>`;
        }
    }
}

function calcDifference(row) {
    const system = parseFloat(row.querySelector('.system-stock').value) || 0;
    const physical = parseFloat(row.querySelector('.physical-count').value) || 0;
    row.querySelector('.difference').value = (physical - system).toFixed(0);
}

function renumberRows() {
    document.querySelectorAll('#itemsContainer tr:not(.empty-row)').forEach((tr, i) => {
        tr.querySelector('.row-num').textContent = i + 1;
    });
}

async function fetchSystemStock(productId, warehouseId) {
    try {
        const response = await fetch(`/inventory/opname/get-stock?product_id=${productId}&warehouse_id=${warehouseId}`);
        const data = await response.json();
        return data;
    } catch (e) {
        return { stock: 0, unit_cost: 0 };
    }
}

async function loadSystemStocks() {
    const warehouseId = document.getElementById('warehouse_id').value;
    if (!warehouseId) return;

    document.querySelectorAll('#itemsContainer tr:not(.empty-row)').forEach(async (tr) => {
        const select = tr.querySelector('.product-select');
        if (select && select.value) {
            const data = await fetchSystemStock(select.value, warehouseId);
            tr.querySelector('.system-stock').value = data.stock;
            calcDifference(tr);
        }
    });
}

@if($errors->any() && old('items'))
    @php
        $oldItems = old('items', []);
    @endphp
    @foreach($oldItems as $item)
        addProductRow('{{ $item['product_id'] ?? '' }}', 0, '{{ $item['physical_count'] ?? '' }}');
    @endforeach
@endif
$(document).ready(function() {
    $('.product-select').each(function() {
        const $sel = $(this);
        if (!$sel.data('select2')) {
            $sel.select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Cari produk...' });
        }
    });

    $('#itemsContainer').on('change', '.product-select', function() {
        const warehouseId = document.getElementById('warehouse_id').value;
        if (warehouseId && this.value) {
            const tr = this.closest('tr');
            fetchSystemStock(this.value, warehouseId).then(data => {
                tr.querySelector('.system-stock').value = data.stock;
                calcDifference(tr);
            });
        }
    });
});
</script>
@endsection