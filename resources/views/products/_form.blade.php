<div class="form-grid">
    <div class="media-editor span-2">
        <div class="image-preview product-upload-preview"><img id="product-preview" src="{{ isset($product) && $product->image ? asset('storage/'.$product->image) : asset('images/product-placeholder.svg') }}" alt="Product image preview"></div>
        <div class="media-editor-body">
            <p class="eyebrow">Product photo</p>
            <h3>{{ isset($product) ? 'Update the product image' : 'Add a product image' }}</h3>
            <p>A clear, square photo makes products easier to recognize at checkout.</p>
            <label class="field upload-field">Choose image <span class="hint">Optional JPG, PNG, or WebP up to 4 MB</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input data-preview-target="product-preview">@error('image')<span class="field-error">{{ $message }}</span>@enderror</label>
        </div>
    </div>
    <label class="field span-2">Product name<input name="name" value="{{ old('name', $product->name ?? '') }}" maxlength="100" required>@error('name')<span class="field-error">{{ $message }}</span>@enderror</label>
    <label class="field">Price (PHP)<input type="number" name="price" value="{{ old('price', $product->price ?? '') }}" min="0.01" max="99999999.99" step="0.01" required>@error('price')<span class="field-error">{{ $message }}</span>@enderror</label>
    <label class="field">Stock quantity<input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" min="0" max="2147483647" step="1" required>@error('stock_quantity')<span class="field-error">{{ $message }}</span>@enderror</label>
    @isset($product)<input type="hidden" name="original_stock" value="{{ $product->stock_quantity }}">@endisset
</div>
<div class="form-actions"><a class="button secondary" href="{{ isset($product) ? route('products.show', $product) : route('products.index') }}">Cancel</a><button class="button primary" type="submit">{{ $submitLabel }}</button></div>
