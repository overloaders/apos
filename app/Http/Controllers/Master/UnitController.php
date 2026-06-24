<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $units = $query->withCount('products')->orderBy('name', 'asc')->paginate(15);

        return view('master.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:20',
        ]);

        $data = $request->only('name', 'symbol');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('id')) {
            $unit = Unit::findOrFail($request->id);
            $oldValues = $unit->toArray();
            $unit->update($data);
            $newValues = $unit->fresh()->toArray();
            app(ActivityLogger::class)->log('updated', $unit, 'Mengupdate satuan ' . $unit->name, $oldValues, $newValues);
        } else {
            $unit = Unit::create($data);
            app(ActivityLogger::class)->log('created', $unit, 'Membuat satuan ' . $unit->name);
        }

        return redirect()->route('master.units.index')
            ->with('success', 'Satuan berhasil disimpan.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Satuan tidak bisa dihapus karena masih digunakan oleh produk.');
        }

        app(ActivityLogger::class)->log('deleted', $unit, 'Menghapus satuan ' . $unit->name);
        $unit->delete();

        return redirect()->route('master.units.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}
