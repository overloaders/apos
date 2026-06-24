<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product.unit', 'warehouse', 'warehouseDestination']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $mutations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('inventory.mutations.index', compact('mutations'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.mutations.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'warehouse_destination_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $sourceStock = Stock::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();

            if (!$sourceStock || $sourceStock->quantity < $request->quantity) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Stok di gudang asal tidak mencukupi.');
            }

            $sourceStock->quantity -= $request->quantity;
            $sourceStock->save();

            $destStock = Stock::firstOrCreate([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_destination_id,
            ], ['quantity' => 0]);

            $destStock->quantity += $request->quantity;
            $destStock->save();

            $referenceNumber = 'MUT-' . now()->format('YmdHis');

            StockMovement::create([
                'reference_number' => $referenceNumber,
                'type' => 'transfer',
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'warehouse_destination_id' => $request->warehouse_destination_id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            app(ActivityLogger::class)->log('stock_mutation', $sourceStock, 'Melakukan mutasi stok');

            DB::commit();

            return redirect()->route('inventory.mutations.index')
                ->with('success', 'Mutasi stok berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses mutasi stok: ' . $e->getMessage());
        }
    }
}
