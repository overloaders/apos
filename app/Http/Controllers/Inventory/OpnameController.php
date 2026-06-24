<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['warehouse', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $opnames = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('inventory.opname.index', compact('opnames'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'barcode', 'code']);

        return view('inventory.opname.create', compact('warehouses', 'products'));
    }

    public function show(StockOpname $opname)
    {
        $opname->load(['warehouse', 'user', 'approver', 'items.product']);

        return view('inventory.opname.show', compact('opname'));
    }

    public function getStock(Request $request)
    {
        $stock = Stock::where('product_id', $request->product_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->first();

        $product = Product::find($request->product_id);

        return response()->json([
            'stock' => $stock ? (float) $stock->quantity : 0,
            'unit_cost' => $stock ? (float) $stock->average_cost : (float) ($product->cost_price ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_count' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $lastOpname = StockOpname::where('code', 'like', 'OP-' . date('Ymd') . '%')
                ->orderBy('code', 'desc')
                ->first();

            $sequence = $lastOpname ? (int) substr($lastOpname->code, -4) + 1 : 1;
            $code = 'OP-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $opname = StockOpname::create([
                'code' => $code,
                'warehouse_id' => $request->warehouse_id,
                'opname_date' => now(),
                'status' => 'draft',
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();

                $systemCount = $stock ? $stock->quantity : 0;
                $unitCost = $stock ? $stock->average_cost : 0;
                $physicalCount = $item['physical_count'];
                $difference = $physicalCount - $systemCount;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $item['product_id'],
                    'system_stock' => $systemCount,
                    'actual_stock' => $physicalCount,
                    'difference' => $difference,
                    'unit_cost' => $unitCost,
                    'system_value' => $systemCount * $unitCost,
                    'actual_value' => $physicalCount * $unitCost,
                    'difference_value' => $difference * $unitCost,
                ]);
            }

            app(ActivityLogger::class)->log('stock_opname_created', $opname, "Membuat opname stok {$opname->code}");

            DB::commit();

            return redirect()->route('inventory.opname.index')
                ->with('success', 'Stok opname berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat stok opname: ' . $e->getMessage());
        }
    }

    public function approve(StockOpname $opname)
    {
        if ($opname->status !== 'draft') {
            return back()->with('error', 'Hanya opname dengan status draft yang dapat disetujui.');
        }

        DB::beginTransaction();

        try {
            $category = ExpenseCategory::firstOrCreate(
                ['name' => 'Selisih Stok'],
                ['description' => 'Penyesuaian stok hasil opname', 'is_active' => true]
            );

            $lastExpense = Expense::where('code', 'like', 'EXP-' . date('Ymd') . '%')
                ->orderBy('code', 'desc')->first();
            $expSeq = $lastExpense ? (int) substr($lastExpense->code, -4) + 1 : 1;

            foreach ($opname->items as $item) {
                $stockRow = Stock::firstOrNew([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $opname->warehouse_id,
                ], ['quantity' => 0, 'average_cost' => 0]);

                $qtyBefore = (float) $stockRow->quantity;
                $qtyAfter = (float) $item->actual_stock;
                $diff = $qtyAfter - $qtyBefore;

                $stockRow->quantity = $qtyAfter;
                $stockRow->save();

                if ($diff != 0) {
                    $refNum = 'ADJ-OP-' . $opname->code;
                    StockMovement::create([
                        'reference_number' => $refNum,
                        'type' => 'adjustment',
                        'product_id' => $item->product_id,
                        'warehouse_id' => $opname->warehouse_id,
                        'quantity' => abs($diff),
                        'unit_cost' => $item->unit_cost,
                        'notes' => 'Stok opname: ' . $opname->code . ' (sistem: ' . $qtyBefore . ', fisik: ' . $qtyAfter . ')',
                        'user_id' => auth()->id(),
                    ]);

                    $adjValue = $diff * (float) $item->unit_cost;
                    if ($adjValue != 0) {
                        $expCode = 'EXP-' . date('Ymd') . '-' . str_pad($expSeq++, 4, '0', STR_PAD_LEFT);
                        Expense::create([
                            'code' => $expCode,
                            'expense_category_id' => $category->id,
                            'amount' => $adjValue,
                            'expense_date' => now()->toDateString(),
                            'description' => 'Selisih stok opname ' . $opname->code . ' - ' . ($item->product->name ?? ''),
                            'status' => 'approved',
                            'user_id' => auth()->id(),
                            'approved_by' => auth()->id(),
                        ]);
                    }
                }
            }

            $opname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            app(ActivityLogger::class)->log('stock_opname_approved', $opname, "Menyetujui opname stok {$opname->code}");

            DB::commit();

            return redirect()->route('inventory.opname.index')
                ->with('success', 'Stok opname berhasil disetujui. Stok disesuaikan dan selisih telah dicatat ke laporan laba rugi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui stok opname: ' . $e->getMessage());
        }
    }
}