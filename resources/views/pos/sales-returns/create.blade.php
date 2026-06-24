@extends('layouts.app')
@section('title', 'Retur Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('pos.history.index') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <h4 class="d-inline mb-0 fw-bold">Retur Penjualan</h4>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="140">No. Nota</td>
                        <td class="fw-semibold"><code>{{ $sale->code }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Member</td>
                        <td>{{ $sale->member->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="140">Total</td>
                        <td class="fw-semibold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($sale->status === 'completed')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($sale->status === 'refunded')
                                <span class="badge bg-warning">Diretur</span>
                            @else
                                <span class="badge bg-secondary">{{ $sale->status }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('pos.sales-returns.store', $sale) }}" id="returnForm">
    @csrf

    <div class="card">
        <div class="card-header fw-bold">Pilih Item yang Dikembalikan</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Produk</th>
                            <th class="text-center">Qty Terjual</th>
                            <th class="text-center">Sudah Retur</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center" width="120">Qty Retur</th>
                            <th class="text-end" width="150">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            @php
                                $returnedQty = \App\Models\SaleReturnItem::where('sale_item_id', $item->id)->sum('quantity');
                                $availReturn = $item->quantity - $returnedQty;
                            @endphp
                            <tr>
                                <td>
                                    @if($availReturn > 0)
                                        <input type="checkbox" class="item-checkbox" data-index="{{ $loop->index }}" checked>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $item->product->barcode ?? '' }}</small>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ $returnedQty }}</td>
                                <td class="text-center">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td>
                                    @if($availReturn > 0)
                                        <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $item->product_id }}">
                                        <input type="number" name="items[{{ $loop->index }}][quantity]"
                                            class="form-control form-control-sm text-center qty-input"
                                            value="{{ $availReturn }}" min="0.01" max="{{ $availReturn }}"
                                            step="0.01" data-price="{{ $item->unit_price }}"
                                            data-avail="{{ $availReturn }}">
                                    @else
                                        <span class="text-muted small">Tidak bisa retur</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="subtotal-text fw-semibold">Rp {{ number_format($item->unit_price * $availReturn, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alasan Retur</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Opsional">{{ old('reason') }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="text-muted">Total Refund:</span>
                            <span class="fs-5 fw-bold text-danger ms-2" id="totalRefund">Rp 0</span>
                        </div>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-undo-alt me-1"></i>Proses Retur
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function calculateTotal() {
        let total = 0;
        $('.qty-input').each(function() {
            const $input = $(this);
            const $row = $input.closest('tr');
            const $checkbox = $row.find('.item-checkbox');
            if ($checkbox.length && !$checkbox.is(':checked')) return;
            const qty = parseFloat($input.val()) || 0;
            const price = parseFloat($input.data('price')) || 0;
            const subtotal = qty * price;
            total += subtotal;
            $row.find('.subtotal-text').text('Rp ' + subtotal.toLocaleString('id-ID'));
        });
        $('#totalRefund').text('Rp ' + total.toLocaleString('id-ID'));
    }

    $(document).ready(function() {
        calculateTotal();

        $(document).on('input', '.qty-input', calculateTotal);
        $(document).on('change', '.item-checkbox', function() {
            const $row = $(this).closest('tr');
            const $input = $row.find('.qty-input');
            if ($(this).is(':checked')) {
                $input.prop('disabled', false);
                const avail = parseFloat($input.data('avail')) || 0;
                $input.val(avail);
            } else {
                $input.prop('disabled', true).val(0);
            }
            calculateTotal();
        });

        $('#selectAll').on('change', function() {
            $('.item-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
        });

        $('#returnForm').on('submit', function(e) {
            let hasItems = false;
            $('.qty-input').each(function() {
                const $row = $(this).closest('tr');
                const $checkbox = $row.find('.item-checkbox');
                if ($checkbox.length && $checkbox.is(':checked') && parseFloat($(this).val()) > 0) {
                    hasItems = true;
                }
            });
            if (!hasItems) {
                e.preventDefault();
                Swal.fire('Error', 'Pilih minimal satu item untuk diretur.', 'error');
                return false;
            }
        });
    });
</script>
@endsection
