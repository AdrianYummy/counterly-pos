<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $timezone = config('counterly.display_timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $chartStart = $today->subDays(6);
        $chartEnd = $today->addDay();
        $chartSales = Sale::with('product:id,public_id,name')
            ->where('created_at', '>=', $chartStart->utc())
            ->where('created_at', '<', $chartEnd->utc())
            ->get(['id', 'public_id', 'transaction_id', 'product_id', 'quantity', 'total_price', 'created_at']);
        $salesByDate = $chartSales->groupBy(
            fn (Sale $sale) => $sale->created_at->timezone($timezone)->format('Y-m-d'),
        );
        $salesTrend = collect(range(0, 6))->map(function (int $offset) use ($chartStart, $salesByDate) {
            $date = $chartStart->addDays($offset);
            $sales = $salesByDate->get($date->format('Y-m-d'), collect());

            return [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'label' => $date->format('M j'),
                'revenue' => (float) $sales->sum('total_price'),
                'transactions' => $sales->map(fn (Sale $sale) => $sale->transaction_id ?: $sale->public_id)->unique()->count(),
                'units' => $sales->sum('quantity'),
            ];
        });
        $topProducts = $chartSales
            ->groupBy('product_id')
            ->map(fn ($sales) => [
                'name' => $sales->first()->product->name,
                'public_id' => $sales->first()->product->public_id,
                'units' => $sales->sum('quantity'),
                'revenue' => (float) $sales->sum('total_price'),
            ])
            ->sortByDesc('units')
            ->take(5)
            ->values();
        $todaySummary = $salesTrend->last();

        return view('dashboard', [
            'productCount' => Product::count(),
            'customerCount' => Customer::count(),
            'staffCount' => User::count(),
            'todaySales' => $todaySummary['revenue'],
            'todayTransactionCount' => $todaySummary['transactions'],
            'lowStockCount' => Product::where('stock_quantity', '<=', config('counterly.low_stock_threshold'))->count(),
            'recentSales' => Sale::with(['product', 'customer', 'staff'])->latest('created_at')->latest('id')->limit(5)->get(),
            'lowStockProducts' => Product::where('stock_quantity', '<=', config('counterly.low_stock_threshold'))->orderBy('stock_quantity')->limit(5)->get(),
            'recentEdits' => ActivityLog::with('actor')->latest('created_at')->latest('id')->paginate(10, ['*'], 'activity_page')->withQueryString(),
            'salesTrend' => $salesTrend,
            'salesTrendMax' => max(1, (float) $salesTrend->max('revenue')),
            'sevenDayRevenue' => $salesTrend->sum('revenue'),
            'sevenDayTransactions' => $salesTrend->sum('transactions'),
            'topProducts' => $topProducts,
            'topProductMax' => max(1, (int) $topProducts->max('units')),
        ]);
    }
}
