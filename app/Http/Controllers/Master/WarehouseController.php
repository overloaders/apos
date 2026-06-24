<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->orderBy('name', 'asc')->paginate(15);

        return view('master.warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $data = $request->only('name', 'address', 'phone');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('id')) {
            $warehouse = Warehouse::findOrFail($request->id);
            $oldValues = $warehouse->toArray();
            $warehouse->update($data);
            $newValues = $warehouse->fresh()->toArray();
            app(ActivityLogger::class)->log('updated', $warehouse, 'Mengupdate gudang ' . $warehouse->name, $oldValues, $newValues);
        } else {
            $data['code'] = $this->generateCode();
            $warehouse = Warehouse::create($data);
            app(ActivityLogger::class)->log('created', $warehouse, 'Membuat gudang ' . $warehouse->name);
        }

        return redirect()->route('master.warehouses.index')
            ->with('success', 'Gudang berhasil disimpan.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if (\App\Models\StockMovement::where('warehouse_id', $warehouse->id)->orWhere('warehouse_destination_id', $warehouse->id)->count() > 0) {
            return back()->with('error', 'Gudang tidak bisa dihapus karena masih digunakan oleh pergerakan stok.');
        }

        app(ActivityLogger::class)->log('deleted', $warehouse, 'Menghapus gudang ' . $warehouse->name);
        $warehouse->delete();

        return redirect()->route('master.warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $last = Warehouse::orderBy('id', 'desc')->first();
        $number = 1;

        if ($last && $last->code) {
            $lastNumber = (int) str_replace('WH-', '', $last->code);
            $number = $lastNumber + 1;
        }

        return 'WH-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
