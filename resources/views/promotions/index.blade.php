@extends('layouts.app')
@section('title', 'Promosi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Promosi</h4>
    <a href="{{ route('merchandise.promotions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Promosi
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari promosi..." style="width:220px;">
            <select class="form-select form-select-sm" style="width:150px;">
                <option>Semua Status</option>
                <option>Aktif</option>
                <option>Terjadwal</option>
                <option>Selesai</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Promosi</th>
                        <th>Tipe</th>
                        <th>Nilai</th>
                        <th>Min. Pembelian</th>
                        <th>Berlaku Dari</th>
                        <th>Berlaku Sampai</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions ?? [] as $promo)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $promo->name }}</td>
                            <td>
                                @if($promo->type === 'discount_percent')
                                    <span class="badge bg-primary">Diskon %</span>
                                @elseif($promo->type === 'discount_amount')
                                    <span class="badge bg-info">Diskon Rp</span>
                                @else
                                    <span class="badge bg-warning">{{ $promo->type }}</span>
                                @endif
                            </td>
                            <td>
                                @if($promo->type === 'discount_percent')
                                    {{ $promo->value }}%
                                @else
                                    Rp {{ number_format($promo->value, 0, ',', '.') }}
                                @endif
                            </td>
                            <td>Rp {{ number_format($promo->min_purchase ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $promo->start_date->format('d/m/Y') }}</td>
                            <td>{{ $promo->end_date->format('d/m/Y') }}</td>
                            <td>
                                @if($promo->is_active)
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @elseif($promo->start_date->isFuture())
                                    <span class="badge bg-warning badge-status">Terjadwal</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Selesai</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('merchandise.promotions.edit', $promo) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('merchandise.promotions.destroy', $promo) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus promosi ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-percentage fa-2x mb-2 d-block"></i>Belum ada data promosi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $promotions ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) }}
    </div>
</div>
@endsection
