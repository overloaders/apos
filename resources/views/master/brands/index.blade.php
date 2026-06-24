@extends('layouts.app')
@section('title', 'Merek')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Merek</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#brandModal" onclick="resetForm()">
        <i class="fas fa-plus me-1"></i>Tambah Merek
    </button>
</div>

<div class="card">
    <div class="card-header">
        <input type="text" class="form-control form-control-sm" placeholder="Cari merek..." style="width:250px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Merek</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands ?? [] as $brand)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $brand->name }}</td>
                            <td class="text-muted small">{{ $brand->description ?? '-' }}</td>
                            <td><span class="badge bg-primary">{{ $brand->products_count ?? 0 }}</span></td>
                            <td>
                                @if($brand->is_active)
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editBrand({{ $brand }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('master.brands.destroy', $brand) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus merek ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-award fa-2x mb-2 d-block"></i>Belum ada data merek
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="brandForm" action="{{ route('master.brands.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="brandId" value="">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="brandModalLabel">Tambah Merek</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Merek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="brandName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea class="form-control" name="description" id="brandDesc" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="is_active" id="brandActive">
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
        document.getElementById('brandId').value = '';
        document.getElementById('brandModalLabel').textContent = 'Tambah Merek';
        document.getElementById('brandName').value = '';
        document.getElementById('brandDesc').value = '';
        document.getElementById('brandActive').value = '1';
    }
    function editBrand(brand) {
        document.getElementById('brandId').value = brand.id;
        document.getElementById('brandModalLabel').textContent = 'Edit Merek';
        document.getElementById('brandName').value = brand.name;
        document.getElementById('brandDesc').value = brand.description || '';
        document.getElementById('brandActive').value = brand.is_active ? '1' : '0';
        new bootstrap.Modal(document.getElementById('brandModal')).show();
    }
</script>
@endsection
