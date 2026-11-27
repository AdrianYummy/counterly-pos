<x-layouts.app title="Products & Inventory">
    <div class="page-actions">
        <form class="search-form" method="GET">
            <label class="sr-only" for="product-search">Search products</label>
            <input id="product-search" name="search" value="{{ $search }}" placeholder="Search product name" list="product-suggestions" autocomplete="off">
            <datalist id="product-suggestions">@foreach($searchSuggestions as $suggestion)<option value="{{ $suggestion }}">@endforeach</datalist>
            <button class="button secondary" type="submit">Search</button>
            @if($search)<a class="text-link" href="{{ route('products.index') }}">Clear</a>@endif
        </form>
        @if(auth()->user()->isAdmin())<a class="button primary" href="{{ route('products.create') }}"><x-icon name="plus"/>Add Product</a>@endif
    </div>
    <section class="panel">
        <div class="panel-head"><div><p class="eyebrow">Inventory</p><h2>Product records</h2></div><span class="result-count">{{ $products->total() }} results</span></div>
        @if($products->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="box"/></div><h2>{{ $search ? 'No products match your search' : 'No products yet' }}</h2><p>{{ $search ? 'Try a suggested or shorter product name.' : 'Add the first product to begin tracking stock.' }}</p>@if(!$search && auth()->user()->isAdmin())<a class="button primary" href="{{ route('products.create') }}">Add Product</a>@endif</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Product</th><th>Price</th><th>Available stock</th><th>Status</th><th>Created</th><th class="actions-cell">Actions</th></tr></thead><tbody>
            @foreach($products as $product)
                <tr><td><div class="media-cell"><img src="{{ $product->image ? route('media.show', ['path' => $product->image]) : asset('images/product-placeholder.svg') }}" alt=""><strong>{{ $product->name }}</strong></div></td><td><strong>₱{{ number_format((float) $product->price, 2) }}</strong></td><td>{{ number_format($product->stock_quantity) }}</td><td><span class="badge {{ $product->stock_quantity === 0 ? 'danger' : ($product->stock_quantity <= config('counterly.low_stock_threshold') ? 'warning' : 'success') }}">{{ $product->stock_quantity === 0 ? 'Out of stock' : ($product->stock_quantity <= config('counterly.low_stock_threshold') ? 'Low stock' : 'In stock') }}</span></td><td>{{ $product->created_at->timezone(config('counterly.display_timezone'))->format('M j, Y') }}</td><td class="actions-cell"><a class="icon-action view" href="{{ route('products.show', $product) }}" aria-label="View {{ $product->name }}" title="View"><x-icon name="eye"/></a>@if(auth()->user()->isAdmin())<a class="icon-action edit" href="{{ route('products.edit', $product) }}" aria-label="Edit {{ $product->name }}" title="Edit"><x-icon name="edit"/></a><form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="Delete {{ $product->name }}? This cannot be undone.">@csrf @method('DELETE')<button class="icon-action delete" type="submit" aria-label="Delete {{ $product->name }}" title="Delete"><x-icon name="trash"/></button></form>@endif</td></tr>
            @endforeach
            </tbody></table></div><x-pagination :paginator="$products" label="products"/>
        @endif
    </section>
</x-layouts.app>
