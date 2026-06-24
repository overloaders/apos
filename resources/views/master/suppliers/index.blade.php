@extends('layouts.app')
@section('title', 'Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Supplier</h4>
    <a href="{{ route('master.suppliers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Tambah Supplier
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <input type="text" class="form-control form-control-sm" placeholder="Cari supplier..." style="width:250px;">

    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Supplier</th>
                        <th>Perusahaan</th>
                        <th>Kontak</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers ?? [] as $supplier)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $supplier->name }}</td>
                            <td>{{ $supplier->company ?? '-' }}</td>
                            <td>{{ $supplier->contact_person ?? '-' }}</td>
                            <td>{{ $supplier->phone ?? '-' }}</td>
                            <td class="small">{{ $supplier->email ?? '-' }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($supplier->address ?? '-', 40) }}</td>
                            <td>
                                <a href="{{ route('master.suppliers.create') }}?edit={{ $supplier->id }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master.suppliers.destroy', $supplier) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus supplier ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-truck fa-2x mb-2 d-block"></i>Belum ada data supplier
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $suppliers ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) }}
    </div>
</div>
@endsection
