@extends('layouts.app')
@section('title', 'Request Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Request Pembelian</h4>
    <a href="{{ route('purchasing.requests.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Buat Request
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form action="{{ route('purchasing.requests.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nomor request..." style="width:200px;" value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="width:140px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Dipesan</option>
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Request</th>
                        <th>Tanggal</th>
                        <th>Diminta Oleh</th>
                        <th>Jumlah Item</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $request)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $request->request_number }}</code></td>
                            <td>{{ $request->created_at->format('d/m/Y') }}</td>
                            <td>{{ $request->requester->name ?? '-' }}</td>
                            <td>{{ $request->items->sum('quantity') }}</td>
                            <td>
                                @switch($request->status)
                                    @case('pending')
                                        <span class="badge bg-warning badge-status">Pending</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-success badge-status">Disetujui</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger badge-status">Ditolak</span>
                                        @break
                                    @case('ordered')
                                        <span class="badge bg-primary badge-status">Dipesan</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                <a href="{{ route('purchasing.requests.show', $request) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($request->status === 'pending')
                                    <form action="{{ route('purchasing.requests.destroy', $request) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus request ini?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>Belum ada request pembelian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchaseRequests->hasPages())
        <div class="card-footer">
            {{ $purchaseRequests->links() }}
        </div>
    @endif
</div>
@endsection
