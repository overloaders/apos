<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleReturn::with(['sale', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('sale', function ($q2) use ($search) {
                        $q2->where('code', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('pos.sales-returns.index', compact('returns'));
    }

    public function createFromSale(Sale $sale)
    {
        $sale->load(['items.product', 'member']);

        if ($sale->status === 'cancelled') {
            return redirect()->route('pos.history.index')
                ->with('error', 'Penjualan yang dibatalkan tidak dapat diretur.');
        }

        return view('pos.sales-returns.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $sale->load('items.product');

            $totalRefund = 0;

            foreach ($request->items as $item) {
                $saleItem = $sale->items()->findOrFail($item['sale_item_id']);
                $quantity = (float) $item['quantity'];

                $returnedQty = (float) SaleReturnItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $availReturn = $saleItem->quantity - $returnedQty;

                if ($quantity > $availReturn) {
                    throw new \Exception("Qty retur melebihi qty penjualan untuk produk {$saleItem->product->name}");
                }
            }

            $return = SaleReturn::create([
                'return_number' => 'SR-' . date('Ymd') . '-' . strtoupper(uniqid()),
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'total_refund' => 0,
                'reason' => $request->reason,
            ]);

            foreach ($request->items as $item) {
                $saleItem = $sale->items()->findOrFail($item['sale_item_id']);
                $quantity = (float) $item['quantity'];
                $price = (float) $saleItem->unit_price;
                $subtotal = $quantity * $price;

                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $totalRefund += $subtotal;

                Stock::where('product_id', $saleItem->product_id)
                    ->increment('quantity', $quantity);

                StockMovement::create([
                    'reference_number' => $return->return_number,
                    'product_id' => $saleItem->product_id,
                    'warehouse_id' => $sale->cash_register_id 
                        ? optional(\App\Models\CashRegister::find($sale->cash_register_id))->warehouse_id 
                        : 1,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'unit_cost' => $saleItem->unit_price,
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $return->id,
                    'notes' => 'Retur penjualan: ' . $sale->code,
                    'user_id' => auth()->id(),
                ]);
            }

            $return->update(['total_refund' => $totalRefund]);

            $allReturnedQty = SaleReturnItem::whereIn('sale_item_id', $sale->items->pluck('id'))
                ->sum('quantity');
            $totalSaleQty = $sale->items->sum('quantity');

            if ($allReturnedQty >= $totalSaleQty) {
                $sale->update(['status' => 'refunded']);
            }

            DB::commit();

            if (class_exists(ActivityLogger::class)) {
                app(ActivityLogger::class)->log(
                    'sale_return',
                    $return,
                    "Membuat retur penjualan {$return->return_number}"
                );
            }

            return redirect()->route('pos.sales-returns.show', $return)
                ->with('success', 'Retur penjualan berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.member', 'sale.user', 'user', 'items.product']);

        return view('pos.sales-returns.show', compact('saleReturn'));
    }
}
