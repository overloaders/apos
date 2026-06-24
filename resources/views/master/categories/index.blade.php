@extends('layouts.app')
@section('title', 'Kategori Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 font-weight-bold">Kategori Produk</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#categoryModal" onclick="resetForm()">
        <i class="fas fa-plus mr-1"></i>Tambah Kategori
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <input type="text" class="form-control form-control-sm" placeholder="Cari kategori..." style="width:250px;" id="searchInput">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories ?? [] as $category)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">{{ $category->name }}</td>
                            <td class="text-muted">{{ $category->description ?? '-' }}</td>
                            <td><span class="badge badge-primary">{{ $category->products_count ?? 0 }}</span></td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning" title="Edit"
                                    onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('master.categories.destroy', $category) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus kategori ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-tags fa-3x mb-2 d-block opacity-25"></i>Belum ada data kategori
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('master.categories.store') }}" method="POST" id="categoryForm">
                @csrf
                <input type="hidden" name="id" id="categoryId" value="">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modalTitle">Tambah Kategori</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="description" id="categoryDesc" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('btnSubmit').textContent = 'Simpan';
}

function editCategory(id, name, desc) {
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDesc').value = desc;
    document.getElementById('modalTitle').textContent = 'Edit Kategori';
    document.getElementById('btnSubmit').textContent = 'Perbarui';
    $('#categoryModal').modal('show');
}
</script>
@endsection