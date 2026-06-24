<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceivingItem;
use App\Models\Stock;
use App\Models\Expense;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReceiving;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = Sale::with(['member', 'user', 'items.product'])
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('cashier_id')) {
            $query->where('user_id', $request->cashier_id);
        }

        $salesData = $query->orderBy('created_at', 'desc')->paginate(20);

        $summary = Sale::where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as total_discount')
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_subtotal')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as total_tax')
            ->first();

        $totalSales = $summary->total_sales ?? 0;
        $totalTransactions = $summary->transaction_count ?? 0;
        $totalDiscount = $summary->total_discount ?? 0;
        $totalSubtotal = $summary->total_subtotal ?? 0;
        $totalTax = $summary->total_tax ?? 0;
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Chart data
        $dailySales = Sale::where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COALESCE(SUM(total), 0) as total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $chartLabels = $dailySales->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray();
        $chartData = $dailySales->pluck('total')->toArray();

        $categories = \App\Models\Category::orderBy('name')->get();

        if ($request->export === 'excel') {
            $allSales = Sale::with(['member', 'user', 'items.product'])
                ->where('status', 'completed')
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->orderBy('created_at')
                ->get();
            $headers = ['Kode', 'Tanggal', 'Member', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Bayar', 'Kembali', 'Status'];
            $rows = [];
            foreach ($allSales as $s) {
                $rows[] = [
                    $s->code,
                    $s->created_at->format('d/m/Y H:i'),
                    $s->member ? $s->member->name : '-',
                    $s->subtotal,
                    $s->discount_amount,
                    $s->tax_amount,
                    $s->total,
                    $s->amount_paid,
                    $s->change_amount,
                    $s->status,
                ];
            }
            return $this->exportToExcel($rows, $headers, "laporan_penjualan_{$dateFrom}_{$dateTo}");
        }

        if ($request->export === 'csv') {
            return $this->exportSalesCsv($dateFrom, $dateTo);
        }

        return view('reports.sales', compact(
            'salesData', 'totalSales', 'totalTransactions', 'totalDiscount', 'avgTransaction',
            'chartLabels', 'chartData', 'totalSubtotal', 'totalTax',
            'dateFrom', 'dateTo', 'categories'
        ));
    }

    public function salesPrint(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $sales = Sale::with(['member', 'user', 'items.product'])
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at')
            ->get();

        $items = collect();
        $saleSummary = [];
        foreach ($sales as $sale) {
            $saleSummary[] = [
                'code' => $sale->code,
                'subtotal' => $sale->subtotal,
                'discount_amount' => $sale->discount_amount,
                'tax_amount' => $sale->tax_amount,
                'total' => $sale->total,
            ];
            foreach ($sale->items as $item) {
                $items->push([
                    'date' => $sale->created_at->format('d/m/Y H:i'),
                    'nota' => $sale->code,
                    'kasir' => $sale->user->name ?? '-',
                    'barcode' => $item->product->barcode ?? '-',
                    'produk' => $item->product->name ?? '-',
                    'keterangan' => $item->product->description ?? '-',
                    'harga' => $item->unit_price,
                    'qty' => $item->quantity,
                    'diskon' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                ]);
            }
        }

        $totalSales = $sales->sum('total');
        $totalTransactions = $sales->count();
        $totalDiscount = $items->sum('diskon');
        $totalQty = $items->sum('qty');

        $settings = CompanySetting::instance();

        return view('reports.sales_print', compact('items', 'saleSummary', 'totalSales', 'totalTransactions', 'totalDiscount', 'totalQty', 'dateFrom', 'dateTo', 'settings'));
    }

    public function salesDetail(Sale $sale)
    {
        $sale->load(['member', 'user', 'items.product']);

        return view('reports.sales_detail', compact('sale'));
    }

    private function exportSalesCsv(string $dateFrom, string $dateTo)
    {
        $sales = Sale::with(['member', 'items'])
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at')
            ->get();

        $filename = "laporan_penjualan_{$dateFrom}_{$dateTo}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Kode', 'Tanggal', 'Member', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Bayar', 'Kembali', 'Status']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->code,
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->member ? $sale->member->name : '-',
                    $sale->subtotal,
                    $sale->discount_amount,
                    $sale->tax_amount,
                    $sale->total,
                    $sale->amount_paid,
                    $sale->change_amount,
                    $sale->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function purchases(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = PurchaseOrder::with(['supplier', 'items.product'])
            ->withCount('items')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->orderBy('created_at', 'desc')->paginate(20);

        $summary = PurchaseOrder::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($request->filled('supplier_id'), function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            })
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('COALESCE(SUM(total), 0) as total_amount')
            ->first();

        $totalItemsReceived = PurchaseReceivingItem::whereHas('purchaseReceiving.purchaseOrder', function ($q) use ($dateFrom, $dateTo, $request) {
            $q->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
            if ($request->filled('supplier_id')) {
                $q->where('supplier_id', $request->supplier_id);
            }
        })->sum('quantity');

        $totalQty = $purchases->sum(fn($p) => $p->items->sum('quantity'));
        $avgOrder = $summary->order_count > 0 ? $summary->total_amount / $summary->order_count : 0;

        if ($request->export === 'excel') {
            $allPurchases = PurchaseOrder::with(['supplier', 'items.product'])
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->when($request->filled('supplier_id'), fn($q) => $q->where('supplier_id', $request->supplier_id))
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->orderBy('created_at')
                ->get();
            $headers = ['Tanggal', 'No. Pesanan', 'Supplier', 'Barcode', 'Produk', 'Qty', 'Diterima', 'Diretur', 'Harga', 'Diskon', 'Subtotal'];
            $rows = [];
            foreach ($allPurchases as $p) {
                foreach ($p->items as $item) {
                    $rows[] = [
                        $p->order_date->format('d/m/Y'),
                        $p->code,
                        $p->supplier->name ?? '-',
                        $item->product->barcode ?? '-',
                        $item->product->name ?? '-',
                        $item->quantity,
                        $item->received_quantity,
                        $item->returned_quantity ?? 0,
                        $item->unit_price,
                        $item->discount_percent . '%',
                        $item->subtotal,
                    ];
                }
            }
            return $this->exportToExcel($rows, $headers, "laporan_pembelian_{$dateFrom}_{$dateTo}");
        }

        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('reports.purchases', compact('purchases', 'summary', 'dateFrom', 'dateTo', 'suppliers', 'totalItemsReceived', 'avgOrder', 'totalQty'));
    }

    public function purchasesPrint(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = PurchaseOrder::with(['supplier', 'items.product'])
            ->withCount('items')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->orderBy('created_at', 'desc')->get();

        $summary = PurchaseOrder::whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($request->filled('supplier_id'), function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            })
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('COALESCE(SUM(total), 0) as total_amount')
            ->first();

        $totalItemsReceived = PurchaseReceivingItem::whereHas('purchaseReceiving.purchaseOrder', function ($q) use ($dateFrom, $dateTo, $request) {
            $q->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
            if ($request->filled('supplier_id')) {
                $q->where('supplier_id', $request->supplier_id);
            }
        })->sum('quantity');

        $totalQty = $purchases->sum(fn($p) => $p->items->sum('quantity'));
        $avgOrder = $summary->order_count > 0 ? $summary->total_amount / $summary->order_count : 0;
        $settings = CompanySetting::instance();

        return view('reports.purchases_print', compact('purchases', 'summary', 'dateFrom', 'dateTo', 'totalItemsReceived', 'avgOrder', 'settings', 'totalQty'));
    }

    public function stocks(Request $request)
    {
        $query = Stock::with(['product.unit', 'product.category', 'warehouse']);

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

        $stocks = $query->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->paginate(20);

        $totalValue = Stock::selectRaw('SUM(stocks.quantity * stocks.average_cost) as total_value')
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->when($request->filled('warehouse_id'), function ($q) use ($request) {
                $q->where('stocks.warehouse_id', $request->warehouse_id);
            })
            ->first()
            ->total_value ?? 0;

        $warehouses = \App\Models\Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        $allStocks = Stock::when($request->filled('warehouse_id'), function ($q) use ($request) {
            $q->where('warehouse_id', $request->warehouse_id);
        })->get();

        $lowStockCount = $allStocks->filter(function ($s) {
            return $s->quantity > 0 && $s->quantity <= ($s->product->min_stock ?? 0);
        })->count();

        $emptyStockCount = $allStocks->filter(function ($s) {
            return $s->quantity == 0;
        })->count();

        if ($request->export === 'excel') {
            $allStocks = Stock::with(['product.unit', 'product.category', 'warehouse'])
                ->when($request->filled('warehouse_id'), fn($q) => $q->where('warehouse_id', $request->warehouse_id))
                ->when($request->filled('category_id'), fn($q) => $q->whereHas('product', fn($sq) => $sq->where('category_id', $request->category_id)))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $search = $request->search;
                    $q->whereHas('product', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('barcode', 'like', "%{$search}%"));
                })
                ->orderBy('warehouse_id')->orderBy('product_id')->get();
            $headers = ['Barcode', 'Nama Produk', 'Kategori', 'Gudang', 'Stok', 'Minimum', 'Status', 'Nilai'];
            $rows = [];
            foreach ($allStocks as $s) {
                $status = $s->quantity == 0 ? 'Kosong' : ($s->quantity <= ($s->product->min_stock ?? 0) ? 'Menipis' : 'Normal');
                $rows[] = [
                    $s->product->barcode ?? '-',
                    $s->product->name ?? '-',
                    $s->product->category->name ?? '-',
                    $s->warehouse->name ?? '-',
                    $s->quantity,
                    $s->product->min_stock ?? 0,
                    $status,
                    $s->quantity * ($s->average_cost ?? 0),
                ];
            }
            return $this->exportToExcel($rows, $headers, "laporan_stok_" . Carbon::now()->format('Y-m-d'));
        }

        return view('reports.stocks', compact('stocks', 'totalValue', 'warehouses', 'categories', 'lowStockCount', 'emptyStockCount'));
    }

    public function stocksPrint(Request $request)
    {
        $query = Stock::with(['product.unit', 'product.category', 'warehouse']);

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

        $stocks = $query->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->get();

        $totalValue = $stocks->sum(fn($s) => $s->quantity * $s->average_cost);

        $totalItems = $stocks->count();
        $lowStockCount = $stocks->filter(fn($s) => $s->quantity > 0 && $s->quantity <= ($s->product->min_stock ?? 0))->count();
        $emptyStockCount = $stocks->filter(fn($s) => $s->quantity == 0)->count();

        $settings = CompanySetting::instance();

        return view('reports.stocks_print', compact('stocks', 'totalValue', 'totalItems', 'lowStockCount', 'emptyStockCount', 'settings'));
    }

    public function profit(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $totalRevenue = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->sum('total');

        $productSales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->sum('subtotal');

        $totalDiscount = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->sum('discount_amount');

        $totalCOGS = SaleItem::whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereDate('sale_date', '>=', $dateFrom)
                    ->whereDate('sale_date', '<=', $dateTo);
            })
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        $totalExpenses = Expense::where('status', 'approved')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->sum('amount');

        $grossProfit = $totalRevenue - $totalCOGS;
        $netProfit = $grossProfit - $totalExpenses;
        $margin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        $dailySales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('date')
            ->get();

        $chartLabels = [];
        $revenueData = [];
        $cogsData = [];
        $profitData = [];
        $period = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        while ($period->lte($end)) {
            $key = $period->format('Y-m-d');
            $chartLabels[] = $period->format('d/m');
            $daySales = $dailySales->firstWhere('date', $key);
            $rev = $daySales ? (float) $daySales->revenue : 0;

            $dayCogs = SaleItem::whereHas('sale', function ($q) use ($key) {
                    $q->where('status', 'completed')->whereDate('sale_date', $key);
                })
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->sum(DB::raw('sale_items.quantity * products.cost_price'));

            $revenueData[] = $rev;
            $cogsData[] = $dayCogs;
            $profitData[] = $rev - $dayCogs;
            $period->addDay();
        }

        $cogs = $totalCOGS;

        $stockAdjustment = Expense::where('status', 'approved')
            ->whereHas('expenseCategory', fn($q) => $q->where('name', 'Selisih Stok'))
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->sum('amount');

        $operationalExpenses = $totalExpenses - $stockAdjustment;

        if ($request->export === 'excel') {
            $headers = ['Keterangan', 'Jumlah'];
            $rows = [
                ['Penjualan Produk', $productSales],
                ['Diskon Diberikan', -$totalDiscount],
                ['Total Pendapatan Bersih', $totalRevenue],
                ['Harga Pokok Penjualan (HPP)', -$cogs],
                ['Laba Kotor', $grossProfit],
                ['Pengeluaran Operasional', -$operationalExpenses],
                ['Penyesuaian Stok', $stockAdjustment],
                ['Laba Bersih', $netProfit],
                ['Margin', $margin . '%'],
            ];
            return $this->exportToExcel($rows, $headers, "laporan_laba_rugi_{$dateFrom}_{$dateTo}");
        }

        return view('reports.profit', compact(
            'totalRevenue', 'totalCOGS', 'totalExpenses', 'grossProfit',
            'netProfit', 'margin', 'dateFrom', 'dateTo',
            'productSales', 'totalDiscount', 'cogs', 'operationalExpenses',
            'chartLabels', 'revenueData', 'cogsData', 'profitData',
            'stockAdjustment'
        ));
    }

    public function profitPrint(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $totalRevenue = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->sum('total');

        $totalCOGS = SaleItem::whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereDate('sale_date', '>=', $dateFrom)
                    ->whereDate('sale_date', '<=', $dateTo);
            })
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        $totalExpenses = Expense::where('status', 'approved')
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->sum('amount');

        $productSales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->sum('subtotal');

        $totalDiscount = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo)
            ->sum('discount_amount');

        $grossProfit = $totalRevenue - $totalCOGS;
        $netProfit = $grossProfit - $totalExpenses;
        $margin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        $stockAdjustment = Expense::where('status', 'approved')
            ->whereHas('expenseCategory', fn($q) => $q->where('name', 'Selisih Stok'))
            ->whereDate('expense_date', '>=', $dateFrom)
            ->whereDate('expense_date', '<=', $dateTo)
            ->sum('amount');

        $settings = CompanySetting::instance();

        return view('reports.profit_print', compact(
            'totalRevenue', 'totalCOGS', 'totalExpenses', 'grossProfit',
            'netProfit', 'margin', 'dateFrom', 'dateTo', 'settings',
            'productSales', 'totalDiscount', 'stockAdjustment'
        ));
    }

    public function movingStock(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        // Hitung total terjual per produk dalam periode
        $soldQty = SaleItem::select(
                'product_id',
                DB::raw('COALESCE(SUM(quantity), 0) as total_sold')
            )
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereDate('sale_date', '>=', $dateFrom)
                    ->whereDate('sale_date', '<=', $dateTo);
            })
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        // Query produk
        $query = Product::with(['category', 'unit', 'stocks'])->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(20);

        // Klasifikasi
        $movingQties = $soldQty->filter(fn($q) => $q > 0)->sort()->values();
        $count = $movingQties->count();
        if ($count > 0) {
            $q1 = $this->percentile($movingQties->toArray(), 25);
            $q3 = $this->percentile($movingQties->toArray(), 75);
        } else {
            $q1 = $q3 = 0;
        }

        $periodMonths = max(1, Carbon::parse($dateFrom)->diffInMonths(Carbon::parse($dateTo)) + 1);

        $categories = Category::orderBy('name')->get();

        if ($request->export === 'excel') {
            $allProducts = Product::with(['category', 'unit', 'stocks'])->where('is_active', true)
                ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $s = $request->search;
                    $q->where(fn($sq) => $sq->where('name', 'like', "%{$s}%")->orWhere('barcode', 'like', "%{$s}%"));
                })->orderBy('name')->get();
            $headers = ['Barcode', 'Nama Produk', 'Kategori', 'Stok Saat Ini', 'Terjual', 'Rata-rata/Bulan', 'Klasifikasi'];
            $rows = [];
            foreach ($allProducts as $p) {
                $sold = $soldQty[$p->id] ?? 0;
                $avgMonthly = $periodMonths > 0 ? round($sold / $periodMonths, 1) : 0;
                $class = $sold == 0 ? 'Not Moving' : ($sold < $q1 ? 'Slow Moving' : ($sold < $q3 ? 'Moving' : 'Fast Moving'));
                $rows[] = [
                    $p->barcode ?? '-',
                    $p->name,
                    $p->category->name ?? '-',
                    $p->stocks->sum('quantity'),
                    $sold,
                    $avgMonthly,
                    $class,
                ];
            }
            return $this->exportToExcel($rows, $headers, "laporan_pergerakan_stok_{$dateFrom}_{$dateTo}");
        }

        return view('reports.moving_stock', compact(
            'products', 'soldQty', 'q1', 'q3', 'dateFrom', 'dateTo',
            'categories', 'periodMonths'
        ));
    }

    public function movingStockPrint(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $soldQty = SaleItem::select(
                'product_id',
                DB::raw('COALESCE(SUM(quantity), 0) as total_sold')
            )
            ->whereHas('sale', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                    ->whereDate('sale_date', '>=', $dateFrom)
                    ->whereDate('sale_date', '<=', $dateTo);
            })
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        $query = Product::with(['category', 'unit', 'stocks'])->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        $movingQties = $soldQty->filter(fn($q) => $q > 0)->sort()->values();
        $count = $movingQties->count();
        if ($count > 0) {
            $q1 = $this->percentile($movingQties->toArray(), 25);
            $q3 = $this->percentile($movingQties->toArray(), 75);
        } else {
            $q1 = $q3 = 0;
        }

        $periodMonths = max(1, Carbon::parse($dateFrom)->diffInMonths(Carbon::parse($dateTo)) + 1);
        $settings = CompanySetting::instance();

        return view('reports.moving_stock_print', compact(
            'products', 'soldQty', 'q1', 'q3', 'dateFrom', 'dateTo',
            'periodMonths', 'settings'
        ));
    }

    private function percentile(array $data, float $percentile): float
    {
        if (empty($data)) return 0;
        sort($data);
        $index = ($percentile / 100) * (count($data) - 1);
        $floor = floor($index);
        $ceil = ceil($index);
        if ($floor == $ceil) return $data[$floor];
        return $data[$floor] + ($index - $floor) * ($data[$ceil] - $data[$floor]);
    }

    public function purchaseReturns(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = PurchaseReturn::with(['purchaseOrder', 'supplier', 'items.product', 'user']);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $returns = $query->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->orderBy('return_date', 'desc')
            ->paginate(20);

        $summary = PurchaseReturn::whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->selectRaw('COUNT(*) as return_count')
            ->first();

        $grandQty = 0;
        $grandTotal = 0;
        $returnCount = 0;
        foreach ($returns as $ret) {
            $ret->load('items');
            $returnCount++;
            foreach ($ret->items as $item) {
                $grandQty += $item->quantity;
                $grandTotal += $item->quantity * $item->unit_price;
            }
        }

        if ($request->export === 'excel') {
            $allReturns = PurchaseReturn::with(['purchaseOrder', 'supplier', 'items.product', 'user'])
                ->when($request->filled('supplier_id'), fn($q) => $q->where('supplier_id', $request->supplier_id))
                ->whereDate('return_date', '>=', $dateFrom)->whereDate('return_date', '<=', $dateTo)
                ->orderBy('return_date', 'desc')->get();
            $headers = ['Tgl Retur', 'No. Retur', 'No. PO', 'Supplier', 'Barcode', 'Produk', 'Qty', 'Harga', 'Subtotal', 'Alasan', 'Petugas'];
            $rows = [];
            foreach ($allReturns as $ret) {
                foreach ($ret->items as $item) {
                    $rows[] = [
                        $ret->return_date->format('d/m/Y'),
                        $ret->code,
                        $ret->purchaseOrder->code ?? '-',
                        $ret->supplier->name ?? '-',
                        $item->product->barcode ?? '-',
                        $item->product->name ?? '-',
                        $item->quantity,
                        $item->unit_price,
                        $item->quantity * $item->unit_price,
                        $item->reason ?? '-',
                        $ret->user->name ?? '-',
                    ];
                }
            }
            return $this->exportToExcel($rows, $headers, "laporan_retur_pembelian_{$dateFrom}_{$dateTo}");
        }

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchase_returns', compact(
            'returns', 'summary', 'dateFrom', 'dateTo',
            'grandQty', 'grandTotal', 'suppliers', 'returnCount'
        ));
    }

    public function purchaseReturnsPrint(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = PurchaseReturn::with(['purchaseOrder', 'supplier', 'items.product', 'user']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $returns = $query->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo)
            ->orderBy('return_date', 'desc')
            ->get();

        $grandQty = 0;
        $grandTotal = 0;
        foreach ($returns as $ret) {
            $ret->load('items');
            foreach ($ret->items as $item) {
                $grandQty += $item->quantity;
                $grandTotal += $item->quantity * $item->unit_price;
            }
        }

        $suppliers = Supplier::orderBy('name')->get();
        $settings = CompanySetting::instance();

        return view('reports.purchase_returns_print', compact(
            'returns', 'dateFrom', 'dateTo',
            'grandQty', 'grandTotal', 'suppliers', 'settings'
        ));
    }

    public function purchaseReceivings(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = PurchaseReceiving::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'user'])
            ->whereDate('receiving_date', '>=', $dateFrom)
            ->whereDate('receiving_date', '<=', $dateTo);

        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseOrder', fn($q) => $q->where('supplier_id', $request->supplier_id));
        }

        $receivings = $query->orderBy('receiving_date', 'desc')->paginate(20);

        $grandQty = 0;
        $grandTotal = 0;
        foreach ($receivings as $rcv) {
            foreach ($rcv->items as $item) {
                $grandQty += $item->quantity;
                $grandTotal += $item->subtotal;
            }
        }

        if ($request->export === 'excel') {
            $allReceivings = PurchaseReceiving::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'user'])
                ->when($request->filled('supplier_id'), fn($q) => $q->whereHas('purchaseOrder', fn($sq) => $sq->where('supplier_id', $request->supplier_id)))
                ->whereDate('receiving_date', '>=', $dateFrom)->whereDate('receiving_date', '<=', $dateTo)
                ->orderBy('receiving_date', 'desc')->get();
            $headers = ['Tgl Terima', 'No. Terima', 'No. PO', 'Supplier', 'Barcode', 'Produk', 'Qty', 'Harga', 'Subtotal', 'Penerima'];
            $rows = [];
            foreach ($allReceivings as $rcv) {
                foreach ($rcv->items as $item) {
                    $rows[] = [
                        Carbon::parse($rcv->receiving_date)->format('d/m/Y'),
                        $rcv->code,
                        $rcv->purchaseOrder->code ?? '-',
                        $rcv->purchaseOrder->supplier->name ?? '-',
                        $item->product->barcode ?? '-',
                        $item->product->name ?? '-',
                        $item->quantity,
                        $item->unit_price,
                        $item->subtotal,
                        $rcv->user->name ?? '-',
                    ];
                }
            }
            return $this->exportToExcel($rows, $headers, "laporan_penerimaan_{$dateFrom}_{$dateTo}");
        }

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchase_receivings', compact(
            'receivings', 'dateFrom', 'dateTo', 'grandQty', 'grandTotal', 'suppliers'
        ));
    }

    public function purchaseReceivingsPrint(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query = PurchaseReceiving::with(['purchaseOrder.supplier', 'warehouse', 'items.product', 'user'])
            ->whereDate('receiving_date', '>=', $dateFrom)
            ->whereDate('receiving_date', '<=', $dateTo);

        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseOrder', fn($q) => $q->where('supplier_id', $request->supplier_id));
        }

        $receivings = $query->orderBy('receiving_date', 'desc')->get();

        $grandQty = 0;
        $grandTotal = 0;
        foreach ($receivings as $rcv) {
            foreach ($rcv->items as $item) {
                $grandQty += $item->quantity;
                $grandTotal += $item->subtotal;
            }
        }

        $suppliers = Supplier::orderBy('name')->get();
        $settings = CompanySetting::instance();

        return view('reports.purchase_receivings_print', compact(
            'receivings', 'dateFrom', 'dateTo', 'grandQty', 'grandTotal', 'suppliers', 'settings'
        ));
    }

    public function productMargin(Request $request)
    {
        $categoryId = $request->category_id;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $query = Product::with(['category', 'unit'])->where('is_active', true);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->paginate(20);

        foreach ($products as $product) {
            $product->margin_amount = ($product->selling_price ?? 0) - ($product->cost_price ?? 0);
            $product->margin_percent = $product->cost_price > 0
                ? round(($product->margin_amount / $product->cost_price) * 100, 2)
                : 0;
        }

        $categories = Category::orderBy('name')->get();

        if ($request->export === 'excel') {
            $allProducts = Product::with(['category', 'unit'])->where('is_active', true)
                ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                ->orderBy('name')->get();
            $headers = ['Kode', 'Nama Produk', 'Kategori', 'Harga Modal', 'Harga Jual', 'Harga Member', 'Margin (Rp)', 'Margin (%)'];
            $rows = [];
            foreach ($allProducts as $p) {
                $marginAmount = ($p->selling_price ?? 0) - ($p->cost_price ?? 0);
                $marginPercent = $p->cost_price > 0 ? round(($marginAmount / $p->cost_price) * 100, 2) : 0;
                $rows[] = [
                    $p->code,
                    $p->name,
                    $p->category->name ?? '-',
                    $p->cost_price ?? 0,
                    $p->selling_price ?? 0,
                    $p->member_price ?? 0,
                    $marginAmount,
                    $marginPercent . '%',
                ];
            }
            return $this->exportToExcel($rows, $headers, "laporan_margin_produk");
        }

        if ($request->export === 'csv') {
            $allProducts = Product::with(['category', 'unit'])->where('is_active', true)
                ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
                ->orderBy('name')->get();
            $filename = "laporan_margin_produk.csv";
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($allProducts) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Kode', 'Nama Produk', 'Kategori', 'Harga Modal', 'Harga Jual', 'Harga Member', 'Margin (Rp)', 'Margin (%)']);
                foreach ($allProducts as $p) {
                    $marginAmount = ($p->selling_price ?? 0) - ($p->cost_price ?? 0);
                    $marginPercent = $p->cost_price > 0 ? round(($marginAmount / $p->cost_price) * 100, 2) : 0;
                    fputcsv($file, [
                        $p->code, $p->name, $p->category->name ?? '-',
                        $p->cost_price ?? 0, $p->selling_price ?? 0, $p->member_price ?? 0,
                        $marginAmount, $marginPercent . '%',
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return view('reports.product-margin', compact('products', 'categories', 'dateFrom', 'dateTo'));
    }

    public function ppn(Request $request)
    {
        $month = $request->month ?? Carbon::now()->format('Y-m');

        $dateFrom = Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        $dateTo = Carbon::parse($month)->endOfMonth()->format('Y-m-d');

        $query = Sale::with(['member', 'user'])
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $sales = $query->orderBy('created_at')->get();

        $totalSubtotal = $sales->sum('subtotal');
        $totalTax = $sales->sum('tax_amount');
        $totalTransactions = $sales->count();

        if ($request->export === 'excel') {
            $headers = ['Tanggal', 'No Faktur', 'Member', 'Subtotal', 'PPN', 'Total'];
            $rows = [];
            foreach ($sales as $s) {
                $rows[] = [
                    $s->created_at->format('d/m/Y H:i'),
                    $s->code,
                    $s->member ? $s->member->name : '-',
                    $s->subtotal,
                    $s->tax_amount,
                    $s->total,
                ];
            }
            $rows[] = ['TOTAL', '', '', $totalSubtotal, $totalTax, $sales->sum('total')];
            return $this->exportToExcel($rows, $headers, "laporan_ppn_{$month}");
        }

        if ($request->export === 'csv') {
            $filename = "laporan_ppn_{$month}.csv";
            $responseHeaders = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($sales, $totalSubtotal, $totalTax) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Tanggal', 'No Faktur', 'Member', 'Subtotal', 'PPN', 'Total']);
                foreach ($sales as $s) {
                    fputcsv($file, [
                        $s->created_at->format('d/m/Y H:i'),
                        $s->code,
                        $s->member ? $s->member->name : '-',
                        $s->subtotal,
                        $s->tax_amount,
                        $s->total,
                    ]);
                }
                fputcsv($file, ['TOTAL', '', '', $totalSubtotal, $totalTax, $sales->sum('total')]);
                fclose($file);
            };
            return response()->stream($callback, 200, $responseHeaders);
        }

        return view('reports.ppn', compact('sales', 'totalSubtotal', 'totalTax', 'totalTransactions', 'month'));
    }

    private function exportToExcel(array $data, array $headers, string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // Data
        $rowNum = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($col . $rowNum, $value);
                $col++;
            }
            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', $col) as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = $filename . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
