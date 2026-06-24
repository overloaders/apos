<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\ActivityLogger;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'unit']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->orderBy('name', 'asc')->paginate(15);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('master.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('master.products.create', compact('categories', 'units', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'member_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'name', 'barcode', 'category_id', 'unit_id',
            'brand_id', 'cost_price', 'selling_price', 'member_price',
            'min_stock', 'description',
        ]);
        $data['slug'] = Str::slug($request->name);
        $data['code'] = 'PRD' . str_pad(Product::max('id') + 1, 6, '0', STR_PAD_LEFT);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        PriceHistory::create([
            'product_id' => $product->id,
            'old_cost_price' => 0,
            'new_cost_price' => $request->cost_price,
            'old_selling_price' => 0,
            'new_selling_price' => $request->selling_price,
            'old_member_price' => 0,
            'new_member_price' => $request->member_price ?? 0,
            'user_id' => auth()->id(),
            'notes' => 'Harga awal saat produk dibuat',
        ]);

        app(ActivityLogger::class)->log('created', $product, 'Membuat produk ' . $product->name);

        return redirect()->route('master.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'unit', 'stocks', 'prices']);
        return view('master.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['category', 'brand', 'unit']);
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('master.products.create', compact('product', 'categories', 'units', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'brand_id' => 'nullable|exists:brands,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'member_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'name', 'barcode', 'category_id', 'unit_id',
            'brand_id', 'cost_price', 'selling_price', 'member_price',
            'min_stock', 'description',
        ]);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $oldCost = $product->cost_price;
        $oldSelling = $product->selling_price;
        $oldMember = $product->member_price;

        $oldValues = $product->toArray();
        $product->update($data);

        if ((float) $oldCost !== (float) $request->cost_price ||
            (float) $oldSelling !== (float) $request->selling_price ||
            (float) ($oldMember ?? 0) !== (float) ($request->member_price ?? 0)) {
            PriceHistory::create([
                'product_id' => $product->id,
                'old_cost_price' => $oldCost,
                'new_cost_price' => $request->cost_price,
                'old_selling_price' => $oldSelling,
                'new_selling_price' => $request->selling_price,
                'old_member_price' => $oldMember ?? 0,
                'new_member_price' => $request->member_price ?? 0,
                'user_id' => auth()->id(),
                'notes' => 'Update harga produk',
            ]);
        }

        $newValues = $product->fresh()->toArray();
        app(ActivityLogger::class)->log('updated', $product, 'Mengupdate produk ' . $product->name, $oldValues, $newValues);

        return redirect()->route('master.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function priceHistory(Product $product)
    {
        $histories = PriceHistory::where('product_id', $product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('master.products.price-history', compact('product', 'histories'));
    }

    public function destroy(Product $product)
    {
        app(ActivityLogger::class)->log('deleted', $product, 'Menghapus produk ' . $product->name);
        $product->delete();

        return redirect()->route('master.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function printBarcode(Product $product)
    {
        $product->load('category', 'unit');
        return view('master.products.barcode-label', compact('product'));
    }

    public function printBarcodeMultiple(Request $request)
    {
        $ids = $request->input('product_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu produk.');
        }

        $products = Product::whereIn('id', $ids)->with('category', 'unit')->get();

        return view('master.products.barcode-label', compact('products'));
    }

    public function getByBarcode($barcode)
    {
        $product = Product::with(['category', 'unit', 'stocks' => function ($q) {
                $q->select('product_id', 'warehouse_id', 'quantity', 'average_cost');
            }])
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $totalStock = $product->stocks->sum('quantity');

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'selling_price' => $product->selling_price,
            'member_price' => $product->member_price,
            'cost_price' => $product->cost_price,
            'stock' => $totalStock,
            'category' => $product->category ? $product->category->name : null,
            'unit' => $product->unit ? $product->unit->name : null,
        ]);
    }
}
