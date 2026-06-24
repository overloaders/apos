<?php

namespace App\Http\Controllers\Merchandise;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $query = Promotion::with('products');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            } elseif ($request->status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                        ->orWhere('end_date', '<', now());
                });
            }
        }

        $promotions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('promotions.create', compact('products'));
    }

    public function edit(Promotion $promotion)
    {
        $promotion->load('products');
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('promotions.create', compact('promotion', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount_percent,discount_amount,buy_x_get_y,bundle,member_discount',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'is_active' => 'boolean',
        ]);

        $data = $request->only('name', 'type', 'value', 'start_date', 'end_date');
        $data['notes'] = $request->description;
        $data['code'] = 'PRM' . str_pad(Promotion::max('id') + 1, 6, '0', STR_PAD_LEFT);
        $data['is_active'] = $request->boolean('is_active', true);

        $promotion = Promotion::create($data);

        if ($request->filled('product_ids')) {
            foreach ($request->product_ids as $productId) {
                $promotion->products()->create(['product_id' => $productId]);
            }
        }

        app(ActivityLogger::class)->log('created', $promotion, "Membuat promosi {$promotion->name}");

        return redirect()->route('merchandise.promotions.index')
            ->with('success', 'Promo berhasil disimpan.');
    }

    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount_percent,discount_amount,buy_x_get_y,bundle,member_discount',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'is_active' => 'boolean',
        ]);

        $data = $request->only('name', 'type', 'value', 'start_date', 'end_date');
        $data['notes'] = $request->description;
        $data['is_active'] = $request->boolean('is_active', true);

        $old = $promotion->toArray();
        $promotion->update($data);
        $promotion->products()->delete();

        if ($request->filled('product_ids')) {
            foreach ($request->product_ids as $productId) {
                $promotion->products()->create(['product_id' => $productId]);
            }
        }

        app(ActivityLogger::class)->log('updated', $promotion, "Mengupdate promosi {$promotion->name}", $old, $promotion->toArray());

        return redirect()->route('merchandise.promotions.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion)
    {
        app(ActivityLogger::class)->log('deleted', $promotion, "Menghapus promosi {$promotion->name}");
        $promotion->products()->delete();
        $promotion->delete();

        return redirect()->route('merchandise.promotions.index')
            ->with('success', 'Promo berhasil dihapus.');
    }
}
