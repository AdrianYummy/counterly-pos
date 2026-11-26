<x-layouts.app title="Add Product">
    <section class="panel form-panel"><div class="panel-head"><div><p class="eyebrow">Inventory</p><h2>New product</h2><p class="muted">Add pricing, available stock, and an optional display image.</p></div></div><form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">@csrf @include('products._form', ['submitLabel' => 'Create product'])</form></section>
</x-layouts.app>
