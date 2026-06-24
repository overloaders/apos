<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ActivityLogger;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('purchasing.orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('purchasing.orders.create', compact('suppliers', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'expected_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::create([
                'code' => $this->generateCode(),
                'user_id' => auth()->id(),
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'order_date' => now()->toDateString(),
                'notes' => $request->notes,
                'expected_date' => $request->expected_date,
                'status' => 'draft',
                'total' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $subtotal = (string)($item['quantity'] * $item['price']);
                $totalAmount += $subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $po->update(['total' => (string)$totalAmount]);

            DB::commit();

            app(ActivityLogger::class)->log('created', $po, 'Membuat purchase order ' . $po->code);

            return redirect()->route('purchasing.orders.show', $po)
                ->with('success', 'Purchase Order berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat Purchase Order: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $order)
    {
        $order->load(['supplier', 'warehouse', 'items.product.unit', 'receivings']);

        return view('purchasing.orders.show', compact('order'));
    }

    public function updateStatus(PurchaseOrder $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:ordered,cancelled',
        ]);

        $allowedTransitions = [
            'draft' => ['ordered', 'cancelled'],
            'ordered' => ['cancelled'],
        ];

        if (!isset($allowedTransitions[$order->status]) || !in_array($request->status, $allowedTransitions[$order->status])) {
            return back()->with('error', 'Transisi status tidak valid.');
        }

        $oldValues = ['status' => $order->status];
        $order->update(['status' => $request->status]);
        $newValues = ['status' => $order->status];
        app(ActivityLogger::class)->log('updated', $order, 'Mengupdate status PO ' . $order->code . ' menjadi ' . $order->status, $oldValues, $newValues);

        return redirect()->route('purchasing.orders.show', $order)
            ->with('success', 'Status Purchase Order berhasil diperbarui.');
    }

    public function payment(PurchaseOrder $order)
    {
        $order->load('supplier');
        return view('purchasing.orders.payment', compact('order'));
    }

    public function recordPayment(Request $request, PurchaseOrder $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $order->remaining_amount,
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $newPaid = $order->paid_amount + (float) $request->amount;
        $status = $newPaid >= $order->total ? 'paid' : 'partial';

        $order->update([
            'paid_amount' => $newPaid,
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? now() : $order->paid_at,
        ]);

        app(ActivityLogger::class)->log('payment_recorded', $order, 'Mencatat pembayaran PO ' . $order->code);

        return redirect()->route('purchasing.orders.show', $order)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(PurchaseOrder $order)
    {
        if ($order->status !== 'draft') {
            return back()->with('error', 'Hanya PO dengan status draft yang dapat dihapus.');
        }

        app(ActivityLogger::class)->log('deleted', $order, 'Menghapus purchase order ' . $order->code);

        $order->items()->delete();
        $order->delete();

        return redirect()->route('purchasing.orders.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $date = Carbon::today()->format('Ymd');
        $lastToday = PurchaseOrder::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'PO-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
