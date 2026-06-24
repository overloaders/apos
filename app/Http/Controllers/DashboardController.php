<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Member;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $todaySalesTotal = Sale::whereDate('sale_date', $today)
            ->where('status', 'completed')
            ->sum('total');

        $todayTransactionCount = Sale::whereDate('sale_date', $today)
            ->where('status', 'completed')
            ->count();

        $lowStockCount = Product::active()->lowStock()->count();

        $lowStockProducts = Stock::with(['product.category', 'product.unit', 'warehouse'])
            ->whereHas('product', function ($q) {
                $q->where('is_active', true)->where('min_stock', '>', 0);
            })
            ->whereRaw('quantity <= (SELECT min_stock FROM products WHERE products.id = stocks.product_id)')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->get()
            ->unique('product_id')
            ->take(20);

        $totalMembers = Member::where('is_active', true)->count();

        $recentSales = Sale::with('member')
            ->whereDate('sale_date', $today)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $salesChartData = Sale::whereBetween('sale_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(sale_date) as sale_date'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $monthlySales = Sale::whereMonth('sale_date', $today->month)
            ->whereYear('sale_date', $today->year)
            ->where('status', 'completed')
            ->sum('total');

        return view('dashboard', compact(
            'todaySalesTotal',
            'todayTransactionCount',
            'lowStockCount',
            'lowStockProducts',
            'totalMembers',
            'recentSales',
            'salesChartData',
            'monthlySales'
        ));
    }
}
