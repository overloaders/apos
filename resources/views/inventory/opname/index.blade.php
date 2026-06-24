@extends('layouts.app')
@section('title', 'Stok Opname')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Stok Opname</h4>
    <a href="{{ route('inventory.opname.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Buat Stok Opname
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form method="GET" action="{{ route('inventory.opname.index') }}" class="d-flex align-items-center gap-2 w-100">
            <select name="status" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <noscript><button class="btn btn-primary btn-sm">Tampilkan</button></noscript>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Opname</th>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th class="text-center">Item</th>
                        <th class="text-center">Selisih Qty</th>
                        <th class="text-right">Nilai Stok Sistem</th>
                        <th class="text-right">Nilai Stok Fisik</th>
                        <th class="text-right">Nilai Selisih</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames ?? [] as $opname)
                        @php
                            $totalItems = $opname->items->count();
                            $totalDisc = $opname->items->sum('difference');
                            $totalSysVal = $opname->items->sum('system_value');
                            $totalActVal = $opname->items->sum('actual_value');
                            $totalDiffVal = $opname->items->sum('difference_value');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $opname->code }}</code></td>
                            <td>{{ $opname->opname_date->format('d/m/Y') }}</td>
                            <td>{{ $opname->warehouse->name ?? '-' }}</td>
                            <td class="text-center">{{ $totalItems }}</td>
                            <td class="text-center">
                                <span class="fw-bold {{ $totalDisc > 0 ? 'text-success' : ($totalDisc < 0 ? 'text-danger' : '') }}">
                                    {{ $totalDisc > 0 ? '+' : '' }}{{ number_format($totalDisc, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-right">Rp {{ number_format($totalSysVal, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($totalActVal, 0, ',', '.') }}</td>
                            <td class="text-right">
                                <span class="fw-bold {{ $totalDiffVal > 0 ? 'text-success' : ($totalDiffVal < 0 ? 'text-danger' : '') }}">
                                    Rp {{ number_format($totalDiffVal, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                @if($opname->status === 'draft')
                                    <span class="badge bg-secondary badge-status">Draft</span>
                                @elseif($opname->status === 'approved')
                                    <span class="badge bg-success badge-status">Disetujui</span>
                                @elseif($opname->status === 'rejected')
                                    <span class="badge bg-danger badge-status">Ditolak</span>
                                @else
                                    <span class="badge bg-secondary badge-status">{{ $opname->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory.opname.show', $opname) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                    <i class="fas fa-print me-1"></i>Detail
                                </a>
                                @if($opname->status === 'draft')
                                    <form method="POST" action="{{ route('inventory.opname.approve', $opname) }}" class="d-inline" onsubmit="return confirm('Setujui stok opname ini? Stok akan disesuaikan secara otomatis.')">
                                        @csrf
                                        <button class="btn btn-success btn-sm">
                                            <i class="fas fa-check me-1"></i>Setujui
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>Belum ada data stok opname
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $opnames->withQueryString()->links() }}
    </div>
</div>
@endsection