<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name', 'asc')->paginate(15);

        return view('master.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $supplier = null;
        if ($id = request('edit')) {
            $supplier = Supplier::findOrFail($id);
        }

        return view('master.suppliers.create', compact('supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $data = $request->only('name', 'company', 'phone', 'email', 'address', 'city', 'contact_person', 'fax', 'postal_code', 'notes');

        if ($request->filled('id')) {
            $supplier = Supplier::findOrFail($request->id);
            $oldValues = $supplier->toArray();
            $supplier->update($data);
            $newValues = $supplier->fresh()->toArray();
            app(ActivityLogger::class)->log('updated', $supplier, 'Mengupdate supplier ' . $supplier->name, $oldValues, $newValues);
        } else {
            $data['code'] = $this->generateCode();
            $supplier = Supplier::create($data);
            app(ActivityLogger::class)->log('created', $supplier, 'Membuat supplier ' . $supplier->name);
        }

        return redirect()->route('master.suppliers.index')
            ->with('success', 'Supplier berhasil disimpan.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->count() > 0) {
            return back()->with('error', 'Supplier tidak bisa dihapus karena masih digunakan oleh pesanan pembelian.');
        }

        app(ActivityLogger::class)->log('deleted', $supplier, 'Menghapus supplier ' . $supplier->name);
        $supplier->delete();

        return redirect()->route('master.suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $last = Supplier::orderBy('id', 'desc')->first();
        $number = 1;

        if ($last && $last->code) {
            $lastNumber = (int) str_replace('SUP-', '', $last->code);
            $number = $lastNumber + 1;
        }

        return 'SUP-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
