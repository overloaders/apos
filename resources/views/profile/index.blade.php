@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Profil</h4>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="mx-auto mb-3 position-relative d-inline-block">
                    @if(Auth::user()->image_url)
                        <img src="{{ Auth::user()->image_url }}" alt="Foto Profil"
                            class="rounded-circle img-thumbnail"
                            style="width:120px;height:120px;object-fit:cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-secondary mx-auto"
                            style="width:120px;height:120px;">
                            <i class="fas fa-user-circle fa-5x text-white"></i>
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                <span class="badge bg-{{ Auth::user()->role->slug === 'admin' ? 'danger' : (Auth::user()->role->slug === 'manager' ? 'primary' : (Auth::user()->role->slug === 'kasir' ? 'success' : 'secondary')) }} mb-3">
                    {{ Auth::user()->role->name ?? 'N/A' }}
                </span>
                @if(Auth::user()->is_active)
                    <span class="badge bg-success">Aktif</span>
                @else
                    <span class="badge bg-secondary">Nonaktif</span>
                @endif
                <hr>
                <div class="mt-2">
                    <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data" id="photoForm">
                        @csrf
                        <div class="d-flex gap-2 mb-2">
                            <input type="file" class="form-control form-control-sm" name="image" id="profileImage" accept="image/jpeg,image/png,image/gif,image/webp" required style="flex:1;">
                            <button type="submit" class="btn btn-sm btn-primary flex-shrink-0" id="uploadPhotoBtn">
                                <i class="fas fa-upload"></i>
                            </button>
                        </div>
                    </form>
                    @if(Auth::user()->image)
                        <form action="{{ route('profile.photo.delete') }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" data-confirm="Hapus foto profil?">
                                <i class="fas fa-trash mr-1"></i>Hapus Foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Informasi Akun</h6>
                <button class="btn btn-sm btn-outline-primary" onclick="toggleEditProfile()" id="editProfileBtn">
                    <i class="fas fa-edit mr-1"></i>Edit
                </button>
            </div>
            <div class="card-body">
                <div id="profileView">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px;">Nama</td>
                            <td class="fw-semibold">{{ Auth::user()->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Username</td>
                            <td class="fw-semibold">{{ Auth::user()->username }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td class="fw-semibold">{{ Auth::user()->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telepon</td>
                            <td class="fw-semibold">{{ Auth::user()->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat</td>
                            <td class="fw-semibold">{{ Auth::user()->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Role</td>
                            <td>{{ Auth::user()->role->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terdaftar</td>
                            <td class="fw-semibold">{{ Auth::user()->created_at ? Auth::user()->created_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div id="profileEdit" style="display:none;">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Telepon</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', Auth::user()->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="2">{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Simpan
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleEditProfile()">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-bold">Ganti Password</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required minlength="6">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_confirmation" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-1"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleEditProfile() {
        document.getElementById('profileView').style.display =
            document.getElementById('profileView').style.display === 'none' ? '' : 'none';
        document.getElementById('profileEdit').style.display =
            document.getElementById('profileEdit').style.display === 'none' ? '' : 'none';
        var btn = document.getElementById('editProfileBtn');
        if (document.getElementById('profileEdit').style.display !== 'none') {
            btn.innerHTML = '<i class="fas fa-times mr-1"></i>Batal';
            btn.className = 'btn btn-sm btn-outline-secondary';
        } else {
            btn.innerHTML = '<i class="fas fa-edit mr-1"></i>Edit';
            btn.className = 'btn btn-sm btn-outline-primary';
        }
    }

    document.getElementById('profileImage')?.addEventListener('change', function(e) {
        document.getElementById('uploadPhotoBtn').disabled = !e.target.files.length;
    });
</script>
@endsection
