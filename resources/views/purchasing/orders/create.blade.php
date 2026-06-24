@extends('layouts.app')
@section('title', isset($order) ? 'Edit Pesanan Pembelian' : 'Buat Pesanan Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">{{ isset($order) ? 'Edit Pesanan Pembelian' : 'Buat Pesanan Pembelian' }}</h4>
    <a href="{{ route('purchasing.orders.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

    <form action="{{ isset($order) ? '#' : route('purchasing.orders.store') }}" method="POST">
    @csrf
    @if(isset($order)) @method('PUT') @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label fw-semibold">No. Pesanan</label>
            <input type="text" class="form-control" name="order_number" value="{{ old('order_number', $order->order_number ?? 'PO-' . date('YmdHis')) }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal Pesan <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="order_date" value="{{ old('order_date', $order->order_date ?? date('Y-m-d')) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
            <select class="form-select" name="supplier_id" required>
                <option value="">Pilih Supplier</option>
                @foreach($suppliers ?? [] as $sup)
                    <option value="{{ $sup->id }}" {{ old('supplier_id', $order->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Gudang Tujuan</label>
            <select class="form-select" name="warehouse_id">
                <option value="">Pilih Gudang</option>
                @foreach($warehouses ?? [] as $wh)
                    <option value="{{ $wh->id }}" {{ old('warehouse_id', $order->warehouse_id ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Item Pesanan</h6>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                    <i class="fas fa-plus me-1"></i>Tambah Item
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openScanner(scanPurchaseProduct)">
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
                        <th width="150">Harga Satuan</th>
                        <th width="180">Subtotal</th>
                        <th width="50">Aksi</th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr id="itemRow0">
                        <td>1</td>
                        <td>
                            <select class="form-select form-select-sm select2-product" name="items[0][product_id]" required>
                                <option value="">Pilih Produk</option>
                                @foreach($products ?? [] as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} - {{ $prod->barcode }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm" name="items[0][quantity]" value="1" min="1" onchange="calcSubtotal(0)"></td>
                        <td><input type="number" class="form-control form-control-sm" name="items[0][price]" value="0" onchange="calcSubtotal(0)"></td>
                        <td class="fw-semibold subtotal" id="subtotal0">Rp 0</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Total</td>
                        <td class="fw-bold" id="grandTotal">Rp 0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Catatan</label>
            <textarea class="form-control" name="notes" rows="3">{{ old('notes', $order->notes ?? '') }}</textarea>
        </div>
        <div class="col-md-6 d-flex align-items-end justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i>Simpan Pesanan
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
            if (el.type !== 'hidden') el.value = el.type === 'number' ? (el.name.includes('quantity') ? '1' : '0') : '';
        });
        row.querySelector('.subtotal').id = 'subtotal' + itemIndex;
        row.querySelector('.subtotal').textContent = 'Rp 0';
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
            calcGrandTotal();
        }
    }
    function calcSubtotal(idx) {
        const row = document.getElementById('itemRow' + idx);
        const qty = parseFloat(row.querySelectorAll('input')[0].value) || 0;
        const price = parseFloat(row.querySelectorAll('input')[1].value) || 0;
        const sub = qty * price;
        document.getElementById('subtotal' + idx).textContent = 'Rp ' + sub.toLocaleString('id-ID');
        calcGrandTotal();
    }
    function calcGrandTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            total += parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
        });
        document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    function scanPurchaseProduct(product) {
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
