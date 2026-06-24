@extends('layouts.app')
@section('title', 'Satuan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Satuan</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#unitModal" onclick="resetForm()">
        <i class="fas fa-plus me-1"></i>Tambah Satuan
    </button>
</div>

<div class="card">
    <div class="card-header">
        <input type="text" class="form-control form-control-sm" placeholder="Cari satuan..." style="width:250px;">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Satuan</th>
                        <th>Simbol</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units ?? [] as $unit)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $unit->name }}</td>
                            <td><code>{{ $unit->symbol }}</code></td>
                            <td><span class="badge bg-primary">{{ $unit->products_count ?? 0 }}</span></td>
                            <td>
                                @if($unit->is_active)
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editUnit({{ $unit }})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('master.units.destroy', $unit) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus" data-confirm="Hapus satuan ini?"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-balance-scale fa-2x mb-2 d-block"></i>Belum ada data satuan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="unitForm" action="{{ route('master.units.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="unitId" value="">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="unitModalLabel">Tambah Satuan</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="unitName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Simbol</label>
                        <input type="text" class="form-control" name="symbol" id="unitSymbol" placeholder="cth: kg, pcs, liter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select" name="is_active" id="unitActive">
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
        document.getElementById('unitId').value = '';
        document.getElementById('unitModalLabel').textContent = 'Tambah Satuan';
        document.getElementById('unitName').value = '';
        document.getElementById('unitSymbol').value = '';
        document.getElementById('unitActive').value = '1';
    }
    function editUnit(unit) {
        document.getElementById('unitId').value = unit.id;
        document.getElementById('unitModalLabel').textContent = 'Edit Satuan';
        document.getElementById('unitName').value = unit.name;
        document.getElementById('unitSymbol').value = unit.symbol || '';
        document.getElementById('unitActive').value = unit.is_active ? '1' : '0';
        new bootstrap.Modal(document.getElementById('unitModal')).show();
    }
</script>
@endsection
