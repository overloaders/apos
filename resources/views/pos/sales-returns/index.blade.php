@extends('layouts.app')
@section('title', 'Retur Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Retur Penjualan</h4>
    <a href="{{ route('pos.history.index') }}" class="btn btn-primary">
        <i class="fas fa-undo-alt me-1"></i>Buat Retur
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Cari no. retur/nota..." style="width:220px;" value="{{ request('search') }}">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Retur</th>
                        <th>No. Nota</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Total Refund</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns ?? [] as $ret)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $ret->return_number }}</code></td>
                            <td><code>{{ $ret->sale->code ?? '-' }}</code></td>
                            <td>{{ $ret->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $ret->user->name ?? '-' }}</td>
                            <td class="fw-semibold text-danger">Rp {{ number_format($ret->total_refund, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('pos.sales-returns.show', $ret) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-undo-alt fa-2x mb-2 d-block"></i>Belum ada retur penjualan
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
            window.location.href = '{{ route('pos.sales-returns.index') }}?search=' + encodeURIComponent(search);
        }
    });
</script>
@endsection
