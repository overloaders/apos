<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseCategory::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->orderBy('name', 'asc')->paginate(15);

        return view('finance.expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description');

        if ($request->filled('id')) {
            $category = ExpenseCategory::findOrFail($request->id);
            $old = $category->toArray();
            $category->update($data);
            app(ActivityLogger::class)->log('updated', $category, "Mengupdate kategori pengeluaran {$category->name}", $old, $category->toArray());
        } else {
            $category = ExpenseCategory::create($data);
            app(ActivityLogger::class)->log('created', $category, "Membuat kategori pengeluaran {$category->name}");
        }

        return redirect()->route('finance.expense-categories.index')
            ->with('success', 'Kategori pengeluaran berhasil disimpan.');
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh pengeluaran.');
        }

        app(ActivityLogger::class)->log('deleted', $category, "Menghapus kategori pengeluaran {$category->name}");
        $category->delete();

        return redirect()->route('finance.expense-categories.index')
            ->with('success', 'Kategori pengeluaran berhasil dihapus.');
    }
}
