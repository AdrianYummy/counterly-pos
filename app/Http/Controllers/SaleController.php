<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $sales) {}

    public function index(Request $request): View
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'staff_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        $timezone = config('counterly.display_timezone');
        $sales = Sale::with(['product', 'customer', 'staff'])
            ->when($data['search'] ?? null, function ($query, $search) {
                $query->where(fn ($inner) => $inner
                    ->whereHas('product', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('customer', fn ($q) => $q->where('full_name', 'like', '%'.$search.'%'))
                    ->orWhereHas('staff', fn ($q) => $q->where('full_name', 'like', '%'.$search.'%')));
            })
            ->when($data['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id))
            ->when($data['staff_id'] ?? null, fn ($query, $id) => $query->where('sold_by', $id))
            ->when($data['date_from'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', CarbonImmutable::parse($date, $timezone)->startOfDay()->utc()))
            ->when($data['date_to'] ?? null, fn ($query, $date) => $query->where('created_at', '<', CarbonImmutable::parse($date, $timezone)->addDay()->startOfDay()->utc()))
            ->latest('created_at')->latest('id')->paginate(15)->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'filters' => $data,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'staffMembers' => User::orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function create(): View
    {
        return view('sales.create', [
            'products' => Product::where('stock_quantity', '>', 0)->orderBy('name')->get(),
            'customers' => Customer::orderBy('full_name')->get(),
        ]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $sale = $this->sales->record(
            (int) $request->validated('product_id'),
            $request->filled('customer_id') ? (int) $request->validated('customer_id') : null,
            (int) $request->validated('quantity'),
            $request->user(),
        );

        return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and stock updated.');
    }

    public function show(Sale $sale): View
    {
        $sale->load(['product', 'customer', 'staff']);

        return view('sales.show', compact('sale'));
    }
}
