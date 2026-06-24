<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->withCount('products')->orderBy('name', 'asc')->paginate(15);

        return view('master.categories.index', compact('categories'));
    }

    public function create()
    {
        return redirect()->route('master.categories.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description');
        $data['slug'] = Str::slug($request->name);

        if ($request->filled('id')) {
            $category = Category::findOrFail($request->id);
            $oldValues = $category->toArray();
            $category->update($data);
            $newValues = $category->fresh()->toArray();
            app(ActivityLogger::class)->log('updated', $category, 'Mengupdate kategori ' . $category->name, $oldValues, $newValues);
        } else {
            $category = Category::create($data);
            app(ActivityLogger::class)->log('created', $category, 'Membuat kategori ' . $category->name);
        }

        return redirect()->route('master.categories.index')
            ->with('success', 'Kategori berhasil disimpan.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh produk.');
        }

        app(ActivityLogger::class)->log('deleted', $category, 'Menghapus kategori ' . $category->name);
        $category->delete();

        return redirect()->route('master.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
