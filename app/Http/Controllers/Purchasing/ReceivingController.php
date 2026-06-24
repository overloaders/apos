<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceiving;
use App\Models\PurchaseReceivingItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ActivityLogger;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReceiving::with(['purchaseOrder.supplier', 'warehouse', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%");
        }

        $receivings = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('purchasing.receivings.index', compact('receivings'));
    }

    public function create()
    {
        $orders = PurchaseOrder::with('supplier')
            ->whereIn('status', ['ordered', 'partial'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchasing.receivings.create', compact('orders'));
    }

    public function createFromPo(PurchaseOrder $po)
    {
        $po->load(['supplier', 'warehouse', 'items.product.unit']);

        return view('purchasing.receivings.create_from_po', compact('po'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::findOrFail($request->purchase_order_id);
            $receiving = PurchaseReceiving::create([
                'code' => $this->generateCode(),
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'user_id' => auth()->id(),
                'notes' => $request->notes,
                'receiving_date' => now()->toDateString(),
                'status' => 'accepted',
            ]);

            foreach ($request->items as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);
                $price = $poItem->unit_price;
                PurchaseReceivingItem::create([
                    'purchase_receiving_id' => $receiving->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'subtotal' => $item['quantity'] * $price,
                ]);

                PurchaseOrderItem::where('id', $item['purchase_order_item_id'])
                    ->increment('received_quantity', $item['quantity']);

                $stock = Stock::firstOrNew([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->warehouse_id,
                ]);

                $oldQty = $stock->quantity;
                $oldCost = $stock->average_cost ?? 0;
                $newQty = $item['quantity'];

                $stock->quantity = $oldQty + $newQty;
                $stock->average_cost = $oldQty > 0
                    ? (($oldQty * $oldCost) + ($newQty * $price)) / ($oldQty + $newQty)
                    : $price;

                $stock->save();
            }

            $po = PurchaseOrder::findOrFail($request->purchase_order_id);
            $po->load('items');
            $allReceived = $po->items->every(fn($i) => $i->received_quantity >= $i->quantity);
            $po->update(['status' => $allReceived ? 'received' : 'partial']);

            DB::commit();

            app(ActivityLogger::class)->log('created', $receiving, 'Membuat receiving ' . $receiving->code);

            return redirect()->route('purchasing.receivings.show', $receiving)
                ->with('success', 'Penerimaan barang berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReceiving $receiving)
    {
        $receiving->load(['purchaseOrder.supplier', 'warehouse', 'items.product.unit']);

        return view('purchasing.receivings.show', compact('receiving'));
    }

    private function generateCode(): string
    {
        $date = Carbon::today()->format('Ymd');
        $lastToday = PurchaseReceiving::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'RCV-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
