<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($data['search'] ?? '');
        $customers = Customer::query()->when($search, fn ($query) => $query
            ->where(fn ($inner) => $inner->where('full_name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->orderBy('id')->paginate(10)->withQueryString();
        $searchSuggestions = Customer::query()->orderBy('full_name')->limit(100)->get(['full_name', 'email'])
            ->flatMap(fn (Customer $customer) => [$customer->full_name, $customer->email])->unique()->values();

        return view('customers.index', compact('customers', 'search', 'searchSuggestions'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = DB::transaction(function () use ($request) {
            $customer = Customer::create($request->validated());
            ActivityLog::recordCreation($request->user(), 'customer', $customer->public_id, $customer->full_name, $customer->only(['full_name', 'email', 'phone']));

            return $customer;
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $sales = $customer->sales()->with(['product', 'staff'])->latest('created_at')->paginate(5);

        return view('customers.show', compact('customer', 'sales'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $before = $customer->only(['full_name', 'email', 'phone']);

        DB::transaction(function () use ($request, $customer, $before) {
            $customer->update($request->validated());
            $customer->refresh();

            ActivityLog::recordEdit(
                $request->user(),
                'customer',
                $customer->public_id,
                $customer->full_name,
                $before,
                $customer->only(['full_name', 'email', 'phone']),
            );
        });

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            return back()->with('error', 'This customer is used in sales history and cannot be deleted.');
        }
        try {
            DB::transaction(function () use ($request, $customer) {
                $publicId = $customer->public_id;
                $name = $customer->full_name;
                $customer->delete();
                ActivityLog::recordDeletion($request->user(), 'customer', $publicId, $name);
            });
        } catch (QueryException) {
            return back()->with('error', 'This customer became referenced by a sale and cannot be deleted.');
        }

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
