@extends('layouts.app')
@section('title', 'Pengeluaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Pengeluaran</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
        <i class="fas fa-plus me-1"></i>Tambah Pengeluaran
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" placeholder="Cari pengeluaran..." style="width:220px;">
            <select class="form-select form-select-sm" style="width:150px;">
                <option>Semua Status</option>
                <option>Menunggu</option>
                <option>Disetujui</option>
                <option>Ditolak</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Pengeluaran</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses ?? [] as $expense)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold">{{ $expense->code }}</code></td>
                            <td>{{ $expense->expense_date }}</td>
                            <td><span class="badge bg-light text-dark">{{ $expense->category->name ?? '-' }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($expense->description ?? '-', 40) }}</td>
                            <td class="fw-semibold text-danger">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($expense->status === 'approved')
                                    <span class="badge bg-success badge-status">Disetujui</span>
                                @elseif($expense->status === 'rejected')
                                    <span class="badge bg-danger badge-status">Ditolak</span>
                                @else
                                    <span class="badge bg-warning badge-status">Menunggu</span>
                                @endif
                            </td>
                            <td>
                                @if($expense->status === 'pending')
                                    <form action="{{ route('finance.expenses.approve', $expense) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" title="Setujui"><i class="fas fa-check"></i></button>
                                    </form>
                                @endif
                                <form action="{{ route('finance.expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus pengeluaran ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-money-bill-wave fa-2x mb-2 d-block"></i>Belum ada data pengeluaran
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('finance.expenses.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Pengeluaran</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="expense_category_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" name="amount" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="expense_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Bon/Receipt</label>
                        <input type="text" class="form-control" name="receipt_number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection