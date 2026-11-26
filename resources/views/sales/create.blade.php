<x-layouts.app title="Point of Sale">
    @if($products->isEmpty())
        <section class="panel empty-state">
            <div class="empty-icon"><x-icon name="box"/></div>
            <h2>No products available</h2>
            <p>An administrator must add or restock a product before checkout can begin.</p>
            @if(auth()->user()->isAdmin())
                <a class="button primary" href="{{ route('products.create') }}"><x-icon name="plus"/>Add Product</a>
            @endif
        </section>
    @else
        @php
            $oldItems = collect(old('items', []))->keyBy('product_id');
            $requestedProduct = (string) request('product_id');
        @endphp
        <form method="POST" action="{{ route('sales.store') }}" class="pos-layout" data-sale-form>
            @csrf
            <div class="pos-mobile-tabs" aria-label="Point of sale sections">
                <button class="active" type="button" data-pos-view="products" aria-pressed="true">Products</button>
                <button type="button" data-pos-view="checkout" aria-pressed="false">Cart (<span data-mobile-count>0</span>) · <span data-mobile-total>₱0.00</span></button>
            </div>

            <section class="pos-catalog" aria-labelledby="product-catalog-title">
                <div class="workspace-heading">
                    <div>
                        <p class="eyebrow">Product selection</p>
                        <h2 id="product-catalog-title">Build the order</h2>
                        <p>Select multiple products. Select a chosen product again to remove it.</p>
                    </div>
                    <span class="result-count" data-result-count>{{ $products->count() }} available</span>
                </div>
                <label class="catalog-search" for="pos-product-search">
                    <x-icon name="search"/>
                    <span class="sr-only">Search products</span>
                    <input id="pos-product-search" type="search" placeholder="Search products by name" list="pos-product-suggestions" autocomplete="off" data-product-search>
                </label>
                <datalist id="pos-product-suggestions">
                    @foreach($products as $product)<option value="{{ $product->name }}">@endforeach
                </datalist>
                @if($errors->has('items') || $errors->has('items.*'))
                    <span class="field-error block-error">{{ $errors->first('items') ?: $errors->first('items.*') }}</span>
                @endif
                <div class="product-grid" data-product-grid>
                    @foreach($products as $product)
                        @php
                            $oldItem = $oldItems->get((string) $product->public_id);
                            $initialQuantity = $oldItem['quantity'] ?? ($requestedProduct === (string) $product->public_id ? 1 : 0);
                        @endphp
                        <button
                            class="product-tile {{ $initialQuantity ? 'selected' : '' }}"
                            type="button"
                            data-product-card
                            data-product-id="{{ $product->public_id }}"
                            data-name="{{ $product->name }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock_quantity }}"
                            data-image="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}"
                            data-search-value="{{ mb_strtolower($product->name) }}"
                            data-initial-quantity="{{ $initialQuantity }}"
                            aria-pressed="{{ $initialQuantity ? 'true' : 'false' }}"
                        >
                            <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}" alt="">
                            <span class="product-tile-body">
                                <strong>{{ $product->name }}</strong>
                                <span class="product-meta"><b>₱{{ number_format((float) $product->price, 2) }}</b><small>{{ number_format($product->stock_quantity) }} in stock</small></span>
                            </span>
                            <span class="selection-mark" aria-hidden="true">✓</span>
                        </button>
                    @endforeach
                </div>
                <div class="empty-state compact catalog-empty" data-catalog-empty hidden>
                    <h3>No products match your search</h3>
                    <p>Try a shorter or different product name.</p>
                </div>
            </section>

            <aside class="checkout-panel" aria-labelledby="transaction-title">
                <div class="checkout-head">
                    <div><p class="eyebrow">Current transaction</p><h2 id="transaction-title">Order cart</h2></div>
                    <span class="badge success"><span data-cart-count>0</span>&nbsp;items</span>
                </div>

                <div class="selected-product-empty" data-cart-empty>
                    <div class="empty-icon"><x-icon name="cart"/></div>
                    <strong>Your cart is empty</strong>
                    <p>Select one or more products from the list.</p>
                </div>
                <div class="cart-items" data-cart-list></div>
                <template data-cart-item-template>
                    <article class="cart-item" data-cart-item>
                        <img data-cart-image src="{{ asset('images/product-placeholder.svg') }}" alt="">
                        <div class="cart-item-main">
                            <div class="cart-item-heading">
                                <div><strong data-cart-name></strong><small><span data-cart-unit-price></span> each · <span data-cart-stock></span> available</small></div>
                                <button class="cart-remove" type="button" data-cart-remove aria-label="Remove product" title="Remove from cart">×</button>
                            </div>
                            <div class="cart-item-controls">
                                <span>Quantity</span>
                                <div class="quantity-stepper">
                                    <button type="button" data-quantity-minus aria-label="Decrease quantity">−</button>
                                    <input type="number" min="1" step="1" inputmode="numeric" data-cart-quantity aria-label="Product quantity">
                                    <button type="button" data-quantity-plus aria-label="Increase quantity">+</button>
                                </div>
                                <strong data-cart-line-total></strong>
                            </div>
                            <input type="hidden" data-cart-product-input>
                        </div>
                    </article>
                </template>

                <div class="checkout-fields">
                    <label class="field">Customer <span class="hint">Optional</span>
                        <select name="customer_id" data-customer-select>
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->public_id }}" @selected((string) old('customer_id') === (string) $customer->public_id)>{{ $customer->full_name }} · {{ $customer->email }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="check-field">
                        <input type="checkbox" name="email_receipt" value="1" @checked(old('email_receipt', '1')) data-email-receipt>
                        <span><strong>Email receipt to customer</strong><small>Select a named customer to enable this option.</small></span>
                    </label>
                </div>

                <div class="checkout-summary">
                    <div><span>Selected products</span><strong data-product-count>0</strong></div>
                    <div><span>Total units</span><strong data-unit-count>0</strong></div>
                    <div class="total-line"><span>Total due</span><strong data-total-preview>₱0.00</strong></div>
                </div>
                <p class="checkout-note">Stock and totals are verified again by the server. If any item is unavailable, the entire checkout is safely cancelled.</p>
                <button class="button primary checkout-button" type="submit" data-sale-submit disabled>Complete Sale</button>
            </aside>
        </form>
    @endif
</x-layouts.app>
