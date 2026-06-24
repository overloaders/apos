@extends('layouts.app')
@section('title', 'Activity Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Activity Logs</h4>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form method="GET" class="form-inline">
            <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Cari aksi/deskripsi..." style="width:220px;" value="{{ request('search') }}">
            <select name="action" class="form-control form-control-sm mr-2" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Aksi</option>
                @foreach($actions as $act)
                    <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                @endforeach
            </select>
            <select name="user_id" class="form-control form-control-sm mr-2" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="form-control form-control-sm mr-2" style="width:150px;" value="{{ request('date_from') }}" placeholder="Dari">
            <input type="date" name="date_to" class="form-control form-control-sm mr-2" style="width:150px;" value="{{ request('date_to') }}" placeholder="Sampai">
            <button type="submit" class="btn btn-sm btn-primary mr-1"><i class="fas fa-search"></i></button>
            <a href="{{ route('settings.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-undo"></i></a>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>Model</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td><span class="badge bg-info">{{ $log->action }}</span></td>
                            <td class="small">{{ Str::limit($log->description, 80) }}</td>
                            <td class="small text-muted">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</td>
                            <td>
                                <a href="{{ route('settings.activity-logs.show', $log) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-2 d-block"></i>Belum ada activity log
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
