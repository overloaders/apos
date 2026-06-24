@extends('layouts.app')
@section('title', 'Retur Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Retur Pembelian</h4>
    <a href="{{ route('purchasing.returns.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Buat Retur
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari nomor retur..." style="width:200px;" value="{{ request('search') }}">
            <select class="form-select form-select-sm" id="statusFilter" style="width:150px;">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Retur</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns ?? [] as $ret)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $ret->code }}</code></td>
                            <td><code>{{ $ret->purchaseOrder->code ?? '-' }}</code></td>
                            <td>{{ $ret->supplier->name ?? '-' }}</td>
                            <td>{{ $ret->return_date->format('d/m/Y') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($ret->total, 0, ',', '.') }}</td>
                            <td>
                                @switch($ret->status)
                                    @case('draft') <span class="badge bg-secondary badge-status">Draft</span> @break
                                    @case('completed') <span class="badge bg-success badge-status">Selesai</span> @break
                                    @case('cancelled') <span class="badge bg-danger badge-status">Dibatalkan</span> @break
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('purchasing.returns.show', $ret) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-undo-alt fa-2x mb-2 d-block"></i>Belum ada retur pembelian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $returns->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('#searchInput').on('keypress', function(e) {
        if (e.which == 13) {
            const search = $(this).val();
            const status = $('#statusFilter').val();
            window.location.href = '{{ route('purchasing.returns.index') }}?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status);
        }
    });
    $('#statusFilter').on('change', function() {
        const search = $('#searchInput').val();
        const status = $(this).val();
        window.location.href = '{{ route('purchasing.returns.index') }}?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status);
    });
</script>
@endsection
