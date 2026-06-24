<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'approvedBy');

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(15);
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('finance.expenses.index', compact('expenses', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:255',
        ]);

        $data = $request->only('expense_category_id', 'amount', 'expense_date', 'description', 'receipt_number');
        $data['code'] = $this->generateCode();
        $data['status'] = 'pending';
        $data['created_by'] = auth()->id();

        $expense = Expense::create($data);

        app(ActivityLogger::class)->log('created', $expense, "Membuat pengeluaran {$expense->description}");

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function approve(Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Hanya pengeluaran dengan status pending yang dapat disetujui.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(ActivityLogger::class)->log('approved', $expense, "Menyetujui pengeluaran {$expense->description}");

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Pengeluaran berhasil disetujui.');
    }

    public function destroy(Expense $expense)
    {
        app(ActivityLogger::class)->log('deleted', $expense, "Menghapus pengeluaran {$expense->description}");
        $expense->delete();
        return redirect()->route('finance.expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $date = Carbon::today()->format('Ymd');
        $lastToday = Expense::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'EXP-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
