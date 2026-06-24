@extends('layouts.app')
@section('title', 'Detail Request Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Detail Request Pembelian</h4>
    <a href="{{ route('purchasing.requests.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
    @if($purchaseRequest->status === 'pending')
        <div class="d-flex gap-2">
            <form action="{{ route('purchasing.requests.approve', $purchaseRequest) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success" data-confirm="Setujui request ini?">
                    <i class="fas fa-check mr-1"></i>Setujui
                </button>
            </form>
            <form action="{{ route('purchasing.requests.reject', $purchaseRequest) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-danger" data-confirm="Tolak request ini?">
                    <i class="fas fa-times mr-1"></i>Tolak
                </button>
            </form>
        </div>
    @endif
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">No. Request</small>
                <div class="fw-bold"><code>{{ $purchaseRequest->request_number }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Tanggal</small>
                <div class="fw-bold">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Diminta Oleh</small>
                <div class="fw-bold">{{ $purchaseRequest->requester->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Status</small>
                <div>
                    @switch($purchaseRequest->status)
                        @case('pending') <span class="badge bg-warning badge-status">Pending</span> @break
                        @case('approved') <span class="badge bg-success badge-status">Disetujui</span> @break
                        @case('rejected') <span class="badge bg-danger badge-status">Ditolak</span> @break
                        @case('ordered') <span class="badge bg-primary badge-status">Dipesan</span> @break
                    @endswitch
                </div>
            </div>
            @if($purchaseRequest->approved_by)
            <div class="col-md-3">
                <small class="text-muted">Disetujui Oleh</small>
                <div class="fw-bold">{{ $purchaseRequest->approver->name ?? '-' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Disetujui Pada</small>
                <div class="fw-bold">{{ $purchaseRequest->approved_at ? $purchaseRequest->approved_at->format('d/m/Y H:i') : '-' }}</div>
            </div>
            @endif
            @if($purchaseRequest->notes)
            <div class="col-12">
                <small class="text-muted">Catatan</small>
                <div>{{ $purchaseRequest->notes }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Item Yang Diminta</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequest->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->quantity }} {{ $item->product->unit->symbol ?? '' }}</td>
                            <td>{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada item</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
