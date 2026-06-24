<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['requester', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('request_number', 'like', "%{$request->search}%");
        }

        $purchaseRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('purchasing.requests.index', compact('purchaseRequests'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchasing.requests.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $purchaseRequest = PurchaseRequest::create([
                'request_number' => 'PR-' . date('Ymd') . '-' . strtoupper(uniqid()),
                'requested_by' => auth()->id(),
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            app(ActivityLogger::class)->log('created', $purchaseRequest, 'Membuat request pembelian ' . $purchaseRequest->request_number);

            return redirect()->route('purchasing.requests.show', $purchaseRequest)
                ->with('success', 'Request pembelian berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat request: ' . $e->getMessage());
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['requester', 'approver', 'items.product']);

        return view('purchasing.requests.show', compact('purchaseRequest'));
    }

    public function approve(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        app(ActivityLogger::class)->log('approved', $purchaseRequest, 'Menyetujui request pembelian ' . $purchaseRequest->request_number);

        return redirect()->route('purchasing.requests.show', $purchaseRequest)
            ->with('success', 'Request pembelian berhasil disetujui.');
    }

    public function reject(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $purchaseRequest->update(['status' => 'rejected']);

        app(ActivityLogger::class)->log('rejected', $purchaseRequest, 'Menolak request pembelian ' . $purchaseRequest->request_number);

        return redirect()->route('purchasing.requests.show', $purchaseRequest)
            ->with('success', 'Request pembelian berhasil ditolak.');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be deleted.');
        }

        app(ActivityLogger::class)->log('deleted', $purchaseRequest, 'Menghapus request pembelian ' . $purchaseRequest->request_number);

        $purchaseRequest->items()->delete();
        $purchaseRequest->delete();

        return redirect()->route('purchasing.requests.index')
            ->with('success', 'Request pembelian berhasil dihapus.');
    }
}
