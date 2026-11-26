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
        $start = CarbonImmutable::now($timezone)->startOfDay()->utc();
        $end = $start->addDay();

        return view('dashboard', [
            'productCount' => Product::count(),
            'customerCount' => Customer::count(),
            'staffCount' => User::count(),
            'todaySales' => Sale::where('created_at', '>=', $start)->where('created_at', '<', $end)->sum('total_price'),
            'todayTransactionCount' => Sale::where('created_at', '>=', $start)->where('created_at', '<', $end)->count(),
            'lowStockCount' => Product::where('stock_quantity', '<=', config('counterly.low_stock_threshold'))->count(),
            'recentSales' => Sale::with(['product', 'customer', 'staff'])->latest('created_at')->latest('id')->limit(5)->get(),
            'lowStockProducts' => Product::where('stock_quantity', '<=', config('counterly.low_stock_threshold'))->orderBy('stock_quantity')->limit(5)->get(),
            'recentEdits' => ActivityLog::with('actor')->latest('created_at')->latest('id')->paginate(10, ['*'], 'activity_page')->withQueryString(),
        ]);
    }
}
