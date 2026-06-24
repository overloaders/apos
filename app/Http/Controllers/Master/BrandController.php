<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $brands = $query->withCount('products')->orderBy('name', 'asc')->paginate(15);

        return view('master.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description');
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('id')) {
            $brand = Brand::findOrFail($request->id);
            $oldValues = $brand->toArray();
            $brand->update($data);
            $newValues = $brand->fresh()->toArray();
            app(ActivityLogger::class)->log('updated', $brand, 'Mengupdate merek ' . $brand->name, $oldValues, $newValues);
        } else {
            $brand = Brand::create($data);
            app(ActivityLogger::class)->log('created', $brand, 'Membuat merek ' . $brand->name);
        }

        return redirect()->route('master.brands.index')
            ->with('success', 'Merek berhasil disimpan.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', 'Merek tidak bisa dihapus karena masih digunakan oleh produk.');
        }

        app(ActivityLogger::class)->log('deleted', $brand, 'Menghapus merek ' . $brand->name);
        $brand->delete();

        return redirect()->route('master.brands.index')
            ->with('success', 'Merek berhasil dihapus.');
    }
}
