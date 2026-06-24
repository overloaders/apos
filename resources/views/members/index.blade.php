@extends('layouts.app')
@section('title', 'Member')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Member</h4>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#memberModal">
        <i class="fas fa-plus mr-1"></i>Tambah Member
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Cari nama/no. HP..." style="width:250px;" value="{{ request('search') }}">
            <select name="level" class="form-control form-control-sm" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Level</option>
                <option value="bronze" {{ request('level') == 'bronze' ? 'selected' : '' }}>Bronze</option>
                <option value="silver" {{ request('level') == 'silver' ? 'selected' : '' }}>Silver</option>
                <option value="gold" {{ request('level') == 'gold' ? 'selected' : '' }}>Gold</option>
                <option value="platinum" {{ request('level') == 'platinum' ? 'selected' : '' }}>Platinum</option>
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>No. Member</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>Poin</th>
                        <th>Total Belanja</th>
                        <th>Piutang</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code class="fw-semibold"><a href="{{ route('merchandise.members.show', $member) }}" class="text-decoration-none">{{ $member->code }}</a></code></td>
                            <td class="fw-semibold">{{ $member->name }}</td>
                            <td>{{ $member->phone ?? '-' }}</td>
                            <td class="small">{{ $member->email ?? '-' }}</td>
                            <td><span class="badge bg-info">{{ ucfirst($member->membership_level ?? 'bronze') }}</span></td>
                            <td><span class="badge bg-primary">{{ $member->points ?? 0 }} poin</span></td>
                            <td>Rp {{ number_format($member->total_spent ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @if($member->outstanding_balance > 0)
                                    <span class="text-danger fw-semibold">Rp {{ number_format($member->outstanding_balance, 0, ',', '.') }}</span>
                                    <br><small class="text-muted">Limit: Rp {{ number_format($member->credit_limit, 0, ',', '.') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($member->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#memberModal"
                                    data-id="{{ $member->id }}"
                                    data-name="{{ $member->name }}"
                                    data-phone="{{ $member->phone }}"
                                    data-email="{{ $member->email }}"
                                    data-address="{{ $member->address }}"
                                    data-birth_date="{{ $member->birth_date ? $member->birth_date->format('Y-m-d') : '' }}"
                                    data-gender="{{ $member->gender }}"
                                    data-membership_level="{{ $member->membership_level }}"
                                    data-is_active="{{ $member->is_active }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('merchandise.members.destroy', $member) }}" class="d-inline" onsubmit="return confirm('Hapus member {{ $member->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                <i class="fas fa-id-card fa-2x mb-2 d-block"></i>Belum ada data member
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($members->hasPages())
    <div class="card-footer">{{ $members->links() }}</div>
    @endif
</div>

<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('merchandise.members.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="member_id">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Member</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="field_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" id="field_phone" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="field_email" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="birth_date" id="field_birth_date" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" id="field_gender" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Level Member</label>
                        <select name="membership_level" id="field_membership_level" class="form-control">
                            <option value="bronze">Bronze</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" id="field_address" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="field_is_active" class="form-check-input" value="1" checked>
                    <label class="form-check-label" for="field_is_active">Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#memberModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var isEdit = button.data('id') !== undefined;

        $('#modalTitle').text(isEdit ? 'Edit Member' : 'Tambah Member');
        $('#member_id').val(isEdit ? button.data('id') : '');
        $('#field_name').val(isEdit ? button.data('name') : '');
        $('#field_phone').val(isEdit ? button.data('phone') : '');
        $('#field_email').val(isEdit ? button.data('email') : '');
        $('#field_address').val(isEdit ? button.data('address') : '');
        $('#field_birth_date').val(isEdit ? button.data('birth_date') : '');
        $('#field_gender').val(isEdit ? button.data('gender') : '');
        $('#field_membership_level').val(isEdit ? button.data('membership_level') : 'bronze');
        $('#field_is_active').prop('checked', isEdit ? button.data('is_active') == 1 : true);
    });

    $('#memberModal').on('hidden.bs.modal', function() {
        $('#member_id').val('');
        $('#field_name').val('');
        $('#field_phone').val('');
        $('#field_email').val('');
        $('#field_address').val('');
        $('#field_birth_date').val('');
        $('#field_gender').val('');
        $('#field_membership_level').val('bronze');
        $('#field_is_active').prop('checked', true);
    });
});
</script>
@endsection