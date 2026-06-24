@extends('layouts.app')
@section('title', 'Detail Member - ' . $member->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 font-weight-bold">Detail Member</h4>
    <a href="{{ route('merchandise.members.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="fas fa-id-card fa-4x text-primary"></i>
                </div>
                <h5 class="font-weight-bold">{{ $member->name }}</h5>
                <p><code>{{ $member->code }}</code></p>
                <span class="badge badge-info" style="font-size:0.9rem;">{{ $member->getLevelLabel() }}</span>
                @if($member->is_active)
                    <span class="badge badge-success" style="font-size:0.9rem;">Aktif</span>
                @else
                    <span class="badge badge-secondary" style="font-size:0.9rem;">Nonaktif</span>
                @endif
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="font-weight-bold" style="font-size:1.2rem;">{{ (int) $member->points }}</div>
                        <small class="text-muted">Poin</small>
                    </div>
                    <div class="col-4">
                        <div class="font-weight-bold" style="font-size:1.2rem;">{{ $totalSales }}</div>
                        <small class="text-muted">Transaksi</small>
                    </div>
                    <div class="col-4">
                        <div class="font-weight-bold text-primary" style="font-size:1.2rem;">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
                        <small class="text-muted">Total Belanja</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header font-weight-bold">Informasi</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Telepon</td><td class="font-weight-bold">{{ $member->phone ?? '-' }}</td></tr>
                    <tr><td>Email</td><td>{{ $member->email ?? '-' }}</td></tr>
                    <tr><td>Tanggal Lahir</td><td>{{ $member->birth_date ? $member->birth_date->format('d/m/Y') : '-' }}</td></tr>
                    <tr><td>Jenis Kelamin</td><td>{{ $member->gender == 'male' ? 'Laki-laki' : ($member->gender == 'female' ? 'Perempuan' : '-') }}</td></tr>
                    <tr><td>Level</td><td><span class="badge badge-info">{{ $member->getLevelLabel() }}</span></td></tr>
                    <tr><td>Diskon per Level</td><td><span class="text-success font-weight-bold">{{ $member->getDiscountPercent() }}%</span></td></tr>
                    <tr><td>Nilai Poin</td><td><span class="text-info font-weight-bold">1 poin = Rp {{ number_format($member->getPointsValue(), 0, ',', '.') }}</span></td></tr>
                    <tr><td>Total Poin</td><td><span class="font-weight-bold">{{ (int) $member->points }} poin (Rp {{ number_format($member->getPointsRupiahValue(), 0, ',', '.') }})</span></td></tr>
                    <tr><td>Alamat</td><td>{{ $member->address ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Riwayat Transaksi</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>No. Nota</th>
                                <th>Subtotal</th>
                                <th>Diskon</th>
                                <th>Total</th>
                                <th>Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($member->sales as $sale)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $sale->sale_date ? $sale->sale_date->format('d/m/Y') : $sale->created_at->format('d/m/Y') }}</td>
                                    <td><code>{{ $sale->code }}</code></td>
                                    <td>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                                    <td class="text-danger">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                                    <td class="font-weight-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($sale->points_earned > 0)
                                            <span class="badge badge-success">+{{ $sale->points_earned }}</span>
                                        @endif
                                        @if($sale->points_redeemed > 0)
                                            <span class="badge badge-warning">-{{ $sale->points_redeemed }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection