<x-layouts.app title="Edit Product">
    <section class="panel form-panel"><div class="panel-head"><div><p class="eyebrow">Inventory</p><h2>Edit {{ $product->name }}</h2><p class="muted">Stock changes are checked against sales recorded while this form is open.</p></div></div><form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('products._form', ['submitLabel' => 'Save changes'])</form></section>
</x-layouts.app>
