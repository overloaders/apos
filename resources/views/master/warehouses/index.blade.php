@extends('layouts.app')
@section('title', 'Gudang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Gudang</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#warehouseModal" onclick="resetForm()">
        <i class="fas fa-plus me-1"></i>Tambah Gudang
    </button>
</div>

<div class="card">
    <div class="card-header">
        <input type="text" class="form-control form-control-sm" placeholder="Cari gudang..." style="width:250px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Gudang</th>
                        <th>Alamat</th>
                        <th>Penanggung Jawab</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses ?? [] as $warehouse)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $warehouse->name }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($warehouse->address ?? '-', 40) }}</td>
                            <td>{{ $warehouse->person_in_charge ?? '-' }}</td>
                            <td>{{ $warehouse->phone ?? '-' }}</td>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editWarehouse({{ $warehouse }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('master.warehouses.destroy', $warehouse) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus gudang ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-warehouse fa-2x mb-2 d-block"></i>Belum ada data gudang
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="warehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="warehouseForm" action="{{ route('master.warehouses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="warehouseId" value="">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="warehouseModalLabel">Tambah Gudang</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Gudang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="warehouseName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea class="form-control" name="address" id="warehouseAddress" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Penanggung Jawab</label>
                            <input type="text" class="form-control" name="person_in_charge" id="warehousePIC">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telepon</label>
                            <input type="text" class="form-control" name="phone" id="warehousePhone">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="is_active" id="warehouseActive">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
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

@section('scripts')
<script>
    function resetForm() {
        document.getElementById('warehouseId').value = '';
        document.getElementById('warehouseModalLabel').textContent = 'Tambah Gudang';
        document.getElementById('warehouseName').value = '';
        document.getElementById('warehouseAddress').value = '';
        document.getElementById('warehousePIC').value = '';
        document.getElementById('warehousePhone').value = '';
        document.getElementById('warehouseActive').value = '1';
    }
    function editWarehouse(w) {
        document.getElementById('warehouseId').value = w.id;
        document.getElementById('warehouseModalLabel').textContent = 'Edit Gudang';
        document.getElementById('warehouseName').value = w.name;
        document.getElementById('warehouseAddress').value = w.address || '';
        document.getElementById('warehousePIC').value = w.person_in_charge || '';
        document.getElementById('warehousePhone').value = w.phone || '';
        document.getElementById('warehouseActive').value = w.is_active ? '1' : '0';
        new bootstrap.Modal(document.getElementById('warehouseModal')).show();
    }
</script>
@endsection
