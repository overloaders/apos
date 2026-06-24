@extends('layouts.app')
@section('title', 'Detail Activity Log')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Detail Activity Log</h4>
    <a href="{{ route('settings.activity-logs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted">Waktu</small>
                <div class="fw-bold">{{ $activityLog->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">User</small>
                <div class="fw-bold">{{ $activityLog->user->name ?? 'System' }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Aksi</small>
                <div><span class="badge bg-info">{{ $activityLog->action }}</span></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">IP Address</small>
                <div><code>{{ $activityLog->ip_address ?? '-' }}</code></div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Model</small>
                <div class="fw-bold">{{ $activityLog->model_type }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Model ID</small>
                <div class="fw-bold">#{{ $activityLog->model_id }}</div>
            </div>
            <div class="col-md-6">
                <small class="text-muted">User Agent</small>
                <div class="small text-muted">{{ $activityLog->user_agent ?? '-' }}</div>
            </div>
            @if($activityLog->description)
            <div class="col-12">
                <small class="text-muted">Deskripsi</small>
                <div>{{ $activityLog->description }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($activityLog->old_values || $activityLog->new_values)
<div class="card">
    <div class="card-header">
        <h6 class="mb-0 fw-bold">Perubahan Data</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Nilai Lama</th>
                        <th>Nilai Baru</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $old = $activityLog->old_values ?? [];
                        $new = $activityLog->new_values ?? [];
                        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                    @endphp
                    @forelse($keys as $key)
                        <tr>
                            <td class="fw-semibold">{{ $key }}</td>
                            <td class="text-muted">{{ is_array($old[$key] ?? '') ? json_encode($old[$key]) : ($old[$key] ?? '-') }}</td>
                            <td>{{ is_array($new[$key] ?? '') ? json_encode($new[$key]) : ($new[$key] ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data perubahan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
