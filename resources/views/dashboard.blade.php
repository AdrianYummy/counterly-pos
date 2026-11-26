<x-layouts.app title="Dashboard">
    <div class="page-actions">
        <div><p class="muted">A live overview of your store today.</p></div>
        <div class="button-row"><a class="button secondary" href="{{ route('products.create') }}">Add product</a><a class="button primary" href="{{ route('sales.create') }}">Record sale</a></div>
    </div>
    <section class="stat-grid" aria-label="Business summary">
        <article class="stat-card"><span>Products</span><strong>{{ number_format($productCount) }}</strong><a href="{{ route('products.index') }}">View inventory →</a></article>
        <article class="stat-card"><span>Customers</span><strong>{{ number_format($customerCount) }}</strong><a href="{{ route('customers.index') }}">View customers →</a></article>
        <article class="stat-card"><span>Staff</span><strong>{{ number_format($staffCount) }}</strong><a href="{{ route('staff.index') }}">View accounts →</a></article>
        <article class="stat-card accent"><span>Today's sales</span><strong>₱{{ number_format((float) $todaySales, 2) }}</strong><a href="{{ route('sales.index') }}">View history →</a></article>
    </section>
    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-head"><div><p class="eyebrow">Activity</p><h2>Recent sales</h2></div><a href="{{ route('sales.index') }}">View all</a></div>
            @if($recentSales->isEmpty())
                <div class="empty-state"><h3>No sales yet</h3><p>Record the first sale to see activity here.</p><a class="button primary" href="{{ route('sales.create') }}">Record sale</a></div>
            @else
                <div class="table-wrap"><table><thead><tr><th>Product</th><th>Customer</th><th>Qty</th><th>Total</th></tr></thead><tbody>
                @foreach($recentSales as $sale)<tr><td><a href="{{ route('sales.show', $sale) }}">{{ $sale->product->name }}</a><small>{{ $sale->created_at->timezone(config('counterly.display_timezone'))->format('M j, g:i A') }}</small></td><td>{{ $sale->customer?->full_name ?? 'Walk-in customer' }}</td><td>{{ $sale->quantity }}</td><td>₱{{ number_format((float) $sale->total_price, 2) }}</td></tr>@endforeach
                </tbody></table></div>
            @endif
        </section>
        <section class="panel">
            <div class="panel-head"><div><p class="eyebrow">Attention</p><h2>Low stock</h2></div></div>
            @if($lowStockProducts->isEmpty())<div class="empty-state compact"><h3>Stock looks healthy</h3><p>No products are at or below the alert threshold.</p></div>
            @else<ul class="stock-list">@foreach($lowStockProducts as $product)<li><div><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a><small>₱{{ number_format((float) $product->price, 2) }}</small></div><span class="badge {{ $product->stock_quantity === 0 ? 'danger' : 'warning' }}">{{ $product->stock_quantity }} left</span></li>@endforeach</ul>@endif
        </section>
    </div>
</x-layouts.app>
