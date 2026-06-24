<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\SalesTarget;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesTargetController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesTarget::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $salesTargets = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::active()->orderBy('name')->get();

        return view('reports.sales-targets.index', compact('salesTargets', 'users'));
    }

    public function create()
    {
        $users = User::active()->orderBy('name')->get();
        return view('reports.sales-targets.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'target_amount' => 'required|numeric|min:0',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $salesTarget = SalesTarget::create($request->only('user_id', 'target_amount', 'period', 'start_date', 'end_date', 'notes'));

        app(ActivityLogger::class)->log('created', $salesTarget, 'Membuat target penjualan');

        return redirect()->route('reports.sales-targets.index')
            ->with('success', 'Target penjualan berhasil dibuat.');
    }

    public function edit(SalesTarget $salesTarget)
    {
        $users = User::active()->orderBy('name')->get();
        return view('reports.sales-targets.edit', compact('salesTarget', 'users'));
    }

    public function update(Request $request, SalesTarget $salesTarget)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'target_amount' => 'required|numeric|min:0',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $old = $salesTarget->toArray();
        $salesTarget->update($request->only('user_id', 'target_amount', 'period', 'start_date', 'end_date', 'notes'));
        app(ActivityLogger::class)->log('updated', $salesTarget, 'Mengupdate target penjualan', $old, $salesTarget->toArray());

        return redirect()->route('reports.sales-targets.index')
            ->with('success', 'Target penjualan berhasil diperbarui.');
    }

    public function destroy(SalesTarget $salesTarget)
    {
        app(ActivityLogger::class)->log('deleted', $salesTarget, 'Menghapus target penjualan');
        $salesTarget->delete();

        return redirect()->route('reports.sales-targets.index')
            ->with('success', 'Target penjualan berhasil dihapus.');
    }

    public function report()
    {
        $targets = SalesTarget::with('user')
            ->where('start_date', '<=', now())
            ->orderBy('end_date', 'desc')
            ->get();

        return view('reports.sales-targets.index', compact('targets'));
    }
}
