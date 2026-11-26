<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\SaleReceiptNotification;
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
            'product_id' => ['nullable', 'uuid', 'exists:products,public_id'],
            'staff_id' => ['nullable', 'uuid', 'exists:users,public_id'],
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
            ->when($data['product_id'] ?? null, fn ($query, $publicId) => $query->whereHas('product', fn ($product) => $product->where('public_id', $publicId)))
            ->when($data['staff_id'] ?? null, fn ($query, $publicId) => $query->whereHas('staff', fn ($staff) => $staff->where('public_id', $publicId)))
            ->when($data['date_from'] ?? null, fn ($query, $date) => $query->where('created_at', '>=', CarbonImmutable::parse($date, $timezone)->startOfDay()->utc()))
            ->when($data['date_to'] ?? null, fn ($query, $date) => $query->where('created_at', '<', CarbonImmutable::parse($date, $timezone)->addDay()->startOfDay()->utc()))
            ->latest('created_at')->latest('id')->paginate(10)->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'filters' => $data,
            'products' => Product::orderBy('name')->get(['id', 'public_id', 'name']),
            'staffMembers' => User::orderBy('full_name')->get(['id', 'public_id', 'full_name']),
            'searchSuggestions' => collect()
                ->merge(Product::orderBy('name')->limit(50)->pluck('name'))
                ->merge(Customer::orderBy('full_name')->limit(50)->pluck('full_name'))
                ->merge(User::orderBy('full_name')->limit(50)->pluck('full_name'))
                ->unique()->values(),
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
            Product::where('public_id', $request->validated('product_id'))->firstOrFail()->id,
            $request->filled('customer_id') ? Customer::where('public_id', $request->validated('customer_id'))->firstOrFail()->id : null,
            (int) $request->validated('quantity'),
            $request->user(),
        );

        $sale->load(['customer', 'product', 'staff']);
        $emailNotice = null;
        if ($request->boolean('email_receipt') && $sale->customer) {
            try {
                $sale->customer->notify(new SaleReceiptNotification($sale));
                $emailNotice = ' A receipt was emailed to '.$sale->customer->email.'.';
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->route('sales.show', $sale)
                    ->with('success', 'Sale recorded and stock updated.')
                    ->with('warning', 'The sale is safe, but the receipt email could not be sent. Check the mail settings, then use Email Receipt on this page.');
            }
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Sale recorded and stock updated.'.$emailNotice);
    }

    public function show(Sale $sale): View
    {
        $sale->load(['product', 'customer', 'staff']);

        return view('sales.show', compact('sale'));
    }

    public function emailReceipt(Sale $sale): RedirectResponse
    {
        $sale->load(['customer', 'product', 'staff']);
        if (! $sale->customer) {
            return back()->with('error', 'A walk-in transaction has no customer email address.');
        }

        try {
            $sale->customer->notify(new SaleReceiptNotification($sale));
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'The receipt email could not be sent. Check the mail configuration and try again.');
        }

        return back()->with('success', 'Receipt emailed to '.$sale->customer->email.'.');
    }
}
