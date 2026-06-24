<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ActivityLogger;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['purchaseOrder', 'supplier', 'items']);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('purchasing.returns.index', compact('returns'));
    }

    public function create()
    {
        $orders = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereIn('status', ['partial', 'received'])
            ->whereHas('items', fn($q) => $q->whereRaw('received_quantity > returned_quantity'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchasing.returns.create', compact('orders'));
    }

    public function createFromPo(PurchaseOrder $po)
    {
        $po->load(['supplier', 'warehouse', 'items.product.unit']);

        return view('purchasing.returns.create_from_po', compact('po'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.reason' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::findOrFail($request->purchase_order_id);
            $return = PurchaseReturn::create([
                'code' => $this->generateCode(),
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'return_date' => $request->return_date,
                'status' => 'completed',
            ]);

            foreach ($request->items as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);
                $quantity = (float) $item['quantity'];
                $availReturn = $poItem->received_quantity - $poItem->returned_quantity;
                if ($quantity > $availReturn) {
                    throw new \Exception("Qty retur melebihi qty diterima untuk produk {$poItem->product->name}");
                }

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $poItem->unit_price,
                    'reason' => $item['reason'] ?? null,
                ]);

                $poItem->increment('returned_quantity', $quantity);

                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();

                if ($stock) {
                    $stock->decrement('quantity', $quantity);
                }
            }

            DB::commit();

            app(ActivityLogger::class)->log('created', $return, 'Membuat retur pembelian ' . $return->code);

            return redirect()->route('purchasing.returns.show', $return)
                ->with('success', 'Retur berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReturn $return)
    {
        $return->load(['purchaseOrder', 'supplier', 'warehouse', 'user', 'items.product']);

        return view('purchasing.returns.show', compact('return'));
    }

    private function generateCode(): string
    {
        $date = Carbon::today()->format('Ymd');
        $lastToday = PurchaseReturn::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'RET-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
