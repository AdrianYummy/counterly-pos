<x-layouts.app title="Point of Sale">
    @if($products->isEmpty())
        <section class="panel empty-state"><div class="empty-icon"><x-icon name="box"/></div><h2>No products available</h2><p>An administrator must add or restock a product before checkout can begin.</p>@if(auth()->user()->isAdmin())<a class="button primary" href="{{ route('products.create') }}"><x-icon name="plus"/>Add Product</a>@endif</section>
    @else
        <form method="POST" action="{{ route('sales.store') }}" class="pos-layout" data-sale-form>
            @csrf
            <div class="pos-mobile-tabs" aria-label="Point of sale sections"><button class="active" type="button" data-pos-view="products" aria-pressed="true">Products</button><button type="button" data-pos-view="checkout" aria-pressed="false">Sale details · <span data-mobile-total>₱0.00</span></button></div>
            <section class="pos-catalog" aria-labelledby="product-catalog-title">
                <div class="workspace-heading"><div><p class="eyebrow">Product selection</p><h2 id="product-catalog-title">Choose a product</h2><p>One product type is recorded per transaction.</p></div><span class="result-count" data-result-count>{{ $products->count() }} available</span></div>
                <label class="catalog-search" for="pos-product-search"><x-icon name="search"/><span class="sr-only">Search products</span><input id="pos-product-search" type="search" placeholder="Search products by name" list="pos-product-suggestions" autocomplete="off" data-product-search></label>
                <datalist id="pos-product-suggestions">@foreach($products as $product)<option value="{{ $product->name }}">@endforeach</datalist>
                @error('product_id')<span class="field-error block-error">{{ $message }}</span>@enderror
                <div class="product-grid" data-product-grid>
                    @foreach($products as $product)
                        @php($selected = (string) old('product_id', request('product_id')) === (string) $product->public_id)
                        <label class="product-tile {{ $selected ? 'selected' : '' }}" data-product-card data-search-value="{{ mb_strtolower($product->name) }}">
                            <input class="sr-only" type="radio" name="product_id" value="{{ $product->public_id }}" data-product-option data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}" @checked($selected) required>
                            <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}" alt="">
                            <span class="product-tile-body"><strong>{{ $product->name }}</strong><span class="product-meta"><b>₱{{ number_format((float) $product->price, 2) }}</b><small>{{ number_format($product->stock_quantity) }} in stock</small></span></span>
                            <span class="selection-mark" aria-hidden="true">✓</span>
                        </label>
                    @endforeach
                </div>
                <div class="empty-state compact catalog-empty" data-catalog-empty hidden><h3>No products match your search</h3><p>Try a shorter or different product name.</p></div>
            </section>

            <aside class="checkout-panel" aria-labelledby="transaction-title">
                <div class="checkout-head"><div><p class="eyebrow">Current transaction</p><h2 id="transaction-title">Sale details</h2></div><span class="badge success">Ready</span></div>
                <div class="selected-product-empty" data-selected-empty><div class="empty-icon"><x-icon name="cart"/></div><strong>No product selected</strong><p>Choose a product from the list to begin.</p></div>
                <div class="selected-product" data-selected-item hidden><img data-selected-image src="{{ asset('images/product-placeholder.svg') }}" alt=""><div><span>Selected product</span><strong data-selected-name>—</strong><small><span data-unit-price>₱0.00</span> per unit</small></div></div>

                <div class="checkout-fields">
                    <label class="field">Customer <span class="hint">Optional</span><select name="customer_id" data-customer-select><option value="">Walk-in Customer</option>@foreach($customers as $customer)<option value="{{ $customer->public_id }}" @selected((string) old('customer_id') === (string) $customer->public_id)>{{ $customer->full_name }} · {{ $customer->email }}</option>@endforeach</select>@error('customer_id')<span class="field-error">{{ $message }}</span>@enderror</label>
                    <label class="field">Quantity<input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="2147483647" step="1" inputmode="numeric" required data-quantity>@error('quantity')<span class="field-error">{{ $message }}</span>@enderror</label>
                    <label class="check-field"><input type="checkbox" name="email_receipt" value="1" @checked(old('email_receipt', '1')) data-email-receipt><span><strong>Email receipt to customer</strong><small>Select a named customer to enable this option.</small></span></label>
                </div>

                <div class="checkout-summary">
                    <div><span>Available stock</span><strong data-stock-preview>—</strong></div>
                    <div class="total-line"><span>Total due</span><strong data-total-preview>₱0.00</strong></div>
                </div>
                <p class="checkout-note">The server verifies stock and calculates the final amount before saving.</p>
                <button class="button primary checkout-button" type="submit" data-sale-submit disabled>Complete Sale</button>
            </aside>
        </form>
    @endif
</x-layouts.app>
