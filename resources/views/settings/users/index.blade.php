@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Manajemen User</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#userModal" onclick="resetForm()">
        <i class="fas fa-plus me-1"></i>Tambah User
    </button>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form action="{{ route('settings.users.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/email..." style="width:200px;" value="{{ request('search') }}">
            <select name="role" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Terakhir Login</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->image_url)
                                        <img src="{{ $user->image_url }}" alt=""
                                            class="rounded-circle me-2"
                                            style="width:36px;height:36px;object-fit:cover;">
                                    @else
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                    @endif
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleSlug = $user->role->slug ?? '';
                                    $roleColors = [
                                        'admin' => 'danger',
                                        'manager' => 'primary',
                                        'kasir' => 'success',
                                        'gudang' => 'warning',
                                        'purchasing' => 'info',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $roleColors[$roleSlug] ?? 'secondary' }}">
                                    {{ $user->role->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success badge-status">Aktif</span>
                                @else
                                    <span class="badge bg-secondary badge-status">Nonaktif</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='editUser(@json($user))' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Hapus user ini?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users-cog fa-2x mb-2 d-block"></i>Belum ada data user
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="userForm" action="{{ route('settings.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="userMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="userModalLabel">Tambah User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="userName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="userUsername" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="userEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger user-password-required">*</span></label>
                            <input type="password" class="form-control" name="password" id="userPassword">
                            <small class="text-muted">Min. 6 karakter. Kosongkan jika tidak ingin mengubah</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" id="userPasswordConfirm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id" id="userRole" required>
                                <option value="">Pilih Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-permissions='@json($role->permissions)'>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Hak Akses Role Ini:</label>
                            <div id="permissionDisplay" class="p-2 bg-light rounded" style="min-height:42px;font-size:0.85rem;">
                                <span class="text-muted">Pilih role untuk melihat hak akses</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Telepon</label>
                            <input type="text" class="form-control" name="phone" id="userPhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control" name="address" id="userAddress" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Foto Profil</label>
                            <div class="d-flex align-items-center gap-2">
                                <img id="userImagePreview" src="" alt="Preview"
                                    class="rounded-circle d-none me-2"
                                    style="width:50px;height:50px;object-fit:cover;">
                                <input type="file" class="form-control-file" name="image" id="userImage" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="is_active" id="userActive">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
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
    const permissionLabels = @json($permissionLabels);

    function renderPermissions(perms, containerId) {
        const container = document.getElementById(containerId || 'permissionDisplay');
        if (!container) return;
        if (!perms || perms.length === 0) {
            container.innerHTML = '<span class="text-muted">Tidak ada hak akses khusus</span>';
            return;
        }
        if (perms.includes('*')) {
            container.innerHTML = '<span class="badge badge-pill badge-success">Semua Hak Akses (Administrator)</span>';
            return;
        }
        let html = '';
        perms.forEach(function(p) {
            const label = permissionLabels[p] || p;
            html += '<span class="badge badge-pill badge-info mr-1 mb-1">' + label + '</span>';
        });
        container.innerHTML = html;
    }

    document.getElementById('userRole')?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const perms = selected ? JSON.parse(selected.dataset.permissions || '[]') : [];
        renderPermissions(perms, 'permissionDisplay');
    });

    document.getElementById('userImage')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('userImagePreview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    function resetForm() {
        document.getElementById('userForm').action = '{{ route("settings.users.store") }}';
        document.getElementById('userMethod').value = 'POST';
        document.getElementById('userModalLabel').textContent = 'Tambah User';
        document.getElementById('userName').value = '';
        document.getElementById('userUsername').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPassword').value = '';
        document.getElementById('userPasswordConfirm').value = '';
        document.getElementById('userRole').value = '';
        document.getElementById('userPhone').value = '';
        document.getElementById('userAddress').value = '';
        document.getElementById('userActive').value = '1';
        document.getElementById('userImage').value = '';
        const preview = document.getElementById('userImagePreview');
        preview.classList.add('d-none');
        preview.src = '';
        document.querySelector('.user-password-required').style.display = '';
        renderPermissions([], 'permissionDisplay');
    }
    function editUser(user) {
        document.getElementById('userForm').action = '{{ url("settings/users") }}/' + user.id;
        document.getElementById('userMethod').value = 'PUT';
        document.getElementById('userModalLabel').textContent = 'Edit User';
        document.getElementById('userName').value = user.name;
        document.getElementById('userUsername').value = user.username;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userPassword').value = '';
        document.getElementById('userPasswordConfirm').value = '';
        document.getElementById('userRole').value = user.role_id;
        document.getElementById('userPhone').value = user.phone || '';
        document.getElementById('userAddress').value = user.address || '';
        document.getElementById('userActive').value = user.is_active ? '1' : '0';
        document.getElementById('userImage').value = '';
        const preview = document.getElementById('userImagePreview');
        if (user.image_url) {
            preview.src = user.image_url;
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
            preview.src = '';
        }
        document.querySelector('.user-password-required').style.display = 'none';
        const select = document.getElementById('userRole');
        const selected = select.options[select.selectedIndex];
        const perms = selected ? JSON.parse(selected.dataset.permissions || '[]') : [];
        renderPermissions(perms, 'permissionDisplay');
        $('#userModal').modal('show');
    }
</script>
@endsection
