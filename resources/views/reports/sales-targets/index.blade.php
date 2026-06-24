@extends('layouts.app')
@section('title', 'Target Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold">Target Penjualan</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.sales-targets.report') }}" class="btn btn-info">
            <i class="fas fa-chart-bar me-1"></i>Laporan Pencapaian
        </a>
        <a href="{{ route('reports.sales-targets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Buat Target
        </a>
    </div>
</div>

@if(request()->routeIs('reports.sales-targets.report'))
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0 fw-bold">Laporan Pencapaian Target</h6>
        </div>
        <div class="card-body">
            @forelse($targets as $target)
                @php
                    $progress = $target->progress;
                    $barClass = $progress >= 100 ? 'bg-success' : ($progress >= 75 ? 'bg-info' : ($progress >= 50 ? 'bg-warning' : 'bg-danger'));
                @endphp
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <strong>{{ $target->user->name ?? 'Semua Kasir' }}</strong>
                            <span class="badge bg-secondary ms-2">{{ ucfirst($target->period) }}</span>
                        </div>
                        <small class="text-muted">
                            {{ $target->start_date->format('d/m/Y') }} - {{ $target->end_date->format('d/m/Y') }}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Target: Rp {{ number_format($target->target_amount, 0, ',', '.') }}</span>
                        <span>Tercapai: Rp {{ number_format($target->achieved_amount, 0, ',', '.') }}</span>
                        <span class="fw-bold">{{ $progress }}%</span>
                    </div>
                    <div class="progress" style="height: 24px;">
                        <div class="progress-bar {{ $barClass }} progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ min($progress, 100) }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progress }}%
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="fas fa-bullseye fa-2x mb-2 d-block"></i>Belum ada target penjualan
                </div>
            @endforelse
        </div>
    </div>
@else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form action="{{ route('reports.sales-targets.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <select name="user_id" class="form-select form-select-sm" style="width:180px;" onchange="this.form.submit()">
                    <option value="">Semua User</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
                <select name="period" class="form-select form-select-sm" style="width:140px;" onchange="this.form.submit()">
                    <option value="">Semua Periode</option>
                    <option value="daily" {{ request('period') == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ request('period') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
                <input type="date" name="date_from" class="form-control form-control-sm" style="width:150px;" value="{{ request('date_from') }}" placeholder="Dari">
                <input type="date" name="date_to" class="form-control form-control-sm" style="width:150px;" value="{{ request('date_to') }}" placeholder="Sampai">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i></button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>User</th>
                            <th>Target</th>
                            <th>Tercapai</th>
                            <th>Progress</th>
                            <th>Periode</th>
                            <th>Tanggal</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesTargets ?? [] as $target)
                            @php $pct = $target->progress; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $target->user->name ?? 'Semua Kasir' }}</td>
                                <td>Rp {{ number_format($target->target_amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($target->achieved_amount, 0, ',', '.') }}</td>
                                <td style="min-width:150px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:8px;">
                                            <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ min($pct, 100) }}%;"></div>
                                        </div>
                                        <small class="fw-bold {{ $pct >= 100 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">{{ $pct }}%</small>
                                    </div>
                                </td>
                                <td><span class="badge bg-info badge-status">{{ ucfirst($target->period) }}</span></td>
                                <td class="small">{{ $target->start_date->format('d/m/Y') }} - {{ $target->end_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('reports.sales-targets.edit', $target) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('reports.sales-targets.destroy', $target) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Hapus target ini?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-bullseye fa-2x mb-2 d-block"></i>Belum ada target penjualan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($salesTargets) && $salesTargets->hasPages())
            <div class="card-footer">
                {{ $salesTargets->links() }}
            </div>
        @endif
    </div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endsection
