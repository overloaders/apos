<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Category;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Stock::with(['product.unit', 'warehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $stocks = $query->orderBy('product_id', 'asc')->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('inventory.stocks.index', compact('stocks', 'warehouses', 'categories'));
    }

    public function card(Product $product, Request $request)
    {
        $product->load(['unit', 'category']);

        $query = StockMovement::where('product_id', $product->id)
            ->with(['warehouse', 'user']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderBy('created_at', 'asc')->get();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $balance = 0;
        $movements->each(function ($m) use (&$balance) {
            if (in_array($m->type, ['in'])) {
                $balance += $m->quantity;
            } else {
                $balance -= $m->quantity;
            }
            $m->balance = $balance;
        });

        return view('inventory.stocks.card', compact('product', 'movements', 'warehouses'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $stock = Stock::firstOrNew([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
            ]);

            if ($request->type === 'increase') {
                $stock->quantity = ($stock->quantity ?? 0) + $request->quantity;
            } else {
                $currentQty = $stock->quantity ?? 0;
                if ($currentQty < $request->quantity) {
                    return back()->withInput()->with('error', 'Stok tidak mencukupi untuk pengurangan.');
                }
                $stock->quantity = $currentQty - $request->quantity;
            }

            $stock->save();

            StockMovement::create([
                'reference_number' => 'ADJ-' . date('Ymd') . '-' . strtoupper(uniqid()),
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'type' => 'adjustment',
                'quantity' => $request->quantity,
                'unit_cost' => 0,
                'notes' => $request->notes . ' (' . ($request->type === 'increase' ? 'Penambahan' : 'Pengurangan') . ')',
                'user_id' => auth()->id(),
            ]);

            $productName = Product::find($request->product_id)->name;
            app(ActivityLogger::class)->log('stock_adjustment', $stock, "Melakukan penyesuaian stok produk {$productName}");

            DB::commit();

            return redirect()->route('inventory.stocks.index')
                ->with('success', 'Penyesuaian stok berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses penyesuaian stok: ' . $e->getMessage());
        }
    }
}
