<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\CashRegister;
use App\Models\Sale;
use App\Services\ActivityLogger;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Shift::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if (!auth()->user()->hasPermission('settings.manage')) {
            $query->where('user_id', auth()->id());
        }

        $shifts = $query->orderBy('created_at', 'desc')->paginate(15);

        $registers = CashRegister::orderBy('name')->get();

        $isAdmin = auth()->user()->hasPermission('settings.manage');

        $activeShifts = Shift::where('status', 'open')
            ->when(!$isAdmin, fn($q) => $q->where('user_id', auth()->id()))
            ->count();

        $openShifts = Shift::where('status', 'open')
            ->when(!$isAdmin, fn($q) => $q->where('user_id', auth()->id()))
            ->get();
        $shiftSales = Sale::whereIn('shift_id', $openShifts->pluck('id'))->sum('total');
        $cashInDrawer = $openShifts->sum('opening_cash') + $shiftSales;
        $shiftTransactions = Sale::whereIn('shift_id', $openShifts->pluck('id'))->count();

        $closedShifts = Shift::where('status', 'closed')
            ->when(!$isAdmin, fn($q) => $q->where('user_id', auth()->id()))
            ->get();
        $closedSales = Sale::whereIn('shift_id', $closedShifts->pluck('id'))->sum('total');
        $closedCash = $closedShifts->sum('closing_cash');
        $closedTransactions = Sale::whereIn('shift_id', $closedShifts->pluck('id'))->count();

        return view('pos.shifts', compact('shifts', 'registers', 'activeShifts', 'shiftSales', 'cashInDrawer', 'shiftTransactions', 'closedShifts', 'closedSales', 'closedCash', 'closedTransactions'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'cash_register_id' => 'required|exists:cash_registers,id',
            'notes' => 'nullable|string',
        ]);

        $activeShift = Shift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($activeShift) {
            return back()->with('error', 'Anda sudah memiliki shift yang sedang berjalan.');
        }

        $shift = Shift::create([
            'code' => $this->generateShiftCode(),
            'user_id' => auth()->id(),
            'cash_register_id' => $request->cash_register_id,
            'opening_cash' => $request->opening_cash,
            'status' => 'open',
            'opened_at' => now(),
            'notes' => $request->notes,
        ]);

        app(ActivityLogger::class)->log('shift_open', $shift, 'Membuka shift');

        return redirect()->route('pos.cashier.index')
            ->with('success', 'Shift berhasil dibuka.');
    }

    public function close(Shift $shift, Request $request)
    {
        if ($shift->status !== 'open') {
            return back()->with('error', 'Hanya shift dengan status open yang dapat ditutup.');
        }

        if ($shift->user_id !== auth()->id() && !auth()->user()->hasPermission('settings.manage')) {
            return back()->with('error', 'Anda hanya dapat menutup shift milik sendiri.');
        }

        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift->update([
            'closing_cash' => $request->closing_cash,
            'status' => 'closed',
            'closed_at' => now(),
            'notes' => $request->notes,
        ]);

        app(ActivityLogger::class)->log('shift_close', $shift, 'Menutup shift');

        return redirect()->route('pos.shifts.index')
            ->with('success', 'Shift berhasil ditutup.');
    }

    private function generateShiftCode(): string
    {
        $date = now()->format('Ymd');
        $lastToday = Shift::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'SHIFT-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
