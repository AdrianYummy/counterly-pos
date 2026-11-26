<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($data['search'] ?? '');
        $products = Product::query()
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('id')->paginate(10)->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'price', 'stock_quantity']);
        if ($request->hasFile('image')) {
            $data['image'] = $this->images->store($request->file('image'), 'products');
        }
        $product = Product::create($data);

        return redirect()->route('products.show', $product)->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->loadCount('sales');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $newImage = $request->hasFile('image') ? $this->images->store($request->file('image'), 'products') : null;
        $oldImage = $product->image;

        try {
            DB::transaction(function () use ($request, $product, $newImage) {
                /** @var Product $locked */
                $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
                if ($locked->stock_quantity !== (int) $request->validated('original_stock')) {
                    throw ValidationException::withMessages([
                        'stock_quantity' => 'Stock changed while this form was open. Reload the page before changing stock.',
                    ]);
                }
                $data = $request->safe()->only(['name', 'price', 'stock_quantity']);
                if ($newImage) {
                    $data['image'] = $newImage;
                }
                $locked->update($data);
            });
        } catch (\Throwable $exception) {
            if ($newImage) {
                $this->images->delete($newImage);
            }
            throw $exception;
        }

        if ($newImage) {
            $this->images->delete($oldImage);
        }

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->sales()->exists()) {
            return back()->with('error', 'This product is used in sales history and cannot be deleted.');
        }
        try {
            $path = $product->image;
            $product->delete();
            $this->images->delete($path);
        } catch (QueryException) {
            return back()->with('error', 'This product became referenced by a sale and cannot be deleted.');
        }

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
